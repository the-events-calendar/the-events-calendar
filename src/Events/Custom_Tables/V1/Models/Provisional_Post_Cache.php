<?php
/**
 * Handles the cache storage and invalidation with Object Cache of WordPress.
 *
 * @since   6.0.0
 * @since TBD Migrated to The Events Calendar from Events Calendar Pro.
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 */

namespace TEC\Events\Custom_Tables\V1\Models;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator as Provisional_ID_Generator;
use WP_Post;

/**
 * Class Provisional_Post
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 */
class Provisional_Post_Cache {
	/**
	 * Group where all the cache data is stored.
	 *
	 * @since 6.0.0
	 */
	const CACHE_GROUP = 'tec_occurrences_cache';
	/**
	 * Name of the cache used to store all the IDs of posts that has been cached.
	 *
	 * @since 6.0.0
	 */
	const CACHED_IDS = 'tec_occurrences_cached_ids';

	/**
	 * Maps of the keys from the custom table from occurrences mapped back into the name of the legacy implementation.
	 *
	 * @since 6.0.0
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
	 * A reference to the provisional ID generator instance.
	 *
	 * @since 6.0.0
	 *
	 * @var Provisional_ID_Generator
	 */
	private $generator;

	/**
	 * Provisional_Post constructor.
	 *
	 * @param  Provisional_ID_Generator  $generator  A reference to the Provisional ID Generator
	 *                                               that should be used to discern Provisional posts.
	 */
	public function __construct( Provisional_ID_Generator $generator ) {
		$this->generator = $generator;
	}

	/**
	 * Returns the current base value.
	 *
	 * @since 6.0.0
	 * @since 6.2.1 This no longer pulls from a memoized value, it will always defer to the ID generator.
	 *
	 * @return int
	 */
	public function get_base(): int {
		return $this->generator->current();
	}

	/**
	 * If this post ID has been already cached use the value from the cache.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The Occurrence provisional post ID to check.
	 *
	 * @return bool If the Post ID is already in the cache.
	 */
	public function already_cached( int $post_id ): bool {
		return false !== wp_cache_get( $post_id, 'posts' ) && false !== wp_cache_get( $post_id, 'post_meta' );
	}

