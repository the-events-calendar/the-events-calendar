<?php
/**
 * Handles the cache storage and invalidation with Object Cache of WordPress.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models
 */

namespace TEC\Custom_Tables\V1\Models;

use TEC\Custom_Tables\V1\Events\Provisional\ID_Generator as Provisional_ID_Generator;
use WP_Post;

/**
 * Class Provisional_Post
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models
 */
class Provisional_Post_Cache {
	/**
	 * Group where all the cache data is stored.
	 *
	 * @since TBD
	 */
	const CACHE_GROUP = 'tec_occurrences_cache';
	/**
	 * Name of the cache used to store all the IDs of posts that has been cached.
	 *
	 * @since TBD
	 */
	const CACHED_IDS = 'tec_occurrences_cached_ids';

	/**
	 * Maps of the keys from the custom table from occurrences mapped back into the name of the legacy implementation.
	 *
	 * @since TBD
	 *
	 * @var string[] meta_overrides
	 */
	private $meta_overrides = [
		'start_date'     => '_EventStartDate',
		'start_date_utc' => '_EventStartDateUTC',
		'end_date'       => '_EventEndDate',
		'end_date_utc'   => '_EventEndDateUTC',
	];

	/**
	 * The base, a positive integer, of the Provisional Post IDs.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	private $base;

	/**
	 * Provisional_Post constructor.
	 *
	 * @param  Provisional_ID_Generator  $generator  A reference to the Provisional ID Generator
	 *                                               that should be used to discern Provisional posts.
	 */
	public function __construct( Provisional_ID_Generator $generator ) {
		$this->base = $generator->current();
	}

	/**
	 * Returns the current base value.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function get_base() {
		return $this->base;
	}

	/**
	 * If this post ID has been already cached use the value from the cache.
	 *
	 * @since TBD
	 *
	 * @param $post_id
	 *
	 * @return bool If the Post ID is already in the cache.
	 */
	public function already_cached( $post_id ) {
		return false !== wp_cache_get( $post_id, 'posts' ) && false !== wp_cache_get( $post_id, 'post_meta' );
	}

	/**
	 * Hydrates the post and meta caches for an arbitrary set of Occurrences Provisional posts.
	 *
	 * @since TBD
	 *
	 * @param  array<int>  $occurrences_ids  An arbitrary set of Occurrences Provisional Post IDs to hydrate the caches
	 *                                       for.
	 */
	public function hydrate_caches( array $occurrences_ids = [] ) {
		$occurrences = Occurrence::where_in( 'occurrence_id', $occurrences_ids )->get();

		$cached_ids = $this->get_array_from_cache( self::CACHED_IDS );

		/** @var Occurrence $occurrence */
		foreach ( $occurrences as $occurrence ) {
			$this->set_occurrence_cache( $occurrence );
			// Keep track of the different posts ids modified.
			$cached_ids[ $occurrence->post_id ] = true;
		}

		wp_cache_set( self::CACHED_IDS, $cached_ids, self::CACHE_GROUP );
	}

	/**
	 * Save in cache the fields of a single occurrence.
	 *
	 * @since TBD
	 *
	 * @param  Occurrence  $occurrence
	 */
	private function set_occurrence_cache( Occurrence $occurrence ) {
		$provisional_ID = $this->base + $occurrence->occurrence_id;

		if ( $this->already_cached( $provisional_ID ) ) {
			return;
		}

		$post = get_post( $occurrence->post_id );
		$meta = get_post_meta( $occurrence->post_id );

		foreach ( $this->meta_overrides as $property => $meta_key ) {
			$meta[ $meta_key ] = [ $occurrence->{$property} ];
		}

		if ( $occurrence->has_recurrence ) {
			$post->ID        = $provisional_ID;
		}

		// Add a property with the occurrence.
		$post->_tec_occurrence = $occurrence;

		wp_cache_set( $provisional_ID, $post, 'posts' );
		wp_cache_set( $provisional_ID, $meta, 'post_meta' );
		// Create an update a series of hash maps to keep track of which data was saved on the cache.
		$caches                    = $this->get_array_from_cache( $occurrence->post_id );
		$caches[ $provisional_ID ] = $occurrence->post_id;
		wp_cache_set( $occurrence->post_id, $caches, self::CACHE_GROUP );
	}

	/**
	 * Remove all the current cached items.
	 *
	 * Using the values present on the hash with th references to the post id, then just a single deletion of the full
	 * hash map.
	 *
	 * @since TBD
	 */
	public function flush_all() {
		$ids = $this->get_array_from_cache( self::CACHED_IDS );

		foreach ( array_keys( $ids ) as $post_id ) {
			$this->flush_occurrences_from_a_post_id( $post_id );
		}

		wp_cache_delete( self::CACHED_IDS, self::CACHE_GROUP );
	}

	/**
	 * Remove all the occurrences associated with a specific post ID.
	 *
	 * @since TBD
	 *
	 * @param  int  $post_id  The ID of the post ID associated with the occurrences.
	 */
	public function flush_occurrences( $post_id ) {
		$this->flush_post_id_from_cached_ids_list( $post_id );
		$this->flush_occurrences_from_a_post_id( $post_id );
	}

	/**
	 * Flush all the occurrences associated with a single post ID
	 *
	 * @since TBD
	 *
	 * @param  int  $post_id  The ID of the post ID associated with the occurrences.
	 */
	private function flush_occurrences_from_a_post_id( $post_id ) {
		$occurrences             = $this->get_array_from_cache( $post_id );
		$occurrences[ $post_id ] = true;

		foreach ( array_keys( $occurrences ) as $provisional_ID ) {
			wp_cache_delete( $provisional_ID, 'posts' );
			wp_cache_delete( $provisional_ID, 'post_meta' );
		}

		wp_cache_delete( $post_id, self::CACHE_GROUP );
	}

	/**
	 * Remove a single row from the hash with the list of IDs.
	 *
	 * @since TBD
	 *
	 * @param $post_id
	 */
	private function flush_post_id_from_cached_ids_list( $post_id ) {
		$ids = $this->get_array_from_cache( self::CACHED_IDS );

		unset( $ids[ $post_id ] );

		$post = get_post( $post_id );

		// In case we are dealing with a provisional ID, we need to clear the main post ID as well.
		if ( $post instanceof WP_Post && $post->ID !== $post_id ) {
			unset( $ids[ $post->ID ] );
		}

		wp_cache_set( self::CACHED_IDS, $ids, self::CACHE_GROUP );
	}

	/**
	 * Get an array from the cache, making sure when we check on the cache the result is always an array.
	 *
	 * @since TBD
	 *
	 * @param  mixed   $key    The key where the cache is located.
	 * @param  string  $group  The cache group where the key is located.
	 *
	 * @return array An array with the values presented on the cache.
	 */
	private function get_array_from_cache( $key, $group = self::CACHE_GROUP ) {
		$values = wp_cache_get( $key, $group );

		// This means the data has not been set yet.
		if ( false === $values ) {
			return [];
		}

		// If the values turns out not to be an array this means the data has been corrupted, delete this cache entry and start all over again.
		if ( ! is_array( $values ) ) {
			wp_cache_delete( $key, $group );

			return [];
		}

		return $values;
	}
}
