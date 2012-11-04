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
		 */
		public static function createEvent($args) {
			$defaults = array(
				'post_type' => TribeEvents::POSTTYPE
			);			

			$args = wp_parse_args( $args, $defaults);
			$eventId = wp_insert_post($args, true);	

			if( !is_wp_error($eventId) ) {
				TribeEventsAPI::saveEventMeta($eventId, $args, get_post( $eventId ) );
				return $eventId;
			}		
		}
	
		/**
		 * Update an existing event
		 */
		public static function updateEvent( $eventId, $args ) {
			$args['ID'] = $eventId;
		
			if(wp_update_post($args)) {
				TribeEventsAPI::saveEventMeta($eventId, $args, get_post( $eventId ) );
			}

			return $eventId;
		}
	
		/**
		 * Delete an event - all instances if recurring
		 */
		public static function deleteEvent($eventId, $force_delete = false) {
			return wp_delete_post($eventId, $force_delete);		
		}
	
		/**
		 * Used by createEvent and updateEvent - saves all the various event meta 
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
		
			if( !isset( $data['EventShowMapLink'] ) ) update_post_meta( $event_id, '_EventShowMapLink', 'false' );
			if( !isset( $data['EventShowMap'] ) ) update_post_meta( $event_id, '_EventShowMap', 'false' );

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
			
			$tribe_ecp->do_action('tribe_events_event_save', $event_id);

			$cost              = ( isset( $data['EventCost'] ) ) ? $data['EventCost'] : '';
			$data['EventCost'] = $cost;

			//update meta fields
			foreach ( $tribe_ecp->metaTags as $tag ) {
				$htmlElement = ltrim( $tag, '_' );
				if ( isset( $data[$htmlElement] ) && $tag != TribeEvents::EVENTSERROROPT ) {
					if ( is_string($data[$htmlElement]) )
						$data[$htmlElement] = filter_var($data[$htmlElement], FILTER_SANITIZE_STRING);

					update_post_meta( $event_id, $tag, $data[$htmlElement] );
				}
			}

	      	$tribe_ecp->do_action('tribe_events_update_meta', $event_id, false, $data, $event);
		}	
	
		/**
		 * Saves the event organizer information passed via an event
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
		 */
		private static function saveEventVenue($data, $post=null, $post_status='publish') {
			if( isset($data['VenueID']) && $data['VenueID'] > 0) {
				if (count($data) == 1) {
					// Only an ID was passed and we should do nothing.
					return $data['VenueID'];
				} else {
					return TribeEventsAPI::updateVenue($data['VenueID'], $data);
				}
			} else {
				return TribeEventsAPI::createVenue($data, $post_status);
			}
		}	
	
		/**
		 * Creates a new organizer
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
       */
      private static function someOrganizerDataSet($data) {
         foreach(self::$valid_organizer_keys as $key) {
            if(isset($data[$key]) && $data[$key]) return true;
         }

         return false;
      }

		/**
		 * Deletes an organizer
		 */	
		public static function deleteOrganizer($organizerId, $force_delete = false ) {
			wp_delete_post($organizerId, $force_delete);
		}		

		/**
		 * Updates an organizer
		 */		
		public static function updateOrganizer($organizerId, $data) {
			wp_update_post( array('post_title' => $data['Organizer'], 'ID'=>$organizerId ));		
			TribeEventsAPI::saveOrganizerMeta($organizerId, $data);
		}
	
		/**
		 * Saves organizer meta
		 */
		private static function saveOrganizerMeta($organizerId, $data) {
			foreach ($data as $key => $var) {
				update_post_meta($organizerId, '_Organizer'.$key, $var);
			}		
		}
	
		/**
		 * Creates a new venue
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
					return $venueId;
				}
			} else {
				// if the venue is blank, let's save the value as 0 instead
				return 0;
			}
		}	

      /**
       * Check to see if any venue data set
       */
      private static function someVenueDataSet($data) {
         foreach(self::$valid_venue_keys as $key) {
            if( isset($data[$key]) && $data[$key]) return true;
         }

         return false;
      }
	
		/**
		 * Updates a venue
		 */
		public static function updateVenue($venueId, $data) {
			wp_update_post( array('post_title' => $data['Venue'], 'ID'=>$venueId ));		
			TribeEventsAPI::saveVenueMeta($venueId, $data);
		}
	
		/**
		 * Deletes a venue
		 */
		public static function deleteVenue($venueId, $force_delete = false ) {
			wp_delete_post($venueId, $force_delete);
		}	
	
		/**
		 * Saves venue meta
		 */
		private static function saveVenueMeta($venueId, $data) {
			// TODO: We should probably do away with 'StateProvince' and stick to 'State' and 'Province'.
			if (!isset($data['StateProvince']) || $data['StateProvince'] == '') {
				if (isset($data['State']) && $data['State'] != '') {
					$data['StateProvince'] = $data['State'];
				} else if(isset($data['Province'])  && $data['Province'] != '') {
					$data['StateProvince'] = $data['Province'];					
				}
			}
			foreach ($data as $key => $var) {
				update_post_meta($venueId, '_Venue'.$key, $var);
			}		
		}	
	}
}
