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

		$accepted_values = [ '_EventOrganizerID', '_EventVenueID' ];

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
		$keys       = array( '_eventvenueid_in', '_eventorganizerid_in' );
		$meta_query = $q->get( 'meta_query' );

		foreach ( $keys as $key ) {
			if ( ! isset( $meta_query[ $key ] ) ) {
				continue;
			}

			$vars   = $meta_query[ $key ]['value'];
			$result = [];

			foreach ( $vars as $var ) {
				$post_type = get_post_type( $var );

				/**
				 * Get wpml_element_trid (the ID of the translation group) of a translated element, which is
				 * required for the  wpml_get_element_translations filter and others which expect this argument.
				 * @see https://wpml.org/wpml-hook/wpml_element_trid/
				 *
				 * @param null|mixed $empty_value     This is usually the value the filter will be modifying.
				 * @param string|int $var             The ID of the item.
				 * @param string     $element_type    The type of an element. Can be a post type or taxonomy.
				 *
				 * @return bool|mixed|null|string The wpml_element_trid or null if it doesn't exist.
				 */
				$wpml_element_trid = apply_filters( 'wpml_element_trid', null, $var, $post_type );

				// No translation ID? Move along.
				if ( empty( $wpml_element_trid ) ) {
					continue;
				}

				/**
				 * Get the element translations info using wpml_element_trid
				 * @see https://wpml.org/wpml-hook/wpml_get_element_translations/
				 *
				 * @param null|mixed $empty_value     This is usually the value the filter will be modifying.
				 * @param string|int $var             The ID of the translation group
				 * @param string     $element_type    The type of an element. Can be a post type or taxonomy.
				 *
				 * @return array|bool|mixed The wpml_element_trid or null if it doesn't exist.
				 */
				$translations = apply_filters( 'wpml_get_element_translations', null, $wpml_element_trid, $post_type );

				// No translation? Move along.
				if ( ! is_array( $translations ) ) {
					continue;
				}

				$result = array_merge( $result, wp_list_pluck( (array) $translations, 'element_id' ) );
			}

			// Don't bail here on empty $result!

			$meta_query[ $key ]['value'] = $result;
			$q->set( 'meta_query', $meta_query );
		}
	}

}
