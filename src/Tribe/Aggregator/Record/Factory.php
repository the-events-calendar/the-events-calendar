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
			case 'facebook':
				$record = new Tribe__Events__Aggregator__Record__Facebook;
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

		$record = null;

		switch ( $meta[ "{$meta_prefix}origin" ] ) {
			case 'facebook':
				$record = new Tribe__Events__Aggregator__Record__Facebook;
				$record->id = $post_id;
				$record->post = $post;
				$record->setup_meta( $meta );
				break;
		}

		return $record;
	}
}
