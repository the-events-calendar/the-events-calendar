<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__API__Import extends Tribe__Events__Aggregator__API__Abstract {
	public $event_field_map = [
		'title'             => 'post_title',
		'description'       => 'post_content',
		'start_date'        => 'EventStartDate',
		'start_hour'        => 'EventStartHour',
		'start_minute'      => 'EventStartMinute',
		'start_meridian'    => 'EventStartMeridian',
		'end_date'          => 'EventEndDate',
		'end_hour'          => 'EventEndHour',
		'end_minute'        => 'EventEndMinute',
		'end_meridian'      => 'EventEndMeridian',
		'url'               => 'EventURL',
		'parent_id'         => 'parent_id',
		'uid'               => 'uid',
		'dev_start'         => 'dev_start',
		'dev_end'           => 'dev_end',
		'all_day'           => 'EventAllDay',
		'timezone'          => 'EventTimezone',
		'recurrence'        => 'recurrence',
		'categories'        => 'categories',
		'currency_symbol'   => 'EventCurrencySymbol',
		'currency_position' => 'EventCurrencyPosition',
		'cost'              => 'EventCost',
	];

	public $organizer_field_map = [
		'organizer' => 'Organizer',
		'phone'     => 'Phone',
		'website'   => 'Website',
		'email'     => 'Email',
	];

	public $venue_field_map = [
		'venue'                 => 'Venue',
		'address'               => 'Address',
		'city'                  => 'City',
		'stateprovince'         => 'StateProvince',
		'country'               => 'Country',
		'zip'                   => 'Zip',
		'phone'                 => 'Phone',
		'overwrite_coordinates' => 'OverwriteCoords',
		'latitude'              => 'Lat',
		'longitude'             => 'Lng',
	];

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Gets the status (and possibly the results) of an import
	 *
	 * @param string $import_id Event Aggregator import id
	 *
	 * @return stdClass|WP_Error A class containing the service response or a WP_Error if the service could not be reached.
	 */
	public function get( $import_id, $data = [] ) {
		$response = $this->service->get_import( $import_id, $data );

		if ( is_wp_error( $response ) ) {

			/** @var WP_Error $response */
			if ( 'core:aggregator:http_request-limit' === $response->get_error_code() ) {
				$response = (object) [
					'status'       => 'queued',
					'message_code' => 'queued',
					'message'      => tribe( 'events-aggregator.service' )->get_service_message( 'queued' ),
					'data'         => (object) [
						'import_id' => $import_id,
					],
				];
			}

			return $response;
		}

		// let's try to use the localized version of the message if available
		if ( ! empty( $response->message_code ) ) {
			$default           = ! empty( $response->message ) ? $response->message : $this->service->get_unknown_message();
			$message_args      = is_array( $response->data ) ? $response->data : [];
			$response->message = $this->service->get_service_message( $response->message_code, $message_args, $default );
		}

		if ( 'success_import-complete' !== $response->message_code ) {
			return $response;
		}

		$events = [];

		foreach ( $response->data->events as $event ) {
			$events[] = $this->translate_json_to_event( $event );
		}

		return $events;
	}

	/**
	 * Creates an import
	 *
	 * @param array $args Array of arguments for event creation
	 *
	 * @return stdClass|WP_Error
	 */
	public function create( $args ) {
		return $this->service->post_import( $args );
	}

	/**
	 * Update the details of an existing import into EA server.
	 *
	 * @since 5.1.5
	 *
	 * @param $import_id string The ID of the import to be updated.
	 * @param $args      array An key, value array representing the values to update on the EA server.
	 *
	 * @return object|stdClass|string|WP_Error Response from EA server.
	 */
	public function update( $import_id, $args ) {
		return $this->service->update_import( $import_id, $args );
	}

	/**
	 * Translates event JSON to the-events-calendar Event array format
	 *
	 * @param object $json Event data in Event Aggregator format
	 *
	 * @return array
	 */
	public function translate_json_to_event( $json ) {
		if (
			empty( $json->title )
			|| empty( $json->start_date )
			|| empty( $json->start_hour )
			|| empty( $json->start_minute )
			|| empty( $json->end_date )
			|| empty( $json->end_hour )
			|| empty( $json->end_minute )
		) {
			return tribe_error( 'core:aggregator:invalid-event-json' );
		}

		$event = [];

		$event['post_type'] = Tribe__Events__Main::POSTTYPE;

		$event['post_status'] = tribe( 'events-aggregator.settings' )->default_post_status( $json->origin );

		// translate json key/value pairs to event array key/value pairs
		foreach ( get_object_vars( $json ) as $key => $value ) {
			// venues and organizers are a bit more complex. We'll handle those later
			if ( 'venue' === $key || 'organizer' === $key ) {
				continue;
			}

			if ( isset( $this->event_field_map[ $key ] ) ) {
				$key = $this->event_field_map[ $key ];
			}

			$event[ $key ] = $value;
		}

		if ( ! empty( $json->venue ) ) {
			$event['Venue'] = [];

			if ( ! is_array( $json->venue ) ) {
				$json->venue = [ $json->venue ];
			}

			foreach ( $json->venue as $venue ) {
				$venue_data = [];

				if ( empty( $venue->venue ) ) {
					continue;
				}

				foreach ( get_object_vars( $venue ) as $key => $value ) {
					if ( isset( $this->venue_field_map[ $key ] ) ) {
						$key = $this->venue_field_map[ $key ];
					}

					$venue_data[ $key ] = $value;
				}

				$event['Venue'][] = $venue_data;
			}
		}

		if ( ! empty( $json->organizer ) ) {
			$event['Organizer'] = [];

			if ( ! is_array( $json->organizer ) ) {
				$json->organizer = [ $json->organizer ];
			}

			foreach ( $json->organizer as $organizer ) {
				$organizer_data = [];

				if ( empty( $organizer->organizer ) ) {
					continue;
				}

				foreach ( get_object_vars( $organizer ) as $key => $value ) {
					if ( isset( $this->organizer_field_map[ $key ] ) ) {
						$key = $this->organizer_field_map[ $key ];
					}

					$organizer_data[ $key ] = $value;
				}

				$event['Organizer'][] = $organizer_data;
			}
		}

		$show_map_setting = tribe( 'events-aggregator.settings' )->default_map( $json->origin );

		$event['EventShowMap']     = $show_map_setting;
		$event['EventShowMapLink'] = $show_map_setting;

		return $event;
	}
}
