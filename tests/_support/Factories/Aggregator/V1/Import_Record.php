<?php

namespace Tribe\Events\Tests\Factories\Aggregator\V1;

/**
 * Class Import_Record
 *
 * Creates mock import record data.
 *
 * @package Tribe\Events\Tests\Factories\Aggregator\V1
 */
class Import_Record {
	/**
	 * Creates the data simulating a record from a specific origin.
	 *
	 * @param string $origin The origin for this import data
	 * @param array $overrides An array of arguments to override the defaults; e.g. `start` to override
	 * the start date, `end` to override the end date and any other argument. Use `organizer`, `venue` and `image`
	 * to override the respective data.
	 *
	 * @return array|object
	 */
	public function create_and_get_event_record( $origin = 'ical', array $overrides = [] ) {
		$uniqid = uniqid( 'record-', true );

		$event_unique_id_key = $this->get_event_unique_id_field( $origin );

		if ( isset( $overrides['start'] ) ) {
			$start = strtotime( $overrides['start'] );
			unset( $overrides['start'] );
		} else {
			$start = strtotime( 'tomorrow 2pm' );
		}

		if ( isset( $overrides['end'] ) ) {
			$end = strtotime( $overrides['end'] );
			unset( $overrides['end'] );
		} else {
			$end = strtotime( 'tomorrow 2pm' );
		}

		$event_unique_source_id = wp_generate_password( 15, true, true );

		$record = array(
			'title'              => "Event {$uniqid}",
			'description'        => "Event {$uniqid} description",
			'origin'             => $origin,
			'source_name'        => "Record {$uniqid} source name",
			$event_unique_id_key => $event_unique_source_id,
			'start_date'         => date( 'Y-m-d', $start ),
			'end_date'           => date( 'Y-m-d', $end ),
			'start_hour'         => date( 'H', $start ),
			'start_minute'       => date( 'i', $start ),
			'start_meridian'     => date( 'A', $start ),
			'end_hour'           => date( 'H', $end ),
			'end_minute'         => date( 'i', $end ),
			'end_meridian'       => date( 'A', $end ),
			'url'                => "https://example.come/{$event_unique_source_id}",
			'timezone'           => 'UTC',
			'start_date_utc'     => date( 'Y-m-d H:i:s', $start ),
			'end_date_utc'       => date( 'Y-m-d H:i:s', $end ),
			'global_id'          => "{$uniqid}-event",
		);

		$image_overrides     = isset( $overrides['image'] ) ? $overrides['image'] : [];
		$organizer_overrides = isset( $overrides['organizer'] ) ? $overrides['organizer'] : [];
		$organizer_count     = isset( $overrides['organizer_count'] ) ? $overrides['organizer_count'] : 2;
		$venue_overrides     = isset( $overrides['venue'] ) ? $overrides['venue'] : [];

		unset( $overrides['image'], $overrides['organizer'], $overrides['organizer_count'], $overrides['venue'] );

		$record = array_merge( $record, $overrides );

		$record ['image']    = $this->create_and_get_image_record( $origin, $image_overrides );
		$record['organizer'] = $this->create_and_get_many_organizers_record( $origin, $organizer_count, $organizer_overrides );
		$record['venue']     = (object) $this->create_and_get_venue_record( $origin, $venue_overrides );

		return (object) $record;
	}

	/**
	 * Returns the EA unique field for events of this origin.
	 *
	 * @param $origin
	 *
	 * @return mixed
	 */
	protected function get_event_unique_id_field( $origin ) {
		$unique_event_fields = \Tribe__Events__Aggregator__Record__Abstract::$unique_id_fields;
		$event_unique_id_key = $unique_event_fields[ $origin ]['source'];

		return $event_unique_id_key;
	}

	/**
	 * Creates the record entry for an image.
	 *
	 * Uses locally uploaded images.
	 *
	 * @param string $origin
	 * @param array $image_overrides
	 *
	 * @return string|array An image URL of an EA Image data array.
	 */
	public function create_and_get_image_record( $origin, array $image_overrides = [] ) {
		$attachment_factory = new \WP_UnitTest_Factory_For_Attachment();
		$image_id           = $attachment_factory->create_upload_object( codecept_data_dir( 'images/featured-image.jpg' ) );
		$image              = get_post( $image_id );

		return empty( $image_overrides ) ? $image->guid : (object) $image_overrides;
	}

	/**
	 * Creates and returns an array of many organizers record data.
	 *
	 * @param string $origin
	 * @param int $count
	 * @param array $overrides
	 *
	 * @return array
	 */
	public function create_and_get_many_organizers_record( $origin, $count, $overrides = [] ) {
		return array_map( function () use ( $origin, $overrides ) {
			return (object) $this->create_and_get_organizer_record( $origin, $overrides );
		}, range( 1, $count ) );
	}

	/**
	 * Creates the record data for an organizer
	 *
	 * @param string $origin
	 * @param array $overrides
	 *
	 * @return array
	 */
	public function create_and_get_organizer_record( $origin, array $overrides = [] ) {
		$uniqid = uniqid( 'organizer-', true );

		return array_merge( [
			$this->get_organizer_unique_id_field( $origin ) => wp_generate_password( 11, false, false ),
			'organizer'                                     => "{$uniqid} Organizer 1",
			'phone'                                         => '11223344',
			'website'                                       => 'http://example.com',
			'email'                                         => 'organizer@exaple.com',
			'global_id'                                     => "{$uniqid}-organizer",
		], $overrides );
	}

	/**
	 * Returns the EA unique field for organizers of this origin.
	 *
	 * @param $origin
	 *
	 * @return string
	 */
	protected function get_organizer_unique_id_field( $origin ) {
		$unique_organizer_fields = \Tribe__Events__Aggregator__Record__Abstract::$unique_organizer_id_fields;
		$organizer_unique_id_key = isset( $unique_organizer_fields[ $origin ]['source'] ) ? $unique_organizer_fields[ $origin ]['source'] : 'unique_id';

		return $organizer_unique_id_key;
	}

	/**
	 * Returns the record data for a venue.
	 *
	 * @param string $origin
	 * @param array $overrides
	 *
	 * @return array
	 */
	public function create_and_get_venue_record( $origin, array $overrides ) {
		$uniqid = uniqid( 'venue-', true );

		return array_merge( [
			$this->get_venue_unique_id_field( $origin ) => wp_generate_password( 15, true, true ),
			'venue'                                     => "{$uniqid} Venue 1",
			'address'                                   => "5, 5th Av.",
			'city'                                      => 'New York',
			'stateprovince'                             => 'NY',
			'country'                                   => 'US',
			'zip'                                       => '10001',
			'phone'                                     => '12233445',
			'global_id'                                 => "{$uniqid}-venue",
		], $overrides );
	}

	/**
	 * Returns the EA unique field for venues of this origin.
	 *
	 * @param $origin
	 *
	 * @return string
	 */
	protected function get_venue_unique_id_field( $origin ) {
		$unique_venue_fields = \Tribe__Events__Aggregator__Record__Abstract::$unique_venue_id_fields;
		$venue_unique_id_key = isset( $unique_venue_fields[ $origin ]['source'] ) ? $unique_venue_fields[ $origin ]['source'] : 'unique_id';

		return $venue_unique_id_key;
	}
}