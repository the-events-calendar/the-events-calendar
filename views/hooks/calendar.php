<?php
/**
 * @for Calendar Template
 * This file contains the hook logic required to create an effective calendar month view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */
 
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Calendar_Template')){
	class Tribe_Events_Calendar_Template extends Tribe_Template_Factory {
		private static $hide_upcoming_ids;
		private static $current_day;
		private static $current_month;
		private static $current_year;
		private static $event_daily_counts = array();
		private static $first_day_of_month = null;
		private static $posts_per_page_limit = 3;
		private static $tribe_bar_args = array();
		private static $cache_expiration = 3600;

		public static function init(){

			Tribe_Template_Factory::asset_package( 'ajax-calendar' );
			
			$tribe_ecp = TribeEvents::instance();
			$tribe_ecp->date = tribe_get_month_view_date();

			// get all upcoming ids to hide so we're not querying 31 times
			self::$hide_upcoming_ids = TribeEventsQuery::getHideFromUpcomingEvents();

			list( $year, $month ) = explode( '-', $tribe_ecp->date );
			$date = mktime( 12, 0, 0, $month, 1, $year ); // 1st day of month as unix stamp
			self::$first_day_of_month = $date;

			// let's find out how many events are happening each day and share
			self::$event_daily_counts = self::get_daily_counts($date);
			$total_counts = array_unique(self::$event_daily_counts);

			// setup a search term for query or via ajax
			if( !empty( $wp_query->query_vars['s'] )){
				$search_term = $wp_query->query_vars['s'];
			} else if( !empty($_POST['tribe-bar-search'])) {
				$search_term = $_POST['tribe-bar-search'];
			}

			if( count($total_counts) < 2 && !empty($search_term)) {
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'There were no results found for <strong>"%s"</strong> this month. Try searching next month.', 'tribe-events-calendar' ), $search_term ) );
			}



		}

		}

		private static function get_daily_counts( $date ) {
			global $wp_query;
			$count_args = $wp_query->query;
			if ( empty($count_args) ) { // this will likely be empty on Ajax calls
				$count_args['post_type'] = TribeEvents::POSTTYPE;
				$count_args['eventDisplay'] = 'month';
			}
			$count_args['start_date'] = date('Y-m-d', $date) . ' 00:00:00';
			$count_args['end_date'] = date('Y-m-t', $date) . ' 23:59:59';
			$count_args['hide_upcoming_ids'] = self::$hide_upcoming_ids;
			$count_args['post_status'] = is_user_logged_in() ? array( 'publish', 'private' ) : 'publish';

			$cache = new TribeEventsCache();
			$cache_key = 'daily_counts_'.serialize($count_args);
			$found = $cache->get( $cache_key, 'save_post' );
			if ( $found && is_array($found) ) {
				return $found;
			}
			$result = TribeEventsQuery::getEventCounts( $count_args );
			$cache->set( $cache_key, $result, self::$cache_expiration, 'save_post' );
			return $result;
		}

		private static function view_more_link( $date, $args ) {
			if( !empty(self::$event_daily_counts[$date]) && (int) self::$event_daily_counts[$date] > self::$posts_per_page_limit ) {
				$day_link = tribe_get_day_link($date);
				if ( !empty($args) ) {
					$day_link = add_query_arg($args, $day_link);
				}
				printf( '<div class="tribe-events-viewmore"><a href="%s">View All %d &raquo;</a></div>',
					$day_link,
					self::$event_daily_counts[$date]
				);
			}
		}

		/**
		 * @param string $date
		 * @return WP_Query
		 */
		private function get_daily_events( $date ) {
			global $wp_query;
			$tribe_ecp = TribeEvents::instance();
			
			$post_status = is_user_logged_in() ? array( 'publish', 'private' ) : 'publish';

			$args = wp_parse_args(array(
				'eventDate' => $date,
				'start_date' => tribe_event_beginning_of_day( $date ),
				'end_date' => tribe_event_end_of_day( $date ),
				// setup our own custom hide upcoming
				'post__not_in' => self::$hide_upcoming_ids,
				'hide_upcoming' => FALSE,
				'posts_per_page' => self::$posts_per_page_limit,
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'post_status' => $post_status,
				'eventDisplay' => 'custom',
				'no_found_rows' => TRUE
			), $wp_query->query);

			if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
				$cat = get_term_by( 'slug', get_query_var( 'term' ), $tribe_ecp->get_event_taxonomy() );
				$args['eventCat'] = (int) $cat->term_id;
			}

			$cache = new TribeEventsCache();
			$cache_key = 'daily_events_'.serialize($args);
			$found = $cache->get($cache_key, 'save_post');
			if ( $found && is_a($found, 'WP_Query') ) {
				//return $found;
			}

			$result = TribeEventsQuery::getEvents( $args, TRUE );
			$cache->set($cache_key, $result, self::$cache_expiration, 'save_post');
			return $result;
		}
	Tribe_Events_Calendar_Template::init();
}