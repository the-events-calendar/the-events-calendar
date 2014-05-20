<?php
/**
 *
 *
 * @for Week Grid Template
 * This file contains hooks and functions required to set up the week grid view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( !class_exists( 'Tribe_Events_Pro_Week_Template' ) ) {
	class Tribe_Events_Pro_Week_Template extends Tribe_PRO_Template_Factory {

		protected $asset_packages = array( 'ajax-weekview' );
		public static $tribe_bar_args = array();
		public static $today;
		public static $start_of_week_date;
		public static $end_of_week_date;
		public static $day_cutoff_rounded;
		public static $start_of_week;
		public static $week_length = 7;
		public static $week_days;
		public static $events;
		public static $current_map_row = -1;
		public static $current_day = -1;
		public static $event_id = -1;
		public static $prior_event_date = null;
		public static $event_key_track = array();
		public static $loop_type = 'hourly';

		public function __construct() {

			parent::__construct();

			self::$prior_event_date = (object) array( 'EventStartDate'=>null, 'EventEndDate'=>null );
			self::$today = date_i18n( 'Y-m-d', strtotime( 'today' ) );
			self::$start_of_week_date = self::get_rounded_beginning_of_day( tribe_get_first_week_day(), 'Y-m-d H:i:s' );
			self::$end_of_week_date = self::get_rounded_end_of_day( date('Y-m-d', strtotime( self::$start_of_week_date . ' +' . self::$week_length - 1 . ' days' ) ), 'Y-m-d H:i:s' );
			self::$start_of_week = get_option( 'start_of_week', 0 );
			self::$day_cutoff_rounded = date('H:00', strtotime(self::$start_of_week_date));

			// let's get this show on the road
			// self::set_current_day( self::$start_of_week );
			self::setup_loop();
			self::set_week_days();

			// save tribe bar args
			if ( empty( self::$tribe_bar_args ) ) {
				foreach ( $_REQUEST as $key => $value ) {
					if ( $value && strpos( $key, 'tribe-bar-' ) === 0 && $key != 'tribe-bar-date' ) {
						self::$tribe_bar_args[$key] = $value;
					}
				}
			}
		}

		/**
		 * Set the notices used on week view
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @return void
		 * */
		function set_notices() {
			global $wp_query;
			$tribe = TribeEvents::instance();
			$search_term = $geographic_term = '';
			$tax_term = '';

			// We have events to display, no need for notices!
			if ( ! empty( self::$events->all_day ) || ! empty( self::$events->hourly ) ) return;
			
			if ( is_tax( $tribe->get_event_taxonomy() ) ) {
				$tax_term = get_term_by( 'slug', get_query_var( 'term' ), $tribe->get_event_taxonomy() );
				$tax_term = esc_html( $tax_term->name );
			}

			// Was the user searching for a keyword or place?
			if ( !empty( $wp_query->query_vars['s'] ) )
				$search_term = $wp_query->query_vars['s'];

			elseif ( !empty( $_REQUEST['tribe-bar-search'] ) )
				$search_term = $_REQUEST['tribe-bar-search'];

			elseif ( !empty( $_REQUEST['tribe-bar-geoloc']) )
				$geographic_term = $_REQUEST['tribe-bar-geoloc'];

			// Set an appropriate notice
			if ( ! empty( $search_term ) )
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'There were no results found for <strong>"%s"</strong> this week. Try searching another week.', 'tribe-events-calendar-pro' ), esc_html($search_term) ) );

			elseif ( ! empty( $geographic_term ) )
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'No results were found for events in or near <strong>"%s"</strong> this week. Try searching another week.', 'tribe-events-calendar-pro' ), esc_html($geographic_term) ) );
				
			// if attempting to view a category archive.
			elseif ( ! empty( $tax_term ) )
				TribeEvents::setNotice( 'events-not-found', sprintf( __('No matching events listed under %s. Please try viewing the full calendar for a complete list of events.', 'tribe-events-calendar'), $tax_term ) );

			else
				TribeEvents::setNotice( 'event-search-no-results', __( 'No results were found for this week. Try searching another week.', 'tribe-events-calendar-pro' ) );
		}

		/**
		 * Set up hooks for week view
		 *
		 * @return void
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * */
		protected function hooks() {
			parent::hooks();
			add_filter( 'tribe_events_header_attributes',  array( $this, 'header_attributes' ), 10, 2 );
			add_action( 'tribe_events_week_pre_setup_event', array( $this, 'manage_sensitive_info' ) );
		}

		/**
		 * Add/remove filters to hide/show sensitive event info on password protected posts
		 *
		 * @param $post
		 * @return void
		 * @author Jessica Yazbek
		 * @since 3.0
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
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * */
		function header_attributes( $attrs, $current_view ) {
			switch ( $current_view ) {
			case 'week-all-day':
				$attrs['data-hour'] = 'all-day';
				unset( $attrs['data-title'] );
				break;
			case 'week-hourly':
				$event = self::get_hourly_event();
				$start_of_day_timestamp = self::get_rounded_beginning_of_day( self::get_current_date(), 'U' );
				$end_of_day_timestamp = self::get_rounded_end_of_day( self::get_current_date(), 'U' );
				$data_hour =  date( 'G', $start_of_day_timestamp );
				$data_min = date('i', $start_of_day_timestamp );
				if ( strtotime($event->EventStartDate) < $start_of_day_timestamp ) {
					if ( strtotime( $event->EventEndDate ) > $end_of_day_timestamp ) {
						// if there is a day in between start/end we just want to fill the spacer with the total mins in the day.
						$duration = ( $end_of_day_timestamp - $start_of_day_timestamp ) / 60;
					} else {
						$duration = ( strtotime( $event->EventEndDate ) - $start_of_day_timestamp ) / 60;
					}
				} elseif ( strtotime( $event->EventEndDate ) > $end_of_day_timestamp ) {
					// if the event is longer than a day we want to account for that with an offset
					$duration = ( $end_of_day_timestamp - strtotime( $event->EventStartDate ) ) / 60;
					$data_hour = date( 'G', strtotime( $event->EventStartDate ) );
					$data_min = date( 'i', strtotime( $event->EventStartDate ) );
				} else {
					// for a default event continue as everything is normal
					$remaining_minutes_in_day = ( $end_of_day_timestamp - strtotime( $event->EventStartDate ) / 60 );
					$duration = get_post_meta( $event->ID, '_EventDuration', TRUE) / 60;
					if ( $duration > $remaining_minutes_in_day ) {
						// this will happen in the case of a multi-day event that extends beyond the end of the week
						$duration = $remaining_minutes_in_day;
					}
					$data_hour = date( 'G', strtotime( $event->EventStartDate ) );
					$data_min = date( 'i', strtotime( $event->EventStartDate ) );
				}
				$attrs['data-duration'] = abs( $duration );
				$attrs['data-hour'] = $data_hour;
				$attrs['data-min'] = $data_min;
				unset( $attrs['data-title'] );
				break;

			case 'week-header':

				global $wp_query;
				$current_week = $wp_query->get('start_date');

				$attrs['data-view'] = 'week';
				$attrs['data-startofweek'] = get_option( 'start_of_week' );
				$attrs['data-baseurl'] = tribe_get_week_permalink( null, false );
				$attrs['data-date'] = Date('Y-m-d', strtotime( $current_week ) );
				break;
			}

			return apply_filters( 'tribe_events_pro_header_attributes', $attrs, $current_view );
		}

		/**
		 * This will set the self::week_days array with proper offset for start day in settings
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @return  void
		 */
		function set_week_days() {
			$week_days = array();
			$display_format = apply_filters( 'tribe_events_pro_week_header_date_format', tribe_get_option('weekDayFormat', 'D jS') );

			for ( $n = self::$start_of_week; $n < self::$start_of_week + self::$week_length; $n++ ) {
				$day_offset = ( 0 < self::$start_of_week ) ? $n - self::$start_of_week : $n;
				$date = date( 'Y-m-d', strtotime( self::$start_of_week_date . " +$day_offset days" ) );
				$week_days[ $n ] = array(
					'date' => $date,
					'display' => '<span data-full-date="'. date_i18n( $display_format, strtotime( self::$start_of_week_date . " +$day_offset days" ) ) .'">'. date_i18n( $display_format, strtotime( self::$start_of_week_date . " +$day_offset days" ) ) .'</span>',
					'is_today' => ( $date == self::$today ) ? true : false,
					'is_past' => ( $date < self::$today ) ? true : false,
					'is_future' => ( $date > self::$today ) ? true : false,
					'has_events' => ( ! empty( self::$events->all_day_map[0][$n] ) || ! empty( self::$events->hourly[$n] ) ) ? true : false
				);
				// check if there are all day events on this day (this is easy to tell)
				$has_events = ! empty( self::$events->all_day_map[0][$n] ) ? true : false;

				// if not, check the hourly events
				if ( ! $has_events ) {
					$day_start = strtotime( tribe_event_beginning_of_day( $date ) );
					$day_end = strtotime( tribe_event_end_of_day( $date ) );
					foreach ( self::$events->hourly as $hourly_event ) {
						$event_start = strtotime($hourly_event->_EventStartDate);
						$event_end = strtotime($hourly_event->_EventEndDate);
						if ( ( $event_start >= $day_start && $event_start <= $day_end ) 
							// event ends today
							|| ( $event_end >= $day_start && $event_end <= $day_end )
							// event spans across today
							|| ( $event_start <= $day_start && $event_end >= $day_end )
							) {
							$has_events = true;
							break;
			}
					}
				}
				$week_days[$n]['has_events'] = $has_events;
				$week_days[$n] = (object) $week_days[$n];
			}
			self::$week_days = $week_days;
		}

		/**
		 * Checks whether there are more calendar days to display
		 *
		 * @return bool True if calendar days are available, false if not.
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * */
		public static function have_days() {
			if ( self::$current_day < ( self::$start_of_week + self::$week_length ) - 1 ) {
				return true;
			}
			return false;
		}

		/**
		 * Get access to the internal week day list
		 *
		 * @return array $week_days
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 */
		public static function get_week_days() {
			return self::$week_days;
		}

		/**
		 * Get events by type
		 *
		 * @param string  $obj
		 * @return object self::events->{$obj}
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 */
		public static function get_events( $obj = null ) {
			if ( !empty( self::$events->{$obj} ) )
				return (array) self::$events->{$obj};

			return self::$events;
		}

		/**
		 * Break the $wp_query post loop apart into sorted events by type
		 *
		 * @return void
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 */
		function setup_loop() {
			global $wp_query;
			self::$events = (object) array( 'all_day_map' => array(), 'all_day' => array(), 'hourly' => array(), 'hours' => array( 'start'=>null, 'end'=>null ) );

			// get it started off with at least 1 row
			self::$events->all_day_map[] = array_fill( self::$start_of_week, self::$week_length, null );

			foreach ( $wp_query->posts as $event_key_id => $event ) {

				// convert the start and end dates of the event into timestamps
				$event_start_time = strtotime( $event->EventStartDate );
				$event_end_time = strtotime( $event->EventEndDate );

				// if the event start time is greater than the start time of the week then we use the event date otherwise use the beginning of the week date
				$start_date_compare = strtotime( self::$start_of_week_date ) < $event_start_time ? $event->EventStartDate : self::$start_of_week_date;
				$end_date_compare = strtotime( self::$end_of_week_date ) > $event_end_time ? $event->EventEndDate : self::$end_of_week_date;

				// convert the starting event or week date into day of the week
				$event_start_day_of_week = date( 'w', strtotime( $start_date_compare ) );

				// determine the number of days between the starting date and the end of the event
				$event->days_between = tribe_get_days_between( $start_date_compare, $end_date_compare, self::$day_cutoff_rounded );

				// make sure that our days between will not extend past the end of the week
				$event->days_between = $event->days_between >= self::$week_length - $event_start_day_of_week ? ( self::$week_length - $event_start_day_of_week ) : (int) $event->days_between;

				// if this is an all day event
				if (  tribe_get_event_meta( $event->ID, '_EventAllDay' ) ) {
					
					// let's build our hashtable for add day events
					foreach ( self::$events->all_day_map as $hash_id => $days ) {

						// set bool for if we should inset the event id on the current hash row
						$insert_current_row = false;

						// loop through the columns of this hash row
						for ( $n = $event_start_day_of_week; $n <= $event_start_day_of_week + $event->days_between; $n++ ) {
							// create an offset id for cases where the day of the week is less the starting day of the week
							// thus looping the beginning days of the start week starting at 0 around to the end
							$all_day_offset = ( $n < self::$start_of_week ) ? self::$week_length + $n : $n;

							// check for hash collision and setup bool for going to the next row if we can't fit it on this row
							if ( ! empty( self::$events->all_day_map[$hash_id][$all_day_offset] ) || self::$events->all_day_map[$hash_id][$all_day_offset] == '0'  ) {
								$insert_current_row = true;
								break;
							} else {
								$insert_current_row = false;
							}
						}
						// if we should actually insert a new row vs going to the next row
						if ( $insert_current_row && count( self::$events->all_day_map ) == $hash_id + 1 ) {

							// create a new row and fill with week day columns
							self::$events->all_day_map[] = array_fill( self::$start_of_week, self::$week_length, null );

							// change the row id to the last row
							$hash_id = count( self::$events->all_day_map ) -1;

						} else if ( $insert_current_row ) {

							// nullify the hash id
							$hash_id = null;
						}

						// if we still have a hash id then fill the row with the event id
						if ( ! is_null( $hash_id ) ) {

							// loop through each week day we want the event to be inserted
							for ( $n = $event_start_day_of_week; $n <= $event_start_day_of_week + $event->days_between; $n++ ) {
								// create an offset id for cases where the day of the week is less the starting day of the week
								// thus looping the beginning days of the start week starting at 0 around to the end
								$all_day_offset = ( $n < self::$start_of_week ) ? self::$week_length + $n : $n;

								// add the event array key id into the week day column
								self::$events->all_day_map[$hash_id][$all_day_offset] = $event_key_id;
							}

							// break the hashtable since we have successfully added the event into a row
							break;
						}
					}

					// using the array key for the event id for uniqueness of recurring events
					self::$events->all_day[ $event_key_id ] = $event;
				} else {
					$start_hour = date( 'G', strtotime( $event->EventStartDate ) );
					$end_hour = date( 'G', strtotime( $event->EventEndDate ) );
					if ( is_null( self::$events->hours['start'] ) || $start_hour < self::$events->hours['start'] ) {
						self::$events->hours['start'] = $start_hour;
					}
					if ( is_null( self::$events->hours['end'] ) || $end_hour > self::$events->hours['end'] ) {
						self::$events->hours['end'] = $end_hour;
					}
					self::$events->hourly[ $event_key_id ] = $event;
				}
			}		
		}

		/**
		 * increment the current day for tracking the current day of the week within the loop
		 *
		 * @return void
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 */
		public static function the_day() {
			if ( self::$current_day == -1 ) {
				self::$current_day = self::$start_of_week;
			} elseif ( self::$current_day < self::$start_of_week + self::$week_length ) {
				self::$current_day++;
			} else {
				self::reset_the_day();
			}
		}

		/**
		 * reset the internal counter for the current day
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @return void
		 */
		public static function reset_the_day() {
			self::$current_day = -1;
		}

		/**
		 * returns the current iterator for the all day map row
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @return int
		 */
		public static function get_the_day_map() {
			return self::$current_map_row;
		}

		/**
		 * internal mechanism to increment the all day map counter
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @return void
		 */
		public static function the_day_map() {
			self::$current_map_row++;
		}

		/**
		 * reset the internal counter for all day map counter
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @return void
		 */
		public static function reset_the_day_map() {
			self::$current_map_row = -1;
		}

		/**
		 * set internal mechanism for tracking what the current day of the week is within the display loops
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @param int     $day_id
		 */
		function set_current_day( $day_id ) {
			self::$current_day = $day_id;
		}

		/**
		 * get internal increment for current day of the week
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @return int
		 */
		public static function get_current_day() {
			return self::$current_day;
		}

		/**
		 * get the current date based on the current day of week
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @return date( 'Y-m-d' )
		 */
		function get_current_date() {
			return date_i18n( 'Y-m-d', strtotime( self::$start_of_week_date . ' +' . ( self::$current_day - self::$start_of_week ) . ' days' ) );
		}

		/**
		 * css column classes used during loop
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @return void
		 */
		public static function column_classes() {
			// Present
			if ( self::$week_days[ self::$current_day ]->is_today )
				echo 'tribe-week-today';
			// Past
			else if ( self::$week_days[ self::$current_day ]->is_past )
				echo 'tribe-events-past';
			// Future
			else if ( self::$week_days[ self::$current_day ]->is_future )
				echo 'tribe-events-future';
			// Has Events
			if ( self::$week_days[ self::$current_day ]->has_events )
   				echo ' tribe-events-has-events';
		}

		/**
		 * css event wrapper classes used during loop
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @param $classes
		 * @return void
		 */
		function event_classes( $classes ) {

			if ( self::$loop_type == 'allday' ) {
				$event = self::get_allday_event();
			} else {
				$event = self::get_hourly_event();
			}

			global $post;
			$post_switch = $post;

			$post = $event;

			// we need to adjust on behalf of weekly span scripts
			$day_span_length = $event->days_between + 1;
			if ( $day_span_length > 0 )
				$classes[] = 'tribe-dayspan' . $day_span_length . ' ';

			if ( self::$loop_type == 'hourly' && strtotime( self::$prior_event_date->EventStartDate ) < strtotime( $event->EventStartDate ) ) {
				$classes[] = 'tribe-event-overlap ';
			}

			self::$prior_event_date->EventStartDate = $event->EventStartDate;

			$post = $post_switch;
			return $classes;
		}

		/**
		 * set the internal event id for tracking between methods/templates
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @param int     $event_id
		 */
		public static function set_event_id( $event_id ) {
			self::$event_id = $event_id;
		}

		/**
		 * access the internal var for tracking the event id
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @return int self::event_id
		 */
		public static function get_event_id() {
			if ( self::$loop_type == 'allday' && !empty( self::$events->all_day[ self::$event_id ] ) ) {
				return self::$events->all_day[ self::$event_id ]->ID;
			} else if ( self::$loop_type == 'hourly' ) {
					return self::$event_id;
				}
			return null;
		}

		/**
		 * Based on set event id return an all day event
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @return object $event
		 */
		public static function get_allday_event() {
			$event = !empty( self::$events->all_day[ self::$event_id ] ) ? self::$events->all_day[ self::$event_id ] : null;
			return $event;
		}

		/**
		 * Based on set event id return an hourly type event
		 *
		 * @since  3.0
		 * @author tim@imaginesimplicty.com
		 * @return object $event
		 */
		public static function get_hourly_event( $event_id = null ) {
			$event_id = !empty( $event_id ) ? $event_id : self::get_event_id();
			if( empty($event_id))
				return null;

			if ( is_object( $event_id ) ) {
				return $event_id;
			} else if( is_numeric( $event_id ) && !empty( self::$events->hourly[ $event_id ] ) ) {
				return self::$events->hourly[ $event_id ];
			} else {
				return null;
			}
		}

		protected static function get_rounded_beginning_of_day( $date, $format = 'U' ) {
			$date = tribe_event_beginning_of_day( $date, 'Y-m-d H:00:00' );
			return date($format, strtotime($date));
		}

		protected static function get_rounded_end_of_day( $date, $format = 'U' ) {
			$date = ((int)tribe_event_end_of_day( $date, 'U' )) + 1;
			$date = date( 'Y-m-d H:00:00', $date );
			return date($format, strtotime($date));
		}

	}
}
