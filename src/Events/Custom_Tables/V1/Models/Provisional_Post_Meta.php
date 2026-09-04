<?php
/**
 * Handles the generation of a provisional post ID given an occurrence ID.
 *
 * @since 7.3.0
 * @since TBD Migrated to The Events Calendar from Events Calendar Pro.
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 */

namespace TEC\Events\Custom_Tables\V1\Models;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use WP_Post;

/**
 * Class Provisional_Post_Meta
 *
 * @since 7.3.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 */
class Provisional_Post_Meta {
	/**
	 * Hydrates the cache and parses the post meta update query.
	 *
	 * @since 7.3.0
	 *
	 * @param string $query The query string.
	 * @return string The parsed query string or original if wrong query.
	 */
	public function hydrate_provisional_postmeta_query( $query ): string {
		if ( empty( $query ) || ! is_string( $query ) ) {
			return $query;
		}
		$provisional_post = tribe( Provisional_Post::class );
		$provisional_id   = $this->parse_query_postmeta_id( $query );

		if ( false === $provisional_id || ! $provisional_post->is_provisional_post_id( $provisional_id ) ) {
			return $query;
		}

		$occurrence = $provisional_post->get_occurrence_row( $provisional_id );

		if ( ! $occurrence instanceof Occurrence ) {
			// We might be getting a request for a cached Occurrence: it will not be found.
			return $query;
		}

		return $this->occurrence_postmeta_row_sql( $occurrence->post_id );
	}

	/**
	 * Tests the query string for the provisional ID and returns it if found.
	 *
	 * @since 7.3.0
	 *
	 * @param string $query The query being tested for a provisional ID.
	 *
	 * @return false|int
	 */
	private function parse_query_postmeta_id( string $query ) {
		global $wpdb;
		$post_row_pattern = "@^SELECT meta_key, meta_value, meta_id, post_id\s+FROM {$wpdb->postmeta} WHERE post_id = (?<id>\d+)\s+ORDER BY meta_key,meta_id$@";

		if ( ! preg_match( $post_row_pattern, $query, $matches ) || empty( $matches['id'] ) ) {
			return false;
		}

		return (int) $matches['id'];
	}

	/**
	 * Returns the meta query string with the post ID applied.
	 *
	 * @since 7.3.0
	 *
	 * @param int $original_post_id The original post ID.
	 *
	 * @return string
	 */
	private function occurrence_postmeta_row_sql( int $original_post_id ): string {
		global $wpdb;

		// Prepare a query that will return a realistic postmeta row, the provisional ID replaced.
		return $wpdb->prepare(
			"SELECT meta_key, meta_value, meta_id, post_id FROM {$wpdb->postmeta} WHERE post_id = %d ORDER BY meta_key,meta_id",
			$original_post_id
		);
	}

	/**
	 * Hooks on the request to get the post metadata to hydrate the post caches.
	 *
	 * This method is specially important in the context of those calls to `get_post`
	 * for a provisional ID followed by a check of the `_tec_occurrence` property.
	 * The `WP_Post::__get` method will check the meta, thus triggering this method,
	 * and will allow the provisional post caches to be set up correctly, including the
	 * `_tec_occurrence` property.
	 *
	 * @since 6.0.0
	 * @since 7.3.0 Moved from Provisional_Post
	 *
	 * @param mixed  $meta_value The value of the meta.
	 * @param int    $object_id  The ID of the post the meta is for.
	 * @param string $meta_key   The meta key.
	 *
	 * @return mixed The value of the meta, unmodified by this code.
	 */
	public function hydrate_tec_occurrence_meta( $meta_value, int $object_id, string $meta_key ) {
		if ( $meta_key !== '_tec_occurrence' ) {
			return $meta_value;
		}

		$provisional = tribe( Provisional_Post::class );

		if ( ! $provisional->is_provisional_post_id( $object_id ) ) {
			return $meta_value;
		}

		$post = get_post( $object_id );

		if ( ! $post instanceof WP_Post ) {
			// A provisional ID not backed by a live Occurrence: a deleted one, a stale link or a hand-typed ID.
			return $meta_value;
		}

		// Maybe already hydrated? Use `get_object_vars` as `isset` will trigger the `WP_Post::__get` method.
		$occurrence_id = get_object_vars( $post )['_tec_occurrence_id'] ?? null;

		if ( empty( $occurrence_id ) ) {
			// Not already hydrated, let's do it now.
			$provisional->hydrate_caches( [ $object_id ] );

			// Avoid using a method that will either hit the database or cause another `get_post_meta` call.
			$occurrence_id = get_object_vars( $post )['_tec_occurrence_id'] ?? null;
		}

		if ( empty( $occurrence_id ) ) {
			return $meta_value;
		}

		// Attempt to fetch from memoized cache.
		$cache_key = 'event_occurrence_' . $occurrence_id;
		$cache     = tribe_cache();

		// Check if we already memoized this.
		if ( $cache[ $cache_key ] instanceof Occurrence ) {
			return $cache[ $cache_key ];
		}

		// Could not be found in memory, fetch again.
		$occurrence = Occurrence::find( $occurrence_id, 'occurrence_id' );

		if ( $occurrence instanceof Occurrence ) {
			$cache[ $cache_key ] = $occurrence;

			return $occurrence;
		}

		return $meta_value;
	}
}
