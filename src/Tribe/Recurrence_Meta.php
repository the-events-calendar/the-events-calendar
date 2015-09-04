<?php

/**
 * Tribe__Events__Pro__Recurrence_Meta
 *
 * WordPress hooks and filters controlling event recurrence
 */
class Tribe__Events__Pro__Recurrence_Meta {
	const UPDATE_TYPE_ALL = 1;
	const UPDATE_TYPE_FUTURE = 2;
	const UPDATE_TYPE_SINGLE = 3;

	/** @var Tribe__Events__Pro__Recurrence_Scheduler */
	public static $scheduler = null;


	public static function init() {
		add_action( 'tribe_events_update_meta', array(
			__CLASS__,
			'updateRecurrenceMeta',
		), 20, 2 ); // give other meta a chance to save, first
		add_action( 'tribe_events_date_display', array( __CLASS__, 'loadRecurrenceData' ) );
		add_action( 'wp_trash_post', array( __CLASS__, 'handle_trash_request' ) );
		add_action( 'before_delete_post', array( __CLASS__, 'handle_delete_request' ) );
		add_action( 'untrashed_post', array( __CLASS__, 'handle_untrash_request' ) );
		add_filter( 'get_edit_post_link', array( __CLASS__, 'filter_edit_post_link' ), 10, 3 );

		add_filter( 'preprocess_comment', array( __CLASS__, 'set_parent_for_recurring_event_comments' ), 10, 1 );
		add_action( 'pre_get_comments', array( __CLASS__, 'set_post_id_for_recurring_event_comment_queries' ), 10, 1 );
		add_action( 'comment_post_redirect', array( __CLASS__, 'fix_redirect_after_comment_is_posted' ), 10, 2 );
		add_action( 'wp_update_comment_count', array( __CLASS__, 'update_comment_counts_on_child_events' ), 10, 3 );
		add_filter( 'comments_array', array( __CLASS__, 'set_comments_array_on_child_events' ), 10, 2 );

		add_action( 'admin_notices', array( __CLASS__, 'showRecurrenceErrorFlash' ) );
		add_action( 'tribe_recurring_event_error', array( __CLASS__, 'setupRecurrenceErrorMsg' ), 10, 2 );

		add_filter( 'manage_' . Tribe__Events__Main::POSTTYPE . '_posts_columns', array(
			__CLASS__,
			'list_table_column_headers',
		) );
		add_action( 'manage_' . Tribe__Events__Main::POSTTYPE . '_posts_custom_column', array(
			__CLASS__,
			'populate_custom_list_table_columns',
		), 10, 2 );
		add_filter( 'post_class', array( __CLASS__, 'add_recurring_event_post_classes' ), 10, 3 );


		add_filter( 'post_row_actions', array( __CLASS__, 'edit_post_row_actions' ), 10, 2 );
		add_action( 'admin_action_tribe_split', array( __CLASS__, 'handle_split_request' ), 10, 1 );
		add_action( 'wp_before_admin_bar_render', array( __CLASS__, 'admin_bar_render' ) );

		add_filter( 'posts_request', array( __CLASS__, 'recurrence_collapse_sql' ), 10, 2 );

		add_filter( 'tribe_settings_tab_fields', array( __CLASS__, 'inject_settings' ), 10, 2 );

		add_action( 'load-edit.php', array( __CLASS__, 'combineRecurringRequestIds' ) );

		add_action( 'load-post.php', array( __CLASS__, 'enqueue_post_editor_notices' ), 10, 1 );

		add_action( 'updated_post_meta', array( __CLASS__, 'update_child_thumbnails' ), 4, 40 );
		add_action( 'added_post_meta', array( __CLASS__, 'update_child_thumbnails' ), 4, 40 );
		add_action( 'deleted_post_meta', array( __CLASS__, 'remove_child_thumbnails' ), 4, 40 );

		add_action( 'tribe_community_events_enqueue_resources', array( __CLASS__, 'enqueue_recurrence_data' ) );
		add_action( 'tribe_events_community_form_before_template', array( __CLASS__, 'output_recurrence_json_data' ) );

		if ( is_admin() ) {
			add_filter( 'tribe_events_pro_localize_script', array( __CLASS__, 'localize_scripts' ), 10, 3 );
		}

		self::reset_scheduler();
	}

	public static function filter_edit_post_link( $url, $post_id, $context ) {
		if ( ! empty( $post_id ) && tribe_is_recurring_event( $post_id ) && $parent = wp_get_post_parent_id( $post_id ) ) {
			return get_edit_post_link( $parent, $context );
		}

		return $url;
	}

	/**
	 * Change the link for a recurring event to edit its series
	 * @return void
	 */
	public static function admin_bar_render() {
		/** @var WP_Admin_Bar $wp_admin_bar */
		global $post, $wp_admin_bar;

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		if ( is_admin() || ! tribe_is_recurring_event( $post ) ) {
			return;
		}
		if ( get_query_var( 'eventDisplay' ) == 'all' ) {
			return;
		}
		$menu_parent = $wp_admin_bar->get_node( 'edit' );
		if ( ! $menu_parent ) {
			return;
		}
		if ( current_user_can( 'edit_post', $post->ID ) ) {
			$wp_admin_bar->add_node( array(
				'id'     => 'edit-series',
				'title'  => __( 'Edit Series', 'tribe-events-calendar-pro' ),
				'parent' => 'edit',
				'href'   => $menu_parent->href,
			) );
			$wp_admin_bar->add_node( array(
				'id'     => 'split-single',
				'title'  => __( 'Break from Series', 'tribe-events-calendar-pro' ),
				'parent' => 'edit',
				'href'   => esc_url( wp_nonce_url( self::get_split_series_url( $post->ID, false, false ), 'tribe_split_' . $post->ID ) ),
				'meta'   => array(
					'class' => 'tribe-split-single',
				),
			) );
			if ( ! empty( $post->post_parent ) ) {
				$wp_admin_bar->add_node( array(
					'id'     => 'split-series',
					'title'  => __( 'Edit Future Events', 'tribe-events-calendar-pro' ),
					'parent' => 'edit',
					'href'   => esc_url( wp_nonce_url( self::get_split_series_url( $post->ID, false, true ), 'tribe_split_' . $post->ID ) ),
					'meta'   => array(
						'class' => 'tribe-split-all',
					),
				) );
			}
		}
	}

	public static function list_table_column_headers( $columns ) {
		$columns['recurring'] = __( 'Recurring', 'tribe-events-calendar-pro' );

		return $columns;
	}

	public static function populate_custom_list_table_columns( $column, $post_id ) {
		if ( $column == 'recurring' ) {
			if ( tribe_is_recurring_event( $post_id ) ) {
				echo esc_html__( 'Yes', 'tribe-events-calendar-pro' );
			} else {
				echo esc_html__( '—', 'tribe-events-calendar-pro' );
			}
		}
	}

	public static function add_recurring_event_post_classes( $classes, $class, $post_id ) {
		if ( get_post_type( $post_id ) == Tribe__Events__Main::POSTTYPE && tribe_is_recurring_event( $post_id ) ) {
			$classes[] = 'tribe-recurring-event';

			$post = get_post( $post_id );
			if ( empty( $post->post_parent ) ) {
				$classes[] = 'tribe-recurring-event-parent';
			} else {
				$classes[] = 'tribe-recurring-event-child';
			}
		}

		return $classes;
	}

	public static function edit_post_row_actions( $actions, $post ) {
		if ( tribe_is_recurring_event( $post ) ) {
			unset( $actions['inline hide-if-no-js'] );
			$post_type_object   = get_post_type_object( Tribe__Events__Main::POSTTYPE );
			$is_first_in_series = empty( $post->post_parent );
			$first_id_in_series = $post->post_parent ? $post->post_parent : $post->ID;
			if ( isset( $actions['edit'] ) && 'trash' != $post->post_status ) {
				if ( current_user_can( 'edit_post', $post->ID ) ) {
					$split_actions          = array();
					$split_actions['split'] = sprintf( '<a href="%s" class="tribe-split tribe-split-single" title="%s">%s</a>', esc_url( wp_nonce_url( self::get_split_series_url( $post->ID, false, false ), 'tribe_split_' . $post->ID ) ), esc_attr( __( 'Break this event out of its series and edit it independently', 'tribe-events-calendar-pro' ) ), __( 'Edit Single', 'tribe-events-calendar-pro' ) );
					if ( ! $is_first_in_series ) {
						$split_actions['split_all'] = sprintf( '<a href="%s" class="tribe-split tribe-split-all" title="%s">%s</a>', esc_url( wp_nonce_url( self::get_split_series_url( $post->ID, false, true ), 'tribe_split_' . $post->ID ) ), esc_attr( __( 'Split the series in two at this point, creating a new series out of this and all subsequent events', 'tribe-events-calendar-pro' ) ), __( 'Edit Upcoming', 'tribe-events-calendar-pro' ) );
					}
					$actions = Tribe__Events__Main::array_insert_after_key( 'edit', $actions, $split_actions );
				}
				if ( current_user_can( 'edit_post', $first_id_in_series ) ) {
					$edit_series_url = get_edit_post_link( $first_id_in_series, 'display' );
					$actions['edit'] = sprintf( '<a href="%s" title="%s">%s</a>', esc_url( $edit_series_url ), esc_attr( __( 'Edit all events in this series', 'tribe-events-calendar-pro' ) ), __( 'Edit All', 'tribe-events-calendar-pro' ) );
				}
			}
			if ( $is_first_in_series ) {
				if ( ! empty( $actions['trash'] ) ) {
					$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move all events in this series to the Trash', 'tribe-events-calendar-pro' ) ) . "' href='" . esc_url( get_delete_post_link( $post->ID ) ) . "'>" . esc_html__( 'Trash Series', 'tribe-events-calendar-pro' ) . '</a>';
				}
				if ( ! empty( $actions['delete'] ) ) {
					$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete all events in this series permanently', 'tribe-events-calendar-pro' ) ) . "' href='" . esc_url( get_delete_post_link( $post->ID, '', true ) ) . "'>" . esc_html__( 'Delete Series Permanently', 'tribe-events-calendar-pro' ) . '</a>';
				}
			}
			if ( ! empty( $actions['untrash'] ) ) { // if the whole series is in the trash, restore the whole series together
				$first_event = get_post( $first_id_in_series );
				if ( isset( $first_event->post_status ) && $first_event->post_status == 'trash' ) {
					$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore all events in this series from the Trash', 'tribe-events-calendar-pro' ) ) . "' href='" . esc_url( wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $first_id_in_series ) ), 'untrash-post_' . $first_id_in_series ) ) . "'>" . esc_html__( 'Restore Series', 'tribe-events-calendar-pro' ) . '</a>';
				}
			}
		}

		return $actions;
	}

