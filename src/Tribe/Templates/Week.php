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

if ( ! class_exists( 'Tribe__Events__Pro__Templates__Week' ) ) {
	class Tribe__Events__Pro__Templates__Week extends Tribe__Events__Pro__Template_Factory {


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
		private static $current_day = - 1;

		/**
		 * Keeps track of the last event displayed
		 *
		 * @var object
		 */
		private static $previous_event;

		/**
		 * Range of hours to be shown on the week view
		 *
		 * @var array
		 * @see hook tribe_events_week_get_hours
		 */

		private static $hour_range;
		/**
		 * Range of days to be shown on the week view
		 *
		 * @var array
		 * @see hook
		 */
		private static $day_range;

		/**
		 * Hook used for wp ajax response on week view content
		 *
		 * @const
		 */
		const AJAX_HOOK = 'tribe_week';


		/**
		 * __construct()
		 */
		public function __construct() {
			parent::__construct();
			self::$hour_range = tribe_events_week_get_hours();
			self::$day_range  = tribe_events_week_get_days();
		}

		/**
		 * Get the range of hours showing on week view.
		 *
		 * @return array
		 *
		 * @see tribe_events_week_get_hours()
		 */
		public static function get_hour_range() {
			if ( isset( self::$hour_range ) ) {
				return self::$hour_range;
			}

			$beginning_of_day = tribe_event_beginning_of_day( null, 'H' );

			$hours = range( 0, 23 );
			if ( $beginning_of_day > 0 ) {
				for ( $i = 0; $i < $beginning_of_day; $i ++ ) {
					array_push( $hours, array_shift( $hours ) );
				}
			}

			$formatted_hours = array();
			$hour_format     = apply_filters( 'tribe_events_pro_week_hour_format', get_option( 'time_format', 'gA' ) );
			foreach ( $hours as $hour ) {
				$formatted_hours[] = date_i18n( $hour_format, strtotime( $hour . ':00' ) );
			}

			return array_combine( $hours, $formatted_hours );
		}

		/**
		 * Get the array of days we're showing on week view.
		 * Takes into account the first day of the week in WP general settings
		 * Doesn't include weekends if "Hide weekends on Week View" is checked on Events > Settings > Display
		 *
		 * @return array
		 *
		 * @see tribe_events_week_get_days()
		 */
		public static function get_day_range() {
			if ( isset( self::$day_range ) ) {
				return self::$day_range;
			}

			$start_of_week = get_option( 'start_of_week' );
			$days          = range( $start_of_week, $start_of_week + 6 );

			foreach ( $days as $i => $day ) {
				if ( $day > 6 ) {
					$days[ $i ] -= 7;
				}
			}

			if ( tribe_get_option( 'week_view_hide_weekends', false ) == true ) {
				foreach ( $days as $i => $day ) {
					if ( $day == 6 || $day == 0 ) {
						unset ( $days[ $i ] );
					}
				}
			}
			$days            = array_values( $days );
			self::$day_range = $days;

			return $days;
		}

		/**
		 * Set the notices used on week view
		 *
		 * @return void
		 * */
		public function set_notices() {

			// no events this week
			if ( ! $this->any_events() ) {
				$this->nothing_found_notice();
			}
		}

		/**
		 * Check if there are any events showing on the current week
		 *
		 * @return bool
		 */
		private function any_events() {

			// there were no events returned from the query
			if ( ! have_posts() ) {
				return false;
			}

			// there were events returned from the query
			// but don't return true unless they actually fit into the day/hour range we're showing
			foreach ( self::$week_days as $day ) {
				if ( $day['has_events'] ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Display the text that shows when there were no events found on this view
		 *
		 * @return void
		 */
		protected function nothing_found_notice() {
			list( $search_term, $tax_term, $geographic_term ) = $this->get_search_terms();

			if ( ! empty( $search_term ) ) {
				Tribe__Events__Main::setNotice( 'event-search-no-results', sprintf( __( 'There were no results found for <strong>"%s"</strong> this week. Try searching another week.', 'tribe-events-calendar-pro' ), esc_html( $search_term ) ) );
			} elseif ( ! empty( $geographic_term ) ) {
				Tribe__Events__Main::setNotice( 'event-search-no-results', sprintf( __( 'No results were found for events in or near <strong>"%s"</strong> this week. Try searching another week.', 'tribe-events-calendar-pro' ), esc_html( $geographic_term ) ) );
			} // if attempting to view a category archive.
			elseif ( ! empty( $tax_term ) ) {
				Tribe__Events__Main::setNotice( 'events-not-found', sprintf( __( 'No matching events listed under %s. Please try viewing the full calendar for a complete list of events.', 'tribe-events-calendar' ), $tax_term ) );
			} else {
				Tribe__Events__Main::setNotice( 'event-search-no-results', __( 'No results were found for this week. Try searching another week.', 'tribe-events-calendar-pro' ) );
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
			add_action( 'tribe_post_get_template_part_pro/week/single-event', array(
				$this,
				'set_previous_event',
			), 10, 3 );
			add_action( 'tribe_pre_get_template_part_pro/week/single-event', array( $this, 'set_global_post' ), 10, 3 );
		}

		/**
		 * Set the global post to the one that was passed to the template part
		 *
		 * @param $slug
		 * @param $name
		 * @param $data
		 *
		 * @see 'tribe_pre_get_template_part_pro/week/single-event'
		 */
		public function set_global_post( $slug, $name, $data ) {
			$GLOBALS['post'] = $data['event'];
		}


		/**
		 * Keep track of the last event that was outputted, used when determining if we need the overlap class
		 *
		 * @param $slug
		 * @param $name
		 * @param $data
		 *
		 * @see 'tribe_post_get_template_part_pro/week/single-event'
		 */
		public function set_previous_event( $slug, $name, $data ) {
			self::$previous_event = $data['event'];
		}

		/**
		 * Add/remove filters to hide/show sensitive event info on password protected posts
		 *
		 * @param $post
		 *
		 * @return void
		 * @see 'the_post'
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
		 * @see 'tribe_events_header_attributes'
		 * */
		public function header_attributes( $attrs, $current_view ) {
			global $wp_query;
			$attrs['data-view']        = 'week';
			$attrs['data-startofweek'] = get_option( 'start_of_week' );
			$attrs['data-baseurl']     = tribe_get_week_permalink( null, false );
			$attrs['data-date']        = date( 'Y-m-d', strtotime( $wp_query->get( 'start_date' ) ) );

			return apply_filters( 'tribe_events_pro_header_attributes', $attrs, $current_view );
		}

		/**
		 * Perform any functions that need to happen before the view is rendered
		 *
		 * @return  void
		 * @see 'tribe_events_before_view'
		 */
		public function setup_view() {
			$this->setup_days();
		}

		/**
		 * Set up the self::week_days array
		 *
		 * @return void
		 * @see $this->setup_view()
		 */
		private function setup_days() {
			global $wp_query;
			$week_days      = array();

			$day = $wp_query->get( 'start_date' );

			// Array used for calculation of php strtotime relative dates
			$weekday_array = array(
				0 => 'Sunday',
				1 => 'Monday',
				2 => 'Tuesday',
				3 => 'Wednesday',
				4 => 'Thursday',
				5 => 'Friday',
				6 => 'Saturday',
			);

			// build an array with the "day" elements,
			// each "day" is an array that contains the date and the associated all day / hourly events
			// $day_number corresponds to the day of the week in $weekday_array
			foreach ( self::$day_range as $i => $day_number ) {

				// figure out the $date that we're currently looking at
				if ( $day_number >= self::$day_range[0] ) {
					// usually we can just get the date for the next day
					$date = date( 'Y-m-d', strtotime( $day . "+$i days" ) );
				} else {
					// fringe case - someone starts their week in the middle of the week
					// in this case, the "day number" will be less than the first day of the week once the week has looped around
					// so we use a relative strtotime() calc
					$date = date( 'Y-m-d', strtotime( "Next {$weekday_array[$day_number]}", strtotime( $day ) ) );
				}

				$hourly_events  = array();
				$all_day_events = array();

				if ( $wp_query->have_posts() ) {
					// loop through all the wordpress posts and sort them into all day vs hourly for the current $date
					foreach ( $wp_query->posts as $j => $event ) {
						if ( tribe_event_is_on_date( $date, $event ) ) {

							$event->days_between = tribe_get_days_between( $event->EventStartDate, $event->EventEndDate, true );

							if ( tribe_event_is_all_day( $event ) ) {
								$all_day_events[] = $event;
							} else {
								// if the event starts after the end of the hour range we're displaying, or ends before the start, skip it
								$start_hour_today = $date . ' ' . tribe_events_week_get_hours( 'first-hour' );
								$end_hour_today   = tribe_event_end_of_day( $date, 'Y-m-d ' ) . tribe_events_week_get_hours( 'last-hour' );
								if ( tribe_get_start_time( $event, 'U' ) > strtotime( $end_hour_today ) || tribe_get_end_time( $event, 'U' ) < strtotime( $start_hour_today ) ) {
									continue;
								}
								$hourly_events[] = $event;
							}
						}
					}
				}

				$display_format  = apply_filters( 'tribe_events_pro_week_header_date_format', tribe_get_option( 'weekDayFormat', 'D jS' ) );
				$formatted_date  = date_i18n( $display_format, strtotime( $date ) );
				$timestamp_date  = strtotime( $date );
				$timestamp_today = strtotime( 'today' );

				// create the "day" element
				$week_days[] = array(
					'date'           => $date,
					'day_number'     => $day_number,
					'formatted_date' => $formatted_date,
					'is_today'       => ( $timestamp_date == $timestamp_today ) ? true : false,
					'is_past'        => ( $timestamp_date < $timestamp_today ) ? true : false,
					'is_future'      => ( $timestamp_date > $timestamp_today ) ? true : false,
					'hourly_events'  => $hourly_events,
					'all_day_events' => $all_day_events,
					'has_events'     => $hourly_events || $all_day_events,
				);
			}
			self::$week_days = $week_days;
		}

		/**
		 * Return the $week_days array
		 */
		public static function get_week_days() {
			return self::$week_days;
		}

		/**
		 * Check if there are any all day events this week
		 *
		 * @return bool
		 * @see tribe_events_week_has_all_day_events()
		 */
		public static function has_all_day_events() {

			foreach ( self::$week_days as $week_day ) {
				if ( ! empty( $week_day['all_day_events'] ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Build data attributes for an event; needed for week view js
		 *
		 * @param $event
		 *
		 * @return array
		 */
		public static function get_event_attributes( $event ) {

			$event = Tribe__Events__Main::postIdHelper( $event );

			$event = get_post( $event );

			$attrs = array();

			$event_start_timestamp = tribe_get_start_date( $event, null, 'U' );
			$event_end_timestamp   = tribe_get_end_date( $event, null, 'U' );

			if ( tribe_event_is_all_day( $event ) ) {
				$attrs['data-hour'] = 'all-day';
							} else {
				$start_of_day_timestamp = self::get_rounded_beginning_of_day( self::get_current_date() );
				$end_of_day_timestamp   = tribe_event_end_of_day( self::get_current_date(), 'U' );
				if ( has_filter( 'tribe_events_week_get_hours' ) ) {
					// if we're filtering the hour range on week view, stop the events at that hour
					$last_hour_timestamp = strtotime( self::get_current_date() . tribe_events_week_get_hours( 'last-hour' ) );
					$end_of_day_timestamp = min( $end_of_day_timestamp, $last_hour_timestamp );
				}
				$data_hour = date( 'G', $event_start_timestamp );
				$data_min  = date( 'i', $event_start_timestamp );
				if ( $event_start_timestamp < $start_of_day_timestamp ) {
					if ( $event_end_timestamp > $end_of_day_timestamp ) {
						// if there is a day in between start/end we just want to fill the spacer with the total mins in the day.
						$duration = ( $end_of_day_timestamp - $start_of_day_timestamp ) / 60;
					} else {
						$duration = ( $event_end_timestamp - $start_of_day_timestamp ) / 60;
					}
					$data_hour = date( 'G', $start_of_day_timestamp );
					$data_min  = date( 'i', $start_of_day_timestamp );
				} elseif ( $event_end_timestamp > $end_of_day_timestamp ) {
					// if the event is longer than a day we want to account for that with an offset
					$duration = ( $end_of_day_timestamp - $event_start_timestamp ) / 60;
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
		 * @see tribe_events_week_have_days()
		 */
		public static function have_days() {
			if ( empty ( self::$week_days ) ) {
				return false;
			}

			if ( self::$current_day < count( self::$day_range ) - 1 ) {
				return true;
			}

			return false;
		}

		/**
		 * Increment the current day for tracking the current day of the week within the week view loop
		 *
		 * @return void
		 * @see tribe_events_week_the_day()
		 */
		public static function the_day() {
			if ( self::$current_day == ( count( self::$day_range ) - 1 ) ) {
				self::rewind_days();
			}

			self::$current_day ++;

			return self::$week_days[ self::$current_day ];
		}

		/**
		 * Reset the internal counter for the current day
		 *
		 * @return void
		 * @see 'tribe_pre_get_template_part_pro/week/loop'
		 */
		public static function rewind_days() {
			self::$current_day = - 1;
		}

		/**
		 * get current day array
		 *
		 * @return int
		 * @see tribe_events_week_get_current_day()
		 */
		public static function get_current_day() {
			return self::$week_days[ self::$current_day ];
		}

		/**
		 * get the current date based on the current day of week
		 *
		 * @return date( 'Y-m-d' )
		 * @see self::get_rounded_beginning_of_day()
		 * @see self::get_rounded_end_of_day()
		 */
		private static function get_current_date() {
			return date_i18n( 'Y-m-d', strtotime( self::$week_days[ self::$current_day ]['date'] ) );
		}

		/**
		 * Determine and return the CSS classes needed for the day of week headers
		 *
		 * @return string
		 * @see tribe_events_week_day_header_classes()
		 */
		public static function day_header_classes() {
			$day     = self::get_current_day();
			$classes = 'column';
			$classes .= ' tribe-week-day-header-' . $day['day_number'] . ' ';
			if ( $day['is_today'] ) {
				$classes .= ' tribe-week-today';
			}

			return $classes;
		}

		/**
		 * Determine and return the CSS classes needed for each column on week view
		 *
		 * @return string
		 * @see tribe_events_week_column_classes()
		 */
		public static function column_classes() {

			$day     = self::$week_days[ self::$current_day ];
			$classes = 'tribe-events-mobile-day column tribe-events-day-column-' . $day['day_number'] . ' ';

			// Present
			if ( $day['is_today'] ) {
				$classes .= 'tribe-week-today';
			} // Past
			elseif ( $day['is_past'] ) {
				$classes .= 'tribe-events-past';
			} // Future
			elseif ( $day['is_future'] ) {
				$classes .= 'tribe-events-future';
			}
			// Has Events
			if ( ! empty ( $day['all_day_events'] ) || ! empty ( $day['hourly_events'] ) ) {
				$classes .= ' tribe-events-has-events';
			}

			return $classes;
		}

		/**
		 * Determine and return the CSS classes needed for each event on week view
		 *
		 * @param $classes array
		 *
		 * @return string
		 * @see 'tribe_events_event_classes'
		 */
		public function event_classes( $classes ) {

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
			$beginning_of_day = tribe_event_beginning_of_day( $date, 'U' );
			reset( self::$hour_range );
			$date = max( $beginning_of_day, strtotime( $date . ' ' . tribe_events_week_get_hours( 'first-hour' ) ) );
			$date = date( 'Y-m-d H:00:00', $date );
			$date = date( $format, strtotime( $date ) );

			return $date;
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
			$end_of_day = ( (int) tribe_event_end_of_day( $date, 'U' ) ) + 1;
			end( self::$hour_range );
			$date = min( $end_of_day, strtotime( $date . ' ' . tribe_events_week_get_hours( 'last-hour' ) ) );
			$date = date( 'Y-m-d H:00:00', $date );
			$date = date( $format, strtotime( $date ) );

			return $date;
		}

		/**
		 * AJAX handler for Week view
		 * This loads up the week view shard with all the appropriate events for the week
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

				$args = array(
					'post_status'  => $post_status,
					'eventDate'    => $_POST['eventDate'],
					'eventDisplay' => 'week',
				);

				if ( isset( $_POST['tribe_event_category'] ) ) {
					$args[ Tribe__Events__Main::TAXONOMY ] = $_POST['tribe_event_category'];
				}

				global $wp_query;
				$wp_query = Tribe__Events__Query::getEvents( $args, true );

				Tribe__Events__Main::instance()->setDisplay();

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
