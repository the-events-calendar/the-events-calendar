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
		private static $today;
		private static $current_month;
		private static $current_year;
		private static $event_daily_counts = array();
		private static $first_day_of_month = null;
		private static $posts_per_page_limit = 3;
		private static $tribe_bar_args = array();
		private static $cache_expiration = 3600;
		private static $calendar_days = array();
		private static $current_day = -1;
		private static $current_week = -1;
		private static $weeks_on_calendar;

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

			// set up an array of the days of the current month
			self::setup_month();			

			add_filter( 'excerpt_length', array(__CLASS__, 'excerpt_length'));

		}

		/**
		 * Limit the excerpt length on the calendar view
		 *
		 * @return void
		 * @author 
		 **/
		function excerpt_length( $length ) {
			return 30;
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

		private static function setup_month() {
			$tribe_ecp = TribeEvents::instance();
			$tribe_ecp->date = tribe_get_month_view_date();

			// get all upcoming ids to hide so we're not querying 31 times
			self::$hide_upcoming_ids = TribeEventsQuery::getHideFromUpcomingEvents();

			list( $year, $month ) = explode( '-', $tribe_ecp->date );

			$first_date_of_month = mktime( 12, 0, 0, $month, 1, $year ); // 1st day of month as unix stamp

			$startOfWeek = get_option( 'start_of_week', 0 );

			self::$event_daily_counts = self::get_daily_counts($first_date_of_month);

			if ( empty(self::$tribe_bar_args) ) {
				foreach ( $_REQUEST as $key => $value ) {
					if ( $value && strpos($key, 'tribe-bar-') === 0 && $key != 'tribe-bar-date' ) {
						self::$tribe_bar_args[$key] = $value;
					}
				}
			}

			// Var'ng up days, months and years
			self::$today = date_i18n( 'd' );
			self::$current_month = date_i18n( 'm' );
			self::$current_year = date_i18n( 'Y' );

			// single dimensional array of days for the month
			$days = array();

			// setup counters
			$days_in_month = date( 't', intval($first_date_of_month) );
			$days_in_calendar = $days_in_month + $offset;
			while ($days_in_calendar % 7 > 0) {
				$days_in_calendar++;
			}
			$rawOffset = date( 'w', $first_date_of_month ) - $startOfWeek;
			$prev_month_offset = ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset; // month begins on day x
			$week = 0;
			$cur_calendar_day = 0;

			// fill month with required days for previous month
			$days = array_fill(0, $prev_month_offset, array('date' => 'previous'));
			$cur_calendar_day += $offset;

			// add days for this month
			for ($i = 0; $i < $days_in_month; $i++) {
				$day = $i + 1;
				$date = date( 'Y-m-d', strtotime("$year-$month-$day"));
				$days[] = array(
					'daynum'	=> $day,
					'date' 		=> $date,
					'events'	=> self::get_daily_events($date),
				);
			}
			$cur_calendar_day += $days_in_month;

			// add days for next month
			if ($cur_calendar_day < $days_in_calendar) {
				$days = array_merge($days, array_fill($cur_calendar_day, $days_in_calendar - $cur_calendar_day+1, array('date' => 'next')));
			}

			self::$weeks_on_calendar = $days_in_calendar / 7;
			self::$calendar_days = $days;
			self::$current_day = -1;
		}

		/**
		 * Checks whether there are more calendar days to display
		 *
		 * @return bool True if calendar days are available, false if not.
		 * @since 3.0
		 **/
		public static function have_days() {
			if ( self::$current_day + 1 < count(self::$calendar_days) ) {
				return true;
			}
			return false;
		}

		/**
		 * Advances the internal day counter (and week counter, if appropriate)
		 *
		 * @return void
		 * @since 3.0
		 **/
		public static function the_day() {
			self::$current_day++;
			if (self::$current_day % 7 == 0) {
				self::$current_week++;
			}
		}

		/**
		 * Returns the current day according to self::$current_day
		 *
		 * @return void
		 * @author 
		 **/
		public static function get_current_day() {
			return self::$calendar_days[self::$current_day];
		}

		/**
		 * Generates and returns a set of classes for the current day
		 *
		 * @return void
		 * @author 
		 **/
		public static function day_classes() {
			$ppf = '';
			$calendar_day = self::$calendar_days[self::$current_day];
			// echo $calendar_day['date'];
			if ($calendar_day['date'] == 'previous' || $calendar_day['date'] == 'next') {
				$ppf = 'tribe-events-othermonth';
			} else {
				$ppf = 'tribe-events-thismonth';
				list ($year, $month, $day) = explode('-', $calendar_day['date']);
				if ( self::$current_month == $month && self::$current_year == $year) {
					// Past, Present, Future class
					if ( self::$today == $day ) {
						$ppf .= ' tribe-events-present';
					} else if ( self::$today > $day ) {
						$ppf .= ' tribe-events-past';
					} else if ( self::$today < $day ) {
						$ppf .= ' tribe-events-future';
					}
				} else if ( self::$current_month > $month && self::$current_year == $year || self::$current_year > $year ) {
					$ppf .= ' tribe-events-past';
				} else if ( self::$current_month < $month && self::$current_year == $year || self::$current_year < $year ) {
					$ppf .= ' tribe-events-future';
				}

			}

			$column = (self::$current_day) - (self::$current_week * 7);

			if ( $column > 0 && ( $column % 4 == 0 || $column % 5 == 0 || $column % 6 == 0 ) ) {
				$ppf .= ' tribe-events-right';
			}
			return $ppf;
		}

		/**
		 * Returns self::$weeks_on_calendar
		 *
		 * @return int $weeks_on_calendar
		 * @since 3.0
		 **/
		public static function get_weeks_on_calendar() {
			return self::$weeks_on_calendar;
		}

		/**
		 * Returns self::$current_week
		 *
		 * @return int $current_week
		 * @since 3.0
		 **/
		public static function get_current_week() {
			return self::$current_week;
		}

		/**
		 * Generates and returns a set of classes for the current day
		 *
		 * @return void
		 * @author 
		 **/
		public static function event_classes() {

			$post = self::get_current_day();

			// Get our wrapper classes (for event categories, organizer, venue, and defaults)
			$classes = array('hentry', 'vevent');
			$tribe_cat_slugs = tribe_get_event_cat_slugs( $post->ID );
			foreach( $tribe_cat_slugs as $tribe_cat_slug ) {
				$classes[] = 'tribe-events-category-'. $tribe_cat_slug;
			}
			$classes = array_merge($classes, get_post_class('', $post->ID));
			if ( $venue_id = tribe_get_venue_id($post->ID) ) {
				$classes[] = 'tribe-events-venue-'. $venue_id;
			}
			if ( $organizer_id = tribe_get_organizer_id($post->ID) ) {
				$classes[] = 'tribe-events-organizer-'. $organizer_id;
			}
			return implode(' ', $classes);
		}
	} // class Tribe_Events_Calendar_Template

	Tribe_Events_Calendar_Template::init();
}