	private static function get_split_series_url( $id, $context = 'display', $all = false ) {
		if ( ! $post = get_post( $id ) ) {
			return;
		}

		if ( 'revision' === $post->post_type ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if ( ! $post_type_object ) {
			return;
		}

		$args = array( 'action' => 'tribe_split' );
		if ( $all ) {
			$args['split_all'] = 1;
		}
		$url = admin_url( sprintf( $post_type_object->_edit_link, $post->ID ) );
		$url = add_query_arg( $args, $url );
		if ( $context == 'display' ) {
			$url = esc_url( $url );
		}

		return apply_filters( 'tribe_events_get_split_series_link', $url, $post->ID, $context, $all );
	}

	public static function handle_split_request() {
		// TODO: would be nice to have a way to add it back into the series (i.e., an undo)
		$post_id = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : 0;
		check_admin_referer( 'tribe_split_' . $post_id );
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( 'You do not have sufficient capabilities to edit this event', 'tribe-events-calendar-pro' );
			exit();
		}

		$splitter = new Tribe__Events__Pro__Recurrence_Series_Splitter();

		if ( ! empty( $_REQUEST['split_all'] ) ) {
			$splitter->break_remaining_events_from_series( $post_id );
		} else {
			$splitter->break_single_event_from_series( $post_id );
		}

		// TODO: show a message?

		$edit_url = get_edit_post_link( $post_id, false );
		wp_redirect( $edit_url );
		exit();
	}

	public static function handle_trash_request( $post_id ) {
		if ( tribe_is_recurring_event( $post_id ) && ! wp_get_post_parent_id( $post_id ) ) {
			self::trash_all_children( $post_id );
		}
	}

	private static function trash_all_children( $post_id ) {
		$children = self::get_child_event_ids( $post_id );
		foreach ( $children as $child_id ) {
			wp_trash_post( $child_id );
		}
	}

	public static function handle_untrash_request( $post_id ) {
		if ( tribe_is_recurring_event( $post_id ) && ! wp_get_post_parent_id( $post_id ) ) {
			self::untrash_all_children( $post_id );
		}
	}

	private static function untrash_all_children( $post_id ) {
		$children = self::get_child_event_ids( $post_id, array( 'post_status' => 'trash' ) );
		foreach ( $children as $child_id ) {
			wp_untrash_post( $child_id );
		}
	}

	public static function handle_delete_request( $post_id ) {
		if ( tribe_is_recurring_event( $post_id ) ) {
			$parent = wp_get_post_parent_id( $post_id );
			if ( empty( $parent ) ) {
				self::permanently_delete_all_children( $post_id );
			} else {
				$recurrence_meta = get_post_meta( $parent, '_EventRecurrence', true );
				$recurrence_meta = self::add_date_exclusion_to_recurrence( $recurrence_meta, get_post_meta( $post_id, '_EventStartDate', true ) );
				update_post_meta( $parent, '_EventRecurrence', $recurrence_meta );
			}
		}
	}

	/**
	 * Given a date, add a date exclusion to the recurrence meta array
	 *
	 * @param array $recurrence_meta Recurrence meta array that holds recurrence rules/exclusions
	 * @param string $date Date to add to the exclusions array
	 */
	public static function add_date_exclusion_to_recurrence( $recurrence_meta, $date ) {
		if ( ! isset( $recurrence_meta['exclusions'] ) ) {
			$recurrence_meta['exclusions'] = array();
		}

		$recurrence_meta['exclusions'][] = array(
			'type' => 'Custom',
			'custom' => array(
				'type' => 'Date',
				'date' => array(
					'date' => $date,
				),
			),
		);

		return $recurrence_meta;
	}

	private static function permanently_delete_all_children( $post_id ) {
		$children = self::get_child_event_ids( $post_id );
		foreach ( $children as $child_id ) {
			wp_delete_post( $child_id, true );
		}
	}

	/**
	 * Comments on recurring events should be kept with the parent event
	 *
	 * @param array $commentdata
	 *
	 * @return array
	 */
	public static function set_parent_for_recurring_event_comments( $commentdata ) {
		if ( isset( $commentdata['comment_post_ID'] ) && tribe_is_recurring_event( $commentdata['comment_post_ID'] ) ) {
			$event = get_post( $commentdata['comment_post_ID'] );
			if ( ! empty( $event->post_parent ) ) {
				$commentdata['comment_post_ID'] = $event->post_parent;
			}
		}

		return $commentdata;
	}

	/**
	 * When displaying comments on a recurring event, get them from the parent
	 *
	 * @param WP_Comment_Query $query
	 *
	 * @return void
	 */
	public static function set_post_id_for_recurring_event_comment_queries( $query ) {
		if ( ! empty( $query->query_vars['post_id'] ) && tribe_is_recurring_event( $query->query_vars['post_id'] ) ) {
			$event = get_post( $query->query_vars['post_id'] );
			if ( ! empty( $event->post_parent ) ) {
				$query->query_vars['post_id'] = $event->post_parent;
			}
		}
	}

	public static function fix_redirect_after_comment_is_posted( $location, $comment ) {
		if ( tribe_is_recurring_event( $comment->comment_post_ID ) ) {
			if ( isset( $_REQUEST['comment_post_ID'] ) && $_REQUEST['comment_post_ID'] != $comment->comment_post_ID ) {
				$child = get_post( $_REQUEST['comment_post_ID'] );
				if ( $child->post_parent == $comment->comment_post_ID ) {
					$location = str_replace( get_permalink( $comment->comment_post_ID ), get_permalink( $child->ID ), $location );
				}
			}
		}

		return $location;
	}

	public static function update_comment_counts_on_child_events( $parent_id, $new_count, $old_count ) {
		if ( tribe_is_recurring_event( $parent_id ) ) {
			$event = get_post( $parent_id );
			if ( ! empty( $event->post_parent ) ) {
				return; // no idea how we got here, but don't update anything
			}
			/** @var wpdb $wpdb */
			global $wpdb;
			$wpdb->update( $wpdb->posts, array( 'comment_count' => $new_count ), array(
				'post_parent' => $parent_id,
				'post_type'   => Tribe__Events__Main::POSTTYPE,
			) );

			$child_ids = self::get_child_event_ids( $parent_id );
			foreach ( $child_ids as $child ) {
				clean_post_cache( $child );
			}
		}
	}

	public static function set_comments_array_on_child_events( $comments, $post_id ) {
		if ( empty( $comments ) && tribe_is_recurring_event( $post_id ) ) {
			$event = get_post( $post_id );
			if ( ! empty( $event->post_parent ) ) {
				/** @var wpdb $wpdb */
				global $wpdb, $user_ID;
				$commenter            = wp_get_current_commenter();
				$comment_author       = $commenter['comment_author']; // Escaped by sanitize_comment_cookies()
				$comment_author_email = $commenter['comment_author_email'];  // Escaped by sanitize_comment_cookies()
				if ( $user_ID ) {
					$comments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND (comment_approved = '1' OR ( user_id = %d AND comment_approved = '0' ) )  ORDER BY comment_date_gmt", $event->post_parent, $user_ID ) );
				} elseif ( empty( $comment_author ) ) {
					$comments = get_comments( array(
						'post_id' => $event->post_parent,
						'status'  => 'approve',
						'order'   => 'ASC',
					) );
				} else {
					$comments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND ( comment_approved = '1' OR ( comment_author = %s AND comment_author_email = %s AND comment_approved = '0' ) ) ORDER BY comment_date_gmt", $event->post_parent, wp_specialchars_decode( $comment_author, ENT_QUOTES ), $comment_author_email ) );
				}
			}
		}

		return $comments;
	}

	/**
	 * Update event recurrence when a recurring event is saved
	 *
	 * @param integer $event_id id of the event to update
	 * @param array   $data     data defining the recurrence of this event
	 *
	 * @return void
	 */
	public static function updateRecurrenceMeta( $event_id, $data ) {
		if ( ! isset( $data['recurrence'] ) ) {
			return;
		}

		$recurrence_meta = array(
			'rules' => array(),
			'exclusions' => array(),
		);

		$datepicker_format = Tribe__Events__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );

		if ( ! empty( $data['recurrence'] ) ) {
			if ( isset( $data['recurrence']['recurrence-description'] ) ) {
				unset( $data['recurrence']['recurrence-description'] );
			}

			foreach ( array( 'rules', 'exclusions' ) as $rule_type ) {
				if ( ! isset( $data['recurrence'][ $rule_type ] ) ) {
					continue;
				}//end if

				foreach ( $data['recurrence'][ $rule_type ] as $key => &$recurrence ) {
					if ( ! $recurrence ) {
						continue;
					}

					if ( ( empty( $recurrence['type'] ) && empty( $recurrence['custom']['type'] ) ) || 'None' === $recurrence['custom']['type'] ) {
						unset( $data['recurrence'][ $rule_type ][ $key ] );
						continue;
					}

					unset(
						$recurrence['occurrence-count-text'],
						$recurrence['custom']['type-text']
					);

					if ( ! empty( $recurrence['end'] ) ) {
						$recurrence['end'] = Tribe__Events__Date_Utils::datetime_from_format( $datepicker_format, $recurrence['end'] );
					}

					// if this isn't an exclusion and it isn't a Custom rule, then we don't need the custom array index
					if ( 'rules' === $rule_type && 'Custom' !== $recurrence['type'] ) {
						unset( $recurrence['custom'] );
					} else {
						$custom_types = array(
							'date',
							'day',
							'week',
							'month',
							'year',
						);

						$custom_type_key = self::custom_type_to_key( $recurrence['custom']['type'] );

						// clean up extraneous array elements
						foreach ( $custom_types as $type ) {
							if ( $type === $custom_type_key ) {
								continue;
							}

							if ( ! isset( $recurrence['custom'][ $type ] ) ) {
								continue;
							}

							unset( $recurrence['custom'][ $type ] );
						}
					}//end else

					$recurrence['EventStartDate'] = $data['EventStartDate'];
					$recurrence['EventEndDate']   = $data['EventEndDate'];

					if ( self::isRecurrenceValid( $event_id, $recurrence ) ) {
						$recurrence_meta[ $rule_type ][] = $recurrence;
					}
				}
			}
		}//end if

