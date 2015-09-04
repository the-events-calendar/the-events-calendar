<?php
/**
 * Event API - can be used by other plugins to manipulate events
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__API' ) ) {
	class Tribe__Events__API {
		public static $valid_venue_keys = array(
			'Venue',
			'Address',
			'City',
			'Province',
			'State',
			'StateProvince',
			'Province',
			'Zip',
			'Phone'
		);

		public static $valid_organizer_keys = array(
			'Organizer',
			'Phone',
			'Email',
			'Website'
		);

		/**
		 * Create a new event
		 *
		 * @param array $args The post args.
		 *
		 * @return void|int The created event ID.
		 */
		public static function createEvent( $args ) {

			$args['post_type'] = Tribe__Events__Main::POSTTYPE;
			$eventId           = wp_insert_post( $args, true );

			if ( ! is_wp_error( $eventId ) ) {
				Tribe__Events__API::saveEventMeta( $eventId, $args, get_post( $eventId ) );

				return $eventId;
			}
		}

		/**
		 * Update an existing event
		 *
		 * @param int   $eventId The event ID to update.
		 * @param array $args    The post args.
		 *
		 * @return false|int The event ID.
		 */
		public static function updateEvent( $eventId, $args ) {
			$args['ID'] = $eventId;
			$args['post_type'] = Tribe__Events__Main::POSTTYPE;

			if ( wp_update_post( $args ) ) {
				Tribe__Events__API::saveEventMeta( $eventId, $args, get_post( $eventId ) );
			}

			return $eventId;
		}

		/**
		 * Delete an event - all instances if recurring
		 *
		 * @param int  $eventId      The Event ID to delete.
		 * @param bool $force_delete Same as the WP parameter.
		 *
		 * @return mixed
		 */
		public static function deleteEvent( $eventId, $force_delete = false ) {
			return wp_delete_post( $eventId, $force_delete );
		}

		/**
		 * Used by createEvent and updateEvent - saves all the various event meta
		 *
		 * @param int   $event_id The event ID we are modifying meta for.
		 * @param array $data     The meta fields we want saved.
		 * @param       WP_Post   The event itself.
		 *
		 * @return void
		 */
		public static function saveEventMeta( $event_id, $data, $event = null ) {
			$tec = Tribe__Events__Main::instance();

			$data = self::prepare_event_date_meta( $event_id, $data );

			if ( empty( $data['EventHideFromUpcoming'] ) ) {
				delete_post_meta( $event_id, '_EventHideFromUpcoming' );
			}

			update_post_meta( $event_id, '_EventShowMapLink', isset( $data['venue']['EventShowMapLink'] ) );
			update_post_meta( $event_id, '_EventShowMap', isset( $data['venue']['EventShowMap'] ) );

			if ( isset( $data['post_status'] ) ) {
				$post_status = $data['post_status'];
			} else {
				$post_status = get_post_status( $event_id );
			}

			if ( isset( $data['Organizer'] ) ) {
				if ( ! isset( $data['Organizer'][0] ) || ! is_array( $data['Organizer'][0] ) ) {
					// convert old-style single organizer into an array of organizers
					$data['Organizer'] = array( $data['Organizer'] );
				}
				foreach ( $data['Organizer'] as $organizer ) {
					if ( ! empty( $organizer['OrganizerID'] ) ) {
						$organizer_post_status = get_post_status( $organizer['OrganizerID'] );
					} else {
						$organizer_post_status = $post_status;
					}
					$data['EventOrganizerID'][] = self::saveEventOrganizer( $organizer, $event, $organizer_post_status );
				}
			}
			if ( isset( $data['Venue'] ) ) {
				if ( ! empty( $data['Venue']['VenueID'] ) ) {
					$venue_post_status = get_post( $data['Venue']['VenueID'] )->post_status;
				} else {
					$venue_post_status = $post_status;
				}
				$data['EventVenueID'] = self::saveEventVenue( $data['Venue'], $event, $venue_post_status );
			}

			// Ordinarily there is a single cost value for each event, but addons (ie, ticketing plugins) may need
			// to record a number of different pricepoints for the same event
			$event_cost = isset( $data['EventCost'] ) ? (array) $data['EventCost'] : array();
			$data['EventCost'] = (array) apply_filters( 'tribe_events_event_costs', $event_cost, $event_id );

			do_action( 'tribe_events_event_save', $event_id );

			//update meta fields
			foreach ( $tec->metaTags as $tag ) {
				$htmlElement = ltrim( $tag, '_' );
				if ( isset( $data[ $htmlElement ] ) && $tag != Tribe__Events__Main::EVENTSERROROPT ) {
					if ( is_string( $data[ $htmlElement ] ) ) {
						$data[ $htmlElement ] = filter_var( $data[ $htmlElement ], FILTER_SANITIZE_STRING );
					}
					// Fields with multiple values per key
					if ( is_array( $data[ $htmlElement ] ) ) {
						delete_post_meta( $event_id, $tag );
						foreach ( $data[ $htmlElement ] as $value ) {
							add_post_meta( $event_id, $tag, $value );
						}
					}
					// Fields with a single value per key
					else {
						update_post_meta( $event_id, $tag, $data[ $htmlElement ] );
					}
				}
			}

			// Set sticky state for calendar view.
			if ( isset( $data['EventShowInCalendar'] ) && $data['EventShowInCalendar'] == 'yes' && $event->menu_order != '-1' ) {
				$update_event = array(
					'ID'         => $event_id,
					'menu_order' => '-1',
				);
				wp_update_post( $update_event );
			} elseif ( ( ! isset( $data['EventShowInCalendar'] ) || $data['EventShowInCalendar'] != 'yes' ) && $event->menu_order == '-1' ) {
				$update_event = array(
					'ID'         => $event_id,
					'menu_order' => '0',
				);
				wp_update_post( $update_event );
			}

			do_action( 'tribe_events_update_meta', $event_id, $data );
		}

		/**
		 * given a set of meta data, prepare date data if it exists
		 *
		 * @param $data array Associative array of event meta data
		 *
		 * @return array
		 */
		protected static function prepare_event_date_meta( $event_id, $data ) {
			$date_provided = false;

			if ( isset( $data['EventAllDay'] ) ) {
				if ( Tribe__Events__Date_Utils::is_all_day( $data['EventAllDay'] ) ) {
					$data['EventAllDay'] = 'yes';
				} else {
					$data['EventAllDay'] = 'no';
				}
			}

			$datepicker_format = Tribe__Events__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );

			if ( isset( $data['EventStartDate'] ) ) {
				$data['EventStartDate'] = Tribe__Events__Date_Utils::datetime_from_format( $datepicker_format, $data['EventStartDate'] );
			}

			if ( isset( $data['EventEndDate'] ) ) {
				$data['EventEndDate'] = Tribe__Events__Date_Utils::datetime_from_format( $datepicker_format, $data['EventEndDate'] );
			}

			if ( isset( $data['EventAllDay'] ) && 'yes' === $data['EventAllDay'] ) {
				$date_provided = true;
				$data['EventStartDate'] = tribe_event_beginning_of_day( $data['EventStartDate'] );
				$data['EventEndDate']   = tribe_event_end_of_day( $data['EventEndDate'] );
			} elseif ( isset( $data['EventStartDate'] ) && isset( $data['EventEndDate'] ) ) {
				$date_provided = true;
				delete_post_meta( $event_id, '_EventAllDay' );

				$start_date_string = "{$data['EventStartDate']} {$data['EventStartHour']}:{$data['EventStartMinute']}:00";
				$end_date_string = "{$data['EventEndDate']} {$data['EventEndHour']}:{$data['EventEndMinute']}:00";

				if ( isset( $data['EventStartMeridian'] ) ) {
					$start_date_string .= " {$data['EventStartMeridian']}";
				}

				if ( isset( $data['EventEndMeridian'] ) ) {
					$end_date_string .= " {$data['EventEndMeridian']}";
				}

				$data['EventStartDate'] = date( Tribe__Events__Date_Utils::DBDATETIMEFORMAT, strtotime( $start_date_string ) );
				$data['EventEndDate'] = date( Tribe__Events__Date_Utils::DBDATETIMEFORMAT, strtotime( $end_date_string ) );
			}

			if ( ! $date_provided ) {
				$data['EventStartDate'] = get_post_meta( $event_id, '_EventStartDate', true );
				$data['EventEndDate'] = get_post_meta( $event_id, '_EventEndDate', true );
				return $data;
			}

			// If a specific timezone was not specified, default to the sitewide timezone
			if ( ! isset( $data['EventTimezone'] ) ) {
				$data['EventTimezone'] = Tribe__Events__Timezones::wp_timezone_string();
			}

			// Additionally store datetimes in UTC
			$data['EventStartDateUTC'] = Tribe__Events__Timezones::to_utc( $data['EventStartDate'], $data['EventTimezone'] );
			$data['EventEndDateUTC']   = Tribe__Events__Timezones::to_utc( $data['EventEndDate'], $data['EventTimezone'] );
			$data['EventTimezoneAbbr'] = Tribe__Events__Timezones::abbr( $data['EventStartDate'], $data['EventTimezone'] );

			// sanity check that start date < end date
			$start_timestamp = strtotime( $data['EventStartDate'] );
			$end_timestamp   = strtotime( $data['EventEndDate'] );

			if ( $start_timestamp > $end_timestamp ) {
				$data['EventEndDate'] = $data['EventStartDate'];
			}

			$data['EventDuration'] = strtotime( $data['EventEndDate'] ) - $start_timestamp;

			return $data;
		}//end prepare_event_date_meta

		/**
		 * Triggers an update of the cost meta data (min <-> max) for an event.
		 *
		 * This is primarily for internal use where an addon needs to update the cost meta
		 * data for an event (but no other meta fields). To actually add to or modify the
		 * range of cost values the tribe_events_event_costs filter hook should be
		 * leveraged.
		 *
		 * @param $event_id
		 */
		public static function update_event_cost( $event_id ) {
			// Loads current event costs, on construct
			// Tribe__Events__Tickets__Tickets->get_ticket_prices() adds them to this filter
			$event_cost = (array) apply_filters( 'tribe_events_event_costs', array(), $event_id );

			// Kill the old cost meta data
			delete_post_meta( $event_id, '_EventCost' );

			// Add fresh entries for each of the new values
			foreach ( $event_cost as $cost ) {
				add_post_meta( $event_id, '_EventCost', $cost );
			}
		}

		/**
		 * Saves the event organizer information passed via an event
		 *
		 * @param array   $data        The organizer data.
		 * @param WP_Post $post        The post.
		 * @param string  $post_status The intended post status.
		 *
		 * @return mixed
		 */
		private static function saveEventOrganizer( $data, $post = null, $post_status = 'publish' ) {
			if ( isset( $data['OrganizerID'] ) && $data['OrganizerID'] > 0 ) {
				if ( count( $data ) == 1 ) {
					// Only an ID was passed and we should do nothing.
					return $data['OrganizerID'];
				} else {
					Tribe__Events__API::updateOrganizer( $data['OrganizerID'], $data );

					return $data['OrganizerID'];
				}
			} else {
				return Tribe__Events__API::createOrganizer( $data, $post_status );
			}
		}

		/**
		 * Saves the event venue information passed via an event
		 *
		 * @param array   $data        The venue data.
		 * @param WP_Post $post        The venue object.
		 * @param string  $post_status The intended post status.
		 *
		 * @return mixed.
		 */
		private static function saveEventVenue( $data, $post = null, $post_status = 'publish' ) {
			if ( isset( $data['VenueID'] ) && $data['VenueID'] > 0 ) {
				if ( count( $data ) == 1 ) {
					// Only an ID was passed and we should do nothing.
					return $data['VenueID'];
				} else {
					$show_map            = get_post_meta( $data['VenueID'], '_VenueShowMap', true );
					$show_map_link       = get_post_meta( $data['VenueID'], '_VenueShowMapLink', true );
					$data['ShowMap']     = $show_map ? $show_map : 'false';
					$data['ShowMapLink'] = $show_map_link ? $show_map_link : 'false';
					Tribe__Events__API::updateVenue( $data['VenueID'], $data );

					return $data['VenueID'];
				}
			} else {
				// Remove a zero-value venue ID, if set, before creating the new venue
				if ( isset( $data['VenueID'] ) && 0 == $data['VenueID'] ) {
					unset( $data['VenueID'] );
				}
				return Tribe__Events__API::createVenue( $data, $post_status );
			}
		}

		/**
		 * Creates a new organizer
		 *
		 * @param array  $data        The organizer data.
		 * @param string $post_status the intended post status.
		 *
		 * @return mixed
		 */
		public static function createOrganizer( $data, $post_status = 'publish' ) {
			if ( ( isset( $data['Organizer'] ) && $data['Organizer'] ) || self::someOrganizerDataSet( $data ) ) {

				$postdata = array(
					'post_title'  => $data['Organizer'] ? $data['Organizer'] : "Unnamed Organizer",
					'post_type'   => Tribe__Events__Main::ORGANIZER_POST_TYPE,
					'post_status' => $post_status,
				);

				$organizerId = wp_insert_post( $postdata, true );

				if ( ! is_wp_error( $organizerId ) ) {
					self::saveOrganizerMeta( $organizerId, $data );
					do_action( 'tribe_events_organizer_created', $organizerId, $data );

					return $organizerId;
				}
			} else {
				// if the venue is blank, let's save the value as 0 instead
				return 0;
			}
		}

		/**
		 * Check to see if any organizer data set
		 *
		 * @param array $data the organizer data.
		 *
		 * @return bool If there is ANY organizer data set, return true.
		 */
		private static function someOrganizerDataSet( $data ) {
			foreach ( self::$valid_organizer_keys as $key ) {
				if ( isset( $data[$key] ) && $data[$key] ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Deletes an organizer
		 *
		 * @param int  $organizerId  The organizer ID to delete.
		 * @param bool $force_delete Same as WP param.
		 *
		 * @return void
		 */
		public static function deleteOrganizer( $organizerId, $force_delete = false ) {
			wp_delete_post( $organizerId, $force_delete );
		}

		/**
		 * Updates an organizer
		 *
		 * @param int   $organizerId The organizer ID to update.
		 * @param array $data        The organizer data.
		 *
		 * @return void
		 */
		public static function updateOrganizer( $organizerId, $data ) {
			Tribe__Events__API::saveOrganizerMeta( $organizerId, $data );
			do_action( 'tribe_events_organizer_updated', $organizerId, $data );
		}

		/**
		 * Saves organizer meta
		 *
		 * @param int   $organizerId The organizer ID.
		 * @param array $data        The organizer data.
		 *
		 * @return void
		 */
		private static function saveOrganizerMeta( $organizerId, $data ) {
			foreach ( $data as $key => $var ) {
				update_post_meta( $organizerId, '_Organizer' . $key, $var );
			}
		}

		/**
		 * Creates a new venue
		 *
		 * @param array  $data        The venue data.
		 * @param string $post_status the intended post status.
		 *
		 * @return mixed
		 */
		public static function createVenue( $data, $post_status = 'publish' ) {

			if ( ( isset( $data['Venue'] ) && $data['Venue'] ) || self::someVenueDataSet( $data ) ) {
				$postdata = array(
					'post_title'  => $data['Venue'] ? $data['Venue'] : __( "Unnamed Venue", 'tribe-events-calendar' ),
					'post_type'   => Tribe__Events__Main::VENUE_POST_TYPE,
					'post_status' => $post_status,
				);

				$venueId = wp_insert_post( $postdata, true );

				// By default, the show map and show map link options should be on
				$data['ShowMap'] = isset( $data['ShowMap'] ) ? $data['ShowMap'] : 'true';
				$data['ShowMapLink'] = isset( $data['ShowMapLink'] ) ? $data['ShowMapLink'] : 'true';

				if ( ! is_wp_error( $venueId ) ) {
					self::saveVenueMeta( $venueId, $data );
					do_action( 'tribe_events_venue_created', $venueId, $data );

					return $venueId;
				}
			} else {
				// if the venue is blank, let's save the value as 0 instead
				return 0;
			}
		}

		/**
		 * Check to see if any venue data set
		 *
		 * @param array $data the venue data.
		 *
		 * @return bool If there is ANY venue data set, return true.
		 */
		private static function someVenueDataSet( $data ) {
			foreach ( self::$valid_venue_keys as $key ) {
				if ( isset( $data[$key] ) && $data[$key] ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Updates an venue
		 *
		 * @param int   $venueId The venue ID to update.
		 * @param array $data    The venue data.
		 *
		 * @return void
		 */
		public static function updateVenue( $venue_id, $data ) {
			$data['ShowMap']     = isset( $data['ShowMap'] ) ? $data['ShowMap'] : 'false';
			$data['ShowMapLink'] = isset( $data['ShowMapLink'] ) ? $data['ShowMapLink'] : 'false';

			Tribe__Events__API::saveVenueMeta( $venue_id, $data );
			do_action( 'tribe_events_venue_updated', $venue_id, $data );
		}

		/**
		 * Deletes a venue
		 *
		 * @param int  $venueId      The venue ID to delete.
		 * @param bool $force_delete Same as WP param.
		 *
		 * @return void
		 */
		public static function deleteVenue( $venueId, $force_delete = false ) {
			wp_delete_post( $venueId, $force_delete );
		}

		/**
		 * Saves venue meta
		 *
		 * @param int   $venueId The venue ID.
		 * @param array $data    The venue data.
		 *
		 * @return void
		 */
		private static function saveVenueMeta( $venueId, $data ) {
			// TODO: We should probably do away with 'StateProvince' and stick to 'State' and 'Province'.
			if ( ! isset( $data['StateProvince'] ) || $data['StateProvince'] == '' ) {
				if ( isset( $data['State'] ) && $data['State'] != '' &&
					 ( empty( $data['Country'] ) || $data['Country'] == 'US' || $data['Country'] == __( "United States", 'tribe-events-calendar' ) )
				) {
					$data['StateProvince'] = $data['State'];
				} else {
					if ( isset( $data['Province'] ) && $data['Province'] != '' ) {
						$data['StateProvince'] = $data['Province'];
					} else {
						$data['StateProvince'] = '';
					}
				}
			}

			update_post_meta( $venueId, '_EventShowMapLink', isset( $data['EventShowMapLink'] ) );
			update_post_meta( $venueId, '_EventShowMap', isset( $data['EventShowMap'] ) );
			unset( $data['EventShowMapLink'] );
			unset( $data['EventShowMap'] );

			foreach ( $data as $key => $var ) {
				update_post_meta( $venueId, '_Venue' . $key, $var );
			}
		}
	}
}
