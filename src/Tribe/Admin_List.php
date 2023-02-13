<?php
/**
 * Controls Tribe Events Calendar admin list views for events
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

use Tribe__Events__Main as TEC;

if ( ! class_exists( 'Tribe__Events__Admin_List' ) ) {
	class Tribe__Events__Admin_List {
		protected static $start_col_active = true;
		protected static $end_col_active = true;
		protected static $start_col_first = true;

		/**
		 * The init function for this class, adds actions and filters.
		 *
		 */
		public static function init() {
			if ( is_admin() ) {
				if ( ! tribe( 'context' )->doing_ajax() ) {
					// Logic for filtering events by aggregator record.
					add_filter( 'posts_clauses', [ __CLASS__, 'filter_by_aggregator_record' ], 9, 2 );

					// Logic for sorting events by event category or tags
					add_filter( 'posts_clauses', [ __CLASS__, 'sort_by_tax' ], 10, 2 );

					// Logic for sorting events by start or end date
					add_filter( 'posts_clauses', [ __CLASS__, 'sort_by_event_date' ], 11, 2 );

					add_filter( 'posts_fields', [ __CLASS__, 'events_search_fields' ], 10, 2 );

					// Pagination
					add_filter( 'post_limits', [ __CLASS__, 'events_search_limits' ], 10, 2 );

					add_filter(
						'tribe_apm_headers_' . Tribe__Events__Main::POSTTYPE,
						[ __CLASS__, 'column_headers_check' ]
					);

					add_filter( 'views_edit-tribe_events', [ __CLASS__, 'update_event_counts' ] );
				}

				/**
				 * The following actions will need to be fired on AJAX calls, the logic above is required.
				 *
				 * Registers custom event columns category/start date/end date
				 */
				add_action( 'manage_posts_custom_column', [ __CLASS__, 'custom_columns' ], 10, 2 );
				add_filter(
					'manage_' . Tribe__Events__Main::POSTTYPE . '_posts_columns',
					[ __CLASS__, 'column_headers' ]
				);

				// Registers event start/end date as sortable columns
				add_action(
					'manage_edit-' . Tribe__Events__Main::POSTTYPE . '_sortable_columns',
					[ __CLASS__, 'register_sortable_columns' ],
					10,
					2
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

			$fields .= ', tribe_event_start_date.meta_value as EventStartDate, tribe_event_end_date.meta_value as EventEndDate ';

			return $fields;
		}

		/**
		 * Sets whether sorting will be ascending or descending based on input
		 *
		 * @param   WP_Query    $wp_query   Query for a library post type
		 *
		 * @return  string                  ASC/DESC prefixed with a single space
		 */
		public static function get_sort_direction( WP_Query $wp_query ) {
			return 'ASC' == strtoupper( $wp_query->get( 'order' ) ) ? 'ASC' : 'DESC';
		}

		/**
		 * Defines custom logic for sorting events table by start/end date. No matter how user selects
		 * what should be is sorted, always include date sorting in some fashion
		 *
		 * @param   Array       $clauses    SQL clauses for fetching posts
		 * @param   WP_Query    $wp_query   A paginated query for items
		 *
		 * @return  Array                   Modified SQL clauses
		 */
		public static function sort_by_event_date( Array $clauses, WP_Query $wp_query ) {
			// bail if this is not a query for event post type
			if ( $wp_query->get( 'post_type' ) !== Tribe__Events__Main::POSTTYPE ) {
				return $clauses;
			}

			global $wpdb;

			$sort_direction = self::get_sort_direction( $wp_query );

			// only add the start meta query if it is missing
			if ( ! preg_match( '/tribe_event_start_date/', $clauses['join'] ) ) {
				$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS tribe_event_start_date ON {$wpdb->posts}.ID = tribe_event_start_date.post_id AND tribe_event_start_date.meta_key = '_EventStartDate' ";
			}

			// only add the end meta query if it is missing
			if ( ! preg_match( '/tribe_event_end_date/', $clauses['join'] ) ) {
				$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS tribe_event_end_date ON {$wpdb->posts}.ID = tribe_event_end_date.post_id AND tribe_event_end_date.meta_key = '_EventEndDate' ";
			}

			$append_orderby   = false;
			$original_orderby = null;

			if ( ! empty( $clauses['orderby'] ) ) {
				$original_orderby = $clauses['orderby'];

				// if the ONLY orderby clause is the post date, then let's move that toss move that to the
				// end of the orderby. This will forever make post_date play second fiddle to the event start/end dates
				// and that's ok
				$append_orderby = preg_match( '/^[a-zA-Z0-9\-_]+\.post_date (DESC|ASC)$/i', $original_orderby );
			}

			$start_orderby = "tribe_event_start_date.meta_value {$sort_direction}";
			$end_orderby   = "tribe_event_end_date.meta_value {$sort_direction}";

			$date_orderby = "{$start_orderby}, {$end_orderby}";

			if ( ! empty( $wp_query->query['orderby'] ) && 'end-date' == $wp_query->query['orderby'] ) {
				$date_orderby = "{$end_orderby}, {$start_orderby}";
			}

			// Add the date orderby rules *before* any pre-existing orderby rules (to stop them being "trumped")
			if ( empty( $original_orderby ) ) {
				$revised_orderby = $date_orderby;
			} elseif ( $append_orderby ) {
				$revised_orderby = "$date_orderby, $original_orderby";
			} else {
				$revised_orderby = "$original_orderby, $date_orderby";
			}

			$clauses['orderby'] = $revised_orderby;

			return $clauses;
		}

		/**
		 * Defines custom logic for filtering events table by aggregator record.
		 *
		 * @param array<string> $clauses    SQL clauses for fetching posts.
		 * @param WP_Query      $wp_query   A paginated query for items.
		 *
		 * @return array<string>            Modified SQL clauses.
		 */
		public static function filter_by_aggregator_record( array $clauses, WP_Query $wp_query ) {
			// Check for event post type.
			if ( $wp_query->get( 'post_type' ) !== TEC::POSTTYPE ) {
				return $clauses;
			}

			// Check if filtering by aggregator record.
			$parent_record_id = (int) tribe_get_request_var( 'aggregator_record', 0 );
			if ( 0 >= $parent_record_id ) {
				return $clauses;
			}

			global $wpdb;

			$table_alias = 'ea_record_' . substr( uniqid( 'ea_record', true ), 0, 10 );
			// Add the record meta query if it is missing.
			if ( ! preg_match( '/\\s' . preg_quote( $table_alias, '/' ) . '\\s/', $clauses['join'] ) ) {
				$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS {$table_alias} ON {$wpdb->posts}.ID = {$table_alias}.post_id AND {$table_alias}.meta_key = '_tribe_aggregator_record' ";
			}

			// Add the record meta filter if it is missing.
			if ( ! preg_match( '/\\s' . preg_quote( $table_alias , '/' ) . '\\s/', $clauses['where'] ) ) {
				$sub_query = $wpdb->prepare( "SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_parent = %d", $parent_record_id );
				$clauses['where'] .= " AND {$table_alias}.meta_value IN ( {$sub_query} ) ";
			}

			return $clauses;
		}

		/**
		 * Defines custom logic for sorting events table by category or tags
		 *
		 * @param   Array       $clauses    SQL clauses for fetching posts
		 * @param   WP_Query    $wp_query   A paginated query for items
		 *
		 * @return  Array                   Modified SQL clauses
		 */
		public static function sort_by_tax( Array $clauses, WP_Query $wp_query ) {
			if ( ! isset( $wp_query->query['orderby'] ) ) {
				return $clauses;
			}

			switch ( $wp_query->query['orderby'] ) {
				case 'events-cats':
					$taxonomy = Tribe__Events__Main::TAXONOMY;
				break;

				case 'tags':
					$taxonomy = 'post_tag';
				break;

				default:
					return $clauses;
				break;
			}

			global $wpdb;

			// collect the terms in the desired taxonomy for the given post into a single string
			$smashed_terms_sql = "
				SELECT
					GROUP_CONCAT( $wpdb->terms.name ORDER BY name ASC ) smashed_terms
				FROM
					$wpdb->term_relationships
					LEFT JOIN $wpdb->term_taxonomy ON (
						$wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id
						AND taxonomy = '%s'
					)
					LEFT JOIN $wpdb->terms ON (
						$wpdb->term_taxonomy.term_id = $wpdb->terms.term_id
					)
				WHERE $wpdb->term_relationships.object_id = $wpdb->posts.ID
			";

			$smashed_terms_sql = $wpdb->prepare( $smashed_terms_sql, $taxonomy );

			$clauses['fields'] .= ",( {$smashed_terms_sql} ) as smashed_terms ";
			$clauses['orderby'] = 'smashed_terms ' . self::get_sort_direction( $wp_query );
			return $clauses;
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
			if ( ! $query->is_main_query() || $query->get( 'post_type' ) != Tribe__Events__Main::POSTTYPE ) {
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
				$mycolumns[ $key ] = $value;
				if ( $key == 'author' ) {
					$mycolumns['events-cats'] = sprintf( esc_html__( '%s Categories', 'the-events-calendar' ), $events_label_singular );
				}
			}
			$columns = $mycolumns;

			unset( $columns['date'] );
			$columns['start-date'] = esc_html__( 'Start Date', 'the-events-calendar' );
			$columns['end-date']   = esc_html__( 'End Date', 'the-events-calendar' );

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
		 * Allows events to be sorted by start date/end date/category/tags
		 *
		 * @param array $columns The columns array.
		 *
		 * @return array The modified columns array.
		 */
		public static function register_sortable_columns( $columns ) {
			foreach ( [ 'events-cats', 'tags', 'start-date', 'end-date' ] as $sortable ) {
				$columns[ $sortable ] = $sortable;
			}

			return $columns;
		}

		/**
		 * Add the custom columns.
		 *
		 * @param string $column_id The custom column id.
		 * @param int    $post_id   The post id for the data.
		 *
		 */
		public static function custom_columns( $column_id, $post_id ) {
			switch ( $column_id ) {
				case 'events-cats':
					if ( ! taxonomy_exists( Tribe__Events__Main::TAXONOMY ) ) {
						return [];
					}

					$event_cats = wp_get_post_terms(
						$post_id,
						Tribe__Events__Main::TAXONOMY,
						[
							'fields' => 'names',
						]
					);
					$categories_list = '-';
					if ( is_array( $event_cats ) ) {
						$event_cats = array_values( array_filter( $event_cats, static function ( $event_cat ) {
							return is_string( $event_cat ) && $event_cat !== '';
						} ) );

						$categories_list = implode( ', ', $event_cats );
					}
					echo esc_html( $categories_list );
				break;

				case 'start-date':
					echo tribe_get_start_date( $post_id, false );
				break;

				case 'end-date':
					echo tribe_get_display_end_date( $post_id, false );
				break;
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

			foreach ( get_post_stati( [ 'show_in_admin_all_list' => false ] ) as $state ) {
				$total_posts -= $num_posts->$state;
			}

			$counts['all'] = "<a href='edit.php?post_type=tribe_events' class='current'>" . sprintf( esc_html_x( 'All %s', '%s Event count in admin list', 'the-events-calendar' ), "<span class='count'>({$total_posts})</span>" ) . '</a>';

			foreach ( get_post_stati( [ 'show_in_admin_status_list' => true ], 'objects' ) as $status ) {
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

				$counts[ $status_name ] = "<a href='edit.php?post_status=$status_name&amp;post_type=$post_type'$class>" . sprintf( translate_nooped_plural( $status->label_count, $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) . '</a>';
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
			$query .= ' WHERE post_type = %s';
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

			$stats = [];
			foreach ( get_post_stati() as $state ) {
				$stats[ $state ] = 0;
			}

			foreach ( (array) $count as $row ) {
				$stats[ $row['post_status'] ] = $row['num_posts'];
			}

			$stats = (object) $stats;
			wp_cache_set( $cache_key, $stats, 'counts' );

			return $stats;
		}
	}
}
