<?php

/**
 * Translate post ids in Event meta data.
 *
 * @since TBD
 */
class Tribe__Events__Integrations__WPML__Meta {

	/**
	 * @since TBD
	 *
	 * @var Tribe__Events__Integrations__WPML__Meta
	 */
	protected static $instance;

	/**
	 * @since TBD
	 *
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
	 * @since TBD
	 *
	 * @param string $value
	 * @param int    $object_id
	 * @param string $meta_key
	 *
	 * @return string The translated id for _EventOrganizerID & _EventVenueID.
	 */
	public function translate_post_id( $value, $object_id, $meta_key ) {
		global $wpdb;

		$accepted_values = [ '_EventOrganizerID', '_EventOrganizerID_Order', '_EventVenueID' ];

		if ( ! in_array( $meta_key, $accepted_values ) ) {
			return $value;
		}

		$value = $wpdb->get_col( $wpdb->prepare(
			"SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s",
			$object_id,
			$meta_key
		) );

		if ( empty( $value ) ) {
			return false;
		}

		$type = strpos( 'Organizer', $meta_key )
			? Tribe__Events__Organizer::POSTTYPE
			: Tribe__Events__Venue::POSTTYPE;

		foreach ( $value as & $post_id ) {
			if ( is_serialized( $post_id ) ) {
				$array = (array) unserialize( $post_id );
				foreach ( $array as & $id ) {
					$id = apply_filters( 'wpml_object_id', $id, $type, true );
				}
				$post_id = $array;
			} else {
				$post_id = apply_filters( 'wpml_object_id', $post_id, $type, true );
			}
		}

		return $value;
	}

	/**
	 * Query all translations of organizer or venue to fetch events.
	 *
	 * @since TBD
	 *
	 * @param object $q
	 */
	public function include_all_languages( $q ) {
		$keys = array( 'venue', 'organizer' );
		foreach ( $keys as $key ) {
			if ( isset( $q->query_vars[ $key ] ) && is_int( $q->query_vars[ $key ] ) ) {
				$var = $q->query_vars[ $key ];
				$trid = apply_filters( 'wpml_element_trid', null, $var, "post_tribe_{$key}" );
				$translations = apply_filters( 'wpml_get_element_translations', null, $trid, "post_tribe_{$key}" );
				$q->query_vars[ $key ] = wp_list_pluck( $translations, 'element_id' );
			}
		}
	}

}