	/**
	 * Hydrates the post and meta caches for an arbitrary set of Occurrences Provisional posts.
	 *
	 * @since 6.0.0
	 *
	 * @param  array<int>  $occurrences_ids  An arbitrary set of Occurrences Provisional Post IDs to hydrate the caches
	 *                                       for.
	 */
	public function hydrate_caches( array $occurrences_ids = [] ): void {
		$cache = tribe_cache();

		[
			$occurrences,
			$to_hydrate
		] = array_reduce( $occurrences_ids, function ( array $carry, int $occurrence_id ) use ( $cache ): array {
			$cache_key = "event_occurrence_$occurrence_id";
			$cached    = $cache[ $cache_key ];
			if ( $cached instanceof Occurrence ) {
				$carry[0][] = $cached;
			} else {
				$carry[1][] = $this->generator->unprovide_id( $occurrence_id );
			}

			return $carry;
		}, [ [], [] ] );

		if ( ! empty( $to_hydrate ) ) {
			$fetched          = [];
			$chunk_size       = tec_query_batch_size( __METHOD__ );
			$to_hydrate_count = count( $to_hydrate );

			while ( $to_hydrate_count ) {
				$to_hydrate_chunk = array_splice( $to_hydrate, 0, $chunk_size );
				$to_hydrate_count = count( $to_hydrate );

				foreach ( Occurrence::where_in( 'occurrence_id', $to_hydrate_chunk )->all() as $occurrence ) {
					$fetched[]           = $occurrence;
					$cache_key           = "event_occurrence_{$occurrence->occurrence_id}";
					$cache[ $cache_key ] = $occurrence;
				}
			}

			$occurrences = array_merge( $occurrences, $fetched );
		}

		$cached_ids = $this->get_array_from_cache( self::CACHED_IDS );

		$post_ids = wp_list_pluck( $occurrences, 'post_id' );
		_prime_post_caches( array_unique( $post_ids ) );

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
	 * @since 6.0.0
	 *
	 * @param  Occurrence  $occurrence
	 */
	private function set_occurrence_cache( Occurrence $occurrence ): void {
		$provisional_id = $this->get_base() + $occurrence->occurrence_id;

		if ( $this->already_cached( $provisional_id ) ) {
			return;
		}

		$post = get_post( $occurrence->post_id );

		// If it's not a post we bail to avoid warnings.
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		$meta = get_post_meta( $occurrence->post_id );

		foreach ( $this->meta_overrides as $property => $meta_key ) {
			$meta[ $meta_key ] = [ $occurrence->{$property} ];
		}

		// Make the `_tec_occurrence` reference available in meta too to have the WP_Post `isset` method pick it up.
		$meta['_tec_occurrence_id'] = [ $occurrence->occurrence_id ];

		if ( $occurrence->has_recurrence ) {
			$post->ID        = $provisional_id;
		}

		// Add a property with the occurrence.
		$post->_tec_occurrence_id = $occurrence->occurrence_id;

		/**
		 * Allows filtering the content of the hydrated post object cache for an Occurrence.
		 * This filter will fire only the first time an Occurrence cache is primed in the context
		 * of a single request.
		 *
		 * @since 6.0.0
		 *
		 * @param WP_Post    $post           A reference to the modified WP_Post object instance that will be cached
		 *                                   for the Occurrence.
		 * @param Occurrence $occurrence     A reference to the Occurrence for which the post fields are being cached.
		 * @param int        $provisional_id The Occurrence provisional post ID.
		 */
		$post = apply_filters( 'tec_events_pro_custom_tables_v1_occurrence_cache_post', $post, $occurrence );

		/**
		 * Allows filtering the content of the hydrated meta values cache for an Occurrence.
		 * This filter will fire only the first time an Occurrence cache is primed in the context
		 * of a single request.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string,array> $meta           A map of the meta fields that will be cached for the Occurrence; note
		 *                                            the value is an array of arrays.
		 * @param int                 $provisional_id The Occurrence provisional post ID.
		 * @param Occurrence          $occurrence     A reference to the Occurrence for which the post fields are being cached.
		 */
		$meta = apply_filters( 'tec_events_pro_custom_tables_v1_occurrence_cache_meta', $meta, $provisional_id, $occurrence );

		// Clone the post object to avoid change propagation and update the post ID before caching it.
		$post_to_cache = clone $post;
		$post_to_cache->ID = $provisional_id;

		/*
		 * Store the post as `stdClass` to avoid unserialize errors in pre-fetch scenarios, when the `WP_Post` class
		 * is not yet defined.
		 */
		wp_cache_set( $provisional_id, (object) (array) $post_to_cache, 'posts' );
		wp_cache_set( $provisional_id, $meta, 'post_meta' );
		// Create an update a series of hash maps to keep track of which data was saved on the cache.
		$caches = $this->get_array_from_cache( $occurrence->post_id );
		$caches[ $provisional_id ] = $occurrence->post_id;
		wp_cache_set( $occurrence->post_id, $caches, self::CACHE_GROUP );
	}

	/**
	 * Remove all the current cached items.
	 *
	 * Using the values present on the hash with th references to the post id, then just a single deletion of the full
	 * hash map.
	 *
	 * @since 6.0.0
	 */
	public function flush_all(): void {
		$ids = $this->get_array_from_cache( self::CACHED_IDS );

		foreach ( array_keys( $ids ) as $post_id ) {
			$this->flush_occurrences_from_a_post_id( $post_id );
		}

		wp_cache_delete( self::CACHED_IDS, self::CACHE_GROUP );
	}

	/**
	 * Remove all the occurrences associated with a specific post ID.
	 *
	 * @since 6.0.0
	 *
	 * @param  int  $post_id  The ID of the post ID associated with the occurrences.
	 */
	public function flush_occurrences( $post_id ): void {
		$this->flush_post_id_from_cached_ids_list( $post_id );
		$this->flush_occurrences_from_a_post_id( $post_id );
	}

	/**
	 * Flush all the occurrences associated with a single post ID
	 *
	 * @since 6.0.0
	 *
	 * @param  int  $post_id  The ID of the post ID associated with the occurrences.
	 */
	private function flush_occurrences_from_a_post_id( $post_id ): void {
		$occurrences             = $this->get_array_from_cache( $post_id );
		$occurrences[ $post_id ] = true;
		$cache                   = tribe_cache();

		unset( $cache["event_occurrence_$post_id"] );

		foreach ( array_keys( $occurrences ) as $provisional_ID ) {
			wp_cache_delete( $provisional_ID, 'posts' );
			wp_cache_delete( $provisional_ID, 'post_meta' );
		}

		wp_cache_delete( $post_id, self::CACHE_GROUP );
	}

	/**
	 * Remove a single row from the hash with the list of IDs.
	 *
	 * @since 6.0.0
	 *
	 * @param $post_id
	 */
	private function flush_post_id_from_cached_ids_list( $post_id ): void {
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
	 * @since 6.0.0
	 *
	 * @param  mixed   $key    The key where the cache is located.
	 * @param  string  $group  The cache group where the key is located.
	 *
	 * @return array An array with the values presented on the cache.
	 */
	private function get_array_from_cache( $key, $group = self::CACHE_GROUP ): array {
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
