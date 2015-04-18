<?php
/**
 * Controls Tribe Events Calendar admin list views for events
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Admin_List' ) ) {
	class Tribe__Events__Admin_List {
		protected static $start_col_active = true;
		protected static $end_col_active = true;
		protected static $start_col_first = true;

		/**
		 * The init function for this class, adds actions and filters.
		 *
		 * @return void
		 */
		public static function init() {
			if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				add_filter( 'posts_join', array( __CLASS__, 'events_search_join' ), 10, 2 );
				add_filter( 'posts_orderby', array( __CLASS__, 'events_search_orderby' ), 10, 2 );
				add_filter( 'posts_fields', array( __CLASS__, 'events_search_fields' ), 10, 2 );
				add_filter( 'post_limits', array( __CLASS__, 'events_search_limits' ), 10, 2 );
				add_filter(
					'manage_' . Tribe__Events__Main::POSTTYPE . '_posts_columns', array(
						__CLASS__,
						'column_headers'
					)
				);
				add_filter(
					'tribe_apm_headers_' . Tribe__Events__Main::POSTTYPE, array(
						__CLASS__,
						'column_headers_check'
					), 10, 1
				);
				add_filter( 'views_edit-tribe_events', array( __CLASS__, 'update_event_counts' ) );
				add_action( 'manage_posts_custom_column', array( __CLASS__, 'custom_columns' ), 10, 2 );
				add_action(
					'manage_edit-' . Tribe__Events__Main::POSTTYPE . '_sortable_columns', array(
						__CLASS__,
						'register_date_sortables'
					), 10, 2
				);

			}
		}

		/**
		 * Fields filter for standard wordpress templates.  Adds the start and end date to queries in the
		 * events category
		 *
		 * @param string   $fields The current fields query part.
		 * @param WP_Query $query
		 *
		 * @return string The modified form.
		 */
		public static function events_search_fields( $fields, $query ) {
			if ( ! $query->is_main_query() || $query->get( 'post_type' ) != Tribe__Events__Main::POSTTYPE ) {
				return $fields;
			}
			global $wpdb;
			$fields .= ", eventStart.meta_value as EventStartDate, eventEnd.meta_value as EventEndDate ";

			return $fields;
		}

		/**
		 * Join filter for admin queries
		 *
		 * @param $join
		 * @param $query WP_Query
		 *
		 * @return string modified join clause
		 */
		public static function events_search_join( $join, $query ) {
			global $wpdb;
			if ( ! $query->is_main_query() || $query->get( 'post_type' ) != Tribe__Events__Main::POSTTYPE ) {
				return $join;
			}

			$join .= " LEFT JOIN {$wpdb->postmeta} as eventStart ON ( {$wpdb->posts}.ID = eventStart.post_id AND eventStart.meta_key = '_EventStartDate' ) ";
			$join .= " LEFT JOIN {$wpdb->postmeta} as eventEnd ON ( {$wpdb->posts}.ID = eventEnd.post_id AND eventEnd.meta_key = '_EventEndDate' ) ";

			return $join;
		}

		/**
		 * orderby filter for standard admin queries
		 *
		 * @param          string orderby
		 * @param WP_QUery $query
		 *
		 * @return string modified orderby clause
		 */
		public static function events_search_orderby( $orderby_sql, $query ) {
			global $wpdb;
			if ( ! $query->is_main_query() || $query->get( 'post_type' ) != Tribe__Events__Main::POSTTYPE ) {
				return $orderby_sql;
			}


			$endDateSQL = " eventEnd.meta_value ";
			$order      = $query->get( 'order' ) ? $query->get( 'order' ) : 'asc';
			$orderby    = $query->get( 'orderby' ) ? $query->get( 'orderby' ) : 'start-date';
			if ( $orderby == 'event_date' ) {
				$orderby = 'start-date';
			};

			if ( $orderby == 'start-date' ) {
				$orderby_sql = " eventStart.meta_value " . $order . ', ' . $endDateSQL . $order;
			} else {
				if ( $orderby == 'end-date' ) {
					$orderby_sql = $endDateSQL . $order . ", eventStart.meta_value " . $order;
				}
			}

			return $orderby_sql;
		}

		/**
		 * limit filter for admin queries
		 *
		 * @param          string limits clause
		 * @param WP_Query $query
		 *
		 * @return string modified limits clause
		 */
		public static function events_search_limits( $limits, $query ) {
			if ( ! $query->is_main_query() || $query->get( 'post_type' ) != Tribe__Events__Main::POSTTYPE || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return $limits;
			}
			global $current_screen;
			$paged = (int) $query->get( 'paged' );
			if ( empty( $paged ) ) {
				$paged = 1;
			}
			if ( is_admin() ) {
				$option   = str_replace( '-', '_', "{$current_screen->id}_per_page" );
				$per_page = get_user_option( $option );
				$per_page = ( $per_page ) ? (int) $per_page : 20; // 20 is default in backend
			} else {
				$per_page = (int) tribe_get_option( 'postsPerPage', 10 );
			}

			$page_start = ( $paged - 1 ) * $per_page;
			$limits     = 'LIMIT ' . $page_start . ', ' . $per_page;

			return $limits;
		}

		/**
		 * Add the proper column headers.
		 *
		 * @param array $columns The columns.
		 *
		 * @return array The modified column headers.
		 */
		public static function column_headers( $columns ) {
			$events_label_singular = tribe_get_event_label_singular();

			foreach ( (array) $columns as $key => $value ) {
				$mycolumns[$key] = $value;
				if ( $key == 'author' ) {
					$mycolumns['events-cats'] = sprintf( __( '%s Categories', 'tribe-events-calendar' ), $events_label_singular );
				}
			}
			$columns = $mycolumns;

			unset( $columns['date'] );
			$columns['start-date'] = __( 'Start Date', 'tribe-events-calendar' );
			$columns['end-date']   = __( 'End Date', 'tribe-events-calendar' );

			return $columns;
		}

		/**
		 * This will only be fired if Advanced Post Manger is active
		 * Helps ensure dates show correctly if only one or the other of
		 * start & end date columns is showing
		 */
		public static function column_headers_check( $columns ) {
			$active                 = array_keys( $columns );
			self::$end_col_active   = in_array( 'end-date', $active );
			self::$start_col_active = in_array( 'start-date', $active );
			// What if, oddly, end_col is placed first when both are active?
			if ( self::$end_col_active && self::$start_col_active ) {
				self::$start_col_first = ( array_search( 'start-date', $active ) < array_search( 'end-date', $active ) );
			}
		}

		/**
		 * Make it so events can be sorted by start and end dates.
		 *
		 * @param array $columns The columns array.
		 *
		 * @return array The modified columns array.
		 */
		public static function register_date_sortables( $columns ) {
			$columns['start-date'] = 'start-date';
			$columns['end-date']   = 'end-date';

			return $columns;
		}

		/**
		 * Add the custom columns.
		 *
		 * @param string $column_id The custom column id.
		 * @param int    $post_id   The post id for the data.
		 *
		 * @return void
		 */
		public static function custom_columns( $column_id, $post_id ) {
			if ( $column_id == 'events-cats' ) {
				$event_cats = get_the_term_list( $post_id, Tribe__Events__Main::TAXONOMY, '', ', ', '' );
				echo ( $event_cats ) ? strip_tags( $event_cats ) : '—';
			}
			if ( $column_id == 'start-date' ) {
				echo tribe_get_start_date( $post_id, false );
			}
			if ( $column_id == 'end-date' ) {
				echo tribe_get_end_date( $post_id, false );
			}
		}

		/**
		 * Update event counts.
		 *
		 * @param array $counts The counts array.
		 *
		 * @return array The modified counts array.
		 */
		public static function update_event_counts( $counts ) {
			global $post_type, $post_type_object, $locked_post_status, $avail_post_stati;

			$num_posts = self::count_events();

			$total_posts = array_sum( (array) $num_posts );

			foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state ) {
				$total_posts -= $num_posts->$state;
			}

			$counts['all'] = "<a href='edit.php?post_type=tribe_events' class='current'>" . sprintf( __( 'All %s', 'tribe-events-calendar' ), "<span class='count'>($total_posts)</span>" ) . "</a>";

			foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status ) {
				$class = '';

				$status_name = $status->name;

				if ( ! in_array( $status_name, $avail_post_stati ) ) {
					continue;
				}

				if ( empty( $num_posts->$status_name ) ) {
					continue;
				}

				if ( isset( $_REQUEST['post_status'] ) && $status_name == $_REQUEST['post_status'] ) {
					$class = ' class="current"';
				}

				$counts[$status_name] = "<a href='edit.php?post_status=$status_name&amp;post_type=$post_type'$class>" . sprintf( translate_nooped_plural( $status->label_count, $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) . '</a>';
			}

			return $counts;
		}

		/**
		 * Taken from wp_count_posts.
		 *
		 * @return mixed The results.
		 */
		private static function count_events() {
			$type = Tribe__Events__Main::POSTTYPE;
			$perm = 'readable';

			global $wpdb;

			$user = wp_get_current_user();

			$cache_key = $type;

			$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts}";
			$query .= " LEFT JOIN {$wpdb->postmeta} as eventStart ON( {$wpdb->posts}.ID = eventStart.post_id AND eventStart.meta_key = '_EventStartDate') ";
			$query .= " WHERE post_type = %s";
			if ( 'readable' == $perm && is_user_logged_in() ) {
				$post_type_object = get_post_type_object( $type );
				if ( ! current_user_can( $post_type_object->cap->read_private_posts ) ) {
					$cache_key .= '_' . $perm . '_' . $user->ID;
					$query .= " AND (post_status != 'private' OR ( post_author = '$user->ID' AND post_status = 'private' ))";
				}
			}
			$query .= ' GROUP BY post_status';

			$count = wp_cache_get( $cache_key, 'counts' );
			$count = false;
			if ( false !== $count ) {
				return $count;
			}

			$count = $wpdb->get_results( $wpdb->prepare( $query, $type ), ARRAY_A );

			$stats = array();
			foreach ( get_post_stati() as $state ) {
				$stats[$state] = 0;
			}

			foreach ( (array) $count as $row ) {
				$stats[$row['post_status']] = $row['num_posts'];
			}

			$stats = (object) $stats;
			wp_cache_set( $cache_key, $stats, 'counts' );

			return $stats;
		}
	}
}
