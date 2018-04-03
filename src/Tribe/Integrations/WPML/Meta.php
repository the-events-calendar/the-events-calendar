<?php


class Tribe__Events__Integrations__WPML__Meta {

	/**
	 * @var Tribe__Events__Integrations__WPML__Meta
	 */
	protected static $instance;

	/**
	 * @return Tribe__Events__Integrations__WPML__Meta
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Translates post id in the Event meta data.
	 *
	 * @param string  $value
	 * @param int     $object_id
	 * @param boolean $single
	 *
	 * @return string The translated id for _EventOrganizerID & _EventVenueID.
	 */
	public function translate_post_id( $value, $object_id, $meta_key, $single ) {
		global $wpdb;

		if ( '_EventOrganizerID' === $meta_key ) {
			$value = $wpdb->get_var( $wpdb->prepare(
				"SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s",
				$object_id,
				$meta_key
			) );
			$value = apply_filters( 'wpml_object_id', $value, Tribe__Events__Organizer::POSTTYPE, true );
		}

		if ( '_EventVenueID' === $meta_key ) {
			$value = $wpdb->get_var( $wpdb->prepare(
				"SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s",
				$object_id,
				$meta_key
			) );
			$value = apply_filters( 'wpml_object_id', $value, Tribe__Events__Venue::POSTTYPE, true );
		}

		return $value;
	}

}
