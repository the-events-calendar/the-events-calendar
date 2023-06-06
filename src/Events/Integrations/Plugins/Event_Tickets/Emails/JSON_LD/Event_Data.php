<?php

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails\JSON_LD;

/**
 * Class Event_Schema
 *
 * @since 6.1.1
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Emails\JSON_LD
 */
class Event_Data {

	/**
	 * Filter the event data for the JSON-LD schema.
	 *
	 * @since 6.1.1
	 *
	 * @param array $data The event data.
	 * @param TEC\Tickets\Emails\JSON_LD\Event_Schema $args The event object.
	 *
	 * @return array
	 */
	public function filter_event_data( array $data, $schema ): array {
		$args = $schema->get_args();

		if ( ! isset( $args['event'] ) || ! tribe_is_event( $args['event'] ) ) {
			return $data;
		}

		$event = tribe_get_event( $args['event']->ID );

		$event_details = [
			'name'        => $event->post_title,
			'url'         => get_permalink( $event->ID ),
			'description' => $event->post_content,
			'startDate'   => tribe_format_date( $event->start_date_utc, false, 'Y-m-d\TH:i:sP' ), // Follows ISO 8601.
			'endDate'     => tribe_format_date( $event->end_date_utc, false, 'Y-m-d\TH:i:sP' ), // Follows ISO 8601.
		];

		return array_merge( $data, $event_details, $this->get_location_data( $event ) );
	}

	/**
	 * Get the location data for the schema.
	 *
	 * @since 6.1.1
	 *
	 * @param \WP_Post $event The event object.
	 *
	 * @return array<string,mixed> The data for the schema.
	 */
	public function get_location_data( \WP_Post $event ): array {

		$venue_id = tribe_get_venue_id( $event->ID );
		$location = tribe_get_venue_object( $venue_id );

		if ( empty( $location ) ) {
			// Bail out without location, without location the event will not be shown in gmail markup.
			return [];
		}

		// If there is a location, we need to use a Place.
		$data['location'] = [
			'@type'   => 'Place',
			'name'    => $location->post_title,
			'address' => [
				'@type'           => 'PostalAddress',
				'streetAddress'   => $location->address,
				'addressLocality' => $location->city,
				'addressRegion'   => $location->state_province,
				'postalCode'      => $location->zip,
				'addressCountry'  => $location->country,
			],
		];

		return $data;
	}
}