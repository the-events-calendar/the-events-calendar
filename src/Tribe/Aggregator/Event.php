<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Event {

	/**
	 * Slug used to mark Event Orgin on `_EventOrigin` meta
	 *
	 * @var string
	 */
	public static $event_origin = 'event-aggregator';

	/**
	 * Key of the Meta to store the Event origin inside of Aggregator
	 *
	 * @var string
	 */
	public static $origin_key = '_tribe_aggregator_origin';

	/**
	 * Key of the Meta to store the Record that imported this Event
	 *
	 * @var string
	 */
	public static $record_key = '_tribe_aggregator_record';

	/**
	 * Key of the Meta to store the Record's source
	 *
	 * @var string
	 */
	public static $source_key = '_tribe_aggregator_source';

	/**
	 * Key of the Meta to store the Record's last import date
	 *
	 * @var string
	 */
	public static $updated_key = '_tribe_aggregator_updated';

	public $data;

	public function __construct( $data = array() ) {
		// maybe translate service data to an Event array
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
			'image' => 'image',
			'facebook_id' => 'EventFacebookID',
			'meetup_id' => 'EventMeetupID',
			'uid' => 'uid',
			'parent_uid' => 'parent_uid',
			'recurrence' => 'recurrence',
			'categories' => 'categories',
		);

		$venue_field_map = array(
			'facebook_id' => 'VenueFacebookID',
			'meetup_id' => 'VenueMeetupID',
			'venue' => 'Venue',
			'address' => 'Address',
			'city' => 'City',
			'country' => 'Country',
			'province' => 'Province',
			'state' => 'State',
			'stateprovince' => 'StateProvince',
			'province' => 'Province',
			'zip' => 'Zip',
			'phone' => 'Phone',
		);

		$organizer_field_map = array(
			'facebook_id' => 'OrganizerFacebookID',
			'meetup_id' => 'OrganizerMeetupID',
			'organizer' => 'Organizer',
			'phone' => 'Phone',
			'email' => 'Email',
			'website' => 'Website',
		);

		foreach ( $field_map as $origin_field => $target_field ) {
			if ( ! isset( $item->$origin_field ) ) {
				continue;
			}

			$event[ $target_field ] = $item->$origin_field;
		}

		if ( ! empty( $item->venue ) ) {
			$event['Venue'] = array();
			foreach ( $venue_field_map as $origin_field => $target_field ) {
				if ( ! isset( $item->venue->$origin_field ) ) {
					continue;
				}

				$event['Venue'][ $target_field ] = $item->venue->$origin_field;
			}
		}

		if ( ! empty( $item->organizer ) ) {
			$event['Organizer'] = array();
			foreach ( $organizer_field_map as $origin_field => $target_field ) {
				if ( ! isset( $item->organizer->$origin_field ) ) {
					continue;
				}

				$event['Organizer'][ $target_field ] = $item->organizer->$origin_field;
			}
		}

		/**
		 * Filter the translation of service data to Event data
		 *
		 * @param array $event EA Service data converted to Event API fields
		 * @param object $item EA Service item being being translated
		 */
		$event = apply_filters( 'tribe_aggregator_translate_service_data', $event, $item );

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
	public function get_existing_ids( $origin, $values ) {
		global $wpdb;

		$fields = Tribe__Events__Aggregator__Record__Abstract::$unique_id_fields;

		if ( empty( $fields[ $origin ] ) ) {
			return array();
		}

		if ( empty( $values ) ) {
			return array();
		}

		$key = "_{$fields[ $origin ]['target']}";

		$sql = "
			SELECT
				meta_value,
				post_id
			FROM
				{$wpdb->postmeta}
			WHERE
				meta_value IN ( '" . implode( "','", $values ) ."' )
		";

		/**
		 * Allows us to check for legacy meta keys
		 */
		if ( ! empty( $fields[ $origin ]['legacy'] ) ) {
			$keys[] = $key;
			$keys[] = "_{$fields[ $origin ]['legacy']}";

			$sql .= 'AND meta_key IN ( "' . implode( '", "', array_map( 'esc_sql', $keys ) ) .'" )';
		} else {
			$sql .= 'AND meta_key = "' . esc_sql( $key ) . '"';
		}

		return $wpdb->get_results( $sql, OBJECT_K );
	}

	/**
	 * Preserves changed fields by resetting array indexes back to the stored post/meta values
	 *
	 * @param array $event Event array to reset
	 *
	 * @return array
	 */
	public static function preserve_changed_fields( $event ) {
		if ( empty( $event['ID'] ) ) {
			return $event;
		}

		$post = get_post( $event['ID'] );
		$post_meta = Tribe__Events__API::get_and_flatten_event_meta( $event['ID'] );

		if ( empty( $post_meta[ Tribe__Events__API::$modified_field_key ] ) ) {
			$modified = array();
		} else {
			$modified = $post_meta[ Tribe__Events__API::$modified_field_key ];
		}

		$post_fields_to_reset = array(
			'post_title',
			'post_content',
			'post_status',
		);

		// reset any modified post fields
		foreach ( $post_fields_to_reset as $field ) {
			// don't bother resetting if the field hasn't been modified
			if ( ! isset( $modified[ $field ] ) ) {
				continue;
			}

			// don't bother resetting if we aren't trying to update the field
			if ( ! isset( $event[ $field ] ) ) {
				continue;
			}

			// don't bother resetting if we don't have a field to reset to
			if ( ! isset( $post->$field ) ) {
				continue;
			}

			$event[ $field ] = $post->$field;
		}

		$tec = Tribe__Events__Main::instance();

		// reset any modified meta fields
		foreach ( $tec->metaTags as $field ) {
			// don't bother resetting if the field hasn't been modified
			if ( ! isset( $modified[ $field ] ) ) {
				continue;
			}

			// if we don't have a field to reset to, let's unset the event meta field
			if ( ! isset( $post_meta[ $field ] ) ) {
				unset( $event[ $field ] );
				continue;
			}

			$event[ $field ] = $post_meta[ $field ];
		}

		return $event;
	}
}
