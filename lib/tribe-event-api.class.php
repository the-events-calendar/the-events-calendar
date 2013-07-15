<?php
/**
 * Event API - can be used by other plugins to manipulate events
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if (!class_exists('TribeEventsAPI')) {
	class TribeEventsAPI {
      public static $valid_venue_keys = array(
         'Venue', 'Address', 'City', 'Province', 'State', 'StateProvince', 'Province', 'Zip', 'Phone'
      );

      public static $valid_organizer_keys = array(
         'Organizer', 'Phone', 'Email', 'Website'
      );
	
		/**
		 * Create a new event
		 *
		 * @param array $args The post args.
		 * @return void|int The created event ID.
		 */
		public static function createEvent($args) {

			$args['post_type'] = TribeEvents::POSTTYPE;
			$eventId = wp_insert_post($args, true);	

			if( !is_wp_error($eventId) ) {
				TribeEventsAPI::saveEventMeta($eventId, $args, get_post( $eventId ) );
				return $eventId;
			}		
		}
	
		/**
		 * Update an existing event
		 *
		 * @param int $eventId The event ID to update.
		 * @param array $args The post args.
		 * @return false|int The event ID.
		 */
		public static function updateEvent( $eventId, $args ) {
			$args['ID'] = $eventId;

			if ( !in_array( TribeEvents::POSTTYPE, (array)$args['post_type'] ) )
				return false;

			if ( wp_update_post( $args ) ) {
				TribeEventsAPI::saveEventMeta( $eventId, $args, get_post( $eventId ) );
			}

			return $eventId;
		}
	
		/**
		 * Delete an event - all instances if recurring
		 *
		 * @param int $eventId The Event ID to delete.
		 * @param bool $force_delete Same as the WP parameter.
		 * @return mixed
		 */
		public static function deleteEvent($eventId, $force_delete = false) {
			return wp_delete_post($eventId, $force_delete);		
		}
	
		/**
		 * Used by createEvent and updateEvent - saves all the various event meta 
		 *
		 * @param int $event_id The event ID we are modifying meta for.
		 * @param array $data The meta fields we want saved.
		 * @param WP_Post The event itself.
		 * @return void
		 */
		public static function saveEventMeta($event_id, $data, $event = null) {
			$tribe_ecp = TribeEvents::instance();

			if( isset($data['EventAllDay']) && ( $data['EventAllDay'] == 'yes' || $data['EventAllDay'] == true || !isset($data['EventStartDate'] ) ) ) {
				$data['EventStartDate'] = TribeDateUtils::beginningOfDay($data['EventStartDate']);
				$data['EventEndDate'] = TribeDateUtils::endOfDay($data['EventEndDate']);
			} else {
				delete_post_meta( $event_id, '_EventAllDay' );
				if( isset( $data['EventStartMeridian'] ) ) {
					$data['EventStartDate'] = date( TribeDateUtils::DBDATETIMEFORMAT, strtotime($data['EventStartDate'] . " " . $data['EventStartHour'] . ":" . $data['EventStartMinute'] . ":00 " . $data['EventStartMeridian']) );
					$data['EventEndDate'] = date( TribeDateUtils::DBDATETIMEFORMAT, strtotime($data['EventEndDate'] . " " . $data['EventEndHour'] . ":" . $data['EventEndMinute'] . ":00 " . $data['EventEndMeridian']) );
				} else {
					$data['EventStartDate'] = date( TribeDateUtils::DBDATETIMEFORMAT, strtotime($data['EventStartDate'] . " " . $data['EventStartHour'] . ":" . $data['EventStartMinute'] . ":00") );
					$data['EventEndDate'] = date( TribeDateUtils::DBDATETIMEFORMAT, strtotime($data['EventEndDate'] . " " . $data['EventEndHour'] . ":" . $data['EventEndMinute'] . ":00") );				
				}
			}
		
			if(!isset($data['EventHideFromUpcoming']) || !$data['EventHideFromUpcoming']) delete_post_meta($event_id, '_EventHideFromUpcoming');

			// sanity check that start date < end date
			$startTimestamp = strtotime( $data['EventStartDate'] );
			$endTimestamp 	= strtotime( $data['EventEndDate'] );

			if ( $startTimestamp > $endTimestamp ) {
				$data['EventEndDate'] = $data['EventStartDate'];
			}
			
			$data['EventDuration'] = strtotime($data['EventEndDate']) - $startTimestamp;

			$old_data['EventStartDate'] = get_post_meta( $event_id, '_EventStartDate', true );
		
			update_post_meta( $event_id, '_EventShowMapLink', isset( $data['venue']['EventShowMapLink'] ) );
			update_post_meta( $event_id, '_EventShowMap', isset( $data['venue']['EventShowMap'] ) );

			if(isset($data['post_status'])){
				$post_status = $data['post_status'];
			}else{

		//print_r($data);

				if (isset($data["Organizer"]["OrganizerID"]))
					$post_status = get_post($data["Organizer"]['OrganizerID'])->post_status;

				if (isset($data['Venue']["VenueID"]))
					$post_status = get_post($data['Venue']['VenueID'])->post_status;
				
			}
			
			if (isset($data["Organizer"])) {
				$data['EventOrganizerID'] = TribeEventsAPI::saveEventOrganizer($data["Organizer"], $event, $post_status);
			}
			if (isset($data["Venue"])) {
				$data['EventVenueID'] = TribeEventsAPI::saveEventVenue($data["Venue"], $event, $post_status);
			}

			$cost = ( isset( $data['EventCost'] ) ) ? $data['EventCost'] : '';
			$data['EventCost'] = $cost;

			$tribe_ecp->do_action('tribe_events_event_save', $event_id);

			$cost              = ( isset( $data['EventCost'] ) ) ? $data['EventCost'] : '';
			$data['EventCost'] = $cost;			

			//update meta fields
			foreach ( $tribe_ecp->metaTags as $tag ) {
				$htmlElement = ltrim( $tag, '_' );
				if ( isset( $data[$htmlElement] ) && $tag != TribeEvents::EVENTSERROROPT ) {
					if ( is_string($data[$htmlElement]) )
						$data[$htmlElement] = filter_var($data[$htmlElement], FILTER_SANITIZE_STRING);
					if ( isset( $old_data[$htmlElement] ) )
						update_post_meta( $event_id, $tag, $data[$htmlElement], $old_data[$htmlElement] );
					else
						update_post_meta( $event_id, $tag, $data[$htmlElement] );
				}
			}

			// Set sticky state for calendar view.
			if ( isset( $data['EventShowInCalendar'] ) && $data['EventShowInCalendar'] == 'yes' && $event->menu_order != '-1' ) {
				$update_event = array(
					'ID' => $event_id,
					'menu_order' => '-1',
				);
				wp_update_post( $update_event );
			} elseif ( ( !isset( $data['EventShowInCalendar'] ) || $data['EventShowInCalendar'] != 'yes' ) && $event->menu_order == '-1' ) {
				$update_event = array(
					'ID' => $event_id,
					'menu_order' => '0',
				);
				wp_update_post( $update_event );
			}

	      	$tribe_ecp->do_action('tribe_events_update_meta', $event_id, false, $data, $event);
		}	
	
		/**
		 * Saves the event organizer information passed via an event
		 *
		 * @param array $data The organizer data.
		 * @param WP_Post $post The post.
		 * @param string $post_status The intended post status.
		 * @return mixed
		 */
		private static function saveEventOrganizer($data, $post=null, $post_status='publish') {
			if( isset($data['OrganizerID']) && $data['OrganizerID'] > 0) {
				if (count($data) == 1) {
					// Only an ID was passed and we should do nothing.
					return $data['OrganizerID'];
				} else {
					return TribeEventsAPI::updateOrganizer($data['OrganizerID'], $data);
				}
			} else {
				return TribeEventsAPI::createOrganizer($data, $post_status);
			}
		}
	
		/**
		 * Saves the event venue information passed via an event
		 *
		 * @param array $data The venue data.
		 * @param WP_Post $post The venue object.
		 * @param string $post_status The intended post status.
		 * @return mixed.
		 */
		private static function saveEventVenue($data, $post=null, $post_status='publish') {
			if( isset($data['VenueID']) && $data['VenueID'] > 0) {
				if (count($data) == 1) {
					// Only an ID was passed and we should do nothing.
					return $data['VenueID'];
				} else {
					$show_map = get_post_meta( $data['VenueID'], '_VenueShowMap', true );
					$show_map_link = get_post_meta( $data['VenueID'], '_VenueShowMapLink', true );
					$data['ShowMap'] = $show_map ? $show_map : 'false';
					$data['ShowMapLink'] = $show_map_link ? $show_map_link : 'false';
					return TribeEventsAPI::updateVenue($data['VenueID'], $data);
				}
			} else {
				return TribeEventsAPI::createVenue($data, $post_status);
			}
		}	
	
		/**
		 * Creates a new organizer
		 *
		 * @param array $data The organizer data.
		 * @param string $post_status the intended post status.
		 * @return mixed
		 */
		public static function createOrganizer($data, $post_status='publish') {
			if ( (isset($data['Organizer']) && $data['Organizer']) || self::someOrganizerDataSet($data) ) {
				
				$postdata = array(
					'post_title' => $data['Organizer'] ? $data['Organizer'] : "Unnamed Organizer",
					'post_type' => TribeEvents::ORGANIZER_POST_TYPE,
					'post_status' => $post_status,
				);			

				$organizerId = wp_insert_post($postdata, true);		

				if( !is_wp_error($organizerId) ) {
					TribeEventsAPI::saveOrganizerMeta($organizerId, $data);
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
       * @return bool If there is ANY organizer data set, return true.
       */
      private static function someOrganizerDataSet($data) {
         foreach(self::$valid_organizer_keys as $key) {
            if(isset($data[$key]) && $data[$key]) return true;
         }

         return false;
      }

		/**
		 * Deletes an organizer
		 *
		 * @param int $organizerId The organizer ID to delete.
		 * @param bool $force_delete Same as WP param.
		 * @return void
		 */	
		public static function deleteOrganizer($organizerId, $force_delete = false ) {
			wp_delete_post($organizerId, $force_delete);
		}		

		/**
		 * Updates an organizer
		 *
		 * @param int $organizerId The organizer ID to update.
		 * @param array $data The organizer data.
		 * @return void
		 */		
		public static function updateOrganizer($organizerId, $data) {
			wp_update_post( array('post_title' => $data['Organizer'], 'ID'=>$organizerId ));		
			TribeEventsAPI::saveOrganizerMeta($organizerId, $data);
		}
	
		/**
		 * Saves organizer meta
		 *
		 * @param int $organizerId The organizer ID.
		 * @param array $data The organizer data.
		 * @return void
		 */
		private static function saveOrganizerMeta($organizerId, $data) {
			foreach ($data as $key => $var) {
				update_post_meta($organizerId, '_Organizer'.$key, $var);
			}		
		}
	
		/**
		 * Creates a new venue
		 *
		 * @param array $data The venue data.
		 * @param string $post_status the intended post status.
		 * @return mixed
		 */
		public static function createVenue($data, $post_status='publish') {
		
			if ( (isset($data['Venue']) && $data['Venue']) || self::someVenueDataSet($data) ) {
				$postdata = array(
					'post_title' => $data['Venue'] ? $data['Venue'] : "Unnamed Venue",
					'post_type' => TribeEvents::VENUE_POST_TYPE,
					'post_status' => $post_status,
				);			

				$venueId = wp_insert_post($postdata, true);		

				if( !is_wp_error($venueId) ) {
					TribeEventsAPI::saveVenueMeta($venueId, $data);
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
       * @return bool If there is ANY venue data set, return true.
       */
      private static function someVenueDataSet($data) {
         foreach(self::$valid_venue_keys as $key) {
            if( isset($data[$key]) && $data[$key]) return true;
         }

         return false;
      }
	
		/**
		 * Updates an venue
		 *
		 * @param int $venueId The venue ID to update.
		 * @param array $data The venue data.
		 * @return void
		 */	
		public static function updateVenue($venueId, $data) {
			wp_update_post( array('post_title' => $data['Venue'], 'ID'=>$venueId ));		
			$data['ShowMap'] = isset( $data['ShowMap'] ) ? $data['ShowMap'] : 'false';
			$data['ShowMapLink'] = isset( $data['ShowMapLink'] ) ? $data['ShowMapLink'] : 'false';
			TribeEventsAPI::saveVenueMeta($venueId, $data);

			do_action( 'tribe_events_venue_updated', $venueId, $data );
		}
	
		/**
		 * Deletes a venue
		 *
		 * @param int $venueId The venue ID to delete.
		 * @param bool $force_delete Same as WP param.
		 * @return void
		 */	
		public static function deleteVenue($venueId, $force_delete = false ) {
			wp_delete_post($venueId, $force_delete);
		}	
	
		/**
		 * Saves venue meta
		 *
		 * @param int $venueId The venue ID.
		 * @param array $data The venue data.
		 * @return void
		 */
		private static function saveVenueMeta($venueId, $data) {
			// TODO: We should probably do away with 'StateProvince' and stick to 'State' and 'Province'.
			if (!isset($data['StateProvince']) || $data['StateProvince'] == '') {
				if (isset($data['State']) && $data['State'] != '' &&
					( empty($data['Country']) || $data['Country'] == 'US' || $data['Country'] == __("United States", 'tribe-events-calendar'))
				) {
					$data['StateProvince'] = $data['State'];
				} else if(isset($data['Province'])  && $data['Province'] != '') {
					$data['StateProvince'] = $data['Province'];
				} else {
					$data['StateProvince'] = '';
				}
			}

			update_post_meta($venueId, '_EventShowMapLink', isset($data['EventShowMapLink']));
			update_post_meta($venueId, '_EventShowMap', isset($data['EventShowMap']));
			unset($data['EventShowMapLink']);
			unset($data['EventShowMap']);

			foreach ($data as $key => $var) {
				update_post_meta($venueId, '_Venue'.$key, $var);
			}		
		}	
	}
}
