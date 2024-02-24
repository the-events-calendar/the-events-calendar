<?php

use Tribe__Events__Main as Main;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;

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
	public function hook() {
	}

	/**
	 * Translates post id in the Event meta data.
	 *
	 * @since 4.6.21
	 *
	 * @param string $value
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param bool   $single
	 *
	 * @return mixed The translated id for _EventOrganizerID & _EventVenueID or false.
	 */
	public function translate_post_id( $value, $object_id, $meta_key, $single ) {
		if ( ! empty( $_POST ) ) {
			return $value;
		}

		if ( tribe()->getVar( 'ct1_fully_activated' ) ) {
			$object_id = Occurrence::normalize_id( $object_id );
		}

		$accepted_values = [ '_EventOrganizerID', '_EventVenueID' ];

		if ( ! in_array( $meta_key, $accepted_values ) ) {
			return $value;
		}

		$cache = tribe_cache();

		$cache_key = 'wpml_meta_translate_post_id_' . $object_id . '-' . $meta_key;

		if ( isset( $cache[ $cache_key ] ) ) {
			$cached = $cache[ $cache_key ];

			// The cached value must be an array.
			if ( is_array( $cached ) ) {
				return $single ? reset( $cached ) : $cached;
			}
		}

		$original_value = $value;
		$value          = $this->get_post_meta( $object_id, $meta_key );

		if ( empty( $value ) ) {
			/*
			 * Return the original value: if this method is filtering a check, the exact value, not just an empty vaulue,
			 * matters. If the original value is `null` and this method returns an empty string or empty array, the
			 * returned value will make the `get_metadata_raw` function bail out and return the incorrect value.
			 */
			return $original_value;
		}

		$type = false !== strpos( $meta_key, 'Organizer' )
			? Tribe__Events__Organizer::POSTTYPE
			: Tribe__Events__Venue::POSTTYPE;

		foreach ( $value as & $post_id ) {
			if ( is_serialized( $post_id ) ) {
				$ids = (array) unserialize( $post_id );
				foreach ( $ids as & $id ) {
					/**
					 * Returns an element’s ID in the current language or in another specified language.
					 *
					 * @param int    $id     The ID of the post type or taxonomy term to filter
					 * @param string $type   The type of element the ID belongs to.
					 * @param bool   $return true   If set to true it will always return a value (the original value, if translation is missing)
					 */
					$id = (string) apply_filters( 'wpml_object_id', $id, $type, true );
				}
				$post_id = $ids;
			} else {
				/**
				 * Returns an element’s ID in the current language or in another specified language.
				 *
				 * @param int    $post_id The ID of the post type or taxonomy term to filter
				 * @param string $type    The type of element the ID belongs to.
				 * @param bool    true       If set to true it will always return a value (the original value, if translation is missing)
				 */
				$post_id = (string) apply_filters( 'wpml_object_id', $post_id, $type, true );
			}
		}

		$cache[ $cache_key ] = $value;

		return $single ? $value[0] : $value;
	}

	/**
	 * Get meta value skipping filters (using direct DB query).
	 *
	 * @since 4.6.21
	 *
	 * @param int    $post_id
	 * @param string $meta_key
	 *
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
	 * @param WP_Query $query A reference to the WordPress Query object that is being filtered.
	 */
	public function include_all_languages( $query ) {
		$meta_query = (array) $query->get( 'meta_query', [] );

		if ( empty( $meta_query ) ) {
			return;
		}

		// Pre-fill the key to post type map to avoid calling expensive functions for each element.
		$keys = [
			'_EventVenueID'     => Main::VENUE_POST_TYPE,
			'_EventOrganizerID' => Main::ORGANIZER_POST_TYPE,
		];

		foreach ( $keys as $meta_key => $post_type ) {
			foreach ( $this->find_meta_indexes( $meta_query, $meta_key ) as $meta_index ) {
				if ( ! isset( $meta_query[ $meta_index ]['value'] ) ) {
					continue;
				}

				$translated_elements = $this->translate_elements( $post_type, $meta_query[ $meta_index ]['value'] );

				if ( count( $translated_elements ) ) {
					$meta_query[ $meta_index ]['value'] = $translated_elements;
				}
			}

			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Translates, fetching all the available translations, one element using WPML.
	 *
	 * @since 5.8.2
	 *
	 * @param string $element_type The post element type as WPML expects it: `post_<post_type>`.
	 * @param int    $element_id   The ID of the linked post (element) to translate.
	 *
	 * @return array<int> A list of the available translation IDs for the specified linked post.
	 */
	private function translate_element( $element_type, $element_id ) {
		/**
		 * Get trid (the ID of the translation group) of a translated element, which is
		 * required for the  wpml_get_element_translations filter and others which expect this argument.
		 * https://wpml.org/wpml-hook/wpml_element_trid/
		 *
		 * @param mixed  $empty_value  This is usually the value the filter will be modifying.
		 * @param int    $element_id   The ID of the item.
		 * @param string $element_type The type of an element.
		 */
		$trid = apply_filters( 'wpml_element_trid', null, $element_id, $element_type );

		/**
		 * Get the element translations info using trid
		 * https://wpml.org/wpml-hook/wpml_get_element_translations/
		 *
		 * @param mixed  $empty_value  This is usually the value the filter will be modifying.
		 * @param int    $element_id   The ID of the translation group
		 * @param string $element_type The type of an element.
		 */
		$translations = apply_filters( 'wpml_get_element_translations', null, $trid, $element_type );

		return wp_list_pluck( $translations, 'element_id' );
	}

	/**
	 * Finds all the meta query keys matching a criteria for linked a custom post type.
	 *
	 * @since 5.8.2
	 *
	 * @param array<string,mixed> $meta_query The meta query array representation, in the format used by WordPress.
	 * @param string              $target_key The linked post type meta key used to store, on the Event side, the
	 *                                        relation between Events and the linked post type.
	 *
	 * @return array<string> A list of matching meta query array keys.
	 */
	private function find_meta_indexes( array $meta_query, $target_key ) {
		$found_keys = [];

		foreach ( $meta_query as $entry_key => $entry ) {
			if ( isset( $entry['key'] ) && $entry['key'] === $target_key ) {
				$found_keys[] = $entry_key;
			}
		}

		return $found_keys;
	}

	/**
	 * Translates a set of elements IDs using WPMl.
	 *
	 * @since 5.8.2
	 *
	 * @param string     $post_type The post type of the elements to translate.
	 * @param array<int> $elements  A list of IDs of elements to translate.
	 *
	 * @return array<int> A list of translated elements, if any translation is available.
	 */
	private function translate_elements( $post_type, $elements ) {
		$buffer       = [];
		$element_type = 'post_' . $post_type;

		foreach ( $elements as $element_id ) {
			$buffer[] = $this->translate_element( $element_type, $element_id );
		}

		return array_unique( array_filter( array_merge( ...$buffer ) ) );
	}

	/**
	 * Filters the meta keys tracked by the Custom Tables v1 implementation to detect a request
	 * to update an event to add the meta key used by WPML to indicate a post is a duplicate
	 * of another.
	 *
	 * @since 6.0.9
	 *
	 * @param array<string> $meta_keys The list of meta keys tracked by the Custom Tables v1 implementation.
	 *
	 * @return array<string> The list of meta keys tracked by the Custom Tables v1 implementation, including
	 *                       the one used by WPML to indicate a post is a duplicate of another.
	 */
	public static function filter_ct1_update_meta_keys( $meta_keys ) {
		if ( ! is_array( $meta_keys ) ) {
			return $meta_keys;
		}

		$meta_keys[] = '_icl_lang_duplicate_of';

		return $meta_keys;
	}
}
