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
			'Phone',
		);

		public static $valid_organizer_keys = array(
			'Organizer',
			'Phone',
			'Email',
			'Website',
		);

		/**
		 * Create a new event
		 *
		 * @param array $args The post args.
		 *
		 * @return int|WP_Error The created event ID or a WP_Error object if it fails.
		 */
		public static function createEvent( $args ) {
			$args['post_type'] = Tribe__Events__Main::POSTTYPE;

			$args = self::sanitize_event_post_create_update_args( $args );
			/**
			 * Allow filtering of arguments in prior to inserting the event and meta fields.
			 *
			 * @param array $args The fields we want saved.
			 *
			 * @since 4.9.4
			 */
			$args = apply_filters( 'tribe_events_event_insert_args', $args );

			if ( is_wp_error( $args ) ) {
				return $args;
			}

			$event_id = wp_insert_post( $args, true );

			if ( ! is_wp_error( $event_id ) ) {
				self::saveEventMeta( $event_id, $args, get_post( $event_id ) );
			}

			return $event_id;
		}

		/**
		 * Update an existing event
		 *
		 * @param int   $event_id The event ID to update.
		 * @param array $args     The post args.
		 *
		 * @return int|WP_Error The updated event ID or a WP_Error object if it fails.
		 */
		public static function updateEvent( $event_id, $args ) {
			$event_id          = absint( $event_id );
			$post              = get_post( $event_id );
			$args['ID']        = $event_id;
			$args['post_type'] = Tribe__Events__Main::POSTTYPE;

			// allow for the change of the date and the status in the same update request
			if (
				isset( $args['post_date'], $args['post_status'] )
				&& in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) )
				&& $args['post_status'] !== $post->post_status
			) {
				$args['edit_date'] = true;
			}

			/**
			 * Allow hooking prior the update of an event and meta fields.
			 *
			 * @param array   $args The fields we want saved.
			 * @param int     $event_id The event ID we are modifying.
			 * @param WP_Post $post The event itself.
			 *
			 * @since 4.9.4
			 */
			$args = apply_filters( 'tribe_events_event_update_args', $args, $event_id, $post );

			$args = self::sanitize_event_post_create_update_args( $args );
			if ( is_wp_error( $args ) ) {
				return $args;
			}

			/**
			 * Disallow the update for an event via the Tribe API
			 *
			 * @param bool $disallow_update Flag to control the update of a post false by default.
			 * @param int  $event_id The event ID.
			 *
			 * @since 4.9.4
			 */
			if ( apply_filters( 'tribe_events_event_prevent_update', false, $event_id ) ) {
				return $event_id;
			}

			if ( wp_update_post( $args ) ) {
				self::saveEventMeta( $event_id, $args, $post );
			}

			return $event_id;
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
		 * @param int     $event_id The event ID we are modifying meta for.
		 * @param array   $data     The meta fields we want saved.
		 * @param WP_Post $event    The event post, itself.
		 *
		 * @return bool
		 */
		public static function saveEventMeta( $event_id, $data, $event = null ) {
			$tec = Tribe__Events__Main::instance();

			$raw_data = $data;

			$data = self::prepare_event_date_meta( $event_id, $data );

			if ( is_wp_error( $data ) ) {
				/**
				 * Hook fired when saving or updating event meta fields failed due to detection of invalid data.
				 *
				 * Example of invalid data is an EventStartMinute of `60`, since it should be 0-59.
				 *
				 * @param int     $event_id The event ID we are modifying meta for.
				 * @param array   $raw_data The meta fields we tried to send.
				 * @param WP_Post $event    The event itself.
				 *
				 * @since 4.6.20
				 */
				do_action( 'tribe_events_event_save_failed_invalid_meta', $event_id, $raw_data, $event );

				return false;
			}

			if ( empty( $data['EventHideFromUpcoming'] ) ) {
				delete_metadata( 'post', $event_id, '_EventHideFromUpcoming' );
			}

			update_metadata( 'post', $event_id, '_EventShowMapLink', isset( $data['venue']['EventShowMapLink'] ) );
			update_metadata( 'post', $event_id, '_EventShowMap', isset( $data['venue']['EventShowMap'] ) );

			if ( isset( $data['post_status'] ) ) {
				$post_status = $data['post_status'];
			} else {
				$post_status = get_post_status( $event_id );
			}

			// Handle the submission of linked post type posts (like venue and organizer)
			Tribe__Events__Linked_Posts::instance()->handle_submission( $event_id, $data );

			// Ordinarily there is a single cost value for each event, but addons (ie, ticketing plugins) may need
			// to record a number of different price points for the same event
			$event_cost = isset( $data['EventCost'] ) ? (array) $data['EventCost'] : array();
			$data['EventCost'] = (array) apply_filters( 'tribe_events_event_costs', $event_cost, $event_id );

			// If we are saving just one meta, we reset to avoid deleting and re-adding cost every time
			if ( is_array( $data['EventCost'] ) && 1 === count( $data['EventCost'] ) ) {
				$data['EventCost'] = reset( $data['EventCost'] );
			}

			if ( isset( $data['FeaturedImage'] ) ) {
				if ( empty( $data['FeaturedImage'] ) ) {
					delete_post_meta( $event_id, '_thumbnail_id' );
				} else {
					update_metadata( 'post', $event_id, '_thumbnail_id', $data['FeaturedImage'] );
				}
				unset( $data['FeaturedImage'] );
			}

			if ( isset( $data['EventAllDay'] ) && 'yes' === $data['EventAllDay'] ) {
				$data['EventDuration'] = null;
			}

			/**
			 * Allow hooking in prior to updating meta fields.
			 *
			 * @param int     $event_id The event ID we are modifying meta for.
			 * @param array   $data     The meta fields we want saved.
			 * @param WP_Post $event    The event itself.
			 *
			 * @since 4.6
			 */
			do_action( 'tribe_events_event_save', $event_id, $data, $event );

			//update meta fields
			foreach ( $tec->metaTags as $tag ) {
				$htmlElement = ltrim( $tag, '_' );
				if ( isset( $data[ $htmlElement ] ) && $tag != Tribe__Events__Main::EVENTSERROROPT ) {
					if ( is_string( $data[ $htmlElement ] ) ) {
						$data[ $htmlElement ] = filter_var( $data[ $htmlElement ], FILTER_SANITIZE_STRING );
					}
					// Fields with multiple values per key
					if ( is_array( $data[ $htmlElement ] ) ) {
						delete_metadata( 'post', $event_id, $tag );
						foreach ( $data[ $htmlElement ] as $value ) {
							add_metadata( 'post', $event_id, $tag, $value );
						}
					}
					// Fields with a single value per key
					else {
						update_metadata( 'post', $event_id, $tag, $data[ $htmlElement ] );
					}
				}
			}

			// Set sticky state for calendar view.
			if ( $event instanceof WP_Post ) {
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
			}

			// Set featured status
			empty( $data['feature_event'] )
				? tribe( 'tec.featured_events' )->unfeature( $event_id )
				: tribe( 'tec.featured_events' )->feature( $event_id );

			/**
			 * Allow hooking in after all event meta has been saved.
			 *
			 * @param int     $event_id The event ID we are modifying meta for.
			 * @param array   $data     The meta fields we want saved.
			 * @param WP_Post $event    The event itself.
			 *
			 * @since 4.6
			 */
			do_action( 'tribe_events_update_meta', $event_id, $data, $event );

			return true;
		}

		/**
		 * Determines if a meta value has been changed
		 *
		 * @param string $field Field to compare against
		 * @param array $new New data
		 * @param array $old Old post data
		 *
		 * @return boolean
		 */
		public static function is_meta_value_changed( $field, $new, $old ) {
			if ( 0 === strpos( $field, '_' ) ) {
				$field = ltrim( $field, '_' );
			}

			$prefixed_field = "_{$field}";

			if ( isset( $new[ $field ] ) && ! isset( $old[ $prefixed_field ] ) && ! empty( $new[ $field ] ) ) {
				return true;
			}

			if ( ! isset( $new[ $field ] ) && isset( $old[ $prefixed_field ] ) ) {
				return true;
			} elseif ( ! isset( $new[ $field ] ) ) {
				// if the new field isn't set and the old field isn't set, there's no change
				return false;
			}

			$data_value = $new[ $field ];

			if ( is_array( $data_value ) && ! count( $data_value ) ) {
				$data_value = null;
			}

			if ( ! isset( $old[ $prefixed_field ] ) || $data_value !== $old[ $prefixed_field ] ) {
				return true;
			}

			return false;
		}

		/**
		 * Determines if a post value has been changed
		 *
		 * @param string $field Field to compare against
		 * @param array $new New data
		 * @param array $old WP_Post pre-update
		 *
		 * @return boolean
		 */
		public static function is_post_value_changed( $field, $new, $old ) {
			if ( ! is_object( $new ) ) {
				$new = (object) $new;
			}

			if ( ! is_object( $old ) ) {
				$old = (object) $old;
			}

			if ( ! isset( $new->$field ) ) {
				return false;
			}

			if ( isset( $new->$field ) && ! isset( $old->$field ) ) {
				return true;
			}

			if ( $new->$field !== $old->$field ) {
				return true;
			}

			return false;
		}

		/**
		 * given a set of meta data, prepare date data if it exists
		 *
		 * @param $data array Associative array of event meta data
		 *
		 * @return array|WP_Error
		 */
		protected static function prepare_event_date_meta( $event_id, $data ) {
			$date_provided = false;

			if ( isset( $data['EventAllDay'] ) ) {
				if ( Tribe__Date_Utils::is_all_day( $data['EventAllDay'] ) ) {
					$data['EventAllDay'] = 'yes';
				} else {
					$data['EventAllDay'] = 'no';
				}
			}

			$data = self::sanitize_event_post_create_update_args( $data );
			if ( is_wp_error( $data ) ) {
				return $data;
			}

			$datepicker_format = Tribe__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );

			if ( isset( $data['EventStartDate'] ) ) {
				$data['EventStartDate'] = Tribe__Date_Utils::datetime_from_format( $datepicker_format, $data['EventStartDate'] );
			} elseif ( $existing_start_date = get_post_meta( $event_id, '_EventStartDate', true ) ) {
				$data['EventStartDate'] = $existing_start_date;
			}

			if ( isset( $data['EventEndDate'] ) ) {
				$data['EventEndDate'] = Tribe__Date_Utils::datetime_from_format( $datepicker_format, $data['EventEndDate'] );
			} elseif ( $existing_end_date = get_post_meta( $event_id, '_EventEndDate', true ) ) {
				$data['EventEndDate'] = $existing_end_date;
			}

			if ( isset( $data['EventAllDay'] ) && 'yes' === $data['EventAllDay'] ) {
				$date_provided = true;
				$data['EventStartDate'] = tribe_beginning_of_day( $data['EventStartDate'] );
				$data['EventEndDate']   = tribe_end_of_day( $data['EventEndDate'] );
			} elseif ( isset( $data['EventStartDate'], $data['EventEndDate'] ) ) {
				$has_start_time      = isset( $data['EventStartTime'] );
				$has_start_hour_min  = isset( $data['EventStartHour'], $data['EventStartMinute'] );
				$has_end_time        = isset( $data['EventEndTime'] );
				$has_end_hour_minute = isset( $data['EventEndHour'], $data['EventEndMinute'] );

				$date_provided = ( $has_start_time || $has_start_hour_min ) && ( $has_end_time || $has_end_hour_minute );

				delete_post_meta( $event_id, '_EventAllDay' );

				if ( $has_start_time || $has_start_hour_min ) {
					// EventStartTime will always be 24h Format
					if ( $has_start_time ) {
						$start_date_string = "{$data['EventStartDate']} {$data['EventStartTime']}";
					} elseif ( $has_start_hour_min ) {
						$start_date_string = "{$data['EventStartDate']} {$data['EventStartHour']}:{$data['EventStartMinute']}:00";
						if ( isset( $data['EventStartMeridian'] ) ) {
							$start_date_string .= " {$data['EventStartMeridian']}";
						}
					}

					$data['EventStartDate'] = date( Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( $start_date_string ) );
				}

				if ( $has_end_time || $has_end_hour_minute ) {
					// EventEndTime will always be 24h Format
					if ( $has_end_time ) {
						$end_date_string = "{$data['EventEndDate']} {$data['EventEndTime']}";
					} elseif ( $has_end_hour_minute ) {
						$end_date_string = "{$data['EventEndDate']} {$data['EventEndHour']}:{$data['EventEndMinute']}:00";
						if ( isset( $data['EventEndMeridian'] ) ) {
							$end_date_string .= " {$data['EventEndMeridian']}";
						}
					}

					$data['EventEndDate'] = date( Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( $end_date_string ) );
				}
			}

			if ( ! $date_provided ) {
				$data['EventStartDate'] = get_post_meta( $event_id, '_EventStartDate', true );
				$data['EventEndDate']   = get_post_meta( $event_id, '_EventEndDate', true );
			}

			// If a specific timezone was not specified, default to the sitewide timezone
			if ( empty( $data['EventTimezone'] ) ) {
				$data['EventTimezone'] = Tribe__Events__Timezones::wp_timezone_string();
			}

			if ( empty( $data['EventTimezoneAbbr'] ) ) {
				if ( Tribe__Timezones::is_utc_offset( $data['EventTimezone'] ) ) {
					$data['EventTimezoneAbbr'] = $data['EventTimezone'];
				} elseif ( Tribe__Timezones::is_valid_timezone( $data['EventTimezone'] ) ) {
					$data['EventTimezoneAbbr'] = Tribe__Events__Timezones::abbr( $data['EventStartDate'], $data['EventTimezone'] );
				} else {
					$data['EventTimezoneAbbr'] = Tribe__Timezones::wp_timezone_abbr( $data['EventStartDate'] );
				}
			}

			if ( ! $date_provided ) {
				return $data;
			}

			// Additionally store datetimes in UTC
			if ( empty( $data['EventStartDateUTC'] ) ) {
				$data['EventStartDateUTC'] = Tribe__Events__Timezones::to_utc( $data['EventStartDate'], $data['EventTimezone'] );
			}

			if ( empty( $data['EventEndDateUTC'] ) ) {
				$data['EventEndDateUTC']   = Tribe__Events__Timezones::to_utc( $data['EventEndDate'], $data['EventTimezone'] );
			}

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
		 * @param int $event_id The event post ID
		 * @param array $args An array of arguments supported by the `wp_get_object_terms` function.
		 *
		 * @since 4.5
		 *
		 * @see wp_get_object_terms()
		 *
		 * @return array An associative array of terms in the [ <taxonomy> => [ <term_1>, <term_2>, ...], ...] format.
		 */
		public static function get_event_terms( $event_id, array $args = array() ) {
			$terms = array();
			foreach ( get_post_taxonomies( $event_id ) as $taxonomy ) {
				$tax_terms = wp_get_object_terms( $event_id, $taxonomy, $args );
				$terms[ $taxonomy ] = $tax_terms;
			}

			return $terms;
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
			$organizer_id = ! empty( $data['OrganizerID'] ) ? $data['OrganizerID'] : null;
			return Tribe__Events__Organizer::instance()->save( $organizer_id, $data, Tribe__Events__Organizer::POSTTYPE, $post_status );
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
			$venue_id = ! empty( $data['VenueID'] ) ? $data['VenueID'] : null;

			return Tribe__Events__Venue::instance()->save( $venue_id, $data, Tribe__Events__Venue::POSTTYPE, $post_status );
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
			return Tribe__Events__Organizer::instance()->create( $data, $post_status );
		}

		/**
		 * Check to see if any organizer data set
		 *
		 * @param array $data the organizer data.
		 *
		 * @return bool If there is ANY organizer data set, return true.
		 */
		private static function someOrganizerDataSet( $data ) {
			return Tribe__Events__Organizer::instance()->has_organizer_data( $data );
		}

		/**
		 * Deletes an organizer
		 *
		 * @param int  $organizer_id  The organizer ID to delete.
		 * @param bool $force_delete Same as WP param.
		 *
		 */
		public static function deleteOrganizer( $organizer_id, $force_delete = false ) {
			return Tribe__Events__Organizer::instance()->delete( $organizer_id, $force_delete );
		}

		/**
		 * Updates an organizer
		 *
		 * @param int   $organizer_id The organizer ID to update.
		 * @param array $data        The organizer data.
		 *
		 */
		public static function updateOrganizer( $organizer_id, $data ) {
			return Tribe__Events__Organizer::instance()->update( $organizer_id, $data );
		}

		/**
		 * Saves organizer meta
		 *
		 * @param int   $organizer_id The organizer ID.
		 * @param array $data        The organizer data.
		 *
		 */
		private static function saveOrganizerMeta( $organizer_id, $data ) {
			return Tribe__Events__Organizer::instance()->save_meta( $organizer_id, $data );
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
			return Tribe__Events__Venue::instance()->create( $data, $post_status );
		}

		/**
		 * Check to see if any venue data set
		 *
		 * @param array $data the venue data.
		 *
		 * @return bool If there is ANY venue data set, return true.
		 */
		private static function someVenueDataSet( $data ) {
			return Tribe__Events__Venue::instance()->has_venue_data( $data );
		}

		/**
		 * Updates an venue
		 *
		 * @param int   $venue_id The venue ID to update.
		 * @param array $data    The venue data.
		 *
		 */
		public static function updateVenue( $venue_id, $data ) {
			return Tribe__Events__Venue::instance()->update( $venue_id, $data );
		}

		/**
		 * Deletes a venue
		 *
		 * @param int  $venue_id      The venue ID to delete.
		 * @param bool $force_delete Same as WP param.
		 *
		 */
		public static function deleteVenue( $venue_id, $force_delete = false ) {
			return Tribe__Events__Venue::instance()->delete( $venue_id, $force_delete );
		}

		/**
		 * Saves venue meta
		 *
		 * @param int   $venue_id The venue ID.
		 * @param array $data    The venue data.
		 *
		 */
		private static function saveVenueMeta( $venue_id, $data ) {
			return Tribe__Events__Venue::instance()->save_meta( $venue_id, $data );
		}

		/**
		 * Gets all post meta and flattens it out a bit
		 *
		 * @param int $event_id Post ID for event
		 *
		 * @return array
		 */
		public static function get_and_flatten_event_meta( $event_id ) {
			$temp_post_meta = get_post_meta( $event_id );
			$post_meta = array();
			foreach ( (array) $temp_post_meta as $key => $value ) {
				if ( 1 === count( $value ) ) {
					$post_meta[ $key ] = maybe_unserialize( reset( $value ) );
				} else {
					$post_meta[ $key ] = maybe_unserialize( $value );
				}
			}

			return $post_meta;
		}

		/**
		 * Sanitize the arguments array before sending to create/update an event post.
		 *
		 * Use this prior to sending arguments to post create/update function.
		 *
		 * @since 4.6.20
		 *
		 * @see Tribe__Events__API::sanitize_meridian_meta_value
		 * @see Tribe__Events__API::sanitize_hour_meta_value
		 * @see Tribe__Events__API::sanitize_minute_meta_value
		 *
		 * @param array $args The arguments sent to create/update an event post.
		 *
		 * @return array|WP_Error
		 */
		public static function sanitize_event_post_create_update_args( $args ) {
			if (
				! is_array( $args )
				|| empty( $args['post_type'] )
				|| Tribe__Events__Main::POSTTYPE !== $args['post_type']
			) {
				return $args;
			}

			// Sanitize if valid, or fail with WP_Error if invalid.
			// Could enhance this with more comprehensive checks in the future.

			// Process meridian fields before hour fields to determine if hours are to be in the 12-hour or 24-hour format.
			if ( ! empty( $args['EventStartMeridian'] ) ) {
				$args['EventStartMeridian'] = self::sanitize_meridian_meta_value( $args['EventStartMeridian'] );
			}

			if ( ! empty( $args['EventEndMeridian'] ) ) {
				$args['EventEndMeridian'] = self::sanitize_meridian_meta_value( $args['EventEndMeridian'] );
			}

			// If meridian is set but we can pretty easily guess the hour is a valid 24-hour format, discard meridian in attempt to be smarter/flexible, thus setting "14pm" (invalid) to "14" (equivalent to 2pm).
			// We take this approach instead of just minus `12` from the integer value of the hour because meridian may be set by defaults.
			// Watch out for sending "12pm" or "12am" as the meridian (12-hour format) will take precedence if it exists.
			if (
				! empty( $args['EventStartMeridian'] )
				&& absint( $args['EventStartHour'] ) > 12
				&& absint( $args['EventStartHour'] ) < 24
			) {
				$args['EventStartMeridian'] = '';
			}

			if (
				! empty( $args['EventEndMeridian'] )
				&& absint( $args['EventEndHour'] ) > 12
				&& absint( $args['EventEndHour'] ) < 24
			) {
				$args['EventEndMeridian'] = '';
			}

			// Now process all but the meridians
			foreach ( $args as $key => &$value ) {
				if ( 'EventStartHour' === $key ) {
					$twelve_hour = ! empty( $args['EventStartMeridian'] );
					$value       = self::sanitize_hour_meta_value( $value, $twelve_hour );
				} elseif ( 'EventEndHour' === $key ) {
					$twelve_hour = ! empty( $args['EventEndMeridian'] );
					$value       = self::sanitize_hour_meta_value( $value, $twelve_hour );
				} elseif (
					'EventStartMinute' === $key
					|| 'EventEndMinute' === $key
				) {
					$value = self::sanitize_minute_meta_value( $value );
				}

				if ( is_wp_error( $value ) ) {
					return $value;
				}
			}

			return $args;
		}

		/**
		 * Sanitize a string to be used as an event meridian post meta value: am|pm.
		 *
		 * Use this prior to sending value to the database.
		 *
		 * @since 4.6.20
		 *
		 * @param string $value            The post meta value to be checked, such as 'am'.
		 * @param bool   $empty_if_invalid If true, set an invalid value to an empty string, else generate WP_Error.
		 *
		 * @return string|WP_Error
		 */
		public static function sanitize_meridian_meta_value( $value, $empty_if_invalid = true ) {
			// lower-case to match the `a` PHP date format used elsewhere
			$new_value = strtolower( trim( $value ) );

			$is_valid = 'am' === $new_value || 'pm' === $new_value;

			$invalid_value = '';

			if ( ! $empty_if_invalid ) {
				$error_message = sprintf(
					esc_html__( 'An event having a post meta value of `%s` meridian is not valid. Make sure it is either `am` or `pm`, or remove it entirely if using the 24-hour format.', 'the-events-calendar' ),
					$value
				);

				$invalid_value = new WP_Error( 'invalid-tribe-events-meridian-meta-value', $error_message );
			}

			return $is_valid ? $new_value : $invalid_value;
		}

		/**
		 * Sanitize a string to be used as an event hour post meta value: 1-12 if `Event{Start|End}Meridian`
		 * is also passed, else 0-23.
		 *
		 * Use this prior to sending value to the database.
		 *
		 * @since 4.6.20
		 *
		 * @param string $value       The post meta value to be checked, such as '07'.
		 * @param bool   $twelve_hour If false, allow 0-23. If true, only allow 1-12.
		 *
		 * @return string|WP_Error
		 */
		public static function sanitize_hour_meta_value( $value, $twelve_hour = false ) {
			$value = (string) $value;

			if ( ! is_numeric( $value ) ) {
				$error_message = sprintf(
					esc_html__( 'An event having a post meta value of `%s` hour (am/pm) is not valid. Make sure it is from 1 to 12.', 'the-events-calendar' ),
					$value
				);

				return new WP_Error( 'non-numeric-tribe-events-hour-meta-value', $error_message );
			}

			$new_value = absint( $value );

			if (
				$twelve_hour
				&& (
					$new_value < 1
					|| $new_value > 12
				)
			) {
				$error_message = sprintf(
					esc_html__( 'An event having a post meta value of `%s` hour (12-hour) is not valid. Make sure it is from 1 to 12.', 'the-events-calendar' ),
					$value
				);

				return new WP_Error( 'invalid-tribe-events-12-hour-meta-value', $error_message );
			} elseif ( $new_value > 23 ) {
				$error_message = sprintf(
					esc_html__( 'An event having a post meta value of `%s` hour (24-hour) is not valid. Make sure it is from 0 to 23.', 'the-events-calendar' ),
					$value
				);

				return new WP_Error( 'invalid-tribe-events-24-hour-meta-value', $error_message );
			}

			return str_pad( $new_value, 2, '0', STR_PAD_LEFT );
		}

		/**
		 * Sanitize a string to be used as an event minute post meta value: 0-59.
		 *
		 * Use this prior to sending value to the database.
		 *
		 * @since 4.6.20
		 *
		 * @param string $value The post meta value to be checked, such as '30'.
		 *
		 * @return string|WP_Error
		 */
		public static function sanitize_minute_meta_value( $value ) {
			$value = (string) $value;

			if ( '' === trim( $value ) ) {
				return '';
			}

			if ( ! is_numeric( $value ) ) {
				$error_message = sprintf(
					esc_html__( 'An event having a post meta value of `%s` minutes is not valid. Make sure it is from 0 to 59.', 'the-events-calendar' ),
					$value
				);

				return new WP_Error( 'non-numeric-tribe-events-minutes-meta-value', $error_message );
			}

			$new_value = absint( $value );

			if ( $new_value > 59 ) {
				$error_message = sprintf(
					esc_html__( 'An event having a post meta value of `%s` minutes is not valid. Make sure it is from 0 to 59.', 'the-events-calendar' ),
					$value
				);

				return new WP_Error( 'invalid-tribe-events-minutes-meta-value', $error_message );
			}

			return str_pad( $new_value, 2, '0', STR_PAD_LEFT );
		}
	}
}
