<?php
/**
 * Models a lazy collection of posts that will store the post IDs in cache during serialization and rebuild the
 * collection items from post IDs during unserialization.
 *
 * @since   5.0.0
 *
 * @package Tribe\Events\Collections
 */

namespace Tribe\Events\Collections;

use Tribe\Utils\Lazy_Collection;

/**
 * Class Lazy_Post_Collection
 *
 * @since   5.0.0
 *
 * @package Tribe\Events\Collections
 */
class Lazy_Post_Collection extends Lazy_Collection {
	/**
	 * The callback function that should be called to rebuild the collection items from an array of post IDs.
	 *
	 * @since 5.0.0
	 *
	 * @var callable|string
	 */
	protected $unserialize_callback;

	/**
	 * Lazy_Post_Collection constructor.
	 *
	 * @since 5.0.0
	 *
	 * @param callable $callback             The callback that should be used to fetch the collection items.
	 * @param string   $unserialize_callback The callback that should be used to rebuild the collection items from the
	 *                                       serialized post IDs.
	 */
	public function __construct( callable $callback, $unserialize_callback = 'get_post' ) {
		parent::__construct( $callback );
		$this->unserialize_callback = $unserialize_callback;
	}

	/**
	 * Plucks the post IDs from the collection items before serialization.
	 *
	 * While serializing a post object w/ added properties will not generate any error during serialization, doing the
	 * same during unserialization will yield a `false` result.
	 * To avoid dealing with the lower level details of how the post object is built or decorated, here we extract
	 * the post IDs to only store those.
	 *
	 * @since 5.0.0
	 *
	 * @param array<\WP_Post> $items The posts part of this collection.
	 *
	 * @return array The collection post IDs and callback.
	 *
	 * @see   Lazy_Post_Collection::custom_unserialize() for the other part of the post handling.
	 */
	protected function before_serialize( array $items ) {
		return [
			'callback' => $this->unserialize_callback,
			'ids'      => array_map( static function ( $item ): int {
				return $item instanceof \WP_Post ? $item->ID : (int) $item;
			}, $items ),
		];
	}

	/**
	 * Custom handling of the lazy collection unserialization, this method will build complete post objects from
	 * the serialized post IDs.
	 *
	 * @since 5.0.0
	 *
	 * @param string $serialized The serialized values, usually an array of post IDs.
	 *
	 * @return array<\WP_Post>|null Either the rebuilt collection, or `null` if the serialized string cannot be
	 *                             unserialized.
	 */
	protected function custom_unserialize( $serialized ) {
		$unserialized = unserialize( $serialized );

		if ( false === $unserialized || ! is_array( $unserialized ) ) {
			return null;
		}

		return array_map( $unserialized['callback'], $unserialized['ids'] );
	}
}
