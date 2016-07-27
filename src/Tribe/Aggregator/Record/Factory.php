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

		if ( empty( $meta[ "{$meta_prefix}origin" ] ) ) {
			return null;
		}

		$record       = $this->get_by_origin( $meta[ "{$meta_prefix}origin" ] );
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
		$args = array(
		);

		$query = new WP_Query( $args );

		if ( is_wp_error( $post ) ) {
			return null;
		}

		$meta = get_post_meta( $post_id );
		$meta_prefix = Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix;

		if ( empty( $meta[ "{$meta_prefix}origin" ] ) ) {
			return null;
		}

		$record       = $this->get_by_origin( $meta[ "{$meta_prefix}origin" ] );
		$record->id   = $post_id;
		$record->post = $post;
		$record->setup_meta( $meta );

		return $record;
	}
}
