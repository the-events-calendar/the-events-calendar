<?php 
	class Tribe_Facebook_Export {
		const FACEBOOK_URL = 'https://graph.facebook.com/';
		const FACEBOOK_EVENTS_ENDPOINT = '/events';
		const EVENT_META = 'tribe_fb_event_id';
		public $profileId;
		public $args = array();

		public function __construct($profileId) {
			$this->profileId = $profileId;
		}
		
		public function createEvent($eventId) {
			$this->setArgs($eventId);
			$this->doPost();
		}
		
		public function updateEvent($eventId) {
			$this->setArgs($eventId);
			$this->doPost();			
		}
		
		private function setArgs($eventId) {
			$ecpEvent = get_post($eventId);
			$fbId = get_post_meta($eventId, self::EVENT_META, true);
			$venueId = get_post_meta($eventId, '_EventVenueID', true);

			$this->args = array(
				 name => get_the_title($eventId),
				 description => get_the_content($eventId) ? get_the_content($eventId) : "",
				 start_time => strtotime(Events_Calendar_Pro::getRealStartDate($eventId)),
				 end_time => strtotime(get_post_meta($eventId, '_EventStartDate', true)),
			);
			
			if($fbId)
				$this->args['id'] = $fbId;
			
			if( $venueId ) {
				$this->args['location'] = get_post_meta($venueId, '_VenueVenue', true);
				$venueAddress = get_post_meta($venueId, '_VenueAddress', true);
				$stateOrProvince = get_post_meta($venueId, '_VenueState', true) ?
				   get_post_meta($venueId, '_VenueState', true):
					get_post_meta($venueId, '_VenueProvince', true);
				
				if( $venueAddress ) {
					$this->args['location'] = json_encode(array(
						 'street'=>$venueAddress,
						 'city' => get_post_meta($venueId, '_VenueCity', true),
						 'state' => $stateOrProvince,
						 'zip' => get_post_meta($venueId, '_VenueZip', true),
						 'country' => get_post_meta($venueId, '_VenueCountry', true),
					 ));
				}
			}			
		}
		
		private function doPost() {
			$baseUrl = self::FACEBOOK_URL . $this->profileId . self::FACEBOOK_EVENTS_ENDPOINT;
			$baseUrl = add_query_arg($this->args, trailingslashit($baseUrl));
			print_r($baseUrl); die();
			$results = wp_remote_post($baseUrl);
			print_r($results); die();
		}
	}
