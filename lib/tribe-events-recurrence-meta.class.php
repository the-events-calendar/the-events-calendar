<?php

/**
 * TribeEventsRecurrenceMeta
 *
 * WordPress hooks and filters controlling event recurrence
 */
class TribeEventsRecurrenceMeta {
	const UPDATE_TYPE_ALL = 1;
	const UPDATE_TYPE_FUTURE = 2;
	const UPDATE_TYPE_SINGLE = 3;
	public static $recurrence_default_meta = array(
		'recType'                        => null,
		'recEndType'                     => null,
		'recEnd'                         => null,
		'recEndCount'                    => null,
		'recCustomType'                  => null,
		'recCustomInterval'              => null,
		'recCustomTypeText'              => null,
		'recCustomRecurrenceDescription' => null,
		'recOccurrenceCountText'         => null,
		'recCustomWeekDay'               => null,
		'recCustomMonthNumber'           => null,
		'recCustomMonthDay'              => null,
		'recCustomYearFilter'            => null,
		'recCustomYearMonthNumber'       => null,
		'recCustomYearMonthDay'          => null,
		'recCustomYearMonth'             => array()
	);

	/** @var TribeEventsRecurrenceScheduler */
	private static $scheduler = null;


	public static function init() {
		add_action( 'tribe_events_update_meta', array(
				__CLASS__,
				'updateRecurrenceMeta'
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

		add_filter( 'manage_' . TribeEvents::POSTTYPE . '_posts_columns', array(
				__CLASS__,
				'list_table_column_headers'
			) );
		add_action( 'manage_' . TribeEvents::POSTTYPE . '_posts_custom_column', array(
				__CLASS__,
				'populate_custom_list_table_columns'
			), 10, 2 );
		add_filter( 'post_class', array( __CLASS__, 'add_recurring_event_post_classes' ), 10, 3 );


		add_filter( 'post_row_actions', array( __CLASS__, 'edit_post_row_actions' ), 10, 2 );
		add_action( 'admin_action_tribe_split', array( __CLASS__, 'handle_split_request' ), 10, 1 );
		add_action( 'wp_before_admin_bar_render', array( __CLASS__, 'admin_bar_render' ) );

		add_filter( 'posts_groupby', array( __CLASS__, 'addGroupBy' ), 10, 2 );

		add_filter( 'tribe_settings_tab_fields', array( __CLASS__, 'inject_settings' ), 10, 2 );

		add_action( 'load-edit.php', array( __CLASS__, 'combineRecurringRequestIds' ) );

		add_action( 'load-post.php', array( __CLASS__, 'enqueue_post_editor_notices' ), 10, 1 );

		add_action( 'updated_post_meta', array( __CLASS__, 'update_child_thumbnails' ), 4, 40 );
		add_action( 'added_post_meta', array( __CLASS__, 'update_child_thumbnails' ), 4, 40 );
		add_action( 'deleted_post_meta', array( __CLASS__, 'remove_child_thumbnails' ), 4, 40 );

		add_filter( 'tribe_events_pro_localize_script', array( __CLASS__, 'localize_scripts' ), 10, 3 );

		self::reset_scheduler();
	}

	public static function filter_edit_post_link( $url, $post_id, $context ) {
		if ( tribe_is_recurring_event( $post_id ) && $parent = wp_get_post_parent_id( $post_id ) ) {
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
				echo __( 'Yes', 'tribe-events-calendar-pro' );
			} else {
				echo __( '—', 'tribe-events-calendar-pro' );
			}
		}
	}

	public static function add_recurring_event_post_classes( $classes, $class, $post_id ) {
		if ( get_post_type( $post_id ) == TribeEvents::POSTTYPE && tribe_is_recurring_event( $post_id ) ) {
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
			$post_type_object   = get_post_type_object( TribeEvents::POSTTYPE );
			$is_first_in_series = empty( $post->post_parent );
			$first_id_in_series = $post->post_parent ? $post->post_parent : $post->ID;
			if ( isset( $actions['edit'] ) && 'trash' != $post->post_status ) {
				if ( current_user_can( 'edit_post', $post->ID ) ) {
					$split_actions          = array();
					$split_actions['split'] = sprintf( '<a href="%s" class="tribe-split tribe-split-single" title="%s">%s</a>', esc_url( wp_nonce_url( self::get_split_series_url( $post->ID, false, false ), 'tribe_split_' . $post->ID ) ), esc_attr( __( 'Break this event out of its series and edit it independently', 'tribe-events-calendar-pro' ) ), __( 'Edit Single', 'tribe-events-calendar-pro' ) );
					if ( ! $is_first_in_series ) {
						$split_actions['split_all'] = sprintf( '<a href="%s" class="tribe-split tribe-split-all" title="%s">%s</a>', esc_url( wp_nonce_url( self::get_split_series_url( $post->ID, false, true ), 'tribe_split_' . $post->ID ) ), esc_attr( __( 'Split the series in two at this point, creating a new series out of this and all subsequent events', 'tribe-events-calendar-pro' ) ), __( 'Edit Upcoming', 'tribe-events-calendar-pro' ) );
					}
					$actions = TribeEvents::array_insert_after_key( 'edit', $actions, $split_actions );
				}
				if ( current_user_can( 'edit_post', $first_id_in_series ) ) {
					$edit_series_url = get_edit_post_link( $first_id_in_series, 'display' );
					$actions['edit'] = sprintf( '<a href="%s" title="%s">%s</a>', esc_url( $edit_series_url ), esc_attr( __( 'Edit all events in this series', 'tribe-events-calendar-pro' ) ), __( 'Edit All', 'tribe-events-calendar-pro' ) );
				}
			}
			if ( $is_first_in_series ) {
				if ( ! empty( $actions['trash'] ) ) {
					$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move all events in this series to the Trash', 'tribe-events-calendar-pro' ) ) . "' href='" . esc_url( get_delete_post_link( $post->ID ) ) . "'>" . __( 'Trash Series', 'tribe-events-calendar-pro' ) . "</a>";
				}
				if ( ! empty( $actions['delete'] ) ) {
					$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete all events in this series permanently', 'tribe-events-calendar-pro' ) ) . "' href='" . esc_url( get_delete_post_link( $post->ID, '', true ) ) . "'>" . __( 'Delete Series Permanently', 'tribe-events-calendar-pro' ) . "</a>";
				}
			}
			if ( ! empty( $actions['untrash'] ) ) { // if the whole series is in the trash, restore the whole series together
				$first_event = get_post( $first_id_in_series );
				if ( $first_event->post_status == 'trash' ) {
					$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore all events in this series from the Trash', 'tribe-events-calendar-pro' ) ) . "' href='" . esc_url( wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $first_id_in_series ) ), 'untrash-post_' . $first_id_in_series ) ) . "'>" . __( 'Restore Series', 'tribe-events-calendar-pro' ) . "</a>";
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

		$splitter = new TribeEventsPro_RecurrenceSeriesSplitter();

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
				$recurrence_meta                     = get_post_meta( $parent, '_EventRecurrence', true );
				$recurrence_meta['excluded-dates'][] = get_post_meta( $post_id, '_EventStartDate', true );
				update_post_meta( $parent, '_EventRecurrence', $recurrence_meta );
			}
		}
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
					'post_type'   => TribeEvents::POSTTYPE
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
				} else if ( empty( $comment_author ) ) {
					$comments = get_comments( array(
							'post_id' => $event->post_parent,
							'status'  => 'approve',
							'order'   => 'ASC'
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
		// save recurrence
		$current = get_post_meta( $event_id, '_EventRecurrence', true );
		if ( ! empty( $data['recurrence'] ) ) {
			$recurrence_meta = wp_parse_args( $data['recurrence'], $current );
			// for an update when the event start/end dates change
			$recurrence_meta['EventStartDate'] = $data['EventStartDate'];
			$recurrence_meta['EventEndDate']   = $data['EventEndDate'];
		} else {
			$recurrence_meta = null;
		}

		if ( ! empty( $current ) || TribeEventsRecurrenceMeta::isRecurrenceValid( $event_id, $recurrence_meta ) ) {
			$updated = update_post_meta( $event_id, '_EventRecurrence', $recurrence_meta );
			TribeEventsRecurrenceMeta::saveEvents( $event_id, $updated );
		}
	}

	/**
	 * Displays the events recurrence form on the event editor screen
	 *
	 * @param integer $postId ID of the current event
	 *
	 * @return void
	 */
	public static function loadRecurrenceData( $postId ) {
		$post = get_post( $postId );
		if ( ! empty( $post->post_parent ) ) {
			return; // don't show recurrence fields for instances of a recurring event
		}
		// convert array to variables that can be used in the view
		extract( TribeEventsRecurrenceMeta::getRecurrenceMeta( $postId ) );

		$premium = TribeEventsPro::instance();
		include( TribeEventsPro::instance()->pluginPath . 'admin-views/event-recurrence.php' );
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
		if ( is_admin() && $current_screen->id == TribeEvents::POSTTYPE ) {
			update_post_meta( $event_id, 'tribe_flash_message', $msg );
		}
	}


	/**
	 * Display an error message if there is a problem with a given recurrence and clear it from the cache
	 * @return void
	 */
	public static function showRecurrenceErrorFlash() {
		global $post, $current_screen;

		if ( $current_screen->base == 'post' && $current_screen->post_type == TribeEvents::POSTTYPE ) {
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
	 * @param integer $postId         ID of the event being updated
	 * @param         $recurrenceData array The actual recurrence data
	 *
	 * @return array
	 */
	public static function getRecurrenceMeta( $postId, $recurrenceData = null ) {
		if ( ! $recurrenceData ) {
			$recurrenceData = get_post_meta( $postId, '_EventRecurrence', true );
		}

		$recurrenceData = self::recurrenceMetaDefault( $recurrenceData );

		$recurrence_meta = array();

		if ( $recurrenceData ) {
			$recurrence_meta['recType']                        = $recurrenceData['type'];
			$recurrence_meta['recEndType']                     = $recurrenceData['end-type'];
			$recurrence_meta['recEnd']                         = $recurrenceData['end'];
			$recurrence_meta['recEndCount']                    = $recurrenceData['end-count'];
			$recurrence_meta['recCustomType']                  = $recurrenceData['custom-type'];
			$recurrence_meta['recCustomInterval']              = $recurrenceData['custom-interval'];
			$recurrence_meta['recCustomTypeText']              = $recurrenceData['custom-type-text'];
			$recurrence_meta['recOccurrenceCountText']         = $recurrenceData['occurrence-count-text'];
			$recurrence_meta['recCustomRecurrenceDescription'] = $recurrenceData['recurrence-description'];
			$recurrence_meta['recCustomWeekDay']               = $recurrenceData['custom-week-day'];
			$recurrence_meta['recCustomMonthNumber']           = $recurrenceData['custom-month-number'];
			$recurrence_meta['recCustomMonthDay']              = $recurrenceData['custom-month-day'];
			$recurrence_meta['recCustomYearMonth']             = $recurrenceData['custom-year-month'];
			$recurrence_meta['recCustomYearFilter']            = $recurrenceData['custom-year-filter'];
			$recurrence_meta['recCustomYearMonthNumber']       = $recurrenceData['custom-year-month-number'];
			$recurrence_meta['recCustomYearMonthDay']          = $recurrenceData['custom-year-month-day'];
			$recurrence_meta['recExcludedDates']               = $recurrenceData['excluded-dates'];
		}

		$recurrence_meta = wp_parse_args( $recurrence_meta, self::$recurrence_default_meta );

		return apply_filters( 'TribeEventsRecurrenceMeta_getRecurrenceMeta', $recurrence_meta );
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
			'type'                     => null, // string - None, Every Day, Every Week, Every Month, Every Year, Custom
			'end-type'                 => null, // string - On, After, Never
			'end'                      => null, // string - YYYY-MM-DD - If end-type is On, recurrence ends on this date
			'end-count'                => null, // int - If end-type is After, recurrence ends after this many instances
			'custom-type'              => null, // string - Daily, Weekly, Monthly, Yearly - only used if type is Custom
			'custom-interval'          => null, // int - If type is Custom, the interval between custom-type units
			'custom-type-text'         => null, // string - Display value for admin
			'occurrence-count-text'    => null, // string - Display value for admin
			'recurrence-description'   => null, // string - Custom description for the recurrence pattern
			'custom-week-day'          => null, // int[] - 1 = Monday, 7 = Sunday, days when type is Custom
			'custom-month-number'      => null, // string|int - 1-31, First-Fifth, or Last
			'custom-month-day'         => null, // int - 1 = Monday, 7 = Sunday
			'custom-year-month'        => array(), // int[] - 1 = January
			'custom-year-filter'       => null, // int - 1 or 0
			'custom-year-month-number' => null, // as custom-month-number, for Yearly custom-type
			'custom-year-month-day'    => null, // as custom-month-day, for Yearly custom-type
			'excluded-dates'           => array(), // dates that the event will not occur
		);
		$meta         = wp_parse_args( (array) $meta, $default_meta );

		return $meta;
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
		extract( TribeEventsRecurrenceMeta::getRecurrenceMeta( $event_id, $recurrence_meta ) );
		$valid    = true;
		$errorMsg = '';

		if ( $recType == "Custom" && $recCustomType == "Monthly" && ( $recCustomMonthDay == '-' || $recCustomMonthNumber == '' ) ) {
			$valid    = false;
			$errorMsg = __( 'Monthly custom recurrences cannot have a dash set as the day to occur on.', 'tribe-events-calendar-pro' );
		} else if ( $recType == "Custom" && $recCustomType == "Yearly" && $recCustomYearMonthDay == '-' ) {
			$valid    = false;
			$errorMsg = __( 'Yearly custom recurrences cannot have a dash set as the day to occur on.', 'tribe-events-calendar-pro' );
		}

		if ( ! $valid ) {
			do_action( 'tribe_recurring_event_error', $event_id, $errorMsg );
		}

		return $valid;
	}

	public static function get_child_event_ids( $post_id, $args = array() ) {
		$cache    = new TribeEventsCache();
		$children = $cache->get( 'child_events_' . $post_id, 'save_post' );
		if ( is_array( $children ) ) {
			return $children;
		}

		$args     = wp_parse_args( $args, array(
			'post_parent'    => $post_id,
			'post_type'      => TribeEvents::POSTTYPE,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_status'    => 'any',
			'meta_key'       => '_EventStartDate',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
		) );
		$children = get_posts( $args );
		$cache->set( 'child_events_' . $post_id, $children, TribeEventsCache::NO_EXPIRATION, 'save_post' );

		return $children;
	}

	public static function get_events_by_slug( $slug ) {
		$cache   = new TribeEventsCache();
		$all_ids = $cache->get( 'events_by_slug_' . $slug, 'save_post' );
		if ( is_array( $all_ids ) ) {
			return $all_ids;
		}
		/** @var wpdb $wpdb */
		global $wpdb;
		$parent_sql = "SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type=%s";
		$parent_sql = $wpdb->prepare( $parent_sql, $slug, TribeEvents::POSTTYPE );
		$parent_id  = $wpdb->get_var( $parent_sql );
		if ( empty( $parent_id ) ) {
			return array();
		}
		$children_sql = "SELECT ID FROM {$wpdb->posts} WHERE ID=%d OR post_parent=%d AND post_type=%s";
		$children_sql = $wpdb->prepare( $children_sql, $parent_id, $parent_id, TribeEvents::POSTTYPE );
		$all_ids      = $wpdb->get_col( $children_sql );

		if ( empty( $all_ids ) ) {
			return array();
		}

		$cache->set( 'events_by_slug_' . $slug, $all_ids, TribeEventsCache::NO_EXPIRATION, 'save_post' );

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
		$cache = new TribeEventsCache();
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
		$cache->set( 'recurrence_start_dates_' . $post_id, $result, TribeEventsCache::NO_EXPIRATION, 'save_post' );

		return $result;
	}

	/**
	 * Do the actual work of saving a recurring series of events
	 *
	 * @param int $postId The event that is being saved
	 *
	 * @return void
	 */
	public static function saveEvents( $postId ) {
		// don't use self::get_child_event_ids() due to caching that hasn't yet flushed
		$existing_instances = get_posts( array(
			'post_parent'    => $postId,
			'post_type'      => TribeEvents::POSTTYPE,
			'posts_per_page' => - 1,
			'fields'         => 'ids',
			'post_status'    => get_post_stati(),
			'meta_key'       => '_EventStartDate',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
		) );

		$recurrence = self::getRecurrenceForEvent( $postId );

		if ( $recurrence ) {
			$recurrence->setMinDate( strtotime( self::$scheduler->get_earliest_date() ) );
			$recurrence->setMaxDate( strtotime( self::$scheduler->get_latest_date() ) );
			$dates     = (array) $recurrence->getDates();
			$to_update = array();

			if ( $recurrence->constrainedByMaxDate() !== false ) {
				update_post_meta( $postId, '_EventNextPendingRecurrence', date( DateSeriesRules::DATE_FORMAT, $recurrence->constrainedByMaxDate() ) );
			}

			foreach ( $existing_instances as $instance ) {
				$start_date = strtotime( get_post_meta( $instance, '_EventStartDate', true ) );
				$found      = array_search( $start_date, $dates );
				if ( $found === false ) {
					do_action( 'tribe_events_deleting_child_post', $instance, $start_date );
					// deleting a post would normally add it to the excluded dates array
					// we don't want that if a child is deleted due to a recurrence change
					remove_action( 'before_delete_post', array( __CLASS__, 'handle_delete_request' ) );
					wp_delete_post( $instance, true );
					add_action( 'before_delete_post', array( __CLASS__, 'handle_delete_request' ) );
				} else {
					$to_update[ $instance ] = $dates[ $found ];
					unset( $dates[ $found ] ); // so we don't re-add it
				}
			}

			$excluded = array_map( 'strtotime', self::get_excluded_dates( $postId ) );

			foreach ( $dates as $date ) {
				if ( ! in_array( $date, $excluded ) ) {
					$instance = new TribeEventsPro_RecurrenceInstance( $postId, $date );
					$instance->save();
				}
			}
			foreach ( $to_update as $instance_id => $date ) {
				$instance = new TribeEventsPro_RecurrenceInstance( $postId, $date, $instance_id );
				$instance->save();
			}
		}
	}

	public static function save_pending_events( $event_id ) {
		if ( wp_get_post_parent_id( $event_id ) != 0 ) {
			return;
		}
		$next_pending = get_post_meta( $event_id, '_EventNextPendingRecurrence', true );
		if ( empty( $next_pending ) ) {
			return;
		}

		$recurrence = self::getRecurrenceForEvent( $event_id );
		$recurrence->setMinDate( strtotime( $next_pending ) );
		$recurrence->setMaxDate( strtotime( self::$scheduler->get_latest_date() ) );
		$dates = (array) $recurrence->getDates();

		if ( empty( $dates ) ) {
			return; // nothing to add right now. try again later
		}

		delete_post_meta( $event_id, '_EventNextPendingRecurrence' );
		if ( $recurrence->constrainedByMaxDate() !== false ) {
			update_post_meta( $event_id, '_EventNextPendingRecurrence', date( DateSeriesRules::DATE_FORMAT, $recurrence->constrainedByMaxDate() ) );
		}

		$excluded = array_map( 'strtotime', self::get_excluded_dates( $event_id ) );
		foreach ( $dates as $date ) {
			if ( ! in_array( $date, $excluded ) ) {
				$instance = new TribeEventsPro_RecurrenceInstance( $event_id, $date );
				$instance->save();
			}
		}

	}

	private static function get_excluded_dates( $event_id ) {
		$meta = self::getRecurrenceMeta( $event_id );
		if ( empty( $meta['recExcludedDates'] ) || ! is_array( $meta['recExcludedDates'] ) ) {
			return array();
		}

		return $meta['recExcludedDates'];
	}

	private static function getRecurrenceForEvent( $event_id ) {
		/** @var string $recType */
		/** @var string $recEndType */
		/** @var string $recEnd */
		/** @var int $recEndCount */
		extract( TribeEventsRecurrenceMeta::getRecurrenceMeta( $event_id ) );
		if ( $recType == 'None' ) {
			require_once( dirname( __FILE__ ) . '/tribe-null-recurrence.php' );

			return new TribeNullRecurrence();
		}
		$rules = TribeEventsRecurrenceMeta::getSeriesRules( $event_id );

		$recStart = strtotime( get_post_meta( $event_id, '_EventStartDate', true ) . '+00:00' );

		switch ( $recEndType ) {
			case 'On':
				// @todo use tribe_events_end_of_day() ?
				$recEnd = strtotime( TribeDateUtils::endOfDay( $recEnd ) );
				break;
			case 'Never':
				$recEnd = TribeRecurrence::NO_END;
				break;
			case 'After':
			default:
				$recEnd = $recEndCount - 1; // subtract one because event is first occurrence
				break;
		}

		$recurrence = new TribeRecurrence( $recStart, $recEnd, $rules, $recEndType == "After", get_post( $event_id ) );

		return $recurrence;
	}

	/**
	 * Decide which rule set to use for finding all the dates in an event series
	 *
	 * @param array $postId The event to find the series for
	 *
	 * @return DateSeriesRules
	 */
	public static function getSeriesRules( $postId ) {
		extract( TribeEventsRecurrenceMeta::getRecurrenceMeta( $postId ) );
		$rules = null;

		if ( ! $recCustomInterval ) {
			$recCustomInterval = 1;
		}

		if ( $recType == "Every Day" || ( $recType == "Custom" && $recCustomType == "Daily" ) ) {
			$rules = new DaySeriesRules( $recType == "Every Day" ? 1 : $recCustomInterval );
		} else if ( $recType == "Every Week" ) {
			$rules = new WeekSeriesRules( 1 );
		} else if ( $recType == "Custom" && $recCustomType == "Weekly" ) {
			$rules = new WeekSeriesRules( $recCustomInterval ? $recCustomInterval : 1, $recCustomWeekDay );
		} else if ( $recType == "Every Month" ) {
			$rules = new MonthSeriesRules( 1 );
		} else if ( $recType == "Custom" && $recCustomType == "Monthly" ) {
			$recCustomMonthDayOfMonth = is_numeric( $recCustomMonthNumber ) ? array( $recCustomMonthNumber ) : null;
			$recCustomMonthNumber     = self::ordinalToInt( $recCustomMonthNumber );
			$rules                    = new MonthSeriesRules( $recCustomInterval ? $recCustomInterval : 1, $recCustomMonthDayOfMonth, $recCustomMonthNumber, $recCustomMonthDay );
		} else if ( $recType == "Every Year" ) {
			$rules = new YearSeriesRules( 1 );
		} else if ( $recType == "Custom" && $recCustomType == "Yearly" ) {
			$rules = new YearSeriesRules( $recCustomInterval ? $recCustomInterval : 1, $recCustomYearMonth, $recCustomYearFilter ? $recCustomYearMonthNumber : null, $recCustomYearFilter ? $recCustomYearMonthDay : null );
		}

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

		$recurrence_rules = TribeEventsRecurrenceMeta::getRecurrenceMeta( $postId );
		$start_date       = TribeEvents::get_series_start_date( $postId );

		$output_text = empty( $recurrence_rules['recCustomRecurrenceDescription'] ) ? self::recurrenceToText( $recurrence_rules, $start_date ) : $recurrence_rules['recCustomRecurrenceDescription'];

		return $output_text;
	}

	/**
	 * Convert the event recurrence meta into a human readable string
	 *
	 * @param array $postId The recurring event
	 *
	 * @return The human readable string
	 */
	public static function recurrenceToText( $recurrence_rules = array(), $start_date ) {
		$text                     = "";
		$custom_text              = "";
		$occurrence_text          = "";
		$recType                  = '';
		$recEndType               = '';
		$recEndCount              = '';
		$recCustomType            = '';
		$recCustomInterval        = null;
		$recCustomMonthNumber     = null;
		$recCustomYearMonthNumber = null;
		$recCustomYearFilter      = '';
		$recCustomYearMonth       = '';
		$recCustomYearMonthDay    = '';
		extract( $recurrence_rules );

		if ( $recType == "Every Day" ) {
			$text            = __( "Every day", 'tribe-events-calendar-pro' );
			$occurrence_text = sprintf( _n( " for %d day", " for %d days", $recEndCount, 'tribe-events-calendar-pro' ), $recEndCount );
			$custom_text     = "";
		} else if ( $recType == "Every Week" ) {
			$text            = __( "Every week", 'tribe-events-calendar-pro' );
			$occurrence_text = sprintf( _n( " for %d week", " for %d weeks", $recEndCount, 'tribe-events-calendar-pro' ), $recEndCount );
		} else if ( $recType == "Every Month" ) {
			$text            = __( "Every month", 'tribe-events-calendar-pro' );
			$occurrence_text = sprintf( _n( " for %d month", " for %d months", $recEndCount, 'tribe-events-calendar-pro' ), $recEndCount );
		} else if ( $recType == "Every Year" ) {
			$text            = __( "Every year", 'tribe-events-calendar-pro' );
			$occurrence_text = sprintf( _n( " for %d year", " for %d years", $recEndCount, 'tribe-events-calendar-pro' ), $recEndCount );
		} else if ( $recType == "Custom" ) {
			if ( $recCustomType == "Daily" ) {
				$text            = $recCustomInterval == 1 ?
					__( "Every day", 'tribe-events-calendar-pro' ) :
					sprintf( __( "Every %d days", 'tribe-events-calendar-pro' ), $recCustomInterval );
				$occurrence_text = sprintf( _n( ", recurring %d time", ", recurring %d times", $recEndCount, 'tribe-events-calendar-pro' ), $recEndCount );
			} else if ( $recCustomType == "Weekly" ) {
				$text            = $recCustomInterval == 1 ?
					__( "Every week", 'tribe-events-calendar-pro' ) :
					sprintf( __( "Every %d weeks", 'tribe-events-calendar-pro' ), $recCustomInterval );
				$custom_text     = sprintf( __( " on %s", 'tribe-events-calendar-pro' ), self::daysToText( $recCustomWeekDay ) );
				$occurrence_text = sprintf( _n( ", recurring %d time", ", recurring %d times", $recEndCount, 'tribe-events-calendar-pro' ), $recEndCount );
			} else if ( $recCustomType == "Monthly" ) {
				$text            = $recCustomInterval == 1 ?
					__( "Every month", 'tribe-events-calendar-pro' ) :
					sprintf( __( "Every %d months", 'tribe-events-calendar-pro' ), $recCustomInterval );
				$number_display  = is_numeric( $recCustomMonthNumber ) ? TribeDateUtils::numberToOrdinal( $recCustomMonthNumber ) : strtolower( $recCustomMonthNumber );
				$custom_text     = sprintf( __( " on the %s %s", 'tribe-events-calendar-pro' ), $number_display, is_numeric( $recCustomMonthNumber ) ? __( "day", 'tribe-events-calendar-pro' ) : self::daysToText( $recCustomMonthDay ) );
				$occurrence_text = sprintf( _n( ", recurring %d time", ", recurring %d times", $recEndCount, 'tribe-events-calendar-pro' ), $recEndCount );
			} else if ( $recCustomType == "Yearly" ) {
				$text = $recCustomInterval == 1 ?
					__( "Every year", 'tribe-events-calendar-pro' ) :
					sprintf( __( "Every %d years", 'tribe-events-calendar-pro' ), $recCustomInterval );

				$customYearNumber = $recCustomYearMonthNumber != - 1 ? TribeDateUtils::numberToOrdinal( $recCustomYearMonthNumber ) : __( "last", 'tribe-events-calendar-pro' );

				$day             = $recCustomYearFilter ? $customYearNumber : TribeDateUtils::numberToOrdinal( date( 'j', strtotime( $start_date ) ) );
				$of_week         = $recCustomYearFilter ? self::daysToText( $recCustomYearMonthDay ) : "";
				$months          = self::monthsToText( $recCustomYearMonth );
				$custom_text     = sprintf( __( " on the %s %s of %s", 'tribe-events-calendar-pro' ), $day, $of_week, $months );
				$occurrence_text = sprintf( _n( ", recurring %d time", ", recurring %d times", $recEndCount, 'tribe-events-calendar-pro' ), $recEndCount );
			}
		}

		// end text
		if ( $recEndType == "On" ) {
			$endText = ' ' . sprintf( __( " until %s", 'tribe-events-calendar-pro' ), date_i18n( get_option( 'date_format' ), strtotime( $recEnd ) ) );
		} elseif ( $recEndType == 'Never' ) {
			$endText = '';
		} else {
			$endText = $occurrence_text;
		}

		return sprintf( __( '%s%s%s', 'tribe-events-calendar-pro' ), $text, $custom_text, $endText );
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
			__( "Monday", 'tribe-events-calendar-pro' ),
			__( "Tuesday", 'tribe-events-calendar-pro' ),
			__( "Wednesday", 'tribe-events-calendar-pro' ),
			__( "Thursday", 'tribe-events-calendar-pro' ),
			__( "Friday", 'tribe-events-calendar-pro' ),
			__( "Saturday", 'tribe-events-calendar-pro' ),
			__( "Sunday", 'tribe-events-calendar-pro' )
		);
		$count     = sizeof( $days );
		$day_text  = "";

		for ( $i = 0; $i < $count; $i ++ ) {
			if ( $count > 2 && $i == $count - 1 ) {
				$day_text .= __( ", and", 'tribe-events-calendar-pro' ) . ' ';
			} else if ( $count == 2 && $i == $count - 1 ) {
				$day_text .= ' ' . __( "and", 'tribe-events-calendar-pro' ) . ' ';
			} else if ( $count > 2 && $i > 0 ) {
				$day_text .= __( ",", 'tribe-events-calendar-pro' ) . ' ';
			}

			$day_text .= $day_words[ $days[ $i ] - 1 ] ? $day_words[ $days[ $i ] - 1 ] : "day";
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
			__( "January", 'tribe-events-calendar-pro' ),
			__( "February", 'tribe-events-calendar-pro' ),
			__( "March", 'tribe-events-calendar-pro' ),
			__( "April", 'tribe-events-calendar-pro' ),
			__( "May", 'tribe-events-calendar-pro' ),
			__( "June", 'tribe-events-calendar-pro' ),
			__( "July", 'tribe-events-calendar-pro' ),
			__( "August", 'tribe-events-calendar-pro' ),
			__( "September", 'tribe-events-calendar-pro' ),
			__( "October", 'tribe-events-calendar-pro' ),
			__( "November", 'tribe-events-calendar-pro' ),
			__( "December", 'tribe-events-calendar-pro' )
		);
		$count       = sizeof( $months );
		$month_text  = "";

		for ( $i = 0; $i < $count; $i ++ ) {
			if ( $count > 2 && $i == $count - 1 ) {
				$month_text .= __( ", and ", 'tribe-events-calendar-pro' );
			} else if ( $count == 2 && $i == $count - 1 ) {
				$month_text .= __( " and ", 'tribe-events-calendar-pro' );
			} else if ( $count > 2 && $i > 0 ) {
				$month_text .= __( ", ", 'tribe-events-calendar-pro' );
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
			case "First":
				return 1;
			case "Second":
				return 2;
			case "Third":
				return 3;
			case "Fourth":
				return 4;
			case "Fifth":
				return 5;
			case "Last":
				return - 1;
			default:
				return null;
		}
	}

	/**
	 * Adds the Group By that hides future occurences of recurring events if setting is set to.
	 *
	 *
	 * @param string $group_by The current group by clause.
	 * @param        $query
	 *
	 * @return string The new group by clause.
	 */
	public static function addGroupBy( $group_by, $query ) {
		if ( tribe_is_month() || tribe_is_week() || tribe_is_day() ) {
			return $group_by;
		}
		if ( ! empty( $query->tribe_is_event_query ) || ! empty( $query->tribe_is_multi_posttype ) ) {
			if ( isset( $query->query_vars['tribeHideRecurrence'] ) && $query->query_vars['tribeHideRecurrence'] == 1 ) {
				global $wpdb;
				$group_by = " IF( {$wpdb->posts}.post_parent = 0, {$wpdb->posts}.ID, {$wpdb->posts}.post_parent )";
			}
		}

		return $group_by;
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
			$args = TribeEvents::array_insert_after_key( 'liveFiltersUpdate', $args, array(
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
	 * @param TribeField $field
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
		if ( isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == TribeEvents::POSTTYPE && ! empty( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) ) {
			$_REQUEST['post'] = array_unique( $_REQUEST['post'] );
		}
	}

	/**
	 * @return void
	 */
	public static function reset_scheduler() {
		require_once( dirname( __FILE__ ) . '/tribe-recurrence-scheduler.php' );
		if ( ! empty( self::$scheduler ) ) {
			self::$scheduler->remove_hooks();
		}
		self::$scheduler = new TribeEventsRecurrenceScheduler( tribe_get_option( 'recurrenceMaxMonthsBefore', 24 ), tribe_get_option( 'recurrenceMaxMonthsAfter', 24 ) );
		self::$scheduler->add_hooks();
	}

	/**
	 * @return TribeEventsRecurrenceScheduler
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
	 * @see  TribeEvents::get_series_start_date()
	 */
	private static function get_series_start_date( $post_id ) {
		if ( method_exists( 'TribeEvents', 'get_series_start_date' ) ) {
			return TribeEvents::get_series_start_date( $post_id );
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
