<?php

class Tribe__Events__Aggregator__Event {
	public $data;

	public function __construct( $data = array() ) {
		// maybe translate service data to and Event array
		if ( is_object( $data ) && ! empty( $item->title ) ) {
			$data = self::translate_service_data( $data );
		}

		$this->data = $data;
	}

	public static function translate_service_data( $item ) {
		$event = array();
		$item = (object) $item;

		$field_map = array(
			'title' => 'post_title',
			'description' => 'post_content',
			'start_date' => 'EventStartDate',
			'start_hour' => 'EventStartHour',
			'start_minute' => 'EventStartMinute',
			'start_meridian' => 'EventStartMeridian',
			'end_date' => 'EventEndDate',
			'end_hour' => 'EventEndHour',
			'end_minute' => 'EventEndMinute',
			'end_meridian' => 'EventEndMeridian',
			'timezone' => 'EventTimezone',
			'url' => 'EventURL',
			'all_day' => 'EventAllDay',
			'facebook_id' => 'EventFacebookID',
			'meetup_id' => 'EventMeetupID',
			'_uid' => 'uid',
		);

		foreach ( $field_map as $origin_field => $target_field ) {
			if ( ! isset( $item->$origin_field ) ) {
				continue;
			}

			$event[ $target_field ] = $item->$origin_field;
		}

		if ( ! empty( $item->venue ) ) {
			$event['Venue'] = array();
			$event['Venue'] = (array) $item->venue;
		}

		if ( ! empty( $item->organizer ) ) {
			$event['organizer'] = (array) $item->organizer;
		}

		return $event;
	}

	/**
	 * Fetch all existing unique IDs from the provided list that exist in meta
	 *
	 * @param string $key Meta key
	 * @param array $values Array of meta values
	 *
	 * @return array
	 */
	private function get_existing_ids( $key, $values ) {
		global $wpdb;

		// sanitize values
		foreach ( $values as &$value ) {
			$value = preg_replace( '/[^a-zA-Z0-9]/', '', $value );
		}

		$sql = "
			SELECT
				meta_value,
				post_id
			FROM
				{$wpdb->prefix}postmeta
			WHERE
				meta_key = %s
				AND meta_value IN ('" . implode( "','", $values ) ."')
		";

		return $wpdb->get_results( $wpdb->prepare( $sql, $key ), OBJECT_K );
	}

	/**
	 * Fetch all existing unique facebook IDs from the provided list that exist in meta
	 *
	 * @param array $ids Array of facebook ids
	 *
	 * @return array
	 */
	public function get_existing_facebook_ids( $ids = array() ) {
		if ( ! $ids ) {
			return array();
		}

		return $this->get_existing_ids( '_EventFacebookID', $ids );
	}

	/**
	 * Fetch all existing unique meetup IDs from the provided list that exist in meta
	 *
	 * @param array $ids Array of meetup ids
	 *
	 * @return array
	 */
	public function get_existing_meetup_ids( $ids = array() ) {
		if ( ! $ids ) {
			return array();
		}

		return $this->get_existing_ids( '_EventMeetupID', $ids );
	}

	/**
	 * Fetch all existing unique ical IDs from the provided list that exist in meta
	 *
	 * @param array $ids Array of ical ids
	 *
	 * @return array
	 */
	public function get_existing_ical_ids( $ids = array() ) {
		if ( ! $ids ) {
			return array();
		}

		return $this->get_existing_ids( '_uid', $ids );
	}
}
