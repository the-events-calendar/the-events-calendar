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

if ( ! class_exists( 'Tribe__Events__Template__Month' ) ) {
	/**
	 * Month view template class
	 */
	class Tribe__Events__Template__Month extends Tribe__Events__Template_Factory {
		/**
		 * Month Type Masks
		 */
		const PREVIOUS_MONTH = - 1;
		const CURRENT_MONTH = 0;
		const NEXT_MONTH = 1;

		/**
		 * Prefix for month view ajax actions
		 */
		const AJAX_HOOK = 'tribe_calendar';

		/**
		 * Number of events per day
		 * @var int
		 * @see tribe_events_month_day_limit
		 */
		private $events_per_day;

		/**
		 * Array of days of the month
		 * @var array
		 */
		private static $calendar_days = array();

		/**
		 * Internal pointer to current day in the month view loop
		 * @var int
		 */
		private static $current_day = - 1;

		/**
		 * Internal pointer to current week in the month view loop
		 * @var int
		 */
		private static $current_week = - 1;

		/**
		 * Query args
		 * @var array|null
		 */
		protected $args;

		/**
		 * Indicates the array index marking the first entry for the current month.
		 * @var int
		 */
		protected $current_month_begins;

		/**
		 * Indicates the array index marking the last entry for the current month.
		 * @var int
		 */
		protected $current_month_ends;

		/**
		 * CSS class for the month view wrapper
		 * @var string
		 */
		protected $body_class = 'events-gridview';

		/**
		 * Excerpt length on month view tooltips
		 * @var int
		 */
		protected $excerpt_length = 30;

		/**
		 * Static asset packages required for month view functionality
		 * @var array
		 */
		protected $asset_packages = array( 'ajax-calendar' );

		/**
		 * HTML cache holder
		 * @var Tribe__Events__Template_Part_Cache
		 */
		private $html_cache;

		/**
		 * Whether the HTML cache is enabled
		 * @var boolean
		 */
		private $use_cache;

		/**
		 * The events in this month
		 * @var
		 */
		private $events_in_month;

		/**
		 * The month date that was requested
		 * @var string
		 */
		private $requested_date;

		/**
		 * The first date to show on the calendar grid (may be in the previous month)
		 * @var bool|string
		 */
		private $first_grid_date;

		/**
		 * The last date to show on the calendar grid (may be in the next month)
		 * @var bool|string
		 */
		private $final_grid_date;


		/**
		 * Set the notices used on month view.
		 *
		 * @param array $args Set of $wp_query params for the month view, if none passed then will default to $wp_query.
		 */
		public function __construct( $args = null ) {
			if ( $args === null ) {
				global $wp_query;
				$args = $wp_query->query;

				if ( ! empty( $wp_query->query_vars['meta_query'] ) ) {
					$args['meta_query'] = $wp_query->query_vars['meta_query'];
				}
			}

			$this->use_cache = tribe_get_option( 'enable_month_view_cache', false );

			// Cache the result of month/content.php
			if ( $this->use_cache ) {
				$cache_expiration = apply_filters( 'tribe_events_month_view_transient_expiration', HOUR_IN_SECONDS );
				$this->html_cache = new Tribe__Events__Template_Part_Cache( 'month/content.php', serialize( $args ), $cache_expiration, 'save_post' );
			}

			$args                  = (array) $args;
			$this->args            = $args;
			$this->events_per_day  = apply_filters( 'tribe_events_month_day_limit', tribe_get_option( 'monthEventAmount', '3' ) );
			$this->requested_date  = $this->requested_date();
			$this->first_grid_date = self::calculate_first_cell_date( $this->requested_date );
			$this->final_grid_date = self::calculate_final_cell_date( $this->requested_date );

			$args = array_merge( $args, array(
				'fields'         => 'ids',
				'start_date'     => tribe_event_beginning_of_day( $this->first_grid_date ),
				'end_date'       => tribe_event_end_of_day( $this->final_grid_date ),
				'post_status'    => array( 'publish' ),
				'posts_per_page' => - 1,
			) );

			if ( is_user_logged_in() ) {
				$args['post_status'][] = 'private';
			}

			$args = apply_filters( 'tribe_events_in_month_args', $args );

			// get all the ids for the events in this month, speeds up queries
			$this->events_in_month = tribe_get_events( $args );

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
		 */
		protected function hooks() {
			parent::hooks();

			// Since we set is_post_type_archive to true on month view, this prevents 'Events' from being added to the page title
			add_filter( 'post_type_archive_title', '__return_false', 10 );

			if ( ! empty( $this->events_in_month ) ) {
				add_filter( 'tribe_events_month_has_events', '__return_true' );
			}
		}

		/**
		 * Unhook all the hooks set up on this view
		 *
		 * @return void
		 */
		protected function unhook() {
			parent::unhook();
			remove_filter( 'post_type_archive_title', '__return_false', 10 );
			if ( ! empty( $this->events_in_month ) ) {
				remove_filter( 'tribe_events_month_has_events', '__return_true' );
			}
		}

		/**
		 * Set the notices used on month view
		 *
		 * @return void
		 */
		public function set_notices() {
			// Our focus is on the current month, not the complete range of events included in the current month view
			// (which may include some leading/trailing days of the next and previous months)
			$slice_length         = $this->current_month_ends - $this->current_month_begins + 1;
			$current_month_counts = array_slice( self::$calendar_days, $this->current_month_begins, $slice_length );

			foreach ( $current_month_counts as $day ) {
				if ( $day['events']->have_posts() ) {
					// some events were found, no need to continue
					return;
				}
			}

			// if we got this far, it means no events were found
			$this->nothing_found_notice();
		}

		/**
		 * Sets an appropriate no results found message.
		 *
		 * @return void
		 */
		protected function nothing_found_notice() {
			if ( ! empty( $this->args['suppress_nothing_found_notice'] ) ) {
				return;
			}

			$events_label_plural = tribe_get_event_label_plural();
			list( $search_term, $tax_term, $geographic_term ) = $this->get_search_terms();

			if ( ! empty( $search_term ) ) {
				Tribe__Events__Main::setNotice( 'event-search-no-results', sprintf( __( 'There were no results found for <strong>"%s"</strong> this month. Try searching next month.', 'tribe-events-calendar' ), esc_html( $search_term ) ) );
			} // if attempting to view a category archive.
			elseif ( ! empty( $tax_term ) ) {
				Tribe__Events__Main::setNotice( 'events-not-found', sprintf( __( 'No matching %s listed under %s. Please try viewing the full calendar for a complete list of events.', 'tribe-events-calendar' ), strtolower( $events_label_plural ), $tax_term ) );
			} else {
				Tribe__Events__Main::setNotice( 'event-search-no-results', __( 'There were no results found.', 'tribe-events-calendar' ) );
			}
		}

		/**
		 * Get the title for month view
		 *
		 * @param string $original_title
		 * @param string $sep
		 *
		 * @return string
		 */
		protected function get_title( $original_title, $sep = null ) {
			$new_title = parent::get_title( $original_title, $sep );
			if ( get_query_var( 'eventDate' ) && has_filter( 'tribe_month_grid_view_title' ) ) {
				_deprecated_function( "The 'tribe_month_grid_view_title' filter", '3.8', " the 'tribe_get_events_title' filter" );
				$title_date = date_i18n( tribe_get_option( 'monthAndYearFormat', 'F Y' ), strtotime( get_query_var( 'eventDate' ) ) );
				$new_title  = apply_filters( 'tribe_month_grid_view_title', $new_title, $sep, $title_date );
			} elseif ( has_filter( 'tribe_events_this_month_title' ) ) {
				_deprecated_function( "The 'tribe_events_this_month_title' filter", '3.8', " the 'tribe_get_events_title' filter" );
				$new_title = apply_filters( 'tribe_events_this_month_title', $new_title, $sep );
			}

			return $new_title;
		}

		/**
		 * Get the view more link
		 *
		 * @param integer $date
		 *
		 * @return string
		 */
		private static function view_more_link( $date ) {
			$day_link       = tribe_get_day_link( $date );
			$tribe_bar_args = self::get_tribe_bar_args();
			if ( ! empty( $tribe_bar_args ) ) {
				$day_link = add_query_arg( $tribe_bar_args, $day_link );
			}

			return esc_url_raw( $day_link );
		}

		/**
		 * Get the events for a single day
		 *
		 * @param string $date
		 *
		 * @return WP_Query
		 */
		private function get_daily_events( $date ) {

			$args   = wp_parse_args(
				array(
					'start_date'     => tribe_event_beginning_of_day( $date ),
					'end_date'       => tribe_event_end_of_day( $date ),
					'posts_per_page' => $this->events_per_day,
					'post__in'       => $this->events_in_month,
					'orderby'        => 'menu_order',
				), $this->args
			);
			$result = tribe_get_events( $args, true );

			return $result;
		}

		/**
		 * Sets up an array of $days based on the current query, that can be used in the calendar loop
		 *
		 * @return void
		 */
		public function setup_view() {

			if ( $this->use_cache && $this->html_cache->get() !== false ) {
				return;
			}

			$days = array();

			$date = $this->first_grid_date; // Start with the first grid date

			// don't run a query if no events in the month
			if ( empty( $this->events_in_month ) ) {
				$empty_query = new WP_Query();
			}

			// Populate complete date range including leading/trailing days from adjacent months
			while ( $date <= $this->final_grid_date ) {

				if ( ! empty( $this->events_in_month ) ) {
					$day_events = self::get_daily_events( $date );
				} else {
					$day_events = $empty_query;
				}


				$day = (int) substr( $date, - 2 );

				$prev_month = (int) substr( $date, 5, 2 ) < (int) substr( $this->requested_date, 5, 2 );
				$next_month = (int) substr( $date, 5, 2 ) > (int) substr( $this->requested_date, 5, 2 );

				$month_type = self::CURRENT_MONTH;
				if ( $prev_month ) {
					$month_type = self::PREVIOUS_MONTH;
				}
				if ( $next_month ) {
					$month_type = self::NEXT_MONTH;
				}

				$days[] = array(
					'daynum'       => $day,
					'date'         => $date,
					'events'       => $day_events,
					'total_events' => $day_events->found_posts,
					'view_more'    => ( $day_events->found_posts > $this->events_per_day ) ? self::view_more_link( $date ) : false,
					'month'        => $month_type,
				);

				// Record the indicies marking the portion of the array relating to the current month
				if ( ! isset( $this->current_month_begins ) && self::CURRENT_MONTH === $month_type ) {
					$this->current_month_begins = count( $days ) - 1;
				}
				if ( isset( $this->current_month_begins ) && ! isset( $this->current_month_ends ) && self::CURRENT_MONTH !== $month_type ) {
					$this->current_month_ends = count( $days ) - 1;
				}

				// Advance forward one day
				$date = date( Tribe__Events__Date_Utils::DBDATEFORMAT, strtotime( "$date +1 day" ) );
			}

			// If the month ended without bleeding into the next month, our current_month_ends property may not be set
			if ( ! isset( $this->current_month_ends ) ) {
				$this->current_month_ends = count( $days ) - 1;
			}

			// store set of found days for use in calendar loop functions
			self::$calendar_days = $days;
		}

		/**
		 * Returns the requested date as a Y-m (yyyy-mm) formatted string.
		 *
		 * If the requested date is invalid (such as 1984-25) the current month is returned instead and
		 * an appropriate notice presented to the user.
		 *
		 * @return string
		 */
		protected function requested_date() {
			// We expect the date to be Y-m (yyyy-mm) format, ie year and date only
			$date = isset( $this->args['eventDate'] ) ? $this->args['eventDate'] : tribe_get_month_view_date();

			// Test and return unmodified if valid
			if ( false !== strtotime( $date . '-01' ) ) {
				return $date;
			} else {
				Tribe__Events__Main::setNotice( 'requested-date-invalid',
					sprintf( __( 'The requested date "%s" was not valid &ndash; showing the current month instead', 'tribe-events-calendar' ), esc_html( $date ) ) );

				return date_i18n( 'Y-m' );
			}
		}

		/**
		 * Loop through the $_REQUEST and find all tribe bar args.
		 *
		 * @return array
		 */
		protected static function get_tribe_bar_args() {
			$tribe_bar_args = array();
			foreach ( $_REQUEST as $key => $value ) {
				if ( $value && strpos( $key, 'tribe' ) === 0 && $key != 'tribe-bar-date' ) {
					$tribe_bar_args[ $key ] = $value;
				}
			}

			return $tribe_bar_args;
		}

		/**
		 * Return the date of the first day in the month view grid.
		 *
		 * This is not necessarily the 1st of the specified month, rather it is the date of the
		 * first grid cell which could be anything upto 6 days earlier than the 1st of the month.
		 *
		 * @param string  $month
		 * @param integer $start_of_week
		 *
		 * @return bool|string (Y-m-d)
		 */
		public static function calculate_first_cell_date( $month, $start_of_week = null ) {
			if ( null === $start_of_week ) {
				$start_of_week = (int) get_option( 'start_of_week', 0 );
			}

			$day_1 = Tribe__Events__Date_Utils::first_day_in_month( $month );
			if ( $day_1 < $start_of_week ) {
				$day_1 += 7;
			}

			$diff = $day_1 - $start_of_week;
			if ( $diff >= 0 ) {
				$diff = "-$diff";
			}

			try {
				$date = new DateTime( $month );
				$date = new DateTime( $date->format( 'Y-m-01' ) );
				$date->modify( "$diff days" );

				return $date->format( Tribe__Events__Date_Utils::DBDATEFORMAT );
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Return the date of the first day in the month view grid.
		 *
		 * This is not necessarily the last day of the specified month, rather it is the date of
		 * the final grid cell which could be anything upto 6 days into the next month.
		 *
		 * @param string  $month
		 * @param integer $start_of_week
		 *
		 * @return bool|string (Y-m-d)
		 */
		public static function calculate_final_cell_date( $month, $start_of_week = null ) {
			if ( null === $start_of_week ) {
				$start_of_week = (int) get_option( 'start_of_week', 0 );
			}

			$last_day    = Tribe__Events__Date_Utils::last_day_in_month( $month );
			$end_of_week = Tribe__Events__Date_Utils::week_ends_on( $start_of_week );
			if ( $end_of_week < $last_day ) {
				$end_of_week += 7;
			}

			$diff = $end_of_week - $last_day;
			if ( $diff >= 0 ) {
				$diff = "+$diff";
			}

			try {
				$date = new DateTime( $month );
				$date = new DateTime( $date->format( 'Y-m-t' ) );
				$date->modify( "$diff days" );

				return $date->format( Tribe__Events__Date_Utils::DBDATEFORMAT );
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Checks whether there are more calendar days to display
		 *
		 * @return bool True if calendar days are available, false if not.
		 */
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
		 */
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
		 * @return void
		 */
		public static function rewind_days() {
			self::$current_day  = - 1;
			self::$current_week = - 1;
		}

		/**
		 * Returns the current day according to self::$current_day
		 *
		 * @return array|boolean
		 */
		public static function get_current_day() {
			if ( count( self::$calendar_days ) && self::$current_day < count( self::$calendar_days ) && isset( self::$calendar_days[ self::$current_day ] ) ) {
				return self::$calendar_days[ self::$current_day ];
			}

			return false;
		}

		/**
		 * Generates and returns a set of classes for the current day
		 *
		 * @return string Classes
		 */
		public static function day_classes() {

			$calendar_day           = self::get_current_day();
			$calendar_day_timestamp = strtotime( $calendar_day['date'] );
			$today                  = strtotime( 'today' );

			// Start by determining which month we're looking at
			if ( $calendar_day['month'] == self::CURRENT_MONTH ) {
				$classes = 'tribe-events-thismonth';
			} else {
				$classes = 'tribe-events-othermonth';
			}

			// Check if the calendar day is in the past, present, or future
			if ( $calendar_day_timestamp < $today ) {
				$classes .= ' tribe-events-past';
			} elseif ( $calendar_day_timestamp == $today ) {
				$classes .= ' tribe-events-present';
			} elseif ( $calendar_day_timestamp > $today ) {
				$classes .= ' tribe-events-future';
			}

			// The day has some events
			if ( $calendar_day['total_events'] > 0 ) {
				$classes .= ' tribe-events-has-events';
			}

			// Needed for mobile js
			$classes .= ' mobile-trigger tribe-event-day-' . date_i18n( 'd', $calendar_day_timestamp );

			// Determine which column of the grid the day is in
			$column = ( self::$current_day ) - ( self::$current_week * 7 );
			if ( $column > 0 && ( $column % 4 == 0 || $column % 5 == 0 || $column % 6 == 0 ) ) {
				$classes .= ' tribe-events-right';
			}

			return $classes;
		}

		/**
		 * Returns self::$current_week
		 *
		 * @return int $current_week
		 */
		public static function get_current_week() {
			return self::$current_week;
		}

		/**
		 * Generates and returns a set of classes for the current day
		 *
		 * @param  string $classes = ''
		 *
		 * @return string Classes
		 */
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
			foreach ( tribe_get_organizer_ids( $post->ID ) as $organizer_id ) {
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
		public function ajax_response() {

			if ( isset( $_POST['eventDate'] ) && $_POST['eventDate'] ) {

				Tribe__Events__Query::init();

				$post_status = array( 'publish' );
				if ( is_user_logged_in() ) {
					$post_status[] = 'private';
				}
				// set the global query var for eventDisplay
				$query_args = array(
					'post_type'    => Tribe__Events__Main::POSTTYPE,
					'eventDisplay' => 'month',
					'eventDate'    => $_POST['eventDate'],
					'post_status'  => $post_status,
				);

				Tribe__Events__Main::instance()->displaying = 'month';

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
	} // class Tribe__Events__Template__Month
}
