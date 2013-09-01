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

if( !class_exists('Tribe_Events_Month_Template')){
	/**
	 * Grid view template class
	 */
	class Tribe_Events_Month_Template extends Tribe_Template_Factory {
		private static $hide_upcoming_ids;
		private static $today;
		private static $current_month;
		private static $current_year;
		private static $event_daily_counts = array();
		private static $event_daily_ids = array();
		private static $first_day_of_month = null;
		private static $posts_per_page_limit = 3;
		private static $tribe_bar_args = array();
		private static $cache_expiration = 3600;
		private static $calendar_days = array();
		private static $current_day = -1;
		private static $current_week = -1;
		protected static $args;
		protected $body_class = 'events-gridview';
		protected $excerpt_length = 30;
		protected $asset_packages = array( 'ajax-calendar' );

		/**
		 * Set the notices used on month view
		 *
		 * @param array $args Set of $wp_query params for the month view, if none passed then will default to $wp_query
		 * @since 3.0
		 */
		public function __construct( $args = null ) {
			if ( $args === null ) {
				global $wp_query;
				$args = $wp_query->query;
			}

			self::$args = $args;
			self::$posts_per_page_limit = apply_filters( 'tribe_events_month_day_limit', tribe_get_option( 'monthEventAmount', '3' ) );

			// don't enqueue scripts and js when we're not constructing month view, 
			// they'll have to be enqueued separately
			if ( ! tribe_is_month() ) {
				$this->asset_packages = array();
			}

			parent::__construct();
		}

		/**
		 * Add any special hooks for this view
		 *
		 * @return void
		 * @author Jessica Yazbek
		 * @since 3.0.2
		 **/
		protected function hooks() {
			parent::hooks();

			// Since we set is_post_type_archive to true on month view, this prevents 'Events' from being added to the page title
			add_filter('post_type_archive_title', '__return_false', 10);
		}

		/**
		 * Unhook all the hooks set up on this view
		 *
		 * @return void
		 * @author 
		 **/
		protected function unhook() {
			parent::unhook();
			remove_filter('post_type_archive_title', '__return_false', 10);
		}


		/**
		 * Set the notices used on month view
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function set_notices() {
			global $wp_query;
			// setup a search term for query or via ajax
			if( !empty( $wp_query->query_vars['s'] )){
				$search_term = $wp_query->query_vars['s'];
			} else if( !empty($_POST['tribe-bar-search'])) {
				$search_term = $_POST['tribe-bar-search'];
			}

			$total_counts = array_unique(self::$event_daily_counts);

			if( count($total_counts) < 2 && !empty($search_term)) {
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'There were no results found for <strong>"%s"</strong> this month. Try searching next month.', 'tribe-events-calendar' ), esc_html( $search_term ) ) );
			}
		}


		/**
		 * Get number of events per day
		 *
		 * @param int $date
		 * @return array
		 * @since 3.0
		 */
		private static function get_daily_counts( $date ) {
			global $wp_query;

			$count_args = self::$args;

			$count_args['eventDisplay'] = 'month';
			$count_args['eventDate'] = date( 'Y-m', strtotime( $date ) );
			$count_args['start_date'] = tribe_event_beginning_of_day( $date );
			$count_args['end_date'] = tribe_event_end_of_day( date('Y-m-t', strtotime( $date ) ) );
			$count_args['hide_upcoming_ids'] = self::$hide_upcoming_ids;
			$count_args['post_status'] = is_user_logged_in() ? array( 'publish', 'private' ) : 'publish';

			$result = TribeEventsQuery::getEventCounts( $count_args );

			self::$event_daily_counts = $result['counts'];
			self::$event_daily_ids = $result['event_ids'];
		}


		/**
		 * Get the view more link
		 *
		 * @param int $date
		 * @param array $args
		 * @return string
		 * @since 3.0
		 */
		private static function view_more_link( $date, $args ) {
			if( !empty(self::$event_daily_counts[$date]) && (int) self::$event_daily_counts[$date] > self::$posts_per_page_limit ) {
				$day_link = tribe_get_day_link($date);
				if ( !empty($args) ) {
					$day_link = add_query_arg($args, $day_link);
				}
				return $day_link;
			}
			return '';
		}

		/**
		 * @param string $date
		 * @return WP_Query
		 */
		private function get_daily_events( $date ) {
			global $wp_query;
			$tribe_ecp = TribeEvents::instance();

			$post_status = is_user_logged_in() ? array( 'publish', 'private' ) : 'publish';

			$args = wp_parse_args( array(
				'post__in' => self::$event_daily_ids[$date],
				'post_type' => TribeEvents::POSTTYPE,
				'start_date' => tribe_event_beginning_of_day($date),
				'end_date' => tribe_event_end_of_day($date),
				'eventDisplay' => 'custom',
				'no_found_rows' => true,
				'posts_per_page' => self::$posts_per_page_limit,
			), self::$args );
			$result = TribeEventsQuery::getEvents( $args, true );
			return $result;
		}

		/**
		 * Sets up an array of $days based on the current query, that can be used in the calendar loop
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function setup_view() {

			$tribe_ecp = TribeEvents::instance();
			$tribe_ecp->date = isset( self::$args['eventDate'] ) ? self::$args['eventDate'] : tribe_get_month_view_date();

			do_action('log', 'eventDate', 'tribe-events-query', $tribe_ecp->date);

			// get all upcoming ids to hide so we're not querying 31 times
			self::$hide_upcoming_ids = TribeEventsQuery::getHideFromUpcomingEvents();

			list( $year, $month ) = explode( '-', $tribe_ecp->date );

			$startOfWeek = get_option( 'start_of_week', 0 );

			$first_day_of_month = date( 'Y-m-01', strtotime( $tribe_ecp->date ) );

			self::get_daily_counts($first_day_of_month);

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
			$rawOffset = date( 'w', strtotime($tribe_ecp->date) ) - $startOfWeek;
			$prev_month_offset = (int) ( ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset ); // month begins on day x
			$days_in_month = (int) date( 't', strtotime($tribe_ecp->date) );
			$days_in_calendar =  $days_in_month + $prev_month_offset;
			while ($days_in_calendar % 7 > 0) {
				$days_in_calendar++;
			}
			$week = 0;
			$cur_calendar_day = 0;

			// fill month with required days for previous month
			if ( $prev_month_offset > 0 )
				$days = array_fill( 0, $prev_month_offset, array( 'date' => 'previous' ) );

			// get $cur_calendar_day up to speed
			$cur_calendar_day += $prev_month_offset;

			// if (in_array(0, self::$event_daily_counts)) {
				$empty_query = new WP_Query();
			// }

			// add days for this month
			for ($i = 0; $i < $days_in_month; $i++) {
				$day = $i + 1;
				$date = date( 'Y-m-d', strtotime("$year-$month-$day"));

				$total_events = ! empty( self::$event_daily_counts[$date] ) ? self::$event_daily_counts[$date] : 0;

				$days[] = array(
					'daynum'       => $day,
					'date'         => $date,
					'events'       => $total_events ? self::get_daily_events( $date ) : $empty_query,
					'total_events' => $total_events,
					'view_more'    => self::view_more_link( $date, self::$tribe_bar_args ),
				);
			}

			// get $cur_calendar_day up to speed
			$cur_calendar_day += $days_in_month;

			// check if $cur_calendar_day is less than $days_in_calendar, if so, add days for next month
			if ($cur_calendar_day < $days_in_calendar) {
				$days = array_merge($days, array_fill($cur_calendar_day, $days_in_calendar - $cur_calendar_day, array('date' => 'next')));
			}

			// store set of found days for use in calendar loop functions
			self::$calendar_days = $days;
		}

		/**
		 * Checks whether there are more calendar days to display
		 *
		 * @return bool True if calendar days are available, false if not.
		 * @since 3.0
		 **/
		public static function have_days() {
			if ( self::$current_day + 1 < count( self::$calendar_days ) ) {
				return true;
			} elseif ( self::$current_day + 1 == count( self::$calendar_days ) && count( self::$calendar_days ) > 0 ) {
				do_action_ref_array('tribe_events_calendar_loop_end', array(&$this));
				// Do some cleaning up after the loop
				self::rewind_days();
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
			if ( self::have_days() ) {
				self::$current_day++;
				if ( self::$current_day % 7 == 0 ) {
					self::$current_week++;
				}
			}
		}

		/**
		 * Rewind the posts and reset post index.
		 *
		 * @since 1.5.0
		 * @access public
		 */
		public static function rewind_days() {
			self::$current_day = -1;
			self::$current_week = -1;
		}

		/**
		 * Returns the current day according to self::$current_day
		 *
		 * @return int
		 * @since 3.0
		 **/
		public static function get_current_day() {
			if ( count( self::$calendar_days ) && self::$current_day < count( self::$calendar_days ) && isset( self::$calendar_days[self::$current_day] ) ) {
				return self::$calendar_days[self::$current_day];
			}
			return false;
		}

		/**
		 * Generates and returns a set of classes for the current day
		 *
		 * @return string Classes
		 * @since 3.0
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
				if ( $calendar_day['total_events'] > 0 ) {
   				$ppf .= ' tribe-events-has-events';
   			}

			}

			$column = (self::$current_day) - (self::$current_week * 7);

			if ( $column > 0 && ( $column % 4 == 0 || $column % 5 == 0 || $column % 6 == 0 ) ) {
				$ppf .= ' tribe-events-right';
			}
			return $ppf;
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
		 * @return string Classes
		 * @since 3.0
		 **/
		public function event_classes( $classes ) {

			$day = self::get_current_day();

			if ( !isset( $day['events'] ) )
				return $classes;

			$post = $day['events']->post;

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

			if ($day['events']->current_post + 1 == $day['events']->post_count) {
				$classes[] = 'tribe-events-last';
			}

			return $classes;
		}
	} // class Tribe_Events_Month_Template
}
