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
	 * @param string $value
	 * @param int    $object_id
	 * @param string $meta_key
	 *
	 * @return string The translated id for _EventOrganizerID & _EventVenueID.
	 */
	public function translate_post_id( $value, $object_id, $meta_key ) {
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

	/**
	 * Query all translations of organizer or venue to fetch events.
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
