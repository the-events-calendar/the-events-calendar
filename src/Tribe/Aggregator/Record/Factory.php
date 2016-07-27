<?php

class Tribe__Events__Aggregator__Record__Factory {
	/**
	 * Returns an appropriate Record object for the given origin
	 *
	 * @param string $origin Import origin
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract|null
	 */
	public static function get_by_origin( $origin ) {
		$record = null;

		switch ( $origin ) {
			case 'ical':
				$record = new Tribe__Events__Aggregator__Record__iCal;
				break;
			case 'facebook':
				$record = new Tribe__Events__Aggregator__Record__Facebook;
				break;
			case 'meetup':
				$record = new Tribe__Events__Aggregator__Record__Meetup;
				break;
		}

		return $record;
	}

	/**
	 * Returns an appropriate Record object for the given post id
	 *
	 * @param int $post_id WP Post ID of record
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract|null
	 */
	public static function get_by_post_id( $post_id ) {
		$post = get_post( $post_id );

		if ( is_wp_error( $post ) ) {
			return null;
		}

		$meta = get_post_meta( $post_id );
		$meta_prefix = Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix;
		$origin = reset( $meta[ "{$meta_prefix}origin" ] );

		if ( empty( $origin ) ) {
			return new WP_Error( 'tribe-invalid-import-record', __( 'The Import Record is missing the origin meta key', 'the-events-calendar' ) );
		}

		$record       = self::get_by_origin( $origin );
		$record->id   = $post_id;
		$record->post = $post;
		$record->setup_meta( $meta );

		return $record;
	}

	/**
	 * Returns an appropriate Record object for the given import id
	 *
	 * @param int $import_id Aggregator import id
	 *
	 * @return Tribe__Events__Aggregator__Record__Abstract|null
	 */
	public static function get_by_import_id( $import_id ) {
		$meta_prefix = Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix;

		$args = array(
			'post_type' => Tribe__Events__Aggregator__Records::$post_type,
			'meta_key' => $meta_prefix . 'import_id',
			'meta_value' => $import_id,
			'post_status' => array(
				'pending',
				Tribe__Events__Aggregator__Records::$status->success,
			),
		);

		$query = new WP_Query( $args );

		if ( empty( $query->post ) ) {
			return new WP_Error( 'tribe-invalid-import-id', sprintf( __( 'Unable to find an Import Record with the import_id of %s', 'the-events-calendar' ), $import_id ) );
		}

		$post = $query->post;
		$post_id = $post->ID;

		$meta = get_post_meta( $post_id );
		$origin = reset( $meta[ "{$meta_prefix}origin" ] );

		if ( empty( $origin ) ) {
			return new WP_Error( 'tribe-invalid-import-record', __( 'The Import Record is missing the origin meta key', 'the-events-calendar' ) );
		}

		$record       = self::get_by_origin( $origin );
		$record->id   = $post_id;
		$record->post = $post;
		$record->setup_meta( $meta );

		return $record;
	}
}
