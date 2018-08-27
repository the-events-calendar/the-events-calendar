<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Ignored_Events' ) ) {
	/**
	 * Ignored Events are fully powered by this class
	 */
	class Tribe__Events__Ignored_Events {
		public static $ignored_status = 'tribe-ignored';

		public static $legacy_deleted_post = 'deleted_event';

		public static $legacy_origin = 'ical-importer';

		/**
		 * Where we save the previous Status when ignoring an Event
		 *
		 * @since 4.5.13
		 * @var string
		 */
		public static $key_previous_status = '_tribe_ignored_event_previous_status';

		/**
		 * Static Singleton Factory Method
		 *
		 * @return self
		 */
		public static function instance() {
			return tribe( 'tec.ignored-events' );
		}

		public function action_assets() {
			$plugin = Tribe__Events__Main::instance();
			$localize = array();

			if ( ! empty( $_GET['post'] ) && $this->can_ignore( $_GET['post'] ) ) {
				$post = get_post( $_GET['post'] );
				if ( self::$ignored_status === $post->post_status ) {
					$localize['single'] = array(
						'link_text'   => esc_html__( 'Delete Permanently', 'the-events-calendar' ),
						'link_title'  => esc_attr__( 'Ignored events that are deleted will be removed permanently. They can be recreated via import.', 'the-events-calendar' ),
						'link_nonce'  => wp_create_nonce( 'delete-post_' . $post->ID ),
						'link_post'   => $post->ID,
						'link_status' => esc_html__( 'Ignored', 'the-events-calendar' ),
					);
				} else {
					$localize['single'] = array(
						'link_text'  => esc_html__( 'Hide & Ignore', 'the-events-calendar' ),
						'link_title' => esc_attr__( 'Ignored events do not show on the calendar but can be updated with future imports', 'the-events-calendar' ),
					);
				}
			}

			if ( isset( $_GET['post_status'] ) && self::$ignored_status === $_GET['post_status'] ) {
				$localize['archive'] = array(
					'delete_label' => esc_html__( 'Delete Permanently', 'the-events-calendar' ),
				);
			}

			$args = array(
				'localize' => array(
					'name' => 'tribe_ignore_events',
					'data' => $localize,
				),
			);

			tribe_asset( $plugin, 'tribe-ignored-events', 'admin-ignored-events.js', array( 'jquery' ), 'admin_enqueue_scripts', $args );
		}

		/**
		 * Filter the displayed bulk actions on the Ignored Events status
		 *
		 * @param   array $actions List of bulk actions
		 *
		 * @return  array
		 */
		public function filter_bulk_actions( $actions ) {
			if ( ! isset( $_GET['post_status'] ) || self::$ignored_status !== $_GET['post_status'] ) {
				return $actions;
			}

			$post_type_obj = get_post_type_object( Tribe__Events__Main::POSTTYPE );

			if ( isset( $actions['trash'] ) ) {
				unset( $actions['trash'] );
			}

			return $actions;
		}

		/**
		 * Makes sure that we have the Required Messages displaying correctly for the Legacy Events Warning
		 *
		 * @param  array $messages  Array of arrays, with the CPT messages for each status
		 * @param  array $counts    Array with the Counts of each one of the messages
		 *
		 * @return array
		 */
		public function filter_updated_messages( $messages, $counts ) {
			if ( ! Tribe__Admin__Helpers::instance()->is_post_type_screen( Tribe__Events__Main::POSTTYPE ) ) {
				return $messages;
			}

			if ( ! isset( $_GET['ids'] ) ) {
				return $messages;
			}

			$check_counts = array_filter( $counts );
			if ( empty( $check_counts ) ) {
				return $messages;
			}

			if ( ! isset( $counts['trashed'] ) || 0 === $counts['trashed'] ) {
				return $messages;
			}

			$ids           = (array) explode( ',', $_GET['ids'] );
			$ignored       = array_filter( $ids, array( $this, 'can_ignore' ) );
			$count_ignored = count( $ignored );

			if ( 0 >= $count_ignored ) {
				return $messages;
			}
			$counts['trashed'] -= $count_ignored;

			$messages[ Tribe__Events__Main::POSTTYPE ] = $messages['post'];

			// we are going to continue to use the language "posts" as that's what WordPress uses for custom post types in this messaging.
			if ( 0 === $counts['trashed'] ) {
				$messages[ Tribe__Events__Main::POSTTYPE ]['trashed'] = '%s ' . _n( 'post moved to Ignored.', 'posts moved to Ignored.', $count_ignored, 'the-events-calendar' );
			} else {
				$GLOBALS['bulk_counts'] = $counts;
				$messages[ Tribe__Events__Main::POSTTYPE ]['trashed'] = _n( '%s post moved to the Trash', '%s posts moved to the Trash', $counts['trashed'], 'the-events-calendar' ) . ' ';
				$messages[ Tribe__Events__Main::POSTTYPE ]['trashed'] .= sprintf( _n( 'and %s post moved to Ignored.', 'and %s posts moved to Ignored.', $count_ignored, 'the-events-calendar' ), $count_ignored );
			}

			$args = array(
				'ids'          => preg_replace( '/[^0-9,]/', '', $_REQUEST['ids'] ),
				'tribe-action' => 'tribe-restore',
				'post_type'    => Tribe__Events__Main::POSTTYPE,
			);
			$url = wp_nonce_url( add_query_arg( $args, 'edit.php' ), 'tribe-restore' );

			// Cant `esc_url` this URL because it will make sprintf throw a warning on WP code
			$messages[ Tribe__Events__Main::POSTTYPE ]['trashed'] .= ' <a href="' . urldecode( $url ) . '" class="tribe-restore-link">' . esc_html__( 'Undo', 'the-events-calendar' ) . '</a>';

			return $messages;
		}

		/**
		 * Returns the HTML for a notice depending on the if we have Legacy Items to be Migrated
		 *
		 * @return string
		 */
		public function render_notice_legacy() {
			if ( ! Tribe__Admin__Helpers::instance()->is_post_type_screen( Tribe__Events__Main::POSTTYPE ) ) {
				return false;
			}

			if ( empty( $_GET['post_status'] ) || $_GET['post_status'] !== self::$ignored_status ) {
				return false;
			}

			if ( ! $this->has_legacy_deleted_posts() ) {
				return false;
			}

			$html = '<p>' . esc_html__( 'Event Aggregator includes a new, better system for removing unwanted imported events from your calendar. Click the button below to transition previously deleted events. This process will remove unwanted records from your database and include recent or upcoming trashed events in your Ignored archive.', 'the-events-calendar' );
			$html .= ' <a href="https://theeventscalendar.com/knowledgebase/ignored-events/" target="_blank">' . esc_html_x( 'Read more about Ignored Events.', 'link to knowlegebase article', 'the-events-calendar' ) . '</a></p>';
			$html .= '<p style="display:inline-block;">' . get_submit_button( esc_html__( 'Migrate Legacy Ignored Events' ), 'secondary', 'tribe-migrate-legacy-events', false ) . '<span class="spinner"></span></p>';

			return Tribe__Admin__Notices::instance()->render( 'legacy-ignored-events', $html );
		}

		/**
		 * Action to Restore Events on the Single Page
		 *
		 * @param  WP_Screen $screen Which WP Screen we are currently in
		 *
		 * @return void|Redirect
		 */
		public function action_restore_events() {
			if ( ! isset( $_GET['action'] ) || 'tribe-restore' !== $_GET['action'] ) {
				return;
			}

			$event = get_post( absint( $_GET['post'] ) );

			if ( ! $event instanceof WP_Post ) {
				return;
			}

			if ( Tribe__Events__Main::POSTTYPE !== $event->post_type ) {
				return;
			}

			if ( self::$ignored_status !== $event->post_status ) {
				return;
			}

			if ( ! function_exists( 'wp_get_referer' ) ) {
				if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
					$sendback = $_SERVER['REQUEST_URI'];
				} elseif ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
					$sendback = $_REQUEST['_wp_http_referer'];
				} elseif ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
					$sendback = $_SERVER['HTTP_REFERER'];
				}
			} else {
				$sendback = wp_get_referer();
			}

			$sendback = remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'locked', 'ids' ), $sendback );

			if ( isset( $_REQUEST['ids'] ) ) {
				$post_ids = explode( ',', $_REQUEST['ids'] );
			} elseif ( ! empty( $_REQUEST['post'] ) ) {
				$post_ids = array_map( 'intval', (array) $_REQUEST['post'] );
			}

			$restored = 0;
			foreach ( (array) $post_ids as $post_id ) {
				if ( ! current_user_can( 'delete_post', $post_id ) ) {
					wp_die( esc_html__( 'You do not have permission to restore this post.', 'the-events-calendar' ) );
				}

				if ( ! $this->restore_event( $post_id ) ) {
					wp_die( esc_html__( 'Error restoring from Ignored Events.', 'the-events-calendar' ) );
				}

				$restored++;
			}
			$sendback = add_query_arg( 'restored', $restored, $sendback );

			wp_redirect( $sendback );
			exit;
		}

		/**
		 * Allows Bulk Actions to Work it's magic (more Complex than it needs to be)
		 *
		 * @return void|false
		 */
		public function action_restore_ignored() {
			if ( ! Tribe__Admin__Helpers::instance()->is_post_type_screen( Tribe__Events__Main::POSTTYPE ) ) {
				return false;
			}

			if ( ! isset( $_GET['ids'] ) ) {
				return false;
			}

			if ( ! isset( $_GET['tribe-action'] ) || 'tribe-restore' !== $_GET['tribe-action'] ) {
				return false;
			}

			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'tribe-restore' ) ) {
				return false;
			}

			$ids = (array) explode( ',', $_GET['ids'] );
			$restored = array();

			foreach ( $ids as $id ) {
				if ( ! current_user_can( 'delete_post', $id ) ) {
					wp_die( esc_html__( 'You do not have permission to restore this post.', 'the-events-calendar' ) );
				}

				$restore = $this->restore_event( $id );

				if ( ! $restore ) {
					wp_die( esc_html__( 'Error restoring from Ignored Events.', 'the-events-calendar' ) );
				}

				$restored[] = $restore;
			}

			$count_restored = count( $restored );

			$message = '<p>' . sprintf( _n( '%s post restored.', '%s posts restored.', $count_restored, 'the-events-calendar' ), $count_restored ) . '</p>';

			if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
				$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'tribe-action', '_wpnonce' ), $_SERVER['REQUEST_URI'] );
			} elseif ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
				$_REQUEST['_wp_http_referer'] = remove_query_arg( array( 'tribe-action', '_wpnonce' ), $_REQUEST['_wp_http_referer'] );
			} elseif ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
				$_SERVER['HTTP_REFERER'] = remove_query_arg( array( 'tribe-action', '_wpnonce' ), $_SERVER['HTTP_REFERER'] );
			}

			return tribe_notice( 'restored-events', $message, 'dismiss=1&type=success' );
		}

		/**
		 * Which Columns are Available on Ignored Events view
		 *
		 * @param  array $columns Columns and it's labels
		 *
		 * @return array
		 */
		public function filter_columns( $columns ) {
			if ( empty( $_GET['post_status'] ) || $_GET['post_status'] !== self::$ignored_status ) {
				return $columns;
			}

			// Remove unwanted Columns
			unset( $columns['author'], $columns['events-cats'], $columns['tags'], $columns['comments'], $columns['recurring'] );

			// Add New Columns backwards
			$columns = Tribe__Main::array_insert_after_key( 'title', $columns, array( 'source' => esc_html__( 'Source', 'the-events-calendar' ) ) );

			$info = ' <span class="dashicons dashicons-editor-help" title="' . esc_attr__( 'The last time this event was imported and/or updated via import.', 'the-events-calendar' ) . '"></span>';
			$columns = Tribe__Main::array_insert_after_key( 'title', $columns, array( 'last-import' => esc_html__( 'Last Import', 'the-events-calendar' ) . $info ) );

			return $columns;
		}

		/**
		 * Filters the Contents of the Columns for the Ignored Events View
		 *
		 * @param  string      $column Which column we are dealing with
		 * @param  int|WP_Post $post   WP Post ID or Object
		 *
		 * @return string|null|false
		 */
		public function action_column_contents( $column, $post ) {
			$record = Tribe__Events__Aggregator__Records::instance()->get_by_event_id( $post );

			if ( tribe_is_error( $record ) ) {
				return false;
			}

			$html = array();
			if ( 'source' === $column ) {
				$html[] = '<p>' . esc_html_x( 'via ', 'record via origin', 'the-events-calendar' ) . '<strong>' . $record->get_label() . '</strong></p>';
				if ( 'ea/ics' === $record->post->post_mime_type || 'ea/csv' === $record->post->post_mime_type ) {
					$file_path = get_attached_file( absint( $record->meta['file'] ) );
					$filename = basename( $file_path );
					$html[] = '<p>' . esc_html__( 'Source:', 'the-events-calendar' ) . ' <code>' . esc_html( $filename ) . '</code></p>';
				} else {
					$html[] = '<p>' . esc_html__( 'Source:', 'the-events-calendar' ) . ' <code>' . esc_html( $record->meta['source'] ) . '</code></p>';
				}
			} elseif ( 'last-import' === $column ) {
				$last_import = null;
				$original = $record->post->post_modified;
				$time = strtotime( $original );
				$now = current_time( 'timestamp' );

				$html[] = '<span title="' . esc_attr( $original ) . '">';
				if ( ( $now - $time ) <= DAY_IN_SECONDS ) {
					$diff = human_time_diff( $time, $now );
					if ( ( $now - $time ) > 0 ) {
						$html[] = sprintf( esc_html_x( 'about %s ago', 'human readable time ago', 'the-events-calendar' ), $diff );
					} else {
						$html[] = sprintf( esc_html_x( 'in about %s', 'in human readable time', 'the-events-calendar' ), $diff );
					}
				} else {
					$html[] = date( Tribe__Date_Utils::DATEONLYFORMAT, $time ) . '<br>' . date( Tribe__Date_Utils::TIMEFORMAT, $time );
				}

				$html[] = '</span>';
			}

			echo implode( "\r\n", (array) $html );
		}

		/**
		 * Add the required Row Actions for the Ignored Events View
		 *
		 * @param  array       $actions List of the current actions
		 * @param  int|WP_Post $post    WP Post ID or Object
		 *
		 * @return array
		 */
		public function filter_actions( $actions, $post ) {
			$event = get_post( $post );

			if ( ! $event instanceof WP_Post ) {
				return $actions;
			}

			if ( Tribe__Events__Main::POSTTYPE !== $event->post_type ) {
				return $actions;
			}
			$title = _draft_or_post_title();

			if ( self::$ignored_status !== $event->post_status ) {
				// Modify when it can be ignored
				if ( $this->can_ignore( $event ) ) {
					$actions['trash'] = sprintf(
						'<a href="%1$s" class="submitdelete" aria-label="%2$s" title="%3$s">%4$s</a>',
						get_delete_post_link( $event->ID ),
						/* translators: %s: post title */
						esc_attr( sprintf( __( 'Hide and Ignore &#8220;%s&#8221;', 'the-events-calendar' ), $title ) ),
						esc_attr__( 'Ignored events do not show on the calendar but can be updated with future imports', 'the-events-calendar' ),
						__( 'Hide & Ignore', 'the-events-calendar' )
					);
				}

				return $actions;
			}

			$origin = get_post_meta( $event->ID, '_EventOrigin', true );

			foreach ( $actions as $key => $html ) {
				/**
				 * @todo  Add logic to prevent removal of edit on EA origin
				 */
				if ( 'edit' === $key && true === false ) {
					continue;
				}

				// Remove all Actions on
				unset( $actions[ $key ] );
			}

			$post_type_object = get_post_type_object( $event->post_type );

			if ( current_user_can( 'delete_post', $event->ID ) ) {
				$actions['restore'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=tribe-restore', $event->ID ) ), 'restore-post_' . $event->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Restore &#8220;%s&#8221; from the Ignored', 'the-events-calendar' ), $title ) ),
					__( 'Restore', 'the-events-calendar' )
				);

				$actions['delete'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					get_delete_post_link( $event->ID, '', true ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Delete &#8220;%s&#8221; permanently', 'the-events-calendar' ), $title ) ),
					__( 'Delete Permanently', 'the-events-calendar' )
				);
			}

			return $actions;
		}

		/**
		 * Add the new Link to the Ignored events on the Events Page
		 *
		 * @param array $views Array of all the previous Views/Links
		 *
		 * @return array $views After adding the new Link
		 */
		public function filter_views( $views = array() ) {
			// This will prevent having the Ignored link twice on the Edit Page
			if ( $this->has_ignored_posts( false ) ) {
				return $views;
			}

			// Are there any legacy deleted posts?
			$counter = $this->count_legacy_deleted_posts();
			if ( 0 >= $counter ) {
				return $views;
			}

			$args = array(
				'post_type' => Tribe__Events__Main::POSTTYPE,
				'post_status' => self::$ignored_status,
			);

			$url = add_query_arg( $args, 'edit.php' );

			$views['import-deleted'] = '<a class="' . ( isset( $_GET['ignored_events'] ) ? 'current' : '' ) . '" href="' . esc_url( $url ) . '">' . esc_html__( 'Ignored', 'the-events-calendar' )
				. sprintf( ' <span class="count">(%d)</span></a>', $counter );

			return $views;
		}

		/**
		 * Count legacy "ignored" posts
		 *
		 * @return int
		 */
		public function count_legacy_deleted_posts() {
			// Fetch any posts on the `deleted_event` CPT
			$counter = wp_count_posts( self::$legacy_deleted_post );

			// Check if we have trash status set
			if ( ! isset( $counter->trash ) ) {
				return 0;
			}

			// Return it been a Int
			return absint( $counter->trash );
		}

		/**
		 * Check if there are any legacy posts
		 *
		 * @return boolean
		 */
		public function has_legacy_deleted_posts() {
			return $this->count_legacy_deleted_posts() > 0;
		}

		/**
		 * Check if there are any ignored posts
		 *
		 * @param  boolean $check_legacy If the method should also check legacy CPTs
		 * @return boolean
		 */
		public function has_ignored_posts( $check_legacy = true ) {
			$has_legacy = $this->has_legacy_deleted_posts();
			if ( true === $check_legacy && true === $has_legacy ) {
				return true;
			}

			$query = $this->get_query( array( 'fields' => 'ids' ) );

			return $query->have_posts();
		}

		/**
		 * Gets all ids for events that have been ignored
		 *
		 * @param null|array $data Array of event IDs/objects/data to check
		 *
		 * @return array
		 */
		public function get( $args = array() ) {
			$query = $this->get_query( $args );

			return $query->posts;
		}

		/**
		 * Gets all ids for events that have been ignored
		 *
		 * @param null|array $data Array of event IDs/objects/data to check
		 *
		 * @return WP_Query
		 */
		public function get_query( $args = array() ) {
			$defaults = array(
				'post_type' => Tribe__Events__Main::POSTTYPE,
				'post_status' => self::$ignored_status,
			);

			$args = wp_parse_args( $args, $defaults );

			return new WP_Query( $args );
		}

		/**
		 * Gets all ids for events that have been ignored
		 *
		 * @param null|array $data Array of event IDs/objects/data to check
		 * @param array $args WP_Query args
		 *
		 * @return array
		 */
		public function get_by_id( $data = array(), $args = array() ) {
			// if there isn't any data, there won't be any results. Bail
			if ( empty( $data ) ) {
				return array();
			}

			// fields we'll look for in objects/arrays
			$search = array(
				'ID',
				'id',
				'post_id',
			);

			$ids = array();

			// if the passed in data is not an array, turn it into one
			if ( ! is_array( $data ) ) {
				$data = array( $data );
			}

			$first = reset( $data );

			if ( is_scalar( $first ) ) {
				$ids = $data;
			} else {
				$id_field = null;
				$first = (object) $first;

				// look through the object for one of the possible ID fields and bail when/if we find it
				foreach ( $search as $field ) {
					if ( empty( $first->$field ) ) {
						continue;
					}

					$id_field = $field;
					break;
				}

				// if we've found an ID field, let's generate an array of those IDs
				if ( $id_field ) {
					$ids = wp_list_pluck( $data, $id_field );
				}
			}

			// if there aren't any IDs, let's make sure we don't get any results
			if ( empty( $ids ) ) {
				return array();
			}

			if ( isset( $args['post__in'] ) ) {
				$args['post__in'] = array_merge( $args['post__in'], $ids );
			} else {
				$args['post__in'] = $ids;
			}

			return $this->get( $args );
		}

		/**
		 * Changes the event to the correct Post Status
		 *
		 * @param  int|WP_Post       $event Which event try to convert
		 * @return bool|int|WP_Error
		 */
		public function ignore_event( $event, $force = false ) {
			$event = get_post( $event );

			if ( ! $force && ! $this->can_ignore( $event ) ) {
				return false;
			}

			// Update only what we need
			$arguments = array(
				'ID' => $event->ID,
				'post_type' => Tribe__Events__Main::POSTTYPE,
				'post_status' => self::$ignored_status,
			);

			// Set the Required Meta to flag it to the Legacy Posts
			if ( self::$legacy_deleted_post === $event->post_type ) {
				update_post_meta( $event->ID, '_tribe_legacy_ignored_event', 1 );
			}

			// Try to update back to the Event CPT
			$updated = wp_update_post( $arguments );

			// Saves on a meta the previous Status
			if ( $updated && 'trash' !== $event->post_status ) {
				update_post_meta( $event->ID, self::$key_previous_status, $event->post_status );
			}

			return $updated;
		}

		/**
		 * Verify if we can Ignore an Event depending on all the Required rules
		 *
		 * @param  int|WP_Post $post ID of the Event or it's Object
		 *
		 * @return bool
		 */
		public function can_ignore( $post ) {
			$event = get_post( $post );

			// If we don't have a post (weird) we also leave
			if ( ! $event instanceof WP_Post ) {
				return false;
			}

			// Verify if it's a Legacy Ignore or Tribe Event
			if ( ! in_array( $event->post_type, array( Tribe__Events__Main::POSTTYPE, self::$legacy_deleted_post ) ) ) {
				return false;
			}

			$ignored_origins = array(
				Tribe__Events__Aggregator__Event::$event_origin,
				'facebook-importer',
				'ical-importer',
			);
			$origin = get_post_meta( $event->ID, '_EventOrigin', true );

			// Verify the Origin
			if ( ! in_array( $origin, $ignored_origins ) ) {
				return false;
			}

			if ( Tribe__Events__Aggregator__Event::$event_origin === $origin ) {
				$aggregator_origin = get_post_meta( $event->ID, Tribe__Events__Aggregator__Event::$origin_key, true );
			}

			return true;
		}

		/**
		 * Restore Event
		 *
		 * @param  int|WP_Post       $event Which event try to convert
		 * @return bool|int|WP_Error
		 */
		public function restore_event( $event ) {
			$event = get_post( $event );

			if ( ! $event instanceof WP_Post ) {
				return false;
			}

			// If we are not in the Event CPT we don't care either
			if ( Tribe__Events__Main::POSTTYPE !== $event->post_type ) {
				return null;
			}

			$restore_status = get_post_meta( $event->ID, self::$key_previous_status, true );

			if ( empty( $restore_status ) ) {
				/**
				 * Which is the default Post status to Restore Ignored Events
				 *
				 * @param  string   $post_status
				 * @param  WP_Post  $event
				 */
				$restore_status = apply_filters( 'tribe_events_ignored_events_default_restore_status', 'publish', $event );
			}

			// Update only what we need
			$arguments = array(
				'ID' => $event->ID,
				'post_status' => $restore_status,
			);

			// Try to update back to the Event CPT
			$updated = wp_update_post( $arguments );

			// Delete the Previous status stored
			if ( $updated ) {
				delete_post_meta( $event->ID, self::$key_previous_status );
			}

			return $updated;
		}

		/**
		 * Register the Ignored Post Status
		 *
		 * @return void
		 */
		public function register_ignored_post_status() {
			$arguments = array(
				'label'                     => esc_html__( 'Ignored', 'the-events-calendar' ),
				'label_count'               => _n_noop( 'Ignored <span class="count">(%s)</span>', 'Ignored <span class="count">(%s)</span>', 'the-events-calendar' ),
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => true,
				'public'                    => false,
				'internal'                  => false,
			);
			register_post_status( self::$ignored_status, $arguments );

			// We need to register this to have the legacy work
			register_post_type( self::$legacy_deleted_post, array(
				'public' => false,
			) );
		}

		/**
		 * Making sure that we have the previous Status saved
		 *
		 * @since  4.5.13
		 *
		 * @param  int|WP_Post  $event  Which event to track the Previous status
		 *
		 * @return bool
		 */
		public function action_track_previous_status( $event ) {
			$event = get_post( $event );

			if ( ! $event instanceof WP_Post ) {
				return false;
			}

			// If we are not in the Event CPT we don't care either
			if ( Tribe__Events__Main::POSTTYPE !== $event->post_type ) {
				return false;
			}

			if ( self::$ignored_status === $event->post_type ) {
				return false;
			}

			if ( 'trash' === $event->post_type ) {
				return false;
			}

			return update_post_meta( $event->ID, self::$key_previous_status, $event->post_status );
		}

		/**
		 * On version 4.4 of WP we get a new Filter to prevent an event from been trashed and/or deleted
		 *
		 * @param  null|bool $check        Boolean or Null depending if we need to delete or not
		 * @param  int       $post         WP Post ID
		 * @param  bool      $force_delete Force the Event delete
		 *
		 * @return null|bool
		 */
		public function action_pre_delete_event( $unused_check, $post, $force_delete ) {
			// If someone is trying to delete it for-reals we actually delete it.
			if ( true === $force_delete ) {
				return null;
			}

			$post = get_post( $post );

			if ( self::$ignored_status === $post->post_status ) {
				return wp_delete_post( $post->ID, true );
			}

			// Important to note that this needs to return null for any invalid ignoring
			if ( ! $this->can_ignore( $post ) ) {
				return null;
			}

			$status = $this->ignore_event( $post );

			// If we couldn't convert we actually trash it
			return ! is_wp_error( $status ) ? $status : null;
		}

		/**
		 * Used to get an Trashed event and move it to the `post_status` of Ignored
		 *
		 * @param  int $post ID of the Post
		 *
		 * @return bool|null
		 */
		public function action_from_trash_to_ignored( $post ) {
			$post = get_post( $post );

			if ( self::$ignored_status === $post->post_status ) {
				return wp_delete_post( $post->ID, true );
			}

			// Important to note that this needs to return null for any invalid ignoring
			if ( ! $this->can_ignore( $post ) ) {
				return null;
			}

			$status = $this->ignore_event( $post );

			// If we couldn't convert we actually trash it
			return ! is_wp_error( $status ) ? $status : null;
		}

		/**
		 * Method that Handles the AJAX converting of Legacy Ignored Events to the new `post_status`
		 * AJAX methods will not return anything, only print a JSON string
		 *
		 * @return void
		 */
		public function ajax_convert_legacy_ignored_events() {
			$response = (object) array(
				'status' => false,
				'text' => esc_html__( 'Error, a unknown bug happened and it was impossible to migrate the Legacy Ignored Events, try again later.', 'the-events-calendar' ),
			);

			$post_type = get_post_type_object( Tribe__Events__Main::POSTTYPE );

			if ( empty( $post_type->cap->edit_posts ) || ! current_user_can( $post_type->cap->edit_posts ) ) {
				$response->status = false;
				$response->text = esc_html__( 'You do not have permission to migrate Legacy Ignored Events', 'the-events-calendar' );

				wp_send_json( $response );
			}

			if ( ! $this->has_legacy_deleted_posts() ) {
				$response->status = true;
				$response->text = esc_html__( 'There were no Legacy Events to be Migrated, you are ready to rock!', 'the-events-calendar' );

				wp_send_json( $response );
			}

			$args = array(
				'post_type' => self::$legacy_deleted_post,
				'post_status' => 'trash',
			);
			$query = new WP_Query( $args );

			foreach ( $query->posts as $event ) {
				$status = $this->ignore_event( $event, true );
				if ( is_wp_error( $status ) ) {
					$response->error[ $event->ID ] = $status->get_error_message();
				} else {
					$response->migrated[ $event->ID ] = true;
				}
			}

			if ( ! empty( $response->error ) ) {
				if ( ! empty( $response->migrated ) ) {
					$response->status = false;
					$response->text = sprintf(
						_n(
							'Migration: %d Legacy Ignored Post was migrated but %d failed. To see the migrated event you will first need to refresh this screen.',
							'Migration: %d Legacy Ignored Posts were migrated but %d failed. To see the migrated events you will first need to refresh this screen.',
							count( $response->migrated ),
							'the-events-calendar'
						),
						count( $response->migrated ),
						count( $response->error )
					);
				} else {
					$response->status = false;
					$response->text = sprintf(
						_n(
							'Migration: %d Legacy Ignored Post failed to be migrated.',
							'Migration: %d Legacy Ignored Posts failed to be migrated.',
							count( $response->error ),
							'the-events-calendar'
						),
						count( $response->error )
					);
				}

				$response->text .= '<ul>';
				foreach ( $response->error as $ID => $message ) {
					$response->text .= '<li>' . sprintf( __( 'Event %d: %s', 'the-events-calendar' ), $ID, $message ) . '</li>';
				}
				$response->text .= '</ul>';

			} elseif ( ! empty( $response->migrated ) ) {
				$response->status = true;
				$response->text = sprintf(
					_n(
						'Migration: %d Legacy Ignored Post was migrated successfully. To see the migrated event you will first need to refresh this screen.',
						'Migration: %d Legacy Ignored Posts were migrated successfully. To see the migrated events you will first need to refresh this screen.',
						count( $response->migrated ),
						'the-events-calendar'
					),
					count( $response->migrated )
				);
			}

			wp_send_json( $response );
		}

		/**
		 * Hooks the filters and actions needed for the class to work.
		 *
		 * @return bool Whether the filters and actions were hooked or not.
		 */
		public function hook() {
			add_action( 'init', array( $this, 'register_ignored_post_status' ) );
			add_action( 'current_screen', array( $this, 'action_restore_events' ) );
			add_action( 'current_screen', array( $this, 'action_restore_ignored' ) );

			/**
			 * `pre_delete_post` only exists after WP 4.4
			 *
			 * @see https://core.trac.wordpress.org/ticket/12706
			 */
			add_filter( 'pre_delete_post', array( $this, 'action_pre_delete_event' ), 10, 3 );
			add_action( 'trashed_post', array( $this, 'action_from_trash_to_ignored' ) );
			add_action( 'wp_trash_post', array( $this, 'action_track_previous_status' ) );

			add_filter( 'views_edit-' . Tribe__Events__Main::POSTTYPE, array( $this, 'filter_views' ) );
			add_filter( 'bulk_actions-edit-' . Tribe__Events__Main::POSTTYPE, array( $this, 'filter_bulk_actions' ), 15 );
			add_filter( 'post_row_actions', array( $this, 'filter_actions' ), 10, 2 );

			add_filter( 'manage_' . Tribe__Events__Main::POSTTYPE . '_posts_columns', array( $this, 'filter_columns' ), 100 );
			add_action( 'manage_' . Tribe__Events__Main::POSTTYPE . '_posts_custom_column', array( $this, 'action_column_contents' ), 100, 2 );

			add_action( 'wp_ajax_tribe_convert_legacy_ignored_events', array( $this, 'ajax_convert_legacy_ignored_events' ) );

			// Modify Success messages
			add_filter( 'bulk_post_updated_messages', array( $this, 'filter_updated_messages' ), 10, 2 );

			// Register assets
			add_action( 'init', array( $this, 'action_assets' ) );

			/**
			 * Register Notices
			 */
			tribe_notice( 'legacy-ignored-events', array( $this, 'render_notice_legacy' ), 'dismiss=1&type=warning' );

			return true;
		}
	}
}