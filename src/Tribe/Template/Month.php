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
		 * The category being viewed on month view
		 * @var
		 */
		private $queried_terms;

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


			// get all the ids for the events in this month, speeds up queries
			$this->set_events_in_month();

			// set the queried terms for use in the daily query sort
			$this->set_queried_terms();


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
		 * Set the queried terms as a class property so it can be repeatedly accessed in get_daily_events
		 *
		 * @see self::get_daily_events()
		 *
		 */
		protected function set_queried_terms() {

			global $wp_query;

			$terms   = array();
			$term_id = isset( $wp_query->query_vars[ Tribe__Events__Main::TAXONOMY ] ) ? $wp_query->query_vars[ Tribe__Events__Main::TAXONOMY ] : null;

			// get the term by id if it's an int
			if ( is_int( $term_id ) ) {
				$terms[0] = $term_id;
			} elseif ( is_string( $term_id ) ) {
				// get the term by slug if it's a string
				$term = get_term_by( 'slug', $term_id, Tribe__Events__Main::TAXONOMY );
				if ( ! is_wp_error( $term ) ) {
					$terms[0] = $term->term_id;
				}
			}

			// make sure child terms are included
			if ( ! empty( $terms ) && is_tax( Tribe__Events__Main::TAXONOMY ) ) {
				$terms = array_merge( $terms, get_term_children( $terms[0], Tribe__Events__Main::TAXONOMY ) );
			}

			$this->queried_terms = $terms;
		}


		/**
		 * Get all the events in the month by directly querying the postmeta table
		 * Also caches the postmeta and terms for the found events
		 */
		protected function set_events_in_month() {
			global $wpdb;

			$grid_start_datetime = tribe_event_beginning_of_day( $this->first_grid_date );
			$grid_end_datetime   = tribe_event_end_of_day( $this->final_grid_date );

			$cache     = new Tribe__Events__Cache();
			$cache_key = 'events_in_month' . $grid_start_datetime . '-' . $grid_end_datetime;

			// if we have a cached result, use that
			$cached_events = $cache->get( $cache_key, 'save_post' );
			if ( $cached_events !== false ) {
				$this->events_in_month = $cached_events;

				return;
			}

			$post_stati = array( 'publish' );
			if ( is_user_logged_in() ) {
				$post_stati[] = 'private';
			}

			$post_stati = implode( "','", $post_stati );

			// note: this query returns the event dates as timestamps, makes date comparison faster later
			$events_request        =
				$wpdb->prepare(
					"SELECT tribe_event_start.post_id as ID,
							tribe_event_start.meta_value as EventStartDate,
							tribe_event_end_date.meta_value as EventEndDate
					FROM $wpdb->postmeta AS tribe_event_start
					LEFT JOIN $wpdb->posts ON tribe_event_start.post_id = $wpdb->posts.ID
					LEFT JOIN $wpdb->postmeta as tribe_event_end_date ON ( tribe_event_start.post_id = tribe_event_end_date.post_id AND tribe_event_end_date.meta_key = '_EventEndDate' )
					WHERE tribe_event_start.meta_key = '_EventStartDate'
					AND ( (tribe_event_start.meta_value >= '%1\$s' AND  tribe_event_start.meta_value <= '%2\$s')
						OR (tribe_event_start.meta_value <= '%1\$s' AND tribe_event_end_date.meta_value >= '%1\$s')
						OR ( tribe_event_start.meta_value >= '%1\$s' AND  tribe_event_start.meta_value <= '%2\$s')
					)
					AND $wpdb->posts.post_status IN('$post_stati')
					ORDER BY $wpdb->posts.menu_order ASC, DATE(tribe_event_start.meta_value) ASC, TIME(tribe_event_start.meta_value) ASC;
					",
					$grid_start_datetime,
					$grid_end_datetime

				);
			$this->events_in_month = $wpdb->get_results( $events_request );

			// cache the postmeta and terms for all these posts in one go
			$event_ids_in_month = wp_list_pluck( $this->events_in_month, 'ID' );
			update_object_term_cache( $event_ids_in_month, Tribe__Events__Main::POSTTYPE );
			update_postmeta_cache( $event_ids_in_month );

			// cache the found events in the object cache
			$cache->set( $cache_key, $this->events_in_month, 0, 'save_post' );
		}

		/**
		 * Get the events for a single day
		 *
		 * @param string $date
		 *
		 * @return WP_Query
		 */
		private function get_daily_events( $date ) {

			$beginning_of_day           = tribe_event_beginning_of_day( $date );
			$beginning_of_day_timestamp = strtotime( $beginning_of_day );

			$end_of_day           = tribe_event_end_of_day( $date );
			$end_of_day_timestamp = strtotime( $end_of_day );

			$event_ids_on_date = array();

			// loop through all the events in the month and find the ones on the requested date
			foreach ( $this->events_in_month as $event ) {

				$event_start = strtotime( $event->EventStartDate );
				$event_end   = strtotime( $event->EventEndDate );

				// check if the event has the term being viewed, if not, skip it
				if ( ! empty ( $this->queried_terms ) ) {
					if ( ! has_term( $this->queried_terms, Tribe__Events__Main::TAXONOMY, $event->ID ) ) {
						continue;
					}
				}
				if ( Tribe__Events__Date_Utils::range_coincides( $beginning_of_day_timestamp, $end_of_day_timestamp, $event_start, $event_end ) ) {
					$event_ids_on_date[] = $event->ID;
				}
			}

			$event_ids_on_date = array_unique( $event_ids_on_date );
			sort( $event_ids_on_date );

			// post__in doesn't work when it's empty, so just don't run the query if there are no IDs
			if ( empty( $event_ids_on_date ) ) {
				return new WP_Query();
			}

			// this query will limit by the current category being viewed, and will skip updating
			// term and meta caches - those were already updated in $this->set_events_in_month()
			$args   = wp_parse_args(
				array(
					'posts_per_page' => $this->events_per_day,
					'post__in'               => $event_ids_on_date,
					'start_date'             => $beginning_of_day,
					'end_date'               => $end_of_day,
					'post_status'            => array( 'publish' ),
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
				), $this->args
			);

			if ( is_user_logged_in() ) {
				$args['post_status'][] = 'private';
			}

			$result = tribe_get_events( $args, true );

			$result->found_posts = count( $event_ids_on_date );

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

			// Populate complete date range including leading/trailing days from adjacent months
			while ( $date <= $this->final_grid_date ) {

					$day_events = self::get_daily_events( $date );
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

				$query_args = $this->maybe_inject_category_arg( $query_args );

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

		/**
		 * Injects the Tribe__Events__Main::TAXONOMY argument into query_args if passed in via POST or GET
		 *
		 * @param array $args Current query args to augment
		 *
		 * @return array Array of args
		 */
		public function maybe_inject_category_arg( $args ) {
			if ( ! empty( $_POST['tribe_event_category'] ) ) {
				$args[ Tribe__Events__Main::TAXONOMY ] = $_POST['tribe_event_category'];
			} elseif ( ! empty( $_GET['tribe_event_category'] ) ) {
				$args[ Tribe__Events__Main::TAXONOMY ] = $_GET['tribe_event_category'];
			}

			return $args;
		}
	} // class Tribe__Events__Template__Month
}
