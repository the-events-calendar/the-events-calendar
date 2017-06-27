<?php

/**
 * Class Tribe__Events__Linked_Posts__Base
 *
 * The base for each linked post managing class.
 *
 * @since TDB
 */
abstract class Tribe__Events__Linked_Posts__Base {
	/**
	 * @var string The post type managed by the linked post class.
	 */
	protected $post_type = '';

	/**
	 * @var string The prefix that will be used for the linked post custom fields.
	 */
	protected $meta_prefix = '';

	/**
	 * Returns an array of post fields that should be used to spot possible duplicates.
	 *
	 * @return array An array of post fields to matching strategy in the format
	 *               [ <post_field> => [ 'match' => <strategy> ] ]
	 *
	 * @see   Tribe__Duplicate__Strategy_Factory for supported strategies
	 *
	 * @since TDB
	 */
	abstract protected function get_duplicate_post_fields();

	/**
	 * Returns an array of post custom fields that should be used to spot possible duplicates.
	 *
	 * @return array An array of post fields to matching strategy in the format
	 *               [ <custom_field> => [ 'match' => <strategy> ] ]
	 *
	 * @see   Tribe__Duplicate__Strategy_Factory for supported strategies
	 *
	 * @since TDB
	 */
	abstract protected function get_duplicate_custom_fields();

	/**
	 * @param string $search
	 *
	 * @return array|bool An array of post IDs or `false` on failure.
	 *
	 * @since TDB
	 */
	public function find_like( $search ) {
		$post_fields = $this->get_duplicate_post_fields();
		$post_fields = array_combine(
			array_keys( $post_fields ),
			array_fill( 0, count( $post_fields ), array( 'match' => 'like' ) )
		);

		$custom_fields = $this->get_duplicate_custom_fields();
		$custom_fields = array_combine(
			array_keys( $custom_fields ),
			array_fill( 0, count( $custom_fields ), array( 'match' => 'like' ) )
		);

		/** @var Tribe__Duplicate__Post $duplicates */
		$duplicates = tribe( 'post-duplicate' );
		$duplicates->set_post_type( $this->post_type );
		$duplicates->use_post_fields( $post_fields );
		$duplicates->use_custom_fields( $custom_fields );
		$duplicates->set_where_operator( 'OR' );

		$merged = array_merge( $post_fields, $custom_fields );

		$data = array_combine(
			array_keys( $merged ),
			array_fill( 0, count( $merged ), $search )
		);

		$found = $duplicates->find_all_for( $data );

		return $found;
	}

	/**
	 * Prefixes a key with the correct meta key prefix if needed.
	 *
	 * @param string $key
	 *
	 * @return string
	 *
	 * @since TDB
	 */
	protected function prefix_key( $key ) {
		if ( 0 !== strpos( $key, $this->meta_prefix ) && in_array( $key, Tribe__Events__Organizer::$meta_keys ) ) {
			return $this->meta_prefix . $key;
		}

		return $key;
	}
}