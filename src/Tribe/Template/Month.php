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
		 * The path to the template file used for the view.
		 * This value is used in Shortcodes/Tribe_Events.php to
		 * locate the correct template file for each shortcode
		 * view.
		 *
		 * @var string
		 */
		public $view_path = 'month/content';

		/**
		 * Number of events per day
		 * @var int
		 * @see tribe_events_month_day_limit
		 */
		private $events_per_day;

		/**
		 * Grid day events
		 * @var array
		 */
		private $event_ids_by_day;

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
		 * @var Tribe__Template_Part_Cache
		 */
		private $html_cache;

		/**
		 * Number of seconds before the month view cache (when enabled) should be
		 * invalidated.
		 *
		 * @var int
		 */
		private $cache_expiration;

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
		private $queried_event_cats;

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
		public function __construct( $args = array() ) {

			// set the proper query args
			$this->set_args( $args );

			// include child categories in the query, save categories for reuse
			$this->set_queried_event_cats();

			/**
			 * Controls whether or not month view caching is enabled.
			 *
			 * Filtering this value can be useful if you need to implement
			 * a fine grained caching policy for month view.
			 *
			 * @param boolean $enable
			 * @param array   $args
			 */
			$this->use_cache = apply_filters( 'tribe_events_enable_month_view_cache',
				$this->should_enable_month_view_cache(),
				$this->args
			);

			// Cache the result of month/content.php
			if ( $this->use_cache ) {
				$this->cache_expiration = apply_filters( 'tribe_events_month_view_transient_expiration', HOUR_IN_SECONDS );
				$this->html_cache = new Tribe__Template_Part_Cache( 'month/content.php', serialize( $this->args ), $this->cache_expiration, 'save_post' );
			}

			$this->events_per_day  = apply_filters( 'tribe_events_month_day_limit', tribe_get_option( 'monthEventAmount', '3' ) );
			$this->requested_date  = $this->requested_date();
			$this->first_grid_date = self::calculate_first_cell_date( $this->requested_date );
			$this->final_grid_date = self::calculate_final_cell_date( $this->requested_date );


			// get all the ids for the events in this month, speeds up queries
			$this->set_events_in_month();

			// don't enqueue scripts and js when we're not constructing month view,
			// they'll have to be enqueued separately
			if ( ! tribe_is_month() ) {
				$this->asset_packages = array();
			}

			parent::__construct();
		}

		/**
		 * Indicates if month view cache should be enabled or not.
		 *
		 * If the month view cache setting itself is not enabled (or not set) then this
		 * method will always return false.
		 *
		 * In other cases, the default rules are to cache everything in the 2 months past
		 * to 12 months in the future range. This policy can be refined or replaced via
		 * the 'tribe_events_enable_month_view_cache' filter hook.
		 *
		 * @return bool
		 */
		protected function should_enable_month_view_cache() {
			// Respect the month view caching setting
			if ( ! tribe_get_option( 'enable_month_view_cache', true ) ) {
				return false;
			}

			// Default to always caching the current month
			if ( ! isset( $this->args[ 'eventDate' ] ) ) {
				return true;
			}

			// If the eventDate argument is not in the expected format then do not cache
			if ( ! preg_match( '/^[0-9]{4}-[0-9]{1,2}$/', $this->args[ 'eventDate' ] ) ) {
				return false;
			}

			// If the requested month is more than 2 months in the past, do not cache
			if ( $this->args[ 'eventDate' ] < date_i18n( 'Y-m', Tribe__Date_Utils::wp_strtotime( '-2 months' ) ) ) {
				return false;
			}

			// If the requested month is more than 1yr in the future, do not cache
			if ( $this->args[ 'eventDate' ] > date_i18n( 'Y-m', Tribe__Date_Utils::wp_strtotime( '+1 year' ) ) ) {
				return false;
			}

			// In all other cases, let's cache it!
			return true;
		}

		/**
		 * Returns an array containing the IDs of all the events in the month.
		 *
		 * @return array
		 */
		public function get_events_in_month_ids() {
			return $this->has_events() ? wp_list_pluck( $this->events_in_month, 'ID' ) : array();
		}

		/**
		 * Add any special hooks for this view
		 * any actions added here should also be removed in the unhook function
		 *
		 */
		protected function hooks() {
			parent::hooks();

			// Since we set is_post_type_archive to true on month view, this prevents 'Events' from being added to the page title
			add_filter( 'post_type_archive_title', '__return_false', 10 );

			if ( ! empty( $this->events_in_month ) ) {
				add_filter( 'tribe_events_month_has_events', array( $this, 'has_events' ) );
			}

			// Print JSON-LD markup on the `wp_head`
			add_action( 'wp_head', array( $this, 'json_ld_markup' ) );
		}

		/**
		 * When dealing with a place that has multiple events, we need to pass all the events as the first param
		 * to allow the class to echo the correct JSON-LD script
		 *
		 * @return void
		 */
		public function json_ld_markup() {

			if ( ! $this->use_cache ) {
				$this->produce_json_ld_markup( true );
				return;
			}

			$cache     = new Tribe__Cache();
			$prefix = 'events_month_jsonld';
			if ( tribe_is_event_category() ) {
				$prefix = sprintf( 'events_cat_%d_month_jsonld', get_queried_object_id() );
			}
			$cache_key = $this->get_month_view_cache_key( $prefix );
			$json_ld   = $cache->get_transient( $cache_key, 'save_post' );

			if ( ! $json_ld ) {
				$json_ld = $this->produce_json_ld_markup();
				$cache->set_transient( $cache_key, $json_ld, $this->cache_expiration, 'save_post' );
			}

			echo $json_ld;
		}

		/**
		 * Renders or returns the JSON LD markup.
		 *
		 * @param bool $echo
		 *
		 * @return string
		 */
		protected function produce_json_ld_markup( $echo = false ) {
			if ( ! $echo ) {
				ob_start();
			}

			$events = wp_list_pluck( $this->events_in_month, 'ID' );
			if ( tribe_is_event_category() ) {
				$events = $this->filter_by_current_term( $events );
			}

			Tribe__Events__JSON_LD__Event::instance()->markup( $events );

			if ( ! $echo ) {
				return ob_get_clean();
			}
		}

		/**
		 * Remove any event that is not the same as the current term of the array of events, it return a modified events
		 * array with the events that only has the current term or original events if the term is not valid.
		 *
		 * @since 4.6.9
		 *
 		 * @param array
		 *
		 * @return array
		 */
		private function filter_by_current_term( $events ) {
			$taxonomy = get_query_var( 'taxonomy' );
			$current_term = get_term_by( 'id', get_queried_object_id(), $taxonomy );

			// get_term_by returns false if term does not exists or on failure.
			if ( false === $current_term ) {
				return $events;
			}

			foreach ( (array) $events as $key => $event_id ) {
				if ( ! has_term( $current_term->term_id, $taxonomy, $event_id ) ) {
					unset( $events[ $key ] );
				}
			}
			return $events;
		}

		/**
		 * Unhook all the hooks set up on this view
		 *
		 */
		protected function unhook() {
			parent::unhook();
			remove_filter( 'post_type_archive_title', '__return_false', 10 );

			if ( ! empty( $this->events_in_month ) ) {
				remove_filter( 'tribe_events_month_has_events', array( $this, 'has_events' ) );
			}

			remove_action( 'wp_head', array( $this, 'json_ld_markup' ) );
		}

		/**
		 * Set the correct args using either passed args, ajax request, or wp_query
		 *
		 *
		 * @param array $args
		 */
		protected function set_args( $args = array() ) {

			$doing_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ? true : false;

			if ( empty( $args ) && $doing_ajax ) {
				$post_status = array( 'publish' );
				if ( is_user_logged_in() ) {
					$post_status[] = 'private';
				}
				// set the global query var for eventDisplay
				$args = array(
					'post_type'    => Tribe__Events__Main::POSTTYPE,
					'eventDisplay' => 'month',
					'eventDate'    => $_POST['eventDate'],
					'post_status'  => $post_status,
					'featured'     => tribe( 'tec.featured_events' )->featured_events_requested(),
				);
			}

			if ( empty( $args ) ) {
				// if no args were passed to the constructor, get them from $wp_query
				global $wp_query;
				$args = $wp_query->query;

				if ( ! empty( $wp_query->query_vars['meta_query'] ) ) {
					$args['meta_query'] = $wp_query->query_vars['meta_query'];
				}
			}

			$this->args = $args;

		}

		/**
		 * Set the notices used on month view
		 *
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
		 */
		protected function nothing_found_notice() {
			if ( ! empty( $this->args['suppress_nothing_found_notice'] ) ) {
				return;
			}

			$events_label_plural_lowercase = tribe_get_event_label_plural_lowercase();
			list( $search_term, $tax_term, $geographic_term ) = $this->get_search_terms();

			if ( ! empty( $search_term ) ) {
				Tribe__Notices::set_notice( 'event-search-no-results', sprintf( esc_html__( 'There were no results found for %s this month. Try searching next month.', 'the-events-calendar' ),
					'<strong>"' . esc_html( $search_term ) . '"</strong>' ) );
			} // if attempting to view a category archive.
			elseif ( ! empty( $tax_term ) ) {
				Tribe__Notices::set_notice( 'events-not-found', sprintf( esc_html__( 'No matching %1$s listed under %2$s. Please try viewing the full calendar for a complete list of events.', 'the-events-calendar' ), $events_label_plural_lowercase, $tax_term ) );
			} else {
				Tribe__Notices::set_notice( 'event-search-no-results', esc_html__( 'There were no results found.', 'the-events-calendar' ) );
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
				$title_date = date_i18n( tribe_get_date_option( 'monthAndYearFormat', 'F Y' ), strtotime( get_query_var( 'eventDate' ) ) );
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
		 * Set the queried terms as a class property
		 * Include child categories of the category currently being viewed
		 */
		protected function set_queried_event_cats() {

			// Check the request for tribe_event_category
			if ( ! empty( $_POST['tribe_event_category'] ) ) {
				$this->args[ Tribe__Events__Main::TAXONOMY ] = $_POST['tribe_event_category'];
			} elseif ( ! empty( $_GET['tribe_event_category'] ) ) {
				$this->args[ Tribe__Events__Main::TAXONOMY ] = $_GET['tribe_event_category'];
			}

			$terms   = array();
			$term_id = isset( $this->args[ Tribe__Events__Main::TAXONOMY ] ) ? $this->args[ Tribe__Events__Main::TAXONOMY ] : null;

			// get the term by id if it's an int
			if ( is_int( $term_id ) ) {
				$terms[0] = $term_id;
			} elseif ( is_string( $term_id ) ) {
				// get the term by slug if it's a string
				$term = get_term_by( 'slug', $term_id, Tribe__Events__Main::TAXONOMY );
				if ( ! ( is_wp_error( $term ) || empty( $term ) ) ) {
					$terms[0] = $term->term_id;
				}
			}

			// make sure child terms are included
			if ( ! empty( $terms ) ) {
				$term_children = get_term_children( $terms[0], Tribe__Events__Main::TAXONOMY );
				if ( is_array( $term_children ) ) {
					$terms = array_merge( $terms, $term_children );
				}
			}
			$this->queried_event_cats = $terms;
		}

		/**
		 * Get all the events in the month by directly querying the postmeta table
		 * Also caches the postmeta and terms for the found events
		 */
		protected function set_events_in_month() {
			global $wpdb;

			$cache     = new Tribe__Cache();
			$cache_key = $this->get_month_view_cache_key( 'events_in_month' );

			// We always use the object cache if available
			$cache_getter = 'get';
			$cache_setter = 'set';
			$expiration = 0;

			// If the site owner has explicitly enabled month view caching however let's use
			// transients instead, to guarantee persistence
			if ( $this->use_cache ) {
				$cache_getter = 'get_transient';
				$cache_setter = 'set_transient';
				$expiration = $this->cache_expiration;
			}

			// If we have a cached result, use that
			$cached_events = $cache->$cache_getter( $cache_key, 'save_post' );

			if ( $cached_events !== false ) {
				$this->events_in_month = $cached_events;
				return;
			}

			$post_stati = array( 'publish' );
			if ( is_user_logged_in() ) {
				$post_stati[] = 'private';
			}

			$post_stati = implode( "','", $post_stati );
			$ignore_hidden_events_AND = $this->hidden_events_fragment();

			$start_date = tribe_beginning_of_day( $this->first_grid_date );
			$end_date   = tribe_end_of_day( $this->final_grid_date );

			/**
			 * Allow bypassing the events in month SQL query to return events an alternative way.
			 *
			 * @param null|array $events_in_month An array of events in month (default null, run the SQL query like normal)
			 * @param string     $start_date      The start date to filter the queried events by
			 * @param string     $end_date        The end date to filter the queried events by
			 *
			 * @since 4.5.9
			 */
			$events_in_month = apply_filters( 'tribe_events_month_get_events_in_month', null, $start_date, $end_date );

			if ( null === $events_in_month ) {
				$start_date_sql = esc_sql( $start_date );
				$end_date_sql = esc_sql( $end_date );

				$events_request = "SELECT tribe_event_start.post_id as ID,
							tribe_event_start.meta_value as EventStartDate,
							tribe_event_end_date.meta_value as EventEndDate
					FROM $wpdb->postmeta AS tribe_event_start
					LEFT JOIN $wpdb->posts ON tribe_event_start.post_id = $wpdb->posts.ID
					LEFT JOIN $wpdb->postmeta as tribe_event_end_date ON ( tribe_event_start.post_id = tribe_event_end_date.post_id AND tribe_event_end_date.meta_key = '_EventEndDate' )
					WHERE $ignore_hidden_events_AND tribe_event_start.meta_key = '_EventStartDate'
					AND (
						(
							tribe_event_start.meta_value >= '{$start_date_sql}'
							AND tribe_event_start.meta_value <= '{$end_date_sql}'
						)
						OR (
							tribe_event_end_date.meta_value >= '{$start_date_sql}'
							AND tribe_event_end_date.meta_value <= '{$end_date_sql}'
						)
						OR (
							tribe_event_start.meta_value < '{$start_date_sql}'
							AND tribe_event_end_date.meta_value > '{$end_date_sql}'
						)
					)
					AND $wpdb->posts.post_status IN('$post_stati')
					ORDER BY $wpdb->posts.menu_order ASC, DATE(tribe_event_start.meta_value) ASC, TIME(tribe_event_start.meta_value) ASC;
					";

				$events_in_month = $wpdb->get_results( $events_request );
			}

			$this->events_in_month = $events_in_month;

			// cache the postmeta and terms for all these posts in one go
			$event_ids_in_month = wp_list_pluck( $this->events_in_month, 'ID' );
			update_object_term_cache( $event_ids_in_month, Tribe__Events__Main::POSTTYPE );
			update_postmeta_cache( $event_ids_in_month );

			// cache the found events in the object cache
			$cache->$cache_setter( $cache_key, $this->events_in_month, $expiration, 'save_post' );
		}

		/**
		 * Returns a string that can be used as a cache key for the current month.
		 *
		 * @param string $prefix
		 *
		 * @return string
		 */
		protected function get_month_view_cache_key( $prefix ) {
			$grid_start_datetime = tribe_beginning_of_day( $this->first_grid_date );
			$grid_end_datetime   = tribe_end_of_day( $this->final_grid_date );
			return $prefix . '-' . $grid_start_datetime . '-' . $grid_end_datetime;
		}

		/**
		 * Returns a posts-not-in SQL fragment for use in a WHERE clause or else an empty
		 * string if it is unneeded.
		 *
		 * @return string
		 */
		protected function hidden_events_fragment() {
			global $wpdb;

			// Despite the method name, this obtains a list of post IDs to be hidden from *all* event listings
			$ignore_events = Tribe__Events__Query::getHideFromUpcomingEvents();

			// If it is empty we don't need to do anything further
			if ( empty( $ignore_events ) ) {
				return '';
			}

			// Let's ensure they are all absolute integers then collapse into a string
			$ignore_events = implode( ',', array_map( 'absint', $ignore_events ) );

			// Terminate with AND so it can easily be combined with the rest of the WHERE clause
			return " $wpdb->posts.ID NOT IN ( $ignore_events ) AND ";
		}

		/**
		 * Retrieves beginning/end times for a given date
		 *
		 * @param string $date Y-m-d date string
		 * @param string $key Key of cached data to retrieve
		 *
		 * return string|int
		 */
		private function get_cutoff_details( $date, $key ) {
			static $beginnings_and_ends = array();

			if ( empty( $beginnings_and_ends[ $date ] ) ) {
				$beginnings_and_ends[ $date ] = array(
					'beginning' => tribe_beginning_of_day( $date ),
					'end' => tribe_end_of_day( $date ),
				);

				$beginnings_and_ends[ $date ]['beginning_timestamp'] = strtotime( $beginnings_and_ends[ $date ]['beginning'] );
				$beginnings_and_ends[ $date ]['end_timestamp'] = strtotime( $beginnings_and_ends[ $date ]['end'] );
			}

			return $beginnings_and_ends[ $date ][ $key ];
		}

		/**
		 * Breaks the possible collection of events down by grid date
		 *
		 * @param string $date Y-m-d formatted date to retrieve events for
		 *
		 * @return array
		 */
		private function get_event_ids_by_day( $date ) {
			if ( ! $this->event_ids_by_day ) {
				$this->event_ids_by_day = array();

				// Let's loop over all of the events in the month and assign them to days
				foreach ( $this->events_in_month as $event ) {
					// if we're querying by category and the event doesn't have it, skip the event
					if ( ! empty ( $this->queried_event_cats ) ) {
						if ( ! has_term( $this->queried_event_cats, Tribe__Events__Main::TAXONOMY, $event ) ) {
							continue;
						}
					}

					$event_start = strtotime( tribe_get_start_date( $event->ID, true, Tribe__Date_Utils::DBDATETIMEFORMAT ) );
					$event_end   = strtotime( tribe_get_end_date( $event->ID, true, Tribe__Date_Utils::DBDATETIMEFORMAT ) );
					$order = get_post_field( 'menu_order', $event->ID );

					// Builds the Index to allow a better ordering of events
					$order_index = $order . ':' . $event_start . ':' . $event->ID;

					$start = date( 'Y-m-d', $event_start );
					$end = date( 'Y-m-d', $event_end );

					$beginning_of_start           = $this->get_cutoff_details( $start, 'beginning' );
					$beginning_of_start_timestamp = $this->get_cutoff_details( $start, 'beginning_timestamp' );
					$end_of_start                 = $this->get_cutoff_details( $start, 'end' );
					$end_of_start_timestamp       = $this->get_cutoff_details( $start, 'end_timestamp' );
					$beginning_of_end             = $this->get_cutoff_details( $end, 'beginning' );
					$beginning_of_end_timestamp   = $this->get_cutoff_details( $end, 'beginning_timestamp' );

					// if the start of the event is earlier than the beginning of the day, consider the event
					// as starting on the day before
					//
					// Example 1:
					// Assuming a cut-off of 6:00am and an event start date/time of August 2nd @ 5:00am. The
					// "start" DATE would be August 2nd and the beginning of the "start" DATE would be August
					// 2nd @ 6:00am. Therefore, the event start DATE shoud be altered to be a day earlier
					// (August 1st) (Note: the following if statement conditional would be true)
					if ( $event_start < $beginning_of_start_timestamp ) {
						$start = date( 'Y-m-d', strtotime( '-1 day', strtotime( $start ) ) );
					}

					// Subtract a day from the $end if it is:
					// * earlier than the beginning of the start DATE OR
					// * earlier than the beginning of the end DATE OR
					// * earlier than the end of the start DATE (as long as the beginning of the end DATE is greater than that of the start DATE)
					//
					// Example 1:
					// Assuming a cut-off of 6:00am and an event end date/time of August 2nd @ 7:00am. The
					// "end" DATE would be August 2nd and the beginning of the "end" DATE would be August
					// 2nd @ 6:00am. Therefore, the event end DATE shoud remain as August 2nd. (Note: the
					// following if statement conditional would be false)
					//
					// Example 2:
					// Assuming a cut-off of 6:00am and an event end date/time of August 2nd @ 5:00am. The
					// "end" DATE would be August 2nd and the beginning of the "end" DATE would be August
					// 2nd @ 6:00am. Therefore, the event end DATE shoud be altered to be a day earlier
					// (August 1st) (Note: this following if statement conditional would be true)
					if (
						$event_end < $beginning_of_start_timestamp
						|| $event_end < $beginning_of_end_timestamp
						|| (
							$event_end < $end_of_start_timestamp
							&& $beginning_of_end_timestamp > $end_of_start_timestamp
						)
					) {
						$end = date( 'Y-m-d', strtotime( '-1 day', strtotime( $end ) ) );
					}

					// determine if there's a difference in days between start and end
					$diff = strtotime( $end ) - strtotime( $start );

					if ( $diff > 0 ) {
						// There IS a difference. How many days?
						$diff_in_days = $diff / DAY_IN_SECONDS;

						// add the event to each day until the event end
						$new_start = $start;
						for ( $i = 0; $i <= $diff_in_days; $i++ ) {
							if ( ! isset( $this->event_ids_by_day[ $new_start ] ) ) {
								$this->event_ids_by_day[ $new_start ] = array();
							}

							$this->event_ids_by_day[ $new_start ][ $order_index ] = $event->ID;

							$new_start = date( 'Y-m-d', strtotime( '+1 day', strtotime( $new_start ) ) );
						}
					} else {
						// nope. The event is a single day event. Add it to the array
						if ( ! isset( $this->event_ids_by_day[ $start ] ) ) {
							$this->event_ids_by_day[ $start ] = array();
						}

						$this->event_ids_by_day[ $start ][ $order_index ] = $event->ID;
					}
				}

				// Now that we've built our event_ids_by_day, let's array_unique and sort
				foreach ( $this->event_ids_by_day as &$day ) {
					$day = array_unique( $day );
					ksort( $day );
				}
			}

			if ( empty( $this->event_ids_by_day[ $date ] ) ) {
				return array();
			}

			return $this->event_ids_by_day[ $date ];
		}

		/**
		 * Get the events for a single day
		 *
		 * @param string $date
		 *
		 * @return WP_Query
		 */
		private function get_daily_events( $date ) {

			$beginning_of_day           = $this->get_cutoff_details( $date, 'beginning' );
			$beginning_of_day_timestamp = $this->get_cutoff_details( $date, 'beginning_timestamp' );

			$end_of_day           = $this->get_cutoff_details( $date, 'end' );
			$end_of_day_timestamp = $this->get_cutoff_details( $date, 'end_timestamp' );

			$event_ids_on_date = $this->get_event_ids_by_day( $date );

			// post__in doesn't work when it's empty, so just don't run the query if there are no IDs
			if ( empty( $event_ids_on_date ) ) {
				return new WP_Query();
			}

			// this  will skip updating term and meta caches - those were already
			// updated in $this->set_events_in_month()
			// expected order of events: sticky events, ongoing multi day events, all day events, then by start time
			$args   = wp_parse_args(
				array(
					'eventDisplay'           => 'month',
					'posts_per_page'         => $this->events_per_day,
					'post__in'               => $event_ids_on_date,
					'start_date'             => $beginning_of_day,
					'end_date'               => $end_of_day,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'no_found_rows'          => false,
					'do_not_inject_date'     => true,

					// Don't replace `orderby` without taking in cosideration `menu_order`
					'orderby'                => 'post__in',
				), $this->args
			);

			/**
			  * Filter Daily Events Query Arguments.
			  *
			  * @param array $args an array of daily events query.
			  *
			  */
			 $args = apply_filters( 'tribe_events_month_daily_events_query_args', $args );

			// we don't need this join since we already checked it
			unset ( $args[ Tribe__Events__Main::TAXONOMY ] );

			return tribe_get_events( $args, true );
		}

		/**
		 * Sets up an array of $days based on the current query, that can be used in the calendar loop
		 *
		 */
		public function setup_view() {

			if ( $this->use_cache && $this->html_cache->get() !== false ) {
				return;
			}

			$days = array();

			$date = $this->first_grid_date; // Start with the first grid date

			// Populate complete date range including leading/trailing days from adjacent months
			while ( $date <= $this->final_grid_date ) {

				$day_events = $this->get_daily_events( $date );
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
					'daynum-id' => Tribe__Events__Utils__Id_Generator::generate_id( $day, $day ),
					'date'         => $date,
					'events'       => $day_events,
					'total_events' => $day_events->found_posts,
					'view_more'    => ( $day_events->found_posts > $this->events_per_day && $this->events_per_day >= 1 ) ? self::view_more_link( $date ) : false,
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
				$date = date( Tribe__Date_Utils::DBDATEFORMAT, strtotime( "$date +1 day" ) );
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
				Tribe__Notices::set_notice( 'requested-date-invalid',
					sprintf( esc_html__( 'The requested date "%s" was not valid &ndash; showing the current month instead', 'the-events-calendar' ), esc_html( $date ) ) );

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

			$day_1 = Tribe__Date_Utils::first_day_in_month( $month );
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

				return $date->format( Tribe__Date_Utils::DBDATEFORMAT );
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

			$last_day    = Tribe__Date_Utils::last_day_in_month( $month );
			$end_of_week = Tribe__Date_Utils::week_ends_on( $start_of_week );
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

				return $date->format( Tribe__Date_Utils::DBDATEFORMAT );
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
		 * Generates and returns a set of classes for the current day.
		 *
		 * @return string
		 */
		public static function day_classes() {
			$current_day  = self::get_current_day();
			$calendar_day = Tribe__Date_Utils::date_only( $current_day['date'] );
			$today        = date_i18n( Tribe__Date_Utils::DBDATEFORMAT );

			// Start by determining which month we're looking at
			if ( $current_day['month'] == self::CURRENT_MONTH ) {
				$classes = 'tribe-events-thismonth';
			} else {
				$classes = 'tribe-events-othermonth';
			}

			// Check if the calendar day is in the past, present, or future
			if ( $calendar_day < $today ) {
				$classes .= ' tribe-events-past';
			} elseif ( $calendar_day === $today ) {
				$classes .= ' tribe-events-present';
			} elseif ( $calendar_day > $today ) {
				$classes .= ' tribe-events-future';
			}

			// The day has some events
			if ( $current_day['total_events'] > 0 ) {
				$classes .= ' tribe-events-has-events';
			}

			// Needed for mobile js
			$daynum   = date( 'd', strtotime( $calendar_day ) );
			$classes .= ' mobile-trigger tribe-event-day-' . $daynum;

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

			// @todo review whether the erasure of any existing classes is generally desirable
			$classes = array();

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

			// Mark 'featured' events
			if ( tribe( 'tec.featured_events' )->is_featured( $post->ID ) ) {
				$classes[] = 'tribe-event-featured';
			}

			return $classes;
		}

		/**
		 * Month View Ajax Handler
		 *
		 */
		public function ajax_response() {

			if ( isset( $_POST['eventDate'] ) && $_POST['eventDate'] ) {

				Tribe__Events__Query::init();

				Tribe__Events__Main::instance()->displaying = 'month';

				global $wp_query;

				$wp_query = tribe_get_events( $this->args, true );

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

		public function has_events() {
			return (bool) $this->events_in_month;
		}

	} // class Tribe__Events__Template__Month
}
