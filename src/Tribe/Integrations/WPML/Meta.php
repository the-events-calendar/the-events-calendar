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

		if ( '_EventOrganizerID' !== $meta_key &&
			 '_EventOrganizerID_Order' !== $meta_key &&
			 '_EventVenueID' !== $meta_key ) {
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
				$array = unserialize( $post_id );
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
		if ( isset( $q->query_vars['organizer'] ) && is_int( $q->query_vars['organizer'] ) ) {
			$trid = apply_filters( 'wpml_element_trid', null, $q->query_vars['organizer'], 'post_tribe_organizer' );
			$translations = apply_filters( 'wpml_get_element_translations', null, $trid, 'post_tribe_organizer' );
			$q->query_vars['organizer'] = wp_list_pluck( $translations, 'element_id' );
		}

		if ( isset( $q->query_vars['venue'] ) && is_int( $q->query_vars['venue'] ) ) {
			$trid = apply_filters( 'wpml_element_trid', null, $q->query_vars['venue'], 'post_tribe_venue' );
			$translations = apply_filters( 'wpml_get_element_translations', null, $trid, 'post_tribe_venue' );
			$q->query_vars['venue'] = wp_list_pluck( $translations, 'element_id' );
		}
	}

}
