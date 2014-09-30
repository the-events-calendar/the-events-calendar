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
			global $wp_query;
			$tribe       = TribeEvents::instance();
			$search_term = '';
			$tax_term    = '';

			// setup a search term for query or via ajax
			if ( ! empty( $wp_query->query_vars['s'] ) ) {
				$search_term = $wp_query->query_vars['s'];
			} elseif ( ! empty( $_REQUEST['tribe-bar-search'] ) ) {
				$search_term = $_REQUEST['tribe-bar-search'];
			}

			if ( is_tax( $tribe->get_event_taxonomy() ) ) {
				$tax_term = get_term_by( 'slug', get_query_var( 'term' ), $tribe->get_event_taxonomy() );
				$tax_term = esc_html( $tax_term->name );
			}

			// If there are no events we should be able to reduce the event_daily_counts array (the number of events in
			// each day this month) to a single element with a value of 0. Where a keyword search returns no events then
			// event_daily_counts may simply be empty.
			$event_counts = array_unique( self::$event_daily_counts );
			$no_events    = ( 1 === count( $event_counts ) && 0 === current( $event_counts ) ) || empty( self::$event_daily_counts );

			if ( $no_events && ! empty( $search_term ) ) {
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'There were no results found for <strong>"%s"</strong> this month. Try searching next month.', 'tribe-events-calendar' ), esc_html( $search_term ) ) );
			} // if attempting to view a category archive.
			elseif ( ! empty( $tax_term ) && $no_events ) {
				TribeEvents::setNotice( 'events-not-found', sprintf( __( 'No matching events listed under %s. Please try viewing the full calendar for a complete list of events.', 'tribe-events-calendar' ), $tax_term ) );
			} elseif ( $no_events ) {
				TribeEvents::setNotice( 'event-search-no-results', __( 'There were no results found.', 'tribe-events-calendar' ) );
			}
		}

		/**
		 * Get the title for month view
		 * @param      $title
		 * @param null $sep
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
		 * @param int $date
		 *
		 * @return array
		 */
		private static function get_daily_counts( $date ) {
			global $wp_query;

			$count_args = self::$args;

			do_action( 'log', 'get_daily_counts $date', 'tribe-events-query', $date );

			$count_args['eventDisplay']        = 'month';
			$count_args['eventDate']           = date( 'Y-m', strtotime( $date ) );
			$count_args['start_date']          = tribe_event_beginning_of_day( $date );
			$count_args['end_date']            = tribe_event_end_of_day( date( 'Y-m-t', strtotime( $date ) + 1 ) );
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

				return $day_link;
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
			do_action( 'log', 'setup view month view args', 'tribe-month', self::$args );
			$requested_date = isset( self::$args['eventDate'] ) ? self::$args['eventDate'] : tribe_get_month_view_date();

			$first_day_of_month = date( 'Y-m-01', strtotime( $requested_date ) );

			do_action( 'log', 'eventDate', 'tribe-events-query', $first_day_of_month );

			// get all upcoming ids to hide so we're not querying 31 times
			self::$hide_upcoming_ids = TribeEventsQuery::getHideFromUpcomingEvents();

			$year  = date( 'Y', strtotime( $first_day_of_month ) );
			$month = date( 'm', strtotime( $first_day_of_month ) );

			$startOfWeek = get_option( 'start_of_week', 0 );


			self::get_daily_counts( $first_day_of_month );

			if ( empty( self::$tribe_bar_args ) ) {
				foreach ( $_REQUEST as $key => $value ) {
					if ( $value && strpos( $key, 'tribe' ) === 0 && $key != 'tribe-bar-date' ) {
						self::$tribe_bar_args[$key] = $value;
					}
				}
			}

			// Var'ng up days, months and years
			self::$today         = date_i18n( 'd' );
			self::$current_month = date_i18n( 'm' );
			self::$current_year  = date_i18n( 'Y' );

			// single dimensional array of days for the month
			$days = array();

			// setup counters
			$rawOffset         = date( 'w', strtotime( $first_day_of_month ) ) - $startOfWeek;
			$prev_month_offset = (int) ( ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset ); // month begins on day x
			$days_in_month     = (int) date( 't', strtotime( $first_day_of_month ) );
			$days_in_calendar  = $days_in_month + $prev_month_offset;
			while ( $days_in_calendar % 7 > 0 ) {
				$days_in_calendar ++;
			}

			$cur_calendar_day = 0;

			// fill month with required days for previous month
			if ( $prev_month_offset > 0 ) {
				$days = array_fill( 0, $prev_month_offset, array( 'date' => 'previous' ) );
			}

			// get $cur_calendar_day up to speed
			$cur_calendar_day += $prev_month_offset;

			$empty_query = new WP_Query();

			// add days for this month
			for ( $i = 0; $i < $days_in_month; $i ++ ) {
				$day  = $i + 1;
				$date = date( 'Y-m-d', strtotime( "$year-$month-$day" ) );

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
			if ( $cur_calendar_day < $days_in_calendar ) {
				$days = array_merge( $days, array_fill( $cur_calendar_day, $days_in_calendar - $cur_calendar_day, array( 'date' => 'next' ) ) );
			}

			// store set of found days for use in calendar loop functions
			self::$calendar_days = $days;
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
			$ppf          = '';
			$calendar_day = self::$calendar_days[self::$current_day];
			if ( $calendar_day['date'] == 'previous' || $calendar_day['date'] == 'next' ) {
				$ppf = 'tribe-events-othermonth';
			} else {
				$ppf = 'tribe-events-thismonth';
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
			}

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
		 * @return string Classes
		 **/
		public function event_classes( $classes ) {

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
