<?php
/**
 * Translate post ids in Event meta data.
 *
 * @since 4.6.21
 */
class Tribe__Events__Integrations__WPML__Meta {

	/**
	 * @since 4.6.21
	 *
	 */
	public function hook() {}

	/**
	 * Translates post id in the Event meta data.
	 *
	 * @since 4.6.21
	 *
	 * @param string $value
	 * @param int    $object_id
	 * @param string $meta_key
	 *
	 * @return mixed The translated id for _EventOrganizerID & _EventVenueID or false.
	 */
	public function translate_post_id( $value, $object_id, $meta_key ) {

		if ( isset( $_POST ) && ! empty( $_POST ) ) {
			return $value;
		}

		$accepted_values = array( '_EventOrganizerID', '_EventOrganizerID_Order', '_EventVenueID' );

		if ( ! in_array( $meta_key, $accepted_values ) ) {
			return $value;
		}

		$value = $this->get_post_meta( $object_id, $meta_key );

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
					/**
					 * Returns an element’s ID in the current language or in another specified language.
					 * @param int     $id    The ID of the post type or taxonomy term to filter
					 * @param string  $type  The type of element the ID belongs to.
					 * @param bool    true   If set to true it will always return a value (the original value, if translation is missing)
					 */
					$id = apply_filters( 'wpml_object_id', $id, $type, true );
				}
				$post_id = $array;
			} else {
				/**
				 * Returns an element’s ID in the current language or in another specified language.
				 * @param int     $post_id   The ID of the post type or taxonomy term to filter
				 * @param string  $type      The type of element the ID belongs to.
				 * @param bool    true       If set to true it will always return a value (the original value, if translation is missing)
				 */
				$post_id = apply_filters( 'wpml_object_id', $post_id, $type, true );
			}
		}

		return $value;
	}

	/**
	 * Get meta value skipping filters (using direct DB query).
	 *
	 * @since 4.6.21
	 *
	 * @param int $post_id
	 * @param string $meta_key
	 * @return array
	 */
	private function get_post_meta( $post_id, $meta_key ) {
		global $wpdb;

		return $wpdb->get_col( $wpdb->prepare(
			"SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s",
			$post_id,
			$meta_key
		) );
	}

	/**
	 * Query all translations of organizer or venue to fetch events.
	 *
	 * @since 4.6.21
	 *
	 * @param object $q
	 */
	public function include_all_languages( $q ) {
		$keys = array( 'venue', 'organizer' );
		foreach ( $keys as $key ) {
			if ( ! isset( $q->query_vars[ $key ] ) ) {
				continue;
			}

			if ( ! is_int( $q->query_vars[ $key ] ) ) {
				continue;
			}

			$var = $q->query_vars[ $key ];
			/**
			 * Get trid (the ID of the translation group) of a translated element, which is
			 * required for the  wpml_get_element_translations filter and others which expect this argument.
			 * https://wpml.org/wpml-hook/wpml_element_trid/
			 *
			 * @param mixed   $empty_value     This is usually the value the filter will be modifying.
			 * @param int     $var             The ID of the item.
			 * @param string  $element_type    The type of an element.
			 */
			$trid = apply_filters( 'wpml_element_trid', null, $var, "post_tribe_{$key}" );
			/**
			 * Get the element translations info using trid
			 * https://wpml.org/wpml-hook/wpml_get_element_translations/
			 *
			 * @param mixed   $empty_value     This is usually the value the filter will be modifying.
			 * @param int     $var             The ID of the translation group
			 * @param string  $element_type    The type of an element.
			 */
			$translations = apply_filters( 'wpml_get_element_translations', null, $trid, "post_tribe_{$key}" );
			$q->query_vars[ $key ] = wp_list_pluck( $translations, 'element_id' );
		}
	}

}