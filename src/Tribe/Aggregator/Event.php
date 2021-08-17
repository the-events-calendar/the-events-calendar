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
	 * Key of the Meta to store the Post Global ID
	 *
	 * @var string
	 */
	public static $global_id_key = '_tribe_aggregator_global_id';

	/**
	 * Key of the Meta to store the Post Global ID lineage
	 *
	 * @var string
	 */
	public static $global_id_lineage_key = '_tribe_aggregator_global_id_lineage';

	/**
	 * Key of the Meta to store the Record's last import date
	 *
	 * @var string
	 */
	public static $updated_key = '_tribe_aggregator_updated';

	public $data;

	public function __construct( $data = [] ) {
		// maybe translate service data to an Event array
		if ( is_object( $data ) && ! empty( $item->title ) ) {
			$data = self::translate_service_data( $data );
		}

		$this->data = $data;
	}

	public static function translate_service_data( $item ) {
		$event = [];
		$item = (object) $item;

		$field_map = [
			'title'              => 'post_title',
			'description'        => 'post_content',
			'excerpt'            => 'post_excerpt',
			'start_date'         => 'EventStartDate',
			'start_hour'         => 'EventStartHour',
			'start_minute'       => 'EventStartMinute',
			'start_meridian'     => 'EventStartMeridian',
			'end_date'           => 'EventEndDate',
			'end_hour'           => 'EventEndHour',
			'end_minute'         => 'EventEndMinute',
			'end_meridian'       => 'EventEndMeridian',
			'timezone'           => 'EventTimezone',
			'url'                => 'EventURL',
			'all_day'            => 'EventAllDay',
			'image'              => 'image',
			'meetup_id'          => 'EventMeetupID',
			'eventbrite_id'      => 'EventBriteID',
			'eventbrite'         => 'eventbrite',
			'uid'                => 'uid',
			'parent_uid'         => 'parent_uid',
			'recurrence'         => 'recurrence',
			'categories'         => 'categories',
			'tags'               => 'tags',
			'id'                 => 'EventOriginalID',
			'currency_symbol'    => 'EventCurrencySymbol',
			'currency_position'  => 'EventCurrencyPosition',
			'cost'               => 'EventCost',
			'show_map'           => 'show_map',
			'show_map_link'      => 'show_map_link',
			'hide_from_listings' => 'hide_from_listings',
			'sticky'             => 'sticky',
			'featured'           => 'feature_event',
		];

		$venue_field_map = [
			'_venue_id'             => 'VenueID',
			'meetup_id'             => 'VenueMeetupID',
			'eventbrite_id'         => 'VenueEventBriteID',
			'venue'                 => 'Venue',
			'address'               => 'Address',
			'city'                  => 'City',
			'country'               => 'Country',
			'state'                 => 'State',
			'stateprovince'         => 'Province',
			'zip'                   => 'Zip',
			'phone'                 => 'Phone',
			'website'               => 'URL',
			'overwrite_coordinates' => 'OverwriteCoords',
			'latitude'              => 'Lat',
			'longitude'             => 'Lng',
		];

		$organizer_field_map = [
			'_organizer_id' => 'OrganizerID',
			'meetup_id'     => 'OrganizerMeetupID',
			'eventbrite_id' => 'OrganizerEventBriteID',
			'organizer'     => 'Organizer',
			'phone'         => 'Phone',
			'email'         => 'Email',
			'website'       => 'Website',
		];

		/**
		 * Allows filtering to add other field mapping values.
		 *
		 * @since 4.6.24
		 *
		 * @param array  $field_map Field map for event object.
		 * @param object $item      Item being translated.
		 */
		$field_map = apply_filters( 'tribe_aggregator_event_translate_service_data_field_map', $field_map, $item );

		/**
		 * Allows filtering to add other field mapping values.
		 *
		 * @since 4.6.24
		 *
		 * @param array  $venue_field_map Field map for venue object.
		 * @param object $item            Item being translated.
		 */
		$venue_field_map = apply_filters( 'tribe_aggregator_event_translate_service_data_venue_field_map', $venue_field_map, $item );

		/**
		 * Allows filtering to add other field mapping values.
		 *
		 * @since 4.6.24
		 *
		 * @param array  $organizer_field_map Field map for organizer object.
		 * @param object $item                Item being translated.
		 */
		$organizer_field_map = apply_filters( 'tribe_aggregator_event_translate_service_data_organizer_field_map', $organizer_field_map, $item );

		foreach ( $field_map as $origin_field => $target_field ) {
			if ( ! isset( $item->$origin_field ) ) {
				continue;
			}

			$event[ $target_field ] = $item->$origin_field;
		}

		if ( ! empty( $item->venue ) ) {
			$event['Venue'] = [];
			foreach ( $venue_field_map as $origin_field => $target_field ) {
				if ( ! isset( $item->venue->$origin_field ) ) {
					continue;
				}

				$event['Venue'][ $target_field ] = $item->venue->$origin_field;
			}
		}

		if ( ! empty( $item->organizer ) ) {
			$event['Organizer'] = [];
			$organizer_entries = is_array( $item->organizer ) ? $item->organizer : [ $item->organizer ];

			foreach ( $organizer_entries as $organizer_entry ) {
				$this_organizer = [];

				foreach ( $organizer_field_map as $origin_field => $target_field ) {
					if ( ! isset( $organizer_entry->$origin_field ) ) {
						continue;
					}

					$this_organizer[ $target_field ] = $organizer_entry->$origin_field;
				}

				$event['Organizer'][] = $this_organizer;
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
			return [];
		}

		if ( empty( $values ) ) {
			return [];
		}

		$key = "_{$fields[ $origin ]['target']}";
		$post_type = Tribe__Events__Main::POSTTYPE;
		$interval = "('" . implode( "','", array_map( 'esc_sql', $values ) ) . "')";

		$sql = "
			SELECT
				pm.meta_value,
				pm.post_id
			FROM
				{$wpdb->postmeta} pm
			JOIN
				{$wpdb->posts} p
			ON
				pm.post_id = p.ID
			WHERE
				p.post_type = '{$post_type}'
				AND meta_value IN {$interval}
		";

		/**
		 * Allows us to check for legacy meta keys
		 */
		if ( ! empty( $fields[ $origin ]['legacy'] ) ) {
			$keys[] = $key;
			$keys[] = "_{$fields[ $origin ]['legacy']}";
			$combined_keys = implode(
				 ', ',
				 array_map(
					 function ( $meta_key ) {
						$meta_key = esc_sql( $meta_key );

						return "'{$meta_key}'";
					},
					 $keys
				)
			);

			// Results in "AND meta_key IN ( 'one', 'two', 'etc' )"
			$sql .= "AND meta_key IN ( {$combined_keys} )";
		} else {
			$key = esc_sql( $key );
			// Results in "AND meta_key = 'one'"
			$sql .= "AND meta_key = '{$key}'";
		}

		return $wpdb->get_results( $sql, OBJECT_K );
	}

	/**
	 * Fetch the Post ID for a given Global ID
	 *
	 * @param array $value The Global ID we are searching for
	 *
	 * @return bool|WP_Post
	 */
	public static function get_post_by_meta( $key, $value = null ) {
		if ( null === $value ) {
			return false;
		}

		$keys = [
			'global_id'         => self::$global_id_key,
			'global_id_lineage' => self::$global_id_lineage_key,
		];

		if ( isset( $keys[ $key ] ) ) {
			$key = $keys[ $key ];
		}

		global $wpdb;

		$sql = "
			SELECT
				post_id
			FROM
				{$wpdb->postmeta}
			WHERE
				meta_key = '" . esc_sql( $key ) . "' AND
				meta_value = '" . esc_sql( $value ) . "'
		";
		$id = (int) $wpdb->get_var( $sql );

		if ( ! $id ) {
			return false;
		}

		return get_post( $id );
	}

	/**
	 * Preserves changed fields by resetting array indexes back to the stored post/meta values
	 *
	 * @param array $data Event array to reset
	 *
	 * @return array
	 */
	public static function preserve_changed_fields( $data ) {
		if ( empty( $data['ID'] ) ) {
			return $data;
		}

		$post       = get_post( $data['ID'] );
		$post_meta  = Tribe__Events__API::get_and_flatten_event_meta( $data['ID'] );
		$post_terms = Tribe__Events__API::get_event_terms( $data['ID'], [ 'fields' => 'ids' ] );
		$modified   = Tribe__Utils__Array::get( $post_meta, Tribe__Tracker::$field_key, [] );
		$tec        = Tribe__Events__Main::instance();

		// Depending on the Post Type we fetch other fields
		if ( Tribe__Events__Main::POSTTYPE === $post->post_type ) {
			$fields = $tec->metaTags;
		} elseif ( Tribe__Events__Venue::POSTTYPE === $post->post_type ) {
			$fields = $tec->venueTags;

			if ( isset( $data['Venue'] ) ) {
				$data['post_title'] = $data['Venue'];
				unset( $data['Venue'] );
			}

			if ( isset( $data['Description'] ) ) {
				$data['post_content'] = $data['Description'];
				unset( $data['Description'] );
			}

			if ( isset( $data['Excerpt'] ) ) {
				$data['post_excerpt'] = $data['Excerpt'];
				unset( $data['Excerpt'] );
			}
		} elseif ( Tribe__Events__Organizer::POSTTYPE === $post->post_type ) {
			$fields = $tec->organizerTags;

			if ( isset( $data['Organizer'] ) ) {
				$data['post_title'] = $data['Organizer'];
				unset( $data['Organizer'] );
			}

			if ( isset( $data['Description'] ) ) {
				$data['post_content'] = $data['Description'];
				unset( $data['Description'] );
			}

			if ( isset( $data['Excerpt'] ) ) {
				$data['post_excerpt'] = $data['Excerpt'];
				unset( $data['Excerpt'] );
			}
		} else {
			$fields = [];
		}

		// add the featured image to the fields
		$fields[] = '_thumbnail_id';

		$post_fields_to_reset = [
			'post_title',
			'post_content',
			'post_status',
			'post_excerpt',
		];

		// reset any modified post fields
		foreach ( $post_fields_to_reset as $field ) {
			// don't bother resetting if the field hasn't been modified
			if ( ! isset( $modified[ $field ] ) ) {
				continue;
			}

			// don't bother resetting if we aren't trying to update the field
			if ( ! isset( $data[ $field ] ) ) {
				continue;
			}

			// don't bother resetting if we don't have a field to reset to
			if ( ! isset( $post->$field ) ) {
				continue;
			}

			$data[ $field ] = $post->$field;
		}

		// reset any modified meta fields
		foreach ( $fields as $field ) {
			// don't bother resetting if the field hasn't been modified
			if ( ! isset( $modified[ $field ] ) ) {
				continue;
			}

			if ( $field === '_thumbnail_id' ) {
				$field_name = 'image';
			} else {
				// If the field name contains a leading underscore we need to strip it (or the field will not save)
				$field_name = trim( $field, '_' );
			}

			// some fields might have been modified emptying them: we still keep that change
			if ( empty( $post_meta[ $field ] ) ) {
				unset( $data[ $field_name ] );
			} else {
				$data[ $field_name ] = $post_meta[ $field ];
			}
		}

		// The start date needs to be adjusted from a MySQL style datetime string to just the date
		if ( isset( $modified['_EventStartDate'] ) && isset( $post_meta['_EventStartDate'] ) ) {
			$start_datetime = strtotime( $post_meta['_EventStartDate'] );
			$data['EventStartDate'] = date( Tribe__Date_Utils::DBDATEFORMAT, $start_datetime );
			$data['EventStartHour'] = date( 'H', $start_datetime );
			$data['EventStartMinute'] = date( 'i', $start_datetime );
			// Date is stored in 24hr format and doesn't need meridian, which might be set already.
			unset( $data['EventStartMeridian'] );
		}

		// The end date needs to be adjusted from a MySQL style datetime string to just the date
		if ( isset( $modified['_EventEndDate'] ) && isset( $post_meta['_EventEndDate'] ) ) {
			$end_datetime = strtotime( $post_meta['_EventEndDate'] );
			$data['EventEndDate'] = date( Tribe__Date_Utils::DBDATEFORMAT, $end_datetime );
			$data['EventEndHour'] = date( 'H', $end_datetime );
			$data['EventEndMinute'] = date( 'i', $end_datetime );
			// Date is stored in 24hr format and doesn't need meridian, which might be set already.
			unset( $data['EventEndMeridian'] );
		}

		// reset any modified taxonomy terms
		$taxonomy_map = [
			'post_tag'                    => 'tags',
			Tribe__Events__Main::TAXONOMY => 'categories',
		];

		foreach ( $post_terms as $taxonomy => $terms ) {
			if ( ! isset( $modified[ $taxonomy ] ) ) {
				continue;
			}

			$tax_key = Tribe__Utils__Array::get( $taxonomy_map, $taxonomy, $taxonomy );
			$data[ $tax_key ] = $post_terms[ $taxonomy ];
		}

		return $data;
	}
}
