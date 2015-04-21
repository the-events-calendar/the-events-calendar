<?php
/**
 * @for     Calendar Template
 * This file contains the hook logic required to create an effective calendar month view.
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe_Events_Month_Template' ) ) {
	/**
	 * Month view template class
	 */
	class Tribe_Events_Month_Template extends Tribe_Template_Factory {
		const PREVIOUS_MONTH = -1;
		const CURRENT_MONTH  = 0;
		const NEXT_MONTH     = 1;

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
		private static $current_day = - 1;
		private static $current_week = - 1;
		protected static $args;

		/**
		 * Indicates the array indicies marking the first and last entries for
		 * the current month.
		 */
		protected $current_month_begins;
		protected $current_month_ends;

		protected $body_class = 'events-gridview';
		protected $excerpt_length = 30;
		protected $asset_packages = array( 'ajax-calendar' );

		const AJAX_HOOK = 'tribe_calendar';

		/**
		 * Set the notices used on month view
		 *
		 * @param array $args Set of $wp_query params for the month view, if none passed then will default to $wp_query
		 */
		public function __construct( $args = null ) {
			if ( $args === null ) {
				global $wp_query;
				$args = $wp_query->query;
			}

			self::$args                 = $args;
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
		 **/
		protected function hooks() {
			parent::hooks();

			// Since we set is_post_type_archive to true on month view, this prevents 'Events' from being added to the page title
			add_filter( 'post_type_archive_title', '__return_false', 10 );
		}

		/**
		 * Unhook all the hooks set up on this view
		 *
		 * @return void
		 **/
		protected function unhook() {
			parent::unhook();
			remove_filter( 'post_type_archive_title', '__return_false', 10 );
		}

		/**
		 * Set the notices used on month view
		 *
		 * @return void
		 **/
		public function set_notices() {
			// Our focus is on the current month, not the complete range of events included in the current month view
			// (which may include some leading/trailing days of the next and previous months)
			$slice_length = $this->current_month_ends - $this->current_month_begins + 1;
			$current_month_counts = array_slice( self::$event_daily_counts, $this->current_month_begins, $slice_length );

			// If there are no events we should be able to reduce the event_daily_counts array (the number of events in
			// each day this month) to a single element with a value of 0. Where a keyword search returns no events then
			// event_daily_counts may simply be empty.
			$event_counts = array_unique( $current_month_counts );
			$no_events = ( 1 === count( $event_counts ) && 0 === current( $event_counts ) ) || empty( self::$event_daily_counts );

			// Use our parent method to assess if a search term was set, etc
			if ( $no_events ) {
				$this->nothing_found_notice();
			}
		}

		/**
		 * Sets an appropriate no results found message.
		 */
		protected function nothing_found_notice() {
			list( $search_term, $tax_term, $geographic_term ) = $this->get_search_terms();

			if ( ! empty( $search_term ) ) {
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'There were no results found for <strong>"%s"</strong> this month. Try searching next month.', 'tribe-events-calendar' ), esc_html( $search_term ) ) );
			} // if attempting to view a category archive.
			elseif ( ! empty( $tax_term ) ) {
				TribeEvents::setNotice( 'events-not-found', sprintf( __( 'No matching events listed under %s. Please try viewing the full calendar for a complete list of events.', 'tribe-events-calendar' ), $tax_term ) );
			} else {
				TribeEvents::setNotice( 'event-search-no-results', __( 'There were no results found.', 'tribe-events-calendar' ) );
			}
		}

		/**
		 * Get the title for month view
		 *
		 * @param  string $original_title
		 * @param  null   $sep
		 *
		 * @return string
		 */
		protected function get_title( $original_title, $sep = null ) {
			$new_title = parent::get_title( $original_title, $sep );
			if ( get_query_var( 'eventDate' ) && has_filter('tribe_month_grid_view_title') ) {
				_deprecated_function( "The 'tribe_month_grid_view_title' filter", '3.8', " the 'tribe_get_events_title' filter" );
				$title_date = date_i18n( tribe_get_option( 'monthAndYearFormat', 'F Y' ), strtotime( get_query_var( 'eventDate' ) ) );
				$new_title  = apply_filters( 'tribe_month_grid_view_title', $new_title, $sep, $title_date );
			} else if ( has_filter( 'tribe_events_this_month_title' ) ) {
				_deprecated_function( "The 'tribe_events_this_month_title' filter", '3.8', " the 'tribe_get_events_title' filter" );
				$new_title = apply_filters( 'tribe_events_this_month_title', $new_title, $sep );
			}
			return $new_title;
		}


		/**
		 * Get number of events per day
		 *
		 * @param  string $start_date
		 * @param  string $end_date
		 *
		 * @return array
		 */
		private static function get_daily_counts( $start_date, $end_date ) {
			global $wp_query;

			$count_args = self::$args;

			do_action( 'log', 'get_daily_counts $date', 'tribe-events-query', $start_date, $end_date );

			$count_args['eventDisplay']        = 'month';
			$count_args['eventDate']           = $start_date;
			$count_args['start_date']          = tribe_event_beginning_of_day( $start_date );
			$count_args['end_date']            = tribe_event_end_of_day( $end_date );
			$count_args['hide_upcoming_ids']   = self::$hide_upcoming_ids;
			$count_args['post_status']         = is_user_logged_in() ? array( 'publish', 'private' ) : 'publish';
			$count_args['tribeHideRecurrence'] = false;

			$result = TribeEventsQuery::getEventCounts( $count_args );

			self::$event_daily_counts = $result['counts'];
			self::$event_daily_ids    = $result['event_ids'];

			foreach ( self::$event_daily_counts as $daily_count ) {
				if ( $daily_count > 0 ) {
					add_filter( 'tribe_events_month_has_events', '__return_true' );
					break;
				}
			}
		}


		/**
		 * Get the view more link
		 *
		 * @param int   $date
		 * @param array $args
		 *
		 * @return string
		 */
		private static function view_more_link( $date, $args ) {
			if ( ! empty( self::$event_daily_counts[$date] ) && (int) self::$event_daily_counts[$date] > self::$posts_per_page_limit ) {
				$day_link = tribe_get_day_link( $date );
				if ( ! empty( $args ) ) {
					$day_link = add_query_arg( $args, $day_link );
				}

				return esc_url_raw( $day_link );
			}

			return '';
		}

		/**
		 * @param string $date
		 *
		 * @return WP_Query
		 */
		private function get_daily_events( $date ) {
			global $wp_query;
			$tribe_ecp = TribeEvents::instance();

			$post_status = is_user_logged_in() ? array( 'publish', 'private' ) : 'publish';

			$args   = wp_parse_args(
				array(
					'post__in'       => self::$event_daily_ids[$date],
					'post_type'      => TribeEvents::POSTTYPE,
					'start_date'     => tribe_event_beginning_of_day( $date ),
					'end_date'       => tribe_event_end_of_day( $date ),
					'eventDisplay'   => 'custom',
					'no_found_rows'  => true,
					'posts_per_page' => self::$posts_per_page_limit,
				), self::$args
			);
			$result = TribeEventsQuery::getEvents( $args, true );

			return $result;
		}

		/**
		 * Sets up an array of $days based on the current query, that can be used in the calendar loop
		 *
		 * @return void
		 **/
		public function setup_view() {
			$requested_date     = isset( self::$args['eventDate'] ) ? self::$args['eventDate'] : tribe_get_month_view_date();
			$first_day_of_month = date( 'Y-m-01', strtotime( $requested_date ) );
			$first_grid_date    = $this->calculate_first_cell_date( $requested_date );
			$final_grid_date    = $this->calculate_final_cell_date( $requested_date );
			$days               = array();

			$this->setup_tribe_bar_args();
			$this->current_day_vals();

			do_action( 'log', 'setup view month view args', 'tribe-month', self::$args );
			do_action( 'log', 'eventDate', 'tribe-events-query', $first_day_of_month );

			self::$hide_upcoming_ids = TribeEventsQuery::getHideFromUpcomingEvents();
			self::get_daily_counts( $first_grid_date, $final_grid_date );

			$date  = $first_grid_date; // Start with the first grid date
			$empty = new WP_Query();   // Use for empty days

			// Populate complete date range including leading/trailing days from adjacent months
			while ( $date <= $final_grid_date ) {
				$day  = (int) substr( $date, -2 );
				$total_events = ! empty( self::$event_daily_counts[$date] ) ? self::$event_daily_counts[$date] : 0;

				$prev_month = (int) substr( $date, 5, 2 ) < (int) substr( $requested_date, 5, 2 );
				$next_month = (int) substr( $date, 5, 2 ) > (int) substr( $requested_date, 5, 2 );

				$month_type = self::CURRENT_MONTH;
				if ( $prev_month ) $month_type = self::PREVIOUS_MONTH;
				if ( $next_month ) $month_type = self::NEXT_MONTH;

				$days[] = array(
					'daynum'       => $day,
					'date'         => $date,
					'events'       => $total_events ? self::get_daily_events( $date ) : $empty,
					'total_events' => $total_events,
					'view_more'    => self::view_more_link( $date, self::$tribe_bar_args ),
					'month'        => $month_type
				);

				// Record the indicies marking the portion of the array relating to the current month
				if ( ! isset( $this->current_month_begins ) && self::CURRENT_MONTH === $month_type ) {
					$this->current_month_begins = count( $days ) - 1;
				}
				if ( isset( $this->current_month_begins ) && ! isset( $this->current_month_ends ) && self::CURRENT_MONTH !== $month_type ) {
					$this->current_month_ends = count( $days ) - 1;
				}

				// Advance forward one day
				$date = date( TribeDateUtils::DBDATEFORMAT, strtotime( "$date +1 day" ) );
			}

			// If the month ended without bleeding into the next month, our current_month_ends property may not be set
			if ( ! isset( $this->current_month_ends ) ) {
				$this->current_month_ends = count( $days ) - 1;
			}

			// store set of found days for use in calendar loop functions
			self::$calendar_days = $days;
		}

		protected function setup_tribe_bar_args() {
			if ( empty( self::$tribe_bar_args ) ) {
				foreach ( $_REQUEST as $key => $value ) {
					if ( $value && strpos( $key, 'tribe' ) === 0 && $key != 'tribe-bar-date' ) {
						self::$tribe_bar_args[$key] = $value;
					}
				}
			}
		}

		protected function current_day_vals() {
			self::$today         = date_i18n( 'd' );
			self::$current_month = date_i18n( 'm' );
			self::$current_year  = date_i18n( 'Y' );
		}

		/**
		 * Return the date of the first day in the month view grid.
		 *
		 * This is not necessarily the 1st of the specified month, rather it is the date of the
		 * first grid cell which could be anything upto 6 days earlier than the 1st of the month.
		 *
		 * @param  string $month
		 * @param  int    $start_of_week
		 *
		 * @return bool|string (Y-m-d)
		 */
		protected function calculate_first_cell_date( $month, $start_of_week = null ) {
			if ( null === $start_of_week ) {
				$start_of_week = (int) get_option( 'start_of_week', 0 );
			}

			$day_1 = TribeDateUtils::first_day_in_month( $month );
			if ( $day_1 < $start_of_week ) $day_1 += 7;

			$diff = $day_1 - $start_of_week;
			if ( $diff >= 0 ) $diff = "-$diff";

			try {
				$date = new DateTime( $month );
				$date = new DateTime( $date->format( 'Y-m-01' ) );
				$date->modify( "$diff days" );
				return $date->format( TribeDateUtils::DBDATEFORMAT );
			}
			catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Return the date of the first day in the month view grid.
		 *
		 * This is not necessarily the last day of the specified month, rather it is the date of
		 * the final grid cell which could be anything upto 6 days into the next month.
		 *
		 * @param  string $month
		 * @param  int    $start_of_week
		 *
		 * @return bool|string (Y-m-d)
		 */
		protected function calculate_final_cell_date( $month, $start_of_week = null ) {
			if ( null === $start_of_week ) {
				$start_of_week = (int) get_option( 'start_of_week', 0 );
			}

			$last_day    = TribeDateUtils::last_day_in_month( $month );
			$end_of_week = TribeDateUtils::week_ends_on( $start_of_week );
			if ( $end_of_week < $last_day ) $end_of_week += 7;

			$diff = $end_of_week - $last_day;
			if ( $diff >= 0 ) $diff = "+$diff";

			try {
				$date = new DateTime( $month );
				$date = new DateTime( $date->format( 'Y-m-t' ) );
				$date->modify( "$diff days" );
				return $date->format( TribeDateUtils::DBDATEFORMAT );
			}
			catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Checks whether there are more calendar days to display
		 *
		 * @return bool True if calendar days are available, false if not.
		 **/
		public static function have_days() {
			if ( self::$current_day + 1 < count( self::$calendar_days ) ) {
				return true;
			} elseif ( self::$current_day + 1 == count( self::$calendar_days ) && count( self::$calendar_days ) > 0 ) {
				do_action( 'tribe_events_calendar_loop_end' );
				// Do some cleaning up after the loop
				self::rewind_days();
			}

			return false;
		}

		/**
		 * Advances the internal day counter (and week counter, if appropriate)
		 *
		 * @return void
		 **/
		public static function the_day() {
			if ( self::have_days() ) {
				self::$current_day ++;
				if ( self::$current_day % 7 == 0 ) {
					self::$current_week ++;
				}
			}
		}

		/**
		 * Rewind the posts and reset post index.
		 *
		 * @access public
		 */
		public static function rewind_days() {
			self::$current_day  = - 1;
			self::$current_week = - 1;
		}

		/**
		 * Returns the current day according to self::$current_day
		 *
		 * @return int
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
		 **/
		public static function day_classes() {
			$calendar_day = self::$calendar_days[self::$current_day];

			if ( $calendar_day['month'] == self::PREVIOUS_MONTH || $calendar_day['month'] == self::NEXT_MONTH ) {
				$ppf = 'tribe-events-othermonth';
			} else {
				$ppf = 'tribe-events-thismonth';
			}

			list ( $year, $month, $day ) = explode( '-', $calendar_day['date'] );
			if ( self::$current_month == $month && self::$current_year == $year ) {
				// Past, Present, Future class
				if ( self::$today == $day ) {
					$ppf .= ' tribe-events-present';
				} else {
					if ( self::$today > $day ) {
						$ppf .= ' tribe-events-past';
					} else {
						if ( self::$today < $day ) {
							$ppf .= ' tribe-events-future';
						}
					}
				}
			} else {
				if ( self::$current_month > $month && self::$current_year == $year || self::$current_year > $year ) {
					$ppf .= ' tribe-events-past';
				} else {
					if ( self::$current_month < $month && self::$current_year == $year || self::$current_year < $year ) {
						$ppf .= ' tribe-events-future';
					}
				}
			}
			if ( $calendar_day['total_events'] > 0 ) {
				$ppf .= ' tribe-events-has-events';
			}
			$ppf .= ' mobile-trigger tribe-event-day-' . $day;


			$column = ( self::$current_day ) - ( self::$current_week * 7 );

			if ( $column > 0 && ( $column % 4 == 0 || $column % 5 == 0 || $column % 6 == 0 ) ) {
				$ppf .= ' tribe-events-right';
			}

			return $ppf;
		}

		/**
		 * Returns self::$current_week
		 *
		 * @return int $current_week
		 **/
		public static function get_current_week() {
			return self::$current_week;
		}

		/**
		 * Generates and returns a set of classes for the current day
		 *
		 * @param  string $classes = ''
		 *
		 * @return string Classes
		 **/
		public function event_classes( $classes = '' ) {

			$day = self::get_current_day();

			if ( ! isset( $day['events'] ) ) {
				return $classes;
			}

			$post = $day['events']->post;

			// Get our wrapper classes (for event categories, organizer, venue, and defaults)
			$classes         = array( 'hentry', 'vevent' );
			$tribe_cat_slugs = tribe_get_event_cat_slugs( $post->ID );
			foreach ( $tribe_cat_slugs as $tribe_cat_slug ) {
				$classes[] = 'tribe-events-category-' . $tribe_cat_slug;
			}
			$classes = array_merge( $classes, get_post_class( '', $post->ID ) );
			if ( $venue_id = tribe_get_venue_id( $post->ID ) ) {
				$classes[] = 'tribe-events-venue-' . $venue_id;
			}
			if ( $organizer_id = tribe_get_organizer_id( $post->ID ) ) {
				$classes[] = 'tribe-events-organizer-' . $organizer_id;
			}

			if ( $day['events']->current_post + 1 == $day['events']->post_count ) {
				$classes[] = 'tribe-events-last';
			}

			return $classes;
		}

		/**
		 * Month View Ajax Handler
		 *
		 * @return void
		 */
		function ajax_response() {

			if ( isset( $_POST['eventDate'] ) && $_POST['eventDate'] ) {

				TribeEventsQuery::init();

				// set the global query var for eventDisplay
				$query_args = array(
					'post_type'    => TribeEvents::POSTTYPE,
					'eventDisplay' => 'month',
					'eventDate'    => $_POST['eventDate'],
				);

				TribeEvents::instance()->displaying = 'month';

				if ( isset( $_POST['tribe_event_category'] ) ) {
					$query_args['tribe_events_cat'] = $_POST['tribe_event_category'];
				}

				query_posts( $query_args );

				ob_start();

				tribe_get_view( 'month/content' );

				$response = array(
					'html'    => ob_get_clean(),
					'success' => true,
					'view'    => 'month',
				);
				apply_filters( 'tribe_events_ajax_response', $response );
				header( 'Content-type: application/json' );
				echo json_encode( $response );
				die();
			}
		}

	} // class Tribe_Events_Month_Template
}
