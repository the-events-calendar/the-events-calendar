<?php
/**
 * Controls Tribe Events Calendar admin list views for events
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if (!class_exists('TribeEventsAdminList')) {
	class TribeEventsAdminList {
		public static $events_list;
		protected static $start_col_active = true;
		protected static $end_col_active = true;
		protected static $start_col_first = true;
	
		public static function init() {
			if ( is_admin() ) {
				add_filter( 'posts_distinct', array( __CLASS__, 'events_search_distinct'));
				add_filter( 'posts_join',		array( __CLASS__, 'events_search_join' ) );
				add_filter( 'posts_where',		array( __CLASS__, 'events_search_where' ) );
				add_filter( 'posts_orderby',  array( __CLASS__, 'events_search_orderby' ) );
				add_filter( 'posts_fields',	array( __CLASS__, 'events_search_fields' ) );
				add_filter( 'post_limits',		array( __CLASS__, 'events_search_limits' ) );
				add_filter( 'manage_' . TribeEvents::POSTTYPE . '_posts_columns', array(__CLASS__, 'column_headers'));
				add_filter( 'tribe_apm_headers_' . TribeEvents::POSTTYPE, array(__CLASS__, 'column_headers_check'), 10, 1 );
				add_filter( 'posts_results',  array(__CLASS__, 'cache_posts_results'));
				add_filter( 'get_edit_post_link',  array(__CLASS__, 'add_event_occurrance_to_edit_link'), 10, 2);
				add_filter( 'views_edit-tribe_events',		array( __CLASS__, 'update_event_counts' ) );			
				add_action( 'manage_posts_custom_column', array(__CLASS__, 'custom_columns'), 10, 2);
				add_action( 'manage_edit-' . TribeEvents::POSTTYPE . '_sortable_columns', array(__CLASS__, 'register_date_sortables'), 10, 2);
			
				// event deletion
				add_filter( 'get_delete_post_link', array(__CLASS__, 'add_date_to_recurring_event_trash_link'), 10, 2 );	
				add_filter( 'post_row_actions', array(__CLASS__, 'add_recurring_event_view_link'));
			}
		}
		public static function add_recurring_event_view_link($actions) {
			global $post;
			if ( function_exists('tribe_is_recurring_event') && is_array(self::$events_list) && tribe_is_recurring_event(self::$events_list[0]->ID) && isset(self::$events_list[0]) ) {
				$actions['view'] = '<a href="' . tribe_get_event_link(self::$events_list[0]) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'tribe-events-calendar' ), $post->post_title ) ) . '" rel="permalink">' . __( 'View', 'tribe-events-calendar' ) . '</a>';
			}

			return $actions;
		}
	
		// event deletion
		public static function add_date_to_recurring_event_trash_link( $link, $postId ) {
			if ( function_exists('tribe_is_recurring_event') && is_array(self::$events_list) && tribe_is_recurring_event($postId) && isset(self::$events_list[0]) ) {
				return add_query_arg( array( 'eventDate'=>urlencode( TribeDateUtils::dateOnly( self::$events_list[0]->EventStartDate ) ) ), $link );
			}
		
			return $link;
		} 

		public static function cache_posts_results($posts) {
			if ( get_query_var('post_type') == TribeEvents::POSTTYPE && sizeof(self::$events_list) <= 0 ) {
				// sort by start date
				self::$events_list = $posts; // cache results so i can get the end dates later
			}
		
			return $posts;
		}

		public static function events_search_distinct($distinct) {
			return "DISTINCT";
		}

		/**
		 * fields filter for standard wordpress templates.  Adds the start and end date to queries in the
		 * events category
		 *
		 * @param string fields
		 */
		public static function events_search_fields( $fields ) {
			if ( get_query_var('post_type') != TribeEvents::POSTTYPE ) {
				return $fields;
			}
			global $wpdb;
			$fields .= ", eventStart.meta_value as EventStartDate, IFNULL(DATE_ADD(CAST(eventStart.meta_value AS DATETIME), INTERVAL eventDuration.meta_value SECOND), eventEnd.meta_value) as EventEndDate ";
			return $fields;
		}
		/**
		 * join filter for admin quries
		 *
		 * @param string join clause
		 * @return string modified join clause
		 */
		public static function events_search_join( $join ) {
			global $wpdb;
			if ( get_query_var('post_type') != TribeEvents::POSTTYPE ) {
				return $join;
			}
			$join .= " LEFT JOIN {$wpdb->postmeta} as eventStart ON( {$wpdb->posts}.ID = eventStart.post_id AND eventStart.meta_key = '_EventStartDate') ";
			$join .= " LEFT JOIN {$wpdb->postmeta} as eventDuration ON( {$wpdb->posts}.ID = eventDuration.post_id AND eventDuration.meta_key = '_EventDuration') ";
			$join .= " LEFT JOIN {$wpdb->postmeta} as eventEnd ON( {$wpdb->posts}.ID = eventEnd.post_id AND eventEnd.meta_key = '_EventEndDate') ";
			return $join;
		}
		/**
		 * where filter for admin queries
		 *
		 * @param string where clause
		 * @return string modified where clause
		 */
		public static function events_search_where( $where ) {
			if ( get_query_var('post_type') != TribeEvents::POSTTYPE ) {
				return $where;
			}

			//$where .= ' AND ( eventStart.meta_key = "_EventStartDate" AND eventDuration.meta_key = "_EventDuration" ) ';

			return $where;
		}

		/**
		 * orderby filter for standard admin queries
		 *
		 * @param string orderby
		 * @return string modified orderby clause
		 */
		public static function events_search_orderby( $orderby_sql ) {
			if ( get_query_var('post_type') != TribeEvents::POSTTYPE ) {
				return $orderby_sql;
			}
		
			$endDateSQL = " IFNULL(DATE_ADD(CAST(eventStart.meta_value AS DATETIME), INTERVAL eventDuration.meta_value SECOND), eventEnd.meta_value) ";
			$order = get_query_var('order') ? get_query_var('order') : 'asc';
			$orderby = get_query_var('orderby') ? get_query_var('orderby') : 'start-date';
		
			if ($orderby == 'start-date')
				$orderby_sql = ' eventStart.meta_value ' . $order . ', ' . $endDateSQL . $order;
			else if ($orderby == 'end-date')
				$orderby_sql = $endDateSQL . $order . ', eventStart.meta_value ' . $order;

			return $orderby_sql;
		}

		/**
		 * limit filter for admin queries
		 *
		 * @param string limits clause
		 * @return string modified limits clause
		 */
		public static function events_search_limits( $limits ) {
			if ( get_query_var('post_type') != TribeEvents::POSTTYPE ) {
				return $limits;
			}
			global $current_screen;
			$paged = (int) get_query_var('paged');
			if (empty($paged)) {
					$paged = 1;
			}
			if ( is_admin() ) {
				$option = str_replace( '-', '_', "{$current_screen->id}_per_page" );
				$per_page = get_user_option( $option );
				$per_page = ( $per_page ) ? (int) $per_page : 20; // 20 is default in backend
			}
			else {
				$per_page = (int) tribe_get_option('postsPerPage', 10);
			}

			$page_start = ( $paged - 1 ) * $per_page;
			$limits = 'LIMIT ' . $page_start . ', ' . $per_page;
			return $limits;
		}

		public static function column_headers( $columns ) {
			global $tribe_ecp;

			foreach ( (array) $columns as $key => $value ) {
				$mycolumns[$key] = $value;
				if ( $key =='author' )
					$mycolumns['events-cats'] = __( 'Event Categories', 'tribe-events-calendar' );
			}
			$columns = $mycolumns;

			unset($columns['date']);
			$columns['start-date'] = __( 'Start Date', 'tribe-events-calendar' );
			$columns['end-date'] = __( 'End Date', 'tribe-events-calendar' );
			if ( function_exists( 'tribe_is_recurring_event' ) ) {
				$columns['recurring'] = __( 'Recurring?', 'tribe-events-calendar' );
			}
			
			return $columns;
		}
		
		/**
		 * This will only be fired if Advanced Post Manger is active
		 * Helps ensure dates show correctly if only one or the other of
		 * start & end date columns is showing
		 */
		public static function column_headers_check( $columns ) {
			$active = array_keys($columns);
			self::$end_col_active = in_array('end-date', $active);
			self::$start_col_active = in_array('start-date', $active);
			// What if, oddly, end_col is placed first when both are active?
			if ( self::$end_col_active && self::$start_col_active ) {
				self::$start_col_first = ( array_search('start-date', $active) < array_search('end-date', $active) );
			}
		}

		public static function register_date_sortables($columns) {
			$columns['start-date'] = 'start-date';
			$columns['end-date'] = 'end-date';

			return $columns;
		}		

		public static function custom_columns( $column_id, $post_id ) {
			if(self::$events_list && sizeof(self::$events_list) > 0) {
				if ( $column_id == 'events-cats' ) {
					$event_cats = get_the_term_list( $post_id, TribeEvents::TAXONOMY, '', ', ', '' );
					echo ( $event_cats ) ? strip_tags( $event_cats ) : '—';
				}
				if ( $column_id == 'start-date' ) {
					echo tribe_event_format_date(strtotime(self::$events_list[0]->EventStartDate), false);
					if ( ! self::$end_col_active || ! self::$start_col_first ) self::advance_date();
				}
				if ( $column_id == 'end-date' ) {
					echo tribe_event_format_date(strtotime(self::$events_list[0]->EventEndDate), false);
					if ( self::$start_col_first) self::advance_date();
				}

				if ( $column_id == 'recurring' ) {
					echo sizeof(get_post_meta($post_id, '_EventStartDate')) > 1 ? __("Yes", 'tribe-events-calendar') : __("No", 'tribe-events-calendar');
				}
			} else {
				self::ajax_custom_columns($column_id, $post_id);
			}
		}
		
		protected static function advance_date() {
			array_shift( self::$events_list );
		}
	
		public static function ajax_custom_columns ($column_id, $post_id) {
				if ( $column_id == 'events-cats' ) {
					$event_cats = get_the_term_list( $post_id, TribeEvents::TAXONOMY, '', ', ', '' );
					echo ( $event_cats ) ? strip_tags( $event_cats ) : '—';
				}
			
				if ( $column_id == 'recurring' ) {
					echo sizeof(get_post_meta($post_id, '_EventStartDate')) > 1 ? "Yes" : "No";
				}			
			
				if ( $column_id == 'start-date' ) {
					echo tribe_get_start_date($post_id, false);
				}
				if ( $column_id == 'end-date' ) {
					echo tribe_get_end_date($post_id, false);
				}
		}
	
		public static function add_event_occurrance_to_edit_link($link, $eventId) {
			if ( get_query_var('post_type') != TribeEvents::POSTTYPE ) {
				return $link;
			}

			// if is a recurring event
			if ( function_exists('tribe_is_recurring_event') && tribe_is_recurring_event($eventId) && isset(self::$events_list[0])) {
				$link = add_query_arg('eventDate', urlencode( TribeDateUtils::dateOnly( self::$events_list[0]->EventStartDate ) ), $link);
			}
		
			return $link;
		}
	
		// update counts
		public static function update_event_counts($counts) {
			global $post_type, $post_type_object, $locked_post_status, $avail_post_stati;		

			$num_posts = self::count_events();
		
			$total_posts = array_sum( (array) $num_posts );

			foreach ( get_post_stati( array('show_in_admin_all_list' => false) ) as $state ) {
				$total_posts -= $num_posts->$state;
			}

			$counts['all'] = "<a href='edit.php?post_type=tribe_events' class='current'>".sprintf(__( 'All %s', 'tribe-events-calendar'),"<span class='count'>($total_posts)</span>")."</a>";
		
			foreach ( get_post_stati(array('show_in_admin_status_list' => true), 'objects') as $status ) {
				$class = '';

				$status_name = $status->name;

				if ( !in_array( $status_name, $avail_post_stati ) )
					continue;

				if ( empty( $num_posts->$status_name ) )
					continue;

				if ( isset($_REQUEST['post_status']) && $status_name == $_REQUEST['post_status'] )
					$class = ' class="current"';

				$counts[$status_name] = "<a href='edit.php?post_status=$status_name&amp;post_type=$post_type'$class>" . sprintf( translate_nooped_plural( $status->label_count, $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) . '</a>';
			}		

			return $counts;
		}
	
		// taken from wp_count_posts;
		private static function count_events() {
			$type = TribeEvents::POSTTYPE;
			$perm = 'readable';

			global $wpdb;

			$user = wp_get_current_user();

			$cache_key = $type;

			$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts}";
			$query .= " LEFT JOIN {$wpdb->postmeta} as eventStart ON( {$wpdb->posts}.ID = eventStart.post_id AND eventStart.meta_key = '_EventStartDate') ";		
			$query .= " WHERE post_type = %s";
			if ( 'readable' == $perm && is_user_logged_in() ) {
				$post_type_object = get_post_type_object($type);
				if ( !current_user_can( $post_type_object->cap->read_private_posts ) ) {
					$cache_key .= '_' . $perm . '_' . $user->ID;
					$query .= " AND (post_status != 'private' OR ( post_author = '$user->ID' AND post_status = 'private' ))";
				}
			}
			$query .= ' GROUP BY post_status';

			$count = wp_cache_get($cache_key, 'counts');
			$count = false;
			if ( false !== $count )
				return $count;

			$count = $wpdb->get_results( $wpdb->prepare( $query, $type ), ARRAY_A );

			$stats = array();
			foreach ( get_post_stati() as $state )
				$stats[$state] = 0;

			foreach ( (array) $count as $row )
				$stats[$row['post_status']] = $row['num_posts'];

			$stats = (object) $stats;
			wp_cache_set($cache_key, $stats, 'counts');

			return $stats;
		}	
	}
	TribeEventsAdminList::init();
}