		$updated = update_post_meta( $event_id, '_EventRecurrence', $recurrence_meta );
		self::saveEvents( $event_id, $updated );
	}//end updateRecurrenceMeta

	/**
	 * Displays the events recurrence form on the event editor screen
	 *
	 * @param integer $post_id ID of the current event
	 *
	 * @return void
	 */
	public static function loadRecurrenceData( $post_id ) {
		$post = get_post( $post_id );
		if ( ! empty( $post->post_parent ) ) {
			return; // don't show recurrence fields for instances of a recurring event
		}

		self::enqueue_recurrence_data( $post_id );

		$premium = Tribe__Events__Pro__Main::instance();
		include Tribe__Events__Pro__Main::instance()->pluginPath . 'src/admin-views/event-recurrence.php';
	}//end loadRecurrenceData

	/**
	 * Localizes recurrence JS data
	 */
	public static function enqueue_recurrence_data( $post_id = null ) {
		wp_enqueue_style( Tribe__Events__Main::POSTTYPE . '-recurrence', tribe_events_pro_resource_url( 'events-recurrence.css' ), array(), apply_filters( 'tribe_events_pro_css_version', Tribe__Events__Pro__Main::VERSION ) );

		if ( $post_id ) {
			// convert array to variables that can be used in the view
			$recurrence = self::getRecurrenceMeta( $post_id );

			wp_localize_script( Tribe__Events__Main::POSTTYPE.'-premium-admin', 'tribe_events_pro_recurrence_data', $recurrence );
		}

		wp_localize_script( Tribe__Events__Main::POSTTYPE.'-premium-admin', 'tribe_events_pro_recurrence_strings', array(
			'date' => self::date_strings(),
			'recurrence' => self::recurrence_strings(),
			'exclusion' => array(),
		) );
	}

	/**
	 * Outputs recurrence data in JSON format when localize script won't work
	 *
	 * @param int $post_id Post ID of event
	 */
	public static function output_recurrence_json_data( $post_id ) {
		// convert array to variables that can be used in the view
		$recurrence = Tribe__Events__Pro__Recurrence_Meta::getRecurrenceMeta( $post_id );
		?>
		<script>
		var tribe_events_pro_recurrence_data = <?php echo json_encode( $recurrence ); ?>;
		</script>
		<?php
	}

	public static function filter_passthrough( $data ) {
		return $data;
	}

	/**
	 * Setup an error message if there is a problem with a given recurrence.
	 * The message is saved in an option to survive the page load and is deleted after being displayed
	 *
	 * @param array $event The event object that is being saved
	 * @param array $msg   The message to display
	 *
	 * @return void
	 */
	public static function setupRecurrenceErrorMsg( $event_id, $msg ) {
		global $current_screen;

		// only do this when editing events
		if ( is_admin() && $current_screen->id == Tribe__Events__Main::POSTTYPE ) {
			update_post_meta( $event_id, 'tribe_flash_message', $msg );
		}
	}

	/**
	 * Display an error message if there is a problem with a given recurrence and clear it from the cache
	 * @return void
	 */
	public static function showRecurrenceErrorFlash() {
		global $post, $current_screen;

		if ( $current_screen->base == 'post' && $current_screen->post_type == Tribe__Events__Main::POSTTYPE ) {
			$msg = get_post_meta( $post->ID, 'tribe_flash_message', true );

			if ( $msg ) {
				echo '<div class="error"><p>Recurrence not saved: ' . $msg . '</p></div>';
				delete_post_meta( $post->ID, 'tribe_flash_message' );
			}
		}
	}

	/**
	 * Convenience method for turning event meta into keys available to turn into PHP variables
	 *
	 * @param integer $post_id ID of the event being updated
	 * @param array $recurrence_data The actual recurrence data
	 *
	 * @return array
	 */
	public static function getRecurrenceMeta( $post_id, $recurrence_data = null ) {
		if ( ! $recurrence_data ) {
			$recurrence_data = get_post_meta( $post_id, '_EventRecurrence', true );

			// update legacy data
			if ( $recurrence_data && ! isset( $recurrence_data['rules'] ) ) {
				$recurrence_data = self::get_legacy_recurrence_meta( $post_id, $recurrence_data );
			}
		}

		$recurrence_data = self::recurrenceMetaDefault( $recurrence_data );

		return apply_filters( 'Tribe__Events__Pro__Recurrence_Meta_getRecurrenceMeta', $recurrence_data );
	}

	/**
	 * Convenience method for turning event meta into keys available to turn into PHP variables
	 *
	 * @param integer $post_id         ID of the event being updated
	 * @param         $recurrence_data array The actual recurrence data
	 *
	 * @return array
	 */
	public static function get_legacy_recurrence_meta( $post_id, $recurrence_data = null ) {
		if ( ! $recurrence_data ) {
			$recurrence_data = get_post_meta( $post_id, '_EventRecurrence', true );
		}

		$record = array();

		if ( $recurrence_data ) {
			$record['EventStartDate'] = empty( $recurrence_data['EventStartDate'] ) ? tribe_get_start_date( $post_id ) : $recurrence_data['EventStartDate'];
			$record['EventEndDate'] = empty( $recurrence_data['EventEndDate'] ) ? tribe_get_end_date( $post_id ) : $recurrence_data['EventEndDate'];
			$record['type'] = empty( $recurrence_data['type'] ) ? null : $recurrence_data['type'];
			$record['end-type'] = empty( $recurrence_data['end-type'] ) ? null : $recurrence_data['end-type'];
			$record['end'] = empty( $recurrence_data['end'] ) ? null : $recurrence_data['end'];
			$record['end-count'] = empty( $recurrence_data['end-count'] ) ? null : $recurrence_data['end-count'];

			$record['custom'] = array();
			$record['custom']['type'] = empty( $recurrence_data['custom-type'] ) ? null : $recurrence_data['custom-type'];
			$record['custom']['interval'] = empty( $recurrence_data['custom-interval'] ) ? null : $recurrence_data['custom-interval'];

			$record['custom']['day']['same-time'] = 'yes';

			$record['custom']['week'] = array();
			$record['custom']['week']['day'] = empty( $recurrence_data['custom-week-day'] ) ? null : $recurrence_data['custom-week-day'];
			$record['custom']['week']['same-time'] = 'yes';

			$record['custom']['month'] = array();
			$record['custom']['month']['number'] = empty( $recurrence_data['custom-month-number'] ) ? null : $recurrence_data['custom-month-number'];
			$record['custom']['month']['day'] = empty( $recurrence_data['custom-month-day'] ) ? null : $recurrence_data['custom-month-day'];
			$record['custom']['month']['same-time'] = 'yes';

			$record['custom']['year'] = array();
			$record['custom']['year']['month'] = empty( $recurrence_data['custom-year-month'] ) ? null : $recurrence_data['custom-year-month'];
			$record['custom']['year']['filter'] = empty( $recurrence_data['custom-year-filter'] ) ? null : $recurrence_data['custom-year-filter'];
			$record['custom']['year']['month-number'] = empty( $recurrence_data['custom-year-month-number'] ) ? null : $recurrence_data['custom-year-month-number'];
			$record['custom']['year']['month-day'] = empty( $recurrence_data['custom-year-month-day'] ) ? null : $recurrence_data['custom-year-month-day'];
			$record['custom']['year']['same-time'] = 'yes';

			$recurrence_meta['rules'][] = $record;

			if ( ! empty( $recurrence_data['excluded-dates'] ) ) {
				foreach ( (array) $recurrence_data['excluded-dates'] as $date ) {
					self::add_date_exclusion_to_recurrence( $recurrence_meta, $date );
				}
			}
		}

		return apply_filters( 'tribe_pro_legacy_recurrence_meta', $recurrence_meta );
	}

	/**
	 * converts a custom type to a custom type array index slug
	 *
	 * @param string $custom_type Friendly Custom-Type value
	 *
	 * @return string
	 */
	public static function custom_type_to_key( $custom_type ) {
		switch ( $custom_type ) {
			case 'Date': return 'date';
			case 'Yearly': return 'year';
			case 'Monthly': return 'month';
			case 'Weekly': return 'week';
			case 'Daily':
			default:
				return 'day';
		}
	}

	/**
	 * Clean up meta array by providing defaults.
	 *
	 * @param  array $meta
	 *
	 * @return array of $meta merged with defaults
	 */
	protected static function recurrenceMetaDefault( $meta = array() ) {
		$default_meta = array(
			'rules' => array(),
			'exclusions' => array(),
		);

		if ( $meta ) {
			if ( isset( $meta['rules'] ) ) {
				return $meta;
			} else {
				$default_meta['rules'][] = $meta;
			}
		}

		return $default_meta;
	}

	/**
	 * Recurrence validation method.  This is checked after saving an event, but before splitting a series out into multiple occurrences
	 *
	 * @param int   $event_id        The event object that is being saved
	 * @param array $recurrence_meta Recurrence information for this event
	 *
	 * @return bool
	 */
	public static function isRecurrenceValid( $event_id, $recurrence_meta ) {
		$valid    = true;
		$errorMsg = '';

		if ( isset( $recurrence_meta['type'] ) && 'Custom' === $recurrence_meta['type'] ) {
			if ( ! isset( $recurrence_meta['custom']['type'] ) ) {
				$valid    = false;
				$errorMsg = __( 'Custom recurrences must have a type selected.', 'tribe-events-calendar-pro' );
			} elseif (
				! isset( $recurrence_meta['custom']['day'] )
				&& ! isset( $recurrence_meta['custom']['week'] )
				&& ! isset( $recurrence_meta['custom']['month'] )
				&& ! isset( $recurrence_meta['custom']['year'] )
			) {
				$valid    = false;
				$errorMsg = __( 'Custom recurrences must have all data present.', 'tribe-events-calendar-pro' );
			} elseif (
				'Monthly' === $recurrence_meta['custom']['type']
				&& (
					empty( $recurrence_meta['custom']['month']['day'] )
					|| empty( $recurrence_meta['custom']['month']['number'] )
					|| '-' === $recurrence_meta['custom']['month']['day']
					|| '' === $recurrence_meta['custom']['month']['number']
				)
			) {
				$valid    = false;
				$errorMsg = __( 'Monthly custom recurrences cannot have a dash set as the day to occur on.', 'tribe-events-calendar-pro' );
			} elseif (
				'Yearly' === $recurrence_meta['custom']['type']
				&& (
					empty( $recurrence_meta['custom']['year']['month-day'] )
					|| '-' === $recurrence_meta['custom']['year']['month-day']
				)
			) {
				$valid    = false;
				$errorMsg = __( 'Yearly custom recurrences cannot have a dash set as the day to occur on.', 'tribe-events-calendar-pro' );
			}
		}

		if ( ! $valid ) {
			do_action( 'tribe_recurring_event_error', $event_id, $errorMsg );
		}

		return $valid;
	}

	public static function get_child_event_ids( $post_id, $args = array() ) {
		$cache    = new Tribe__Events__Cache();
		$children = $cache->get( 'child_events_' . $post_id, 'save_post' );
		if ( is_array( $children ) ) {
			return $children;
		}

		$args     = wp_parse_args( $args, array(
			'post_parent'    => $post_id,
			'post_type'      => Tribe__Events__Main::POSTTYPE,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_status'    => 'any',
			'meta_key'       => '_EventStartDate',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
		) );
		$children = get_posts( $args );
		$cache->set( 'child_events_' . $post_id, $children, Tribe__Events__Cache::NO_EXPIRATION, 'save_post' );

		return $children;
	}

	public static function get_events_by_slug( $slug ) {
		$cache   = new Tribe__Events__Cache();
		$all_ids = $cache->get( 'events_by_slug_' . $slug, 'save_post' );
		if ( is_array( $all_ids ) ) {
			return $all_ids;
		}
		/** @var wpdb $wpdb */
		global $wpdb;
		$parent_sql = "SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type=%s";
		$parent_sql = $wpdb->prepare( $parent_sql, $slug, Tribe__Events__Main::POSTTYPE );
		$parent_id  = $wpdb->get_var( $parent_sql );
		if ( empty( $parent_id ) ) {
			return array();
		}
		$children_sql = "SELECT ID FROM {$wpdb->posts} WHERE ID=%d OR post_parent=%d AND post_type=%s";
		$children_sql = $wpdb->prepare( $children_sql, $parent_id, $parent_id, Tribe__Events__Main::POSTTYPE );
		$all_ids      = $wpdb->get_col( $children_sql );

		if ( empty( $all_ids ) ) {
			return array();
		}

		$cache->set( 'events_by_slug_' . $slug, $all_ids, Tribe__Events__Cache::NO_EXPIRATION, 'save_post' );

		return $all_ids;
	}

	/**
	 * Get the start dates of all instances of the event,
	 * in ascending order
	 *
	 * @param int $post_id
	 *
	 * @return array Start times, as Y-m-d H:i:s
	 */
	public static function get_start_dates( $post_id ) {
		if ( empty( $post_id ) ) {
			return array();
		}
		$cache = new Tribe__Events__Cache();
		$dates = $cache->get( 'event_dates_' . $post_id, 'save_post' );
		if ( is_array( $dates ) ) {
			return $dates;
		}
		/** @var wpdb $wpdb */
		global $wpdb;
		$ancestors = get_post_ancestors( $post_id );
		$post_id   = empty( $ancestors ) ? $post_id : end( $ancestors );
		$sql       = "SELECT meta_value FROM {$wpdb->postmeta} m INNER JOIN {$wpdb->posts} p ON p.ID=m.post_id AND (p.post_parent=%d OR p.ID=%d) WHERE meta_key='_EventStartDate' ORDER BY meta_value ASC";
		$sql       = $wpdb->prepare( $sql, $post_id, $post_id );
		$result    = $wpdb->get_col( $sql );
		$cache->set( 'recurrence_start_dates_' . $post_id, $result, Tribe__Events__Cache::NO_EXPIRATION, 'save_post' );

		return $result;
	}

	/**
	 * Do the actual work of saving a recurring series of events
	 *
	 * @param int $event_id The event that is being saved
	 *
	 * @return void
	 */
	public static function saveEvents( $event_id ) {
		// don't use self::get_child_event_ids() due to caching that hasn't yet flushed
		$existing_instances = get_posts( array(
			'post_parent'    => $event_id,
			'post_type'      => Tribe__Events__Main::POSTTYPE,
			'posts_per_page' => - 1,
			'fields'         => 'ids',
			'post_status'    => get_post_stati(),
			'meta_key'       => '_EventStartDate',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
		) );

		$recurrences = self::get_recurrence_for_event( $event_id );

		$to_create = array();
		$exclusions = array();
		$to_update = array();
		$to_delete = array();
		$possible_next_pending = array();
		$earliest_date = strtotime( self::$scheduler->get_earliest_date() );
		$latest_date = strtotime( self::$scheduler->get_latest_date() );

		foreach ( $recurrences['rules'] as &$recurrence ) {
			if ( ! $recurrence ) {
				continue;
			}
			$recurrence->setMinDate( $earliest_date );
			$recurrence->setMaxDate( $latest_date );
			$to_create = array_merge( $to_create, $recurrence->getDates() );

			if ( $recurrence->constrainedByMaxDate() !== false ) {
				$possible_next_pending[] = $recurrence->constrainedByMaxDate();
			}
		}

		$to_create = array_unique( $to_create );

		// find days we should exclude
		foreach ( $recurrences['exclusions'] as &$recurrence ) {
			if ( ! $recurrence ) {
				continue;
			}

			$recurrence->setMinDate( $earliest_date );
			$recurrence->setMaxDate( $latest_date );
			$exclusions = array_merge( $exclusions, $recurrence->getDates() );
		}

		// make sure we don't create excluded dates
		$exclusions = array_unique( $exclusions );
		$to_create = array_diff( $to_create, $exclusions );

		if ( $possible_next_pending ) {
			update_post_meta( $event_id, '_EventNextPendingRecurrence', date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, min( $possible_next_pending ) ) );
		}

		foreach ( $existing_instances as $instance ) {
			$start_date = strtotime( get_post_meta( $instance, '_EventStartDate', true ) . '+00:00' );
			$found = array_search( $start_date, $to_create );
			$should_be_excluded = array_search( $start_date, $exclusions );

			if ( $found === false || false !== $should_be_excluded ) {
				$to_delete[ $instance ] = $start_date;
			} else {
				$to_update[ $instance ] = $to_create[ $found ];
				unset( $to_create[ $found ] ); // so we don't re-add it
			}
		}

		// Store the list of instances to create/update/delete etc for future processing
		$queue = new Tribe__Events__Pro__Recurrence__Queue( $event_id );
		$queue->update( $to_create, $to_update, $to_delete, $exclusions );

		// ...but don't wait around, process a small initial batch right away
		Tribe__Events__Pro__Main::instance()->queue_processor->process_batch( $event_id );
	}//end saveEvents

	/**
	 * Deletes events when a change in recurrence pattern renders them obsolete.
	 *
	 * This should not be used when removing individual instances from an otherwise unchanged
	 * pattern - wp_delete_post() can be used directly to facilitate that.
	 *
	 * This method takes care of temporarily unhooking our 'before_delete_post' callback
	 * to avoid the deleted instance being added to the exclusion list.
	 *
	 * @param $instance_id
	 * @param $start_date
	 */
	public static function delete_unexcluded_event( $instance_id, $start_date ) {
		do_action( 'tribe_events_deleting_child_post', $instance_id, $start_date );
		remove_action( 'before_delete_post', array( __CLASS__, 'handle_delete_request' ) );
		wp_delete_post( $instance_id, true );
		add_action( 'before_delete_post', array( __CLASS__, 'handle_delete_request' ) );
	}

	public static function save_pending_events( $event_id ) {
		if ( wp_get_post_parent_id( $event_id ) != 0 ) {
			return;
		}
		$next_pending = get_post_meta( $event_id, '_EventNextPendingRecurrence', true );
		if ( empty( $next_pending ) ) {
			return;
		}

		$latest_date = strtotime( self::$scheduler->get_latest_date() );

		$recurrences = self::get_recurrence_for_event( $event_id );
		foreach ( $recurrences['rules'] as &$recurrence ) {
			$recurrence->setMinDate( strtotime( $next_pending ) );
			$recurrence->setMaxDate( $latest_date );
			$dates = (array) $recurrence->getDates();

			if ( empty( $dates ) ) {
				return; // nothing to add right now. try again later
			}

			delete_post_meta( $event_id, '_EventNextPendingRecurrence' );
			if ( $recurrence->constrainedByMaxDate() !== false ) {
				update_post_meta( $event_id, '_EventNextPendingRecurrence', date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, $recurrence->constrainedByMaxDate() ) );
			}

			$excluded = array_map( 'strtotime', self::get_excluded_dates( $event_id ) );
			foreach ( $dates as $date ) {
				if ( ! in_array( $date, $excluded ) ) {
					$instance = new Tribe__Events__Pro__Recurrence_Instance( $event_id, $date );
					$instance->save();
				}
			}
		}

	}

	private static function get_excluded_dates( $event_id ) {
		$meta = self::getRecurrenceMeta( $event_id );
		if ( empty( $meta['exclusions'] ) || ! is_array( $meta['exclusions'] ) ) {
			return array();
		}

		return $meta['exclusions'];
	}

	public static function get_recurrence_for_event( $event_id ) {
		/** @var string $recType */
		/** @var string $recEndType */
		/** @var string $recEnd */
		/** @var int $recEndCount */
		$recurrence_meta = self::getRecurrenceMeta( $event_id );

		$recurrences = array(
			'rules' => array(),
			'exclusions' => array(),
		);

		if ( ! $recurrence_meta['rules'] ) {
			$recurrences[] = new Tribe__Events__Pro__Null_Recurrence();
			return $recurrences;
		}

		foreach ( array( 'rules', 'exclusions' ) as $rule_type ) {
			foreach ( $recurrence_meta[ $rule_type ] as &$recurrence ) {
				$rule = self::get_series_rule( $recurrence, $rule_type );

				$custom_type = 'none';

				if ( isset( $recurrence['custom']['type'] ) ) {
					$custom_type = self::custom_type_to_key( $recurrence['custom']['type'] );
				}

				$start_time = null;
				$end_time = null;

				if (
					(
						! isset( $recurrence['custom'][ $custom_type ]['same-time'] )
						|| 'no' === $recurrence['custom'][ $custom_type ]['same-time']
					)
					&& isset( $recurrence['custom']['start-time'] )
					&& isset( $recurrence['custom']['end-time'] )
				) {
					$start_time = "{$recurrence['custom']['start-time']['hour']}:{$recurrence['custom']['start-time']['minute']}:00 {$recurrence['custom']['start-time']['meridian']}";
					$end_time = "{$recurrence['custom']['end-time']['hour']}:{$recurrence['custom']['end-time']['minute']}:00 {$recurrence['custom']['end-time']['meridian']}";
				}

				$start = strtotime( get_post_meta( $event_id, '_EventStartDate', true ) . '+00:00' );

				$is_after = false;

				if ( 'rules' === $rule_type ) {
					switch ( $recurrence['end-type'] ) {
						case 'On':
							$end = strtotime( tribe_event_end_of_day( $recurrence['end'] ) );
							break;
						case 'Never':
							$end = Tribe__Events__Pro__Recurrence::NO_END;
							break;
						case 'After':
						default:
							$end = $recurrence['end-count'] - 1; // subtract one because event is first occurrence
							$is_after = true;
							break;
					}
				} else {
					$end = Tribe__Events__Pro__Recurrence::NO_END;
				}

				$recurrences[ $rule_type ][] = new Tribe__Events__Pro__Recurrence( $start, $end, $rule, $is_after, get_post( $event_id ), $start_time, $end_time );
			}
		}

		return $recurrences;
	}

	/**
	 * Decide which rule set to use for finding all the dates in an event series
	 *
	 * @param array $postId The event to find the series for
	 *
	 * @return Tribe__Events__Pro__Date_Series_Rules__Rules_Interface
	 */
	public static function get_series_rule( $recurrence, $rule_type = 'rules' ) {
		if ( 'exclusions' === $rule_type ) {
			$recurrence['type'] = 'Custom';
		}

		$rule = null;

		if ( 'Custom' === $recurrence['type'] && ! isset( $recurrence['custom']['interval'] ) ) {
			$recurrence['custom']['interval'] = 1;
		}

		if (
			'Custom' === $recurrence['type']
			&& isset( $recurrence['custom']['type'] )
			&& 'Date' === $recurrence['custom']['type']
		) {
			$rule = new Tribe__Events__Pro__Date_Series_Rules__Date( strtotime( $recurrence['custom']['date']['date'] ) );
		} elseif (
			'Every Day' === $recurrence['type']
			|| (
				'Custom' === $recurrence['type']
				&& isset( $recurrence['custom']['type'] )
				&& 'Daily' === $recurrence['custom']['type'] )
		) {
			$rule = new Tribe__Events__Pro__Date_Series_Rules__Day( 'Every Day' === $recurrence['type'] ? 1 : $recurrence['custom']['interval'] );
		} elseif ( 'Every Week' === $recurrence['type'] ) {
			$rule = new Tribe__Events__Pro__Date_Series_Rules__Week( 1 );
		} elseif (
			'Custom' === $recurrence['type']
			&& 'Weekly' === $recurrence['custom']['type']
		) {
			$rule = new Tribe__Events__Pro__Date_Series_Rules__Week(
				$recurrence['custom']['interval'],
				$recurrence['custom']['week']['day']
			);
		} elseif ( 'Every Month' === $recurrence['type'] ) {
			$rule = new Tribe__Events__Pro__Date_Series_Rules__Month( 1 );
		} elseif (
			'Custom' === $recurrence['type']
			&& 'Monthly' === $recurrence['custom']['type']
		) {
			$day_of_month = isset( $recurrence['custom']['month']['number'] ) && is_numeric( $recurrence['custom']['month']['number'] ) ? array( $recurrence['custom']['month']['number'] ) : null;
			$month_number = self::ordinalToInt( $recurrence['custom']['month']['number'] );
			$rule = new Tribe__Events__Pro__Date_Series_Rules__Month(
				$recurrence['custom']['interval'],
				$day_of_month,
				$month_number,
				$recurrence['custom']['month']['day']
			);
		} elseif ( 'Every Year' === $recurrence['type'] ) {
			$rule = new Tribe__Events__Pro__Date_Series_Rules__Year( 1 );
		} elseif (
			'Custom' === $recurrence['type']
			&& 'Yearly' === $recurrence['custom']['type']
		) {
			$rule = new Tribe__Events__Pro__Date_Series_Rules__Year(
				$recurrence['custom']['interval'],
				$recurrence['custom']['year']['month'],
				$recurrence['custom']['year']['filter'] ? $recurrence['custom']['year']['month'] : null,
				$recurrence['custom']['year']['filter'] ? $recurrence['custom']['year']['month-day'] : null
			);
		}

		return $rule;
	}//end get_series_rule

	/**
	 * Decide which rule set to use for finding all the dates in an event series
	 *
	 * @param array $postId The event to find the series for
	 *
	 * @return Tribe__Events__Pro__Date_Series_Rules__Rules_Interface
	 */
	public static function getSeriesRules( $postId ) {
		$recurrence_meta = self::getRecurrenceMeta( $postId );
		$rules = array();

		foreach ( $recurrence_meta['rules'] as &$recurrence ) {
			$rules[] = self::get_series_rule( $recurrence );
		}//end foreach

		return $rules;
	}

	/**
	 * Get the recurrence pattern in text format by post id.
	 *
	 * @param int $postId The post id.
	 *
	 * @return sting The human readable string.
	 */
	public static function recurrenceToTextByPost( $postId = null ) {
		if ( $postId == null ) {
			global $post;
			$postId = $post->ID;
		}

		$recurrence_rules = self::getRecurrenceMeta( $postId );
		$start_date       = Tribe__Events__Main::get_series_start_date( $postId );

		$output_text = array();

		foreach ( $recurrence_rules['rules'] as $rule ) {
			$output_text[] = self::recurrenceToText( $rule, $start_date, $postId );
		}

		return implode( _x( ',<br> and ', 'Recurrence rule separator', 'tribe-events-calendar-pro' ), $output_text );
	}

	/**
	 * Build possible strings for recurrence
	 *
	 * @return array
	 */
	public static function recurrence_strings() {
		$strings = array(
			'every-day-on' => __( 'An event every day that lasts %1$s day(s) and %2$s hour(s), the last of which will begin on %3$s', 'tribe-events-calendar-pro' ),
			'every-day-after' => __( 'An event every day that lasts %1$s day(s) and %2$s hour(s), but only create %3$s event(s)', 'tribe-events-calendar-pro' ),
			'every-day-never' => __( 'An event every day that lasts %1$s day(s) and %2$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'every-week-on' => __( 'An event every week on the same day that lasts %1$s day(s) and %2$s hour(s), the last of which will begin on %3$s', 'tribe-events-calendar-pro' ),
			'every-week-after' => __( 'An event every week on the same day that lasts %1$s day(s) and %2$s hour(s), but only create %3$s event(s)', 'tribe-events-calendar-pro' ),
			'every-week-never' => __( 'An event every week on the same day that lasts %1$s day(s) and %2$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'every-month-on' => __( 'An event every month on the same day that lasts %1$s day(s) and %2$s hour(s), the last of which will begin on %3$s', 'tribe-events-calendar-pro' ),
			'every-month-after' => __( 'An event every month on the same day that lasts %1$s day(s) and %2$s hour(s), but only create %3$s event(s)', 'tribe-events-calendar-pro' ),
			'every-month-never' => __( 'An event every month on the same day that lasts %1$s day(s) and %2$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'every-year-on' => __( 'An event every year on the same date that lasts %1$s day(s) and %2$s hour(s), the last of which will begin on %3$s', 'tribe-events-calendar-pro' ),
			'every-year-after' => __( 'An event every year on the same date that lasts %1$s day(s) and %2$s hour(s), but only create %3$s event(s)', 'tribe-events-calendar-pro' ),
			'every-year-never' => __( 'An event every year on the same date that lasts %1$s day(s) and %2$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-daily-on-same-time' => __( 'An event every %1$s day(s) that lasts %2$s day(s) and %3$s hour(s), the last of which will begin on %4$s', 'tribe-events-calendar-pro' ),
			'custom-daily-after-same-time' => __( 'An event every %1$s day(s) that lasts %2$s day(s) and %3$s hour(s), but only create %4$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-daily-never-same-time' => __( 'An event every %1$s day(s) that lasts %2$s day(s) and %3$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-daily-on-diff-time' => __( 'An event every %1$s day(s) that begins at %2$s and lasts %3$s day(s) and %4$s hour(s), the last of which will begin on %5$s', 'tribe-events-calendar-pro' ),
			'custom-daily-after-diff-time' => __( 'An event every %1$s day(s) that begins at %2$s and lasts %3$s day(s) and %4$s hour(s), but only create %5$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-daily-never-diff-time' => __( 'An event every %1$s day(s) that begins at %2$s and lasts %3$s day(s) and %4$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-weekly-on-same-time' => __( 'An event every %1$s week(s) on %2$s that lasts %3$s day(s) and %4$s hour(s), the last of which will begin on %5$s', 'tribe-events-calendar-pro' ),
			'custom-weekly-after-same-time' => __( 'An event every %1$s week(s) on %2$s that lasts %3$s day(s) and %4$s hour(s), but only create %5$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-weekly-never-same-time' => __( 'An event every %1$s week(s) on %2$s that lasts %3$s day(s) and %4$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-weekly-on-diff-time' => __( 'An event every %1$s week(s) on %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s), the last of which will begin on %6$s', 'tribe-events-calendar-pro' ),
			'custom-weekly-after-diff-time' => __( 'An event every %1$s week(s) on %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s), but only create %6$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-weekly-never-diff-time' => __( 'An event every %1$s week(s) on %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-monthly-on-same-time-numeric' => __( 'An event every %1$s month(s) on day %2$s that lasts %3$s day(s) and %4$s hour(s), the last of which will begin on %5$s', 'tribe-events-calendar-pro' ),
			'custom-monthly-after-same-time-numeric' => __( 'An event every %1$s month(s) on day %2$s that lasts %3$s day(s) and %4$s hour(s), but only create %5$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-monthly-never-same-time-numeric' => __( 'An event every %1$s month(s) on day %2$s that lasts %3$s day(s) and %4$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-monthly-on-diff-time-numeric' => __( 'An event every %1$s month(s) on day %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s), the last of which will begin on %6$s', 'tribe-events-calendar-pro' ),
			'custom-monthly-after-diff-time-numeric' => __( 'An event every %1$s month(s) on day %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s), but only create %6$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-monthly-never-diff-time-numeric' => __( 'An event every %1$s month(s) on day %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-monthly-on-same-time' => __( 'An event every %1$s month(s) on %2$s that lasts %3$s day(s) and %4$s hour(s), the last of which will begin on %5$s', 'tribe-events-calendar-pro' ),
			'custom-monthly-after-same-time' => __( 'An event every %1$s month(s) on %2$s that lasts %3$s day(s) and %4$s hour(s), but only create %5$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-monthly-never-same-time' => __( 'An event every %1$s month(s) on %2$s that lasts %3$s day(s) and %4$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-monthly-on-diff-time' => __( 'An event every %1$s month(s) on %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s), the last of which will begin on %7$s', 'tribe-events-calendar-pro' ),
			'custom-monthly-after-diff-time' => __( 'An event every %1$s month(s) on %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s), but only create %6$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-monthly-never-diff-time' => __( 'An event every %1$s month(s) on %2$s that begins at %3$s and lasts %4$s day(s) and %5$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-yearly-on-same-time-unfiltered' => __( 'An event every %1$s year(s) in %2$s on day %3$s that lasts %4$s day(s) and %5$s hour(s), the last of which will begin on %6$s', 'tribe-events-calendar-pro' ),
			'custom-yearly-after-same-time-unfiltered' => __( 'An event every %1$s year(s) in %2$s on day %3$s that lasts %4$s day(s) and %5$s hour(s), but only create %6$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-yearly-never-same-time-unfiltered' => __( 'An event every %1$s year(s) in %2$s on day %3$s that lasts %4$s day(s) and %5$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-yearly-on-diff-time-unfiltered' => __( 'An event every %1$s year(s) in %2$s on day %3$s that begins at %4$s and lasts %5$s day(s) and %6$s hour(s), the last of which will begin on %7$s', 'tribe-events-calendar-pro' ),
			'custom-yearly-after-diff-time-unfiltered' => __( 'An event every %1$s year(s) in %2$s on day %3$s that begins at %4$s and lasts %5$s day(s) and %6$s hour(s), but only create %7$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-yearly-never-diff-time-unfiltered' => __( 'An event every %1$s year(s) in %2$s on day %3$s that begins at %4$s and lasts %5$s day(s) and %6$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-yearly-on-same-time' => __( 'An event every %1$s year(s) in %2$s on %3$s that lasts %4$s day(s) and %5$s hour(s), the last of which will begin on %6$s', 'tribe-events-calendar-pro' ),
			'custom-yearly-after-same-time' => __( 'An event every %1$s year(s) in %2$s on %3$s that lasts %4$s day(s) and %5$s hour(s), but only create %6$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-yearly-never-same-time' => __( 'An event every %1$s year(s) in %2$s on %3$s that lasts %4$s day(s) and %5$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
			'custom-yearly-on-diff-time' => __( 'An event every %1$s year(s) in %2$s on %3$s that begins at %4$s and lasts %5$s day(s) and %6$s hour(s), the last of which will begin on %7$s', 'tribe-events-calendar-pro' ),
			'custom-yearly-after-diff-time' => __( 'An event every %1$s year(s) in %2$s on %3$s that begins at %4$s and lasts %5$s day(s) and %6$s hour(s), but only create %7$s event(s)', 'tribe-events-calendar-pro' ),
			'custom-yearly-never-diff-time' => __( 'An event every %1$s year(s) in %2$s on %3$s that begins at %4$s and lasts %5$s day(s) and %6$s hour(s) with no end date', 'tribe-events-calendar-pro' ),
		);

		return $strings;
	}

	/**
	 * Build possible date-specific strings for recurrence
	 *
	 * @return array
	 */
	public static function date_strings() {
		$strings = array(
			'weekdays' => array(
				__( 'Monday' ),
				__( 'Tuesday' ),
				__( 'Wednesday' ),
				__( 'Thursday' ),
				__( 'Friday' ),
				__( 'Saturday' ),
				__( 'Sunday' ),
			),
			'months' => array(
				__( 'January' ),
				__( 'February' ),
				__( 'March' ),
				__( 'April' ),
				__( 'May' ),
				__( 'June' ),
				__( 'July' ),
				__( 'August' ),
				__( 'September' ),
				__( 'October' ),
				__( 'November' ),
				__( 'December' ),
			),
			'collection_joiner' => _x( 'and', 'Joins the last item in a list of items (i.e. the "and" in Monday, Tuesday, and Wednesday)', 'tribe-events-calendar-pro' ),
			'day_placeholder' => _x( '[day]', 'Placeholder text for a day of the week (or days of the week) before the user has selected any', 'tribe-events-calendar-pro' ),
			'month_placeholder' => _x( '[month]', 'Placeholder text for a month (or months) before the user has selected any', 'tribe-events-calendar-pro' ),
			'day_of_month' => _x( 'day %1$s', 'Describes a day of the month (e.g. "day 5" or "day 27")', 'tribe-events-calendar-pro' ),
			'first_x' => _x( 'the first %1$s', 'Used when displaying: "the first Monday" or "the first day"', 'tribe-events-calendar-pro' ),
			'second_x' => _x( 'the second %1$s', 'Used when displaying: "the second Monday" or "the second day"', 'tribe-events-calendar-pro' ),
			'third_x' => _x( 'the third %1$s', 'Used when displaying: "the third Monday" or "the third day"', 'tribe-events-calendar-pro' ),
			'fourth_x' => _x( 'the fourth %1$s', 'Used when displaying: "the fourth Monday" or "the fourth day"', 'tribe-events-calendar-pro' ),
			'fifth_x' => _x( 'the fifth %1$s', 'Used when displaying: "the fifth Monday" or "the fifth day"', 'tribe-events-calendar-pro' ),
			'last_x' => _x( 'the last %1$s', 'Used when displaying: "the last Monday" or "the last day"', 'tribe-events-calendar-pro' ),
			'day' => _x( 'day', 'Used when displaying the word "day" in "the last day" or "the first day"', 'tribe-events-calendar-pro' ),
		);

		return $strings;
	}

	/**
	 * Convert the event recurrence meta into a human readable string
	 *
	 * @TODO: get this to work for arbitrary recurrence
	 *
	 * @param array $postId The recurring event
	 *
	 * @return The human readable string
	 */
	public static function recurrenceToText( $rule, $start_date, $event_id ) {
		$text = '';
		$recurrence_strings = self::recurrence_strings();
		$date_strings = self::date_strings();

		$interval = 1;
		$is_custom = false;
		$same_time = true;
		$year_filtered = false;
		$rule['type'] = str_replace( ' ', '-', strtolower( $rule['type'] ) );
		$rule['end-type'] = str_replace( ' ', '-', strtolower( $rule['end-type'] ) );
		$formatted_end = date( tribe_get_date_format( true ), strtotime( $rule['end'] ) );

		// if the type is "none", then there's no rules to parse
		if ( 'none' === $rule['type'] ) {
			return;
		}

		if ( 'custom' === $rule['type'] ) {
			$is_custom = true;
			$same_time = 'yes' === $rule['custom'][ self::custom_type_to_key( $rule['custom']['type'] ) ]['same-time'];

			if ( 'Yearly' === $rule['custom']['type'] ) {
				$year_filtered = ! empty( $rule['custom']['year']['filter'] );
			}
		}

		$start_date = strtotime( tribe_get_start_date( $event_id ) );
		$end_date = strtotime( tribe_get_end_date( $event_id ) );

		$num_days = floor( ( $end_date - $start_date ) / DAY_IN_SECONDS );

		// make sure we always round hours UP to when dealing with decimal lengths more than 2. Example: 4.333333 would become 4.34
		$num_hours = ceil( ( ( ( $end_date - $start_date ) / HOUR_IN_SECONDS ) - ( $num_days * 24 ) ) * 100 ) / 100;

		if ( $is_custom && 'custom' === $rule['type'] && ! $same_time ) {
			$new_start_date = date( 'Y-m-d', $start_date ) . ' ' . $rule['custom']['start-time']['hour'] . ':' . $rule['custom']['start-time']['minute'];
			if ( isset( $rule['custom']['start-time']['meridian'] ) ) {
				$new_start_date .= ' ' . $rule['custom']['start-time']['meridian'];
			}

			$new_end_date = date( 'Y-m-d', $end_date ) . ' ' . $rule['custom']['end-time']['hour'] . ':' . $rule['custom']['end-time']['minute'];
			if ( isset( $rule['custom']['end-time']['meridian'] ) ) {
				$new_end_date .= ' ' . $rule['custom']['end-time']['meridian'];
			}

			$new_num_days = floor( ( $new_end_date - $new_start_date ) / DAY_IN_SECONDS );

			// make sure we always round hours UP to when dealing with decimal lengths more than 2. Example: 4.333333 would become 4.34
			$new_num_hours = ceil( ( ( ( $new_end_date - $new_start_date ) / HOUR_IN_SECONDS ) - ( $new_num_days * 24 ) ) * 100 ) / 100;
		}

		$weekdays = array();
		$months = array();
		$month_number = null;
		$month_day = null;
		$month_day_description = null;

		if (
			$is_custom
			&& 'Weekly' === $rule['custom']['type']
			&& ! empty( $rule['custom']['week']['day'] )
		) {
			foreach ( $rule['custom']['week']['day'] as $day ) {
				$weekdays[] = $date_strings['weekdays'][ $day - 1 ];
			}

			if ( ! $weekdays ) {
				$weekdays = $date_strings['day_placeholder'];
			} elseif ( 2 === count( $weekdays ) ) {
				$weekdays = implode( " {$date_strings['collection_joiner']} ", $weekdays );
			} else {
				$weekdays = implode( ', ', $weekdays );
				$weekdays = preg_replace( '/(.*),/', '$1, ' . $date_strings['collection_joiner'], $weekdays );
			}
		} elseif (
			$is_custom
			&& 'Monthly' === $rule['custom']['type']
			&& ! empty( $rule['custom']['month']['number'] )
			&& ! empty( $rule['custom']['month']['day'] )
		) {
			$month_number = $rule['custom']['month']['number'];
			$month_day = $rule['custom']['month']['day'];
		} elseif (
			$is_custom
			&& 'Yearly' === $rule['custom']['type']
			&& ! empty( $rule['custom']['year']['month-number'] )
			&& ! empty( $rule['custom']['year']['month-day'] )
		) {
			$month_number = $rule['custom']['year']['month-number'];
			$month_day = $rule['custom']['year']['month-day'];

			if ( ! empty( $rule['custom']['year']['month'] ) ) {
				foreach ( $rule['custom']['year']['month'] as $month ) {
					$months[] = $date_strings['months'][ $month - 1 ];
				}
			}

			if ( ! $months ) {
				$months = $date_strings['month_placeholder'];
			} elseif ( 2 === count( $months ) ) {
				$months = implode( " {$date_strings['collection_joiner']} ", $months );
			} else {
				$months = implode( ', ', $months );
				$months = preg_replace( '/(.*),/', '$1, ' . $date_strings['collection_joiner'], $months );
			}
		}

		$key = $rule['type'];

		if ( 'custom' === $rule['type'] ) {
			$key .= "-{$rule['custom']['type']}-{$rule['end-type']}-" . ( $same_time ? 'same' : 'diff' ) . '-time';

			if ( 'monthly' === $rule['custom']['type'] && is_numeric( $month_number ) ) {
				$key .= '-numeric';
			} elseif ( 'yearly' === $rule['custom']['type'] && ! $year_filtered ) {
				$key .= '-unfiltered';
			}
		} else {
			$key .= "-{$rule['end-type']}";
		}

		$key = strtolower( $key );

		// if custom rules were set but the custom-specific data is missing, then revert to standard
		// rules (weekly, monthly, and yearly)
		if (
			$is_custom
			&& 'Weekly' === $rule['custom']['type']
			&& ! $weekdays
		) {
			$key = 'every-week-on';
		} elseif (
			$is_custom
			&& 'Monthly' === $rule['custom']['type']
			&& ! $month_number
			&& ! $month_day
		) {
			$key = 'every-month-on';
		} elseif (
			$is_custom
			&& 'Yearly' === $rule['custom']['type']
			&& ! $month_number
			&& ! $month_day
		) {
			$key = 'every-year-on';
		}

		$text = $recurrence_strings[ $key ];

		switch ( $key ) {
			case 'every-day-on':
			case 'every-week-on':
			case 'every-month-on':
			case 'every-year-on':
			case 'every-day-never':
			case 'every-week-never':
			case 'every-month-never':
			case 'every-year-never':
				$text = sprintf(
					$text,
					$num_days,
					$num_hours,
					$formatted_end
				);
				break;
			case 'every-day-after':
			case 'every-week-after':
			case 'every-month-after':
			case 'every-year-after':
				$text = sprintf(
					$text,
					$num_days,
					$num_hours,
					$rule['end-count']
				);
				break;
			case 'custom-daily-on-same-time':
			case 'custom-daily-never-same-time':
				$text = sprintf(
					$text,
					$interval,
					$num_days,
					$num_hours,
					$formatted_end
				);
				break;
			case 'custom-daily-after-same-time':
				$text = sprintf(
					$text,
					$interval,
					$num_days,
					$num_hours,
					$rule['end-count']
				);
				break;
			case 'custom-daily-on-diff-time':
			case 'custom-daily-never-diff-time':
				$text = sprintf(
					$text,
					$interval,
					$new_start_time,
					$new_num_days,
					$new_num_hours,
					$formatted_end
				);
				break;
			case 'custom-daily-after-diff-time':
				$text = sprintf(
					$text,
					$interval,
					$new_start_time,
					$new_num_days,
					$new_num_hours,
					$rule['end-count']
				);
				break;
			case 'custom-weekly-on-same-time':
			case 'custom-weekly-never-same-time':
				$text = sprintf(
					$text,
					$interval,
					$weekdays,
					$num_days,
					$num_hours,
					$formatted_end
				);
				break;
			case 'custom-weekly-after-same-time':
				$text = sprintf(
					$text,
					$interval,
					$weekdays,
					$num_days,
					$num_hours,
					$rule['end-count']
				);
				break;
			case 'custom-weekly-on-diff-time':
			case 'custom-weekly-never-diff-time':
				$text = sprintf(
					$text,
					$interval,
					$weekdays,
					$new_start_time,
					$new_num_days,
					$new_num_hours,
					$formatted_end
				);
				break;
			case 'custom-weekly-after-diff-time':
				$text = sprintf(
					$text,
					$interval,
					$weekdays,
					$new_start_time,
					$new_num_days,
					$new_num_hours,
					$rule['end-count']
				);
				break;
			case 'custom-monthly-on-same-time-numeric':
			case 'custom-monthly-never-same-time-numeric':
			case 'custom-monthly-on-same-time':
			case 'custom-monthly-never-same-time':
				$text = sprintf(
					$text,
					$interval,
					$month_day_description,
					$num_days,
					$num_hours,
					$formatted_end
				);
				break;
			case 'custom-monthly-after-same-time-numeric':
			case 'custom-monthly-after-same-time':
				$text = sprintf(
					$text,
					$interval,
					$month_day_description,
					$num_days,
					$num_hours,
					$rule['end-count']
				);
				break;
			case 'custom-monthly-on-diff-time-numeric':
			case 'custom-monthly-never-diff-time-numeric':
			case 'custom-monthly-on-diff-time':
			case 'custom-monthly-never-diff-time':
				$text = sprintf(
					$text,
					$interval,
					$month_day_description,
					$new_start_time,
					$new_num_days,
					$new_num_hours,
					$formatted_end
				);
				break;
			case 'custom-monthly-after-diff-time-numeric':
			case 'custom-monthly-after-diff-time':
				$text = sprintf(
					$text,
					$interval,
					$month_day_description,
					$new_start_time,
					$new_num_days,
					$new_num_hours,
					$rule['end-count']
				);
				break;
			case 'custom-yearly-on-same-time-unfiltered':
			case 'custom-yearly-never-same-time-unfiltered':
			case 'custom-yearly-on-same-time':
			case 'custom-yearly-never-same-time':
				$text = sprintf(
					$text,
					$interval,
					$months,
					$month_day_description,
					$num_days,
					$num_hours,
					$formatted_end
				);
				break;
			case 'custom-yearly-after-same-time-unfiltered':
			case 'custom-yearly-after-same-time':
				$text = sprintf(
					$text,
					$interval,
					$months,
					$month_day_description,
					$num_days,
					$num_hours,
					$rule['end-count']
				);
				break;
			case 'custom-yearly-on-diff-time-unfiltered':
			case 'custom-yearly-never-diff-time-unfiltered':
			case 'custom-yearly-on-diff-time':
			case 'custom-yearly-never-diff-time':
				$text = sprintf(
					$text,
					$interval,
					$months,
					$month_day_description,
					$new_start_time,
					$new_num_days,
					$new_num_hours,
					$formatted_end
				);
				break;
			case 'custom-yearly-after-diff-time-unfiltered':
			case 'custom-yearly-after-diff-time':
				$text = sprintf(
					$text,
					$interval,
					$months,
					$month_day_description,
					$new_start_time,
					$new_num_days,
					$new_num_hours,
					$rule['end-count']
				);
				break;
		}

		return $text;
	}

	/**
	 * Convert an array of day ids into a human readable string
	 *
	 * @param array $days The day ids
	 *
	 * @return The human readable string
	 */
	private static function daysToText( $days ) {
		$day_words = array(
			__( 'Monday', 'tribe-events-calendar-pro' ),
			__( 'Tuesday', 'tribe-events-calendar-pro' ),
			__( 'Wednesday', 'tribe-events-calendar-pro' ),
			__( 'Thursday', 'tribe-events-calendar-pro' ),
			__( 'Friday', 'tribe-events-calendar-pro' ),
			__( 'Saturday', 'tribe-events-calendar-pro' ),
			__( 'Sunday', 'tribe-events-calendar-pro' ),
		);
		$count     = count( $days );
		$day_text  = '';

		for ( $i = 0; $i < $count; $i ++ ) {
			if ( $count > 2 && $i == $count - 1 ) {
				$day_text .= __( ', and', 'tribe-events-calendar-pro' ) . ' ';
			} elseif ( $count == 2 && $i == $count - 1 ) {
				$day_text .= ' ' . __( 'and', 'tribe-events-calendar-pro' ) . ' ';
			} elseif ( $count > 2 && $i > 0 ) {
				$day_text .= __( ',', 'tribe-events-calendar-pro' ) . ' ';
			}

			$day_text .= $day_words[ $days[ $i ] - 1 ] ? $day_words[ $days[ $i ] - 1 ] : 'day';
		}

		return $day_text;
	}

	/**
	 * Convert an array of month ids into a human readable string
	 *
	 * @param array $months The month ids
	 *
	 * @return The human readable string
	 */
	private static function monthsToText( $months ) {
		$month_words = array(
			__( 'January', 'tribe-events-calendar-pro' ),
			__( 'February', 'tribe-events-calendar-pro' ),
			__( 'March', 'tribe-events-calendar-pro' ),
			__( 'April', 'tribe-events-calendar-pro' ),
			__( 'May', 'tribe-events-calendar-pro' ),
			__( 'June', 'tribe-events-calendar-pro' ),
			__( 'July', 'tribe-events-calendar-pro' ),
			__( 'August', 'tribe-events-calendar-pro' ),
			__( 'September', 'tribe-events-calendar-pro' ),
			__( 'October', 'tribe-events-calendar-pro' ),
			__( 'November', 'tribe-events-calendar-pro' ),
			__( 'December', 'tribe-events-calendar-pro' ),
		);
		$count       = count( $months );
		$month_text  = '';

		for ( $i = 0; $i < $count; $i ++ ) {
			if ( $count > 2 && $i == $count - 1 ) {
				$month_text .= __( ', and ', 'tribe-events-calendar-pro' );
			} elseif ( $count == 2 && $i == $count - 1 ) {
				$month_text .= __( ' and ', 'tribe-events-calendar-pro' );
			} elseif ( $count > 2 && $i > 0 ) {
				$month_text .= __( ', ', 'tribe-events-calendar-pro' );
			}

			$month_text .= $month_words[ $months[ $i ] - 1 ];
		}

		return $month_text;
	}

	/**
	 * Convert an ordinal from an ECP recurrence series into an integer
	 *
	 * @param string $ordinal The ordinal number
	 *
	 * @return An integer representation of the ordinal
	 */
	private static function ordinalToInt( $ordinal ) {
		switch ( $ordinal ) {
			case 'First':
				return 1;
			case 'Second':
				return 2;
			case 'Third':
				return 3;
			case 'Fourth':
				return 4;
			case 'Fifth':
				return 5;
			case 'Last':
				return - 1;
			default:
				return null;
		}
	}

	/**
	 * Collapses subsequent recurrence records and ensures the closest record is returned
	 *
	 * @param string $sql The current SQL statement
	 * @param WP_Query $query WP Query object
	 *
	 * @return string The new SQL statement
	 */
	public static function recurrence_collapse_sql( $sql, $query ) {
		if ( ! isset( $query->query_vars['is_tribe_widget'] ) || ! $query->query_vars['is_tribe_widget'] ){
			if ( tribe_is_month() || tribe_is_week() || tribe_is_day() ) {
				return $sql;
			}
		}

		if ( ! empty( $query->tribe_is_event ) || ! empty( $query->tribe_is_multi_posttype ) ) {
			if ( isset( $query->query_vars['tribeHideRecurrence'] ) && $query->query_vars['tribeHideRecurrence'] ) {
				global $wpdb;

				// if we are collapsing recurrence events, we need to re-jigger the SQL statement so the GROUP BY
				// collapses records in an expected manner

				// We need to relocate the SQL_CALC_FOUND_ROWS to the outer query
				$sql = preg_replace( '/SQL_CALC_FOUND_ROWS/', '', $sql );

				// We don't want to grab the min EventStartDate or EventEndDate because without a group by that collapses everything
				$sql = preg_replace( '/MIN\((' . $wpdb->postmeta . '|tribe_event_end_date).meta_value\) as Event(Start|End)Date/', '$1.meta_value as Event$2Date', $sql );

				// Let's get rid of the group by (non-greedily stop before the ORDER BY or LIMIT
				$sql = preg_replace( '/GROUP BY .+?(ORDER|LIMIT)/', '$1', $sql );

				// Let's extract the LIMIT. We're going to relocate it to the outer query
				$limit_regex = '/LIMIT\s+[0-9]+(\s*,\s*[0-9]+)?/';
				preg_match( $limit_regex, $sql, $limit );
				if ( $limit ) {
					$sql = preg_replace( $limit_regex, '', $sql );
					$limit = $limit[0];
				} else {
					$limit = '';
				}

				$sql = '
					SELECT
						SQL_CALC_FOUND_ROWS *
					FROM (
						' . $sql . "
					) a
					GROUP BY IF( post_parent = 0, ID, post_parent )
					ORDER BY EventStartDate ASC
					{$limit}
				";
			}
		}

		return $sql;
	}

	/**
	 * Adds setting for hiding subsequent occurrences by default.
	 *
	 *
	 * @param array  $args
	 * @param string $id
	 *
	 * @return array
	 */
	public static function inject_settings( $args, $id ) {

		if ( $id == 'general' ) {

			// we want to inject the hiding subsequent occurrences into the general section directly after "Live update AJAX"
			$args = Tribe__Events__Main::array_insert_after_key( 'liveFiltersUpdate', $args, array(
				'hideSubsequentRecurrencesDefault' => array(
					'type'            => 'checkbox_bool',
					'label'           => __( 'Recurring event instances', 'tribe-events-calendar-pro' ),
					'tooltip'         => __( 'Show only the first instance of each recurring event (only affects list-style views).', 'tribe-events-calendar-pro' ),
					'default'         => false,
					'validation_type' => 'boolean',
				),
				'userToggleSubsequentRecurrences'  => array(
					'type'            => 'checkbox_bool',
					'label'           => __( 'Front-end recurring event instances toggle', 'tribe-events-calendar-pro' ),
					'tooltip'         => __( 'Allow users to decide whether to show all instances of a recurring event.', 'tribe-events-calendar-pro' ),
					'default'         => false,
					'validation_type' => 'boolean',
				),
				'recurrenceMaxMonthsBefore'        => array(
					'type'            => 'text',
					'size'            => 'small',
					'label'           => __( 'Clean up recurring events after', 'tribe-events-calendar-pro' ),
					'tooltip'         => __( 'Automatically remove recurring event instances older than this', 'tribe-events-calendar-pro' ),
					'validation_type' => 'positive_int',
					'default'         => 24,
				),
				'recurrenceMaxMonthsAfter'         => array(
					'type'            => 'text',
					'size'            => 'small',
					'label'           => __( 'Create recurring events in advance for', 'tribe-events-calendar-pro' ),
					'tooltip'         => __( 'Recurring events will be created this far in advance', 'tribe-events-calendar-pro' ),
					'validation_type' => 'positive_int',
					'default'         => 24,
				),
			) );
			add_filter( 'tribe_field_div_end', array( __CLASS__, 'add_months_to_settings_field' ), 100, 2 );

		}


		return $args;
	}

	/**
	 * @param string     $html
	 * @param Tribe__Events__Field $field
	 *
	 * @return string
	 */
	public static function add_months_to_settings_field( $html, $field ) {
		if ( in_array( $field->name, array( 'recurrenceMaxMonthsBefore', 'recurrenceMaxMonthsAfter' ) ) ) {
			$html = __( ' months', 'tribe-events-calendar-pro' ) . $html;
		}

		return $html;
	}

	/**
	 * Combines the ['post'] piece of the $_REQUEST variable so it only has unique post ids.
	 *
	 *
	 * @return void
	 */
	public static function combineRecurringRequestIds() {
		if ( isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == Tribe__Events__Main::POSTTYPE && ! empty( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) ) {
			$_REQUEST['post'] = array_unique( $_REQUEST['post'] );
		}
	}

	/**
	 * @return void
	 */
	public static function reset_scheduler() {
		if ( ! empty( self::$scheduler ) ) {
			self::$scheduler->remove_hooks();
		}
		self::$scheduler = new Tribe__Events__Pro__Recurrence_Scheduler( tribe_get_option( 'recurrenceMaxMonthsBefore', 24 ), tribe_get_option( 'recurrenceMaxMonthsAfter', 24 ) );
		self::$scheduler->add_hooks();
	}

	/**
	 * @return Tribe__Events__Pro__Recurrence_Scheduler
	 */
	public static function get_scheduler() {
		if ( empty( self::$scheduler ) ) {
			self::reset_scheduler();
		}

		return self::$scheduler;
	}

	/**
	 * Placed here for compatibility reasons. This can be removed
	 * when Events Calendar 3.2 or greater is released
	 *
	 * @todo Remove this method
	 *
	 * @param int $post_id
	 *
	 * @return string
	 * @see  Tribe__Events__Main::get_series_start_date()
	 */
	private static function get_series_start_date( $post_id ) {
		if ( method_exists( 'Tribe__Events__Main', 'get_series_start_date' ) ) {
			return Tribe__Events__Main::get_series_start_date( $post_id );
		}
		$start_dates = tribe_get_recurrence_start_dates( $post_id );

		return reset( $start_dates );
	}

	public static function update_child_thumbnails( $meta_id, $post_id, $meta_key, $meta_value ) {
		static $recursing = false;
		if ( $recursing || $meta_key != '_thumbnail_id' || ! tribe_is_recurring_event( $post_id ) ) {
			return;
		}
		$recursing = true; // don't repeat this for child events

		$children = self::get_child_event_ids( $post_id );
		foreach ( $children as $child_id ) {
			update_post_meta( $child_id, $meta_key, $meta_value );
		}
		$recursing = false;
	}

	public static function remove_child_thumbnails( $meta_ids, $post_id, $meta_key, $meta_value ) {
		static $recursing = false;
		if ( $recursing || $meta_key != '_thumbnail_id' || ! tribe_is_recurring_event( $post_id ) ) {
			return;
		}
		$recursing = true; // don't repeat this for child events

		$children = self::get_child_event_ids( $post_id );
		foreach ( $children as $child_id ) {
			delete_post_meta( $child_id, $meta_key, $meta_value );
		}
		$recursing = false;
	}

	public static function enqueue_post_editor_notices() {
		if ( ! empty( $_REQUEST['post'] ) && tribe_is_recurring_event( $_REQUEST['post'] ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'display_post_editor_recurring_notice' ), 10, 0 );
		}
	}

	public static function display_post_editor_recurring_notice() {
		$message = __( 'You are currently editing all events in a recurring series.', 'tribe-events-calendar-pro' );
		printf( '<div class="updated"><p>%s</p></div>', $message );

		$pending = get_post_meta( get_the_ID(), '_EventNextPendingRecurrence', true );
		if ( $pending ) {
			$start_dates     = tribe_get_recurrence_start_dates( get_the_ID() );
			$count           = count( $start_dates );
			$last            = end( $start_dates );
			$pending_message = __( '%d instances of this event have been created through %s. <a href="%s">Learn more.</a>', 'tribe-events-calendar-pro' );
			$pending_message = sprintf( $pending_message, $count, date_i18n( tribe_get_date_format( true ), strtotime( $last ) ), 'http://m.tri.be/lq' );
			printf( '<div class="updated"><p>%s</p></div>', $pending_message );
		}
	}

	public static function localize_scripts( $data, $object_name, $script_handle ) {
		if ( ! isset( $data['recurrence'] ) ) {
			$data['recurrence'] = array();
		}
		$data['recurrence'] = array_merge( $data['recurrence'], array(
			'splitAllMessage'               => __( "You are about to split this series in two.\n\nThe event you selected and all subsequent events in the series will be separated into a new series of events that you can edit independently of the original series.\n\nThis action cannot be undone.", 'tribe-events-calendar-pro' ),
			'splitSingleMessage'            => __( "You are about to break this event out of its series.\n\nYou will be able to edit it independently of the original series.\n\nThis action cannot be undone.", 'tribe-events-calendar-pro' ),
			'bulkDeleteConfirmationMessage' => __( 'Are you sure you want to trash all occurrences of these events?', 'tribe-events-calendar-pro' ),
		) );

		return $data;
	}
}
