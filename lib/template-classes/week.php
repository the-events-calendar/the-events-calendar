<?php
/**
 *
 *
 * @for     Week Grid Template
 * This file contains hooks and functions required to set up the week grid view.
 *
 * @package TribeEventsCalendarPro
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe_Events_Pro_Week_Template' ) ) {
	class Tribe_Events_Pro_Week_Template extends Tribe_PRO_Template_Factory {


		/**
		 * The handle for the css/js package needed for this view
		 *
		 * @var array
		 */
		protected $asset_packages = array( 'ajax-weekview' );

		/**
		 * Array of days currently being displayed in the week
		 *
		 * @var array
		 */
		private static $week_days = array();

		/**
		 * Internal counter for keeping track of the week view loop
		 *
		 * @var int
		 */
		private static $current_day = -1;

		/**
		 * Keeps track of the last event displayed
		 *
		 * @var object
		 */
		private static $previous_event;

		/**
		 * Hook used for wp ajax response on week view content
		 *
		 * @const
		 */
		const AJAX_HOOK = 'tribe_week';

		/**
		 * Set the notices used on week view
		 *
		 * @return void
		 * */
		function set_notices() {
			// We have events to display, no need for notices!
			if ( have_posts() ) {
				return;
			}

			$this->nothing_found_notice();
		}

		/**
		 * Display the text that shows when there were no events found on this view
		 *
		 * @return void
		 */
		protected function nothing_found_notice() {
			list( $search_term, $tax_term, $geographic_term ) = $this->get_search_terms();

			if ( ! empty( $search_term ) ) {
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'There were no results found for <strong>"%s"</strong> this week. Try searching another week.', 'tribe-events-calendar-pro' ), esc_html( $search_term ) ) );
			} elseif ( ! empty( $geographic_term ) ) {
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'No results were found for events in or near <strong>"%s"</strong> this week. Try searching another week.', 'tribe-events-calendar-pro' ), esc_html( $geographic_term ) ) );
			} // if attempting to view a category archive.
			elseif ( ! empty( $tax_term ) ) {
				TribeEvents::setNotice( 'events-not-found', sprintf( __( 'No matching events listed under %s. Please try viewing the full calendar for a complete list of events.', 'tribe-events-calendar' ), $tax_term ) );
			} else {
				TribeEvents::setNotice( 'event-search-no-results', __( 'No results were found for this week. Try searching another week.', 'tribe-events-calendar-pro' ) );
			}
		}

		/**
		 * Set up hooks for week view
		 *
		 * @return void
		 * */
		protected function hooks() {
			parent::hooks();
			add_filter( 'tribe_events_header_attributes', array( $this, 'header_attributes' ), 10, 2 );
			add_action( 'tribe_events_week_pre_setup_event', array( $this, 'manage_sensitive_info' ) );
			add_action( 'tribe_pre_get_template_part_pro/week/loop', array( $this, 'rewind_days' ) );
			add_action( 'tribe_post_get_template_part_pro/week/single-event', array( $this, 'set_previous_event' ), 10, 3 );
			add_action( 'tribe_pre_get_template_part_pro/week/single-event', array( $this, 'set_global_post' ), 10, 3 );
		}

		/**
		 * Set the global post on the hourly loop.
		 *
		 * @param $slug
		 * @param $name
		 * @param $data
		 */
		public function set_global_post($slug, $name, $data) {
			$GLOBALS['post'] = $data['event'];
		}


		/**
		 * Keep track of the last event that was outputted, used when determining if we need the overlap class
		 *
		 * @param $slug
		 * @param $name
		 * @param $data
		 */
		public function set_previous_event($slug, $name, $data) {
			self::$previous_event = $data['event'];
		}

		/**
		 * Add/remove filters to hide/show sensitive event info on password protected posts
		 *
		 * @param $post
		 *
		 * @return void
		 **/
		public function manage_sensitive_info( $post ) {

			parent::manage_sensitive_info( $post );

			if ( post_password_required( $post ) ) {
				add_filter( 'tribe_get_template_part_path_pro/week/single-event-tooltip.php', '__return_false' );
			} else {
				remove_filter( 'tribe_get_template_part_path_pro/week/single-event-tooltip.php', '__return_false' );
			}
		}

		/**
		 * Add header attributes for week view
		 *
		 * @return string
		 * */
		function header_attributes( $attrs, $current_view ) {
			global $wp_query;
			$attrs['data-view']        = 'week';
			$attrs['data-startofweek'] = get_option( 'start_of_week' );
			$attrs['data-baseurl']     = tribe_get_week_permalink( null, false );
			$attrs['data-date']        = Date( 'Y-m-d', strtotime( $wp_query->get( 'start_date' ) ) );
			return apply_filters( 'tribe_events_pro_header_attributes', $attrs, $current_view );
		}

		/**
		 * Perform any functions that need to happen before the view is rendered
		 *
		 * @return  void
		 */
		function setup_view() {
			$this->setup_days();
		}

		/**
		 * Set up the self::week_days array
		 *
		 * @return void
		 */
		function setup_days() {
			global $wp_query;
			$week_days = array();

			if ( $wp_query->have_posts() ) {

				$day       = $wp_query->get( 'start_date' );

				// build an array with 7 "day" elements,
				// each "day" is an array that contains the date and the associated all day / hourly events
				for ( $i = 0; $i < 7; $i ++ ) {

					$date               = date( 'Y-m-d', strtotime( $day . "+$i days" ) );
					$timestamp_date     = strtotime( $date );
					$timestamp_today    = strtotime( 'today' );


					$hourly_events      = array();
					$all_day_events     = array();

					// loop through all the wordpress posts and sort them into all day vs hourly
					foreach ( $wp_query->posts as $j => $event ) {
						$wp_query->posts[$j]->days_between = tribe_get_days_between( $event->_EventStartDate, $event->_EventEndDate, true );
						if ( tribe_event_is_on_date( $date, $event ) ) {
							if ( tribe_event_is_all_day( $event ) ) {
								$all_day_events[]   = $event;
							} else {
								$hourly_events[] = $event;
							}
						}
					}

					$display_format = apply_filters( 'tribe_events_pro_week_header_date_format', tribe_get_option( 'weekDayFormat', 'D jS' ) );
					$formatted_date = date_i18n( $display_format, strtotime( $date ) );

					// create the "day" element
					$week_days[] = array(
						'date'           => $date,
						'formatted_date' => $formatted_date,
						'is_today'       => ( $timestamp_date == $timestamp_today ) ? true : false,
						'is_past'        => ( $timestamp_date < $timestamp_today ) ? true : false,
						'is_future'      => ( $timestamp_date > $timestamp_today ) ? true : false,
						'hourly_events'  => $hourly_events,
						'all_day_events' => $all_day_events,
						'has_events'     => $hourly_events || $all_day_events,
					);
				}
			}
			self::$week_days = $week_days;

		}

		/**
		 * Return the $week_days array
		 */
		public function get_week_days() {
			return self::$week_days;
		}

		/**
		 * Build data attributes for an event, needed for week view js
		 *
		 * @param $event
		 *
		 * @return array
		 */
		public static function get_event_attributes($event) {

			$attrs = array();

			$event_start_timestamp = tribe_get_start_date( $event, null, 'U' );
			$event_end_timestamp   = tribe_get_end_date( $event, null, 'U' );

			if ( tribe_event_is_all_day( $event ) ) {
				$attrs['data-hour'] = 'all-day';
			} else {
				$start_of_day_timestamp = self::get_rounded_beginning_of_day( self::get_current_date() );
				$end_of_day_timestamp = self::get_rounded_end_of_day( self::get_current_date() );
				$data_hour = date( 'G', $event_start_timestamp );
				$data_min  = date( 'i', $event_start_timestamp );
				if ( $event_start_timestamp < $start_of_day_timestamp ) {
					if ( $event_end_timestamp > $end_of_day_timestamp ) {
						// if there is a day in between start/end we just want to fill the spacer with the total mins in the day.
						$duration = ( $end_of_day_timestamp - $start_of_day_timestamp ) / 60;
					} else {
						$duration = ( $event_end_timestamp - $start_of_day_timestamp ) / 60;
					}
					$data_hour              = date( 'G', $start_of_day_timestamp );
					$data_min               = date( 'i', $start_of_day_timestamp );
				} elseif ( $event_end_timestamp > $end_of_day_timestamp ) {
					// if the event is longer than a day we want to account for that with an offset
					$duration  = ( $end_of_day_timestamp - $event_start_timestamp ) / 60;
				} else {
					// for a default event continue as everything is normal
					$remaining_minutes_in_day = ( $end_of_day_timestamp - $event_start_timestamp / 60 );
					$duration                 = get_post_meta( $event->ID, '_EventDuration', true ) / 60;
					if ( $duration > $remaining_minutes_in_day ) {
						// this will happen in the case of a multi-day event that extends beyond the end of the day
						$duration = $remaining_minutes_in_day;
					}
				}
				$attrs['data-duration'] = abs( $duration );
				$attrs['data-hour']     = $data_hour;
				$attrs['data-min']      = $data_min;
			}
			return $attrs;

		}

		/**
		 * Checks whether there are more calendar days to display
		 *
		 * @return bool True if calendar days are available, false if not.
		 * */
		public static function have_days() {
			if ( self::$current_day < 6 ) {
				return true;
			}

			return false;
		}

		/**
		 * Increment the current day for tracking the current day of the week within the loop
		 *
		 * @return void
		 */
		public static function the_day() {
			if ( self::$current_day == 7 ) {
				self::rewind_days();
			}

			self::$current_day ++;

			return self::$week_days[ self::$current_day ];
		}

		/**
		 * Reset the internal counter for the current day
		 *
		 * @return void
		 */
		public static function rewind_days() {
			self::$current_day = - 1;
		}

		/**
		 * get current day array
		 *
		 * @return int
		 */
		public static function get_current_day() {
			return self::$week_days[ self::$current_day ];
		}

		/**
		 * get the current date based on the current day of week
		 *
		 * @return date( 'Y-m-d' )
		 */
		function get_current_date() {
			return date_i18n( 'Y-m-d', strtotime( self::$week_days[self::$current_day]['date'] ) );
		}

		/**
		 *
		 */
		function day_header_classes() {
			$day = self::get_current_day();
			$classes = 'column';
			if ( $day['is_today'] ) {
				$classes .= ' tribe-week-today';
			}
			return $classes;
		}

		/**
		 * css column classes used during loop
		 *
		 * @return void
		 */
		public static function column_classes() {

			$day = self::$week_days[ self::$current_day ];
			$classes = '';

			// Present
			if ( $day['is_today'] ) {
				$classes .= 'tribe-week-today';
			} // Past
			else if ( $day['is_past'] ) {
				$classes .= 'tribe-events-past';
			} // Future
			else if ( $day['is_future'] ) {
				$classes .= 'tribe-events-future';
			}
			// Has Events
			if ( ! empty ( $day['all_day_events'] ) || ! empty ( $day['hourly_events'] ) ) {
				$classes .= ' tribe-events-has-events';
			}

			return $classes;
		}

		/**
		 * css event wrapper classes used during loop
		 *
		 * @param $classes
		 *
		 * @return void
		 */
		function event_classes( $classes ) {

			global $post;
			$event = $post;

			// we need to adjust on behalf of weekly span scripts
			$day_span_length = $event->days_between + 1;
			if ( $day_span_length > 0 ) {
				$classes[] = 'tribe-dayspan' . $day_span_length . ' ';
			}

			if ( isset( self::$previous_event ) && ! tribe_event_is_all_day( $event ) && strtotime( self::$previous_event->EventStartDate ) < strtotime( $event->EventStartDate ) ) {
				$classes[] = 'tribe-event-overlap ';
			}

			if ( ! tribe_event_is_all_day( $event ) ) {
				$classes[] = 'tribe-events-week-hourly-single';
			} else {
				$classes[] = 'tribe-events-week-allday-single';
			}

			return $classes;
		}

		/**
		 * Account for :30 EOD cutoffs, which break week view
		 *
		 * @param        $date
		 * @param string $format
		 *
		 * @return bool|string
		 */
		protected static function get_rounded_beginning_of_day( $date, $format = 'U' ) {
			$date = tribe_event_beginning_of_day( $date, 'Y-m-d H:00:00' );

			return date( $format, strtotime( $date ) );
		}

		/**
		 * Account for :30 EOD cutoffs, which break week view
		 *
		 * @param        $date
		 * @param string $format
		 *
		 * @return bool|string
		 */
		protected static function get_rounded_end_of_day( $date, $format = 'U' ) {
			$date = ( (int) tribe_event_end_of_day( $date, 'U' ) ) + 1;
			$date = date( 'Y-m-d H:00:00', $date );

			return date( $format, strtotime( $date ) );
		}

		/**
		 * AJAX handler for Week view
		 * This loads up the week view shard with all the appropriate events for the week
		 *
		 * @return void
		 */
		function ajax_response() {
			if ( isset( $_POST["eventDate"] ) && $_POST["eventDate"] ) {

				TribeEventsQuery::init();

				$post_status = array( 'publish' );
				if ( is_user_logged_in() ) {
					$post_status[] = 'private';
				}

				$args = array(
					'post_status'  => $post_status,
					'eventDate'    => $_POST["eventDate"],
					'eventDisplay' => 'week'
				);

				if ( isset( $_POST['tribe_event_category'] ) ) {
					$args[ TribeEvents::TAXONOMY ] = $_POST['tribe_event_category'];
				}

				global $wp_query;
				$wp_query = TribeEventsQuery::getEvents( $args, true );

				TribeEvents::instance()->setDisplay();

				$response = array(
					'html'    => '',
					'success' => true,
					'view'    => 'week',
				);

				add_filter( 'tribe_is_week', '__return_true' ); // simplest way to declare that this is a week view

				ob_start();

				tribe_get_view( 'pro/week/content' );

				$response['html'] .= ob_get_clean();

				apply_filters( 'tribe_events_ajax_response', $response );

				header( 'Content-type: application/json' );
				echo json_encode( $response );
				die();
			}
		}
	}
}
