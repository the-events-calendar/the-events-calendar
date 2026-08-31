<?php
/**
 * Handles the generation of a provisional post ID given an occurrence ID.
 *
 * @since 6.0.0
 * @since TBD Migrated to The Events Calendar from Events Calendar Pro.
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 */

namespace TEC\Events\Custom_Tables\V1\Models;

use TEC\Events\Custom_Tables\V1\Models\Model;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\Traits\With_Core_Tables;
use TEC\Events\Custom_Tables\V1\WP_Query\Provisional\Provider as WP_Queries;
use Tribe__Cache as Cache;

/**
 * Class Provisional_Post
 *
 * @since 6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 */
class Provisional_Post {
	use With_Core_Tables;

	/**
	 * A reference to the Provisional post cache handler.
	 *
	 * @since 6.0.0
	 *
	 * @var Provisional_Post_Cache
	 */
	private $post_cache;

	/**
	 * Reference to the WP_Queries.
	 *
	 * @since 6.0.0
	 *
	 * @var WP_Queries queries
	 */
	private $queries;

	/**
	 * Reference to the Cache instance.
	 *
	 * @since 6.0.0
	 *
	 * @var Cache
	 */
	private $cache;

	/**
	 * Provisional_Post constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Provisional_Post_Cache $post_cache A reference to the Provisional post cache handler.
	 * @param WP_Queries             $queries    A reference to the WP_Query handler.
	 * @param Cache                  $cache      A cache instance.
	 */
	public function __construct( Provisional_Post_Cache $post_cache, WP_Queries $queries, Cache $cache ) {
		$this->queries    = $queries;
		$this->post_cache = $post_cache;
		$this->cache      = $cache;
	}

	/**
	 * Returns the current base value.
	 *
	 * @since 6.0.0
	 *
	 * @return Provisional_Post_Cache
	 */
	public function get_post_cache() {
		return $this->post_cache;
	}

	/**
	 * Parse the query SQL and hydrate a provisional post information and accessory
	 * data if required.
	 *
	 * @since 6.0.0
	 *
	 * @param string $query The input query to parse.
	 *
	 * @return string The filtered query.
	 */
	public function hydrate_provisional_post_query( $query ): string {
		if ( empty( $query ) || ! is_string( $query ) ) {
			return $query;
		}

		$provisional_id = $this->parse_query_post_id( $query );

		if ( false === $provisional_id || ! $this->is_provisional_post_id( $provisional_id ) ) {
			return $query;
		}

		$occurrence = $this->get_occurrence_row( $provisional_id );

		if ( ! $occurrence instanceof Occurrence ) {
			// We might be getting a request for a cached Occurrence: it will not be found.
			return $query;
		}

		if ( ! $this->post_cache->already_cached( $provisional_id ) ) {
			$this->hydrate_caches( [ $provisional_id ] );
		}

		return $this->occurrence_post_row_sql( $occurrence->post_id, $provisional_id );
	}

	/**
	 * Parses the input SQL statement to check if it's one to fetch a single post
	 * row as the one generated from the `WP_Post::get_instance` method. If the
	 * SQL matches and the requested post ID is provisional, then the required Occurrence
	 * ID is returned.
	 *
	 * @since 6.0.0
	 *
	 * @param string $query The SQL query to parse.
	 *
	 * @return false|int Either the requested Occurrence ID or `false` to indicate this is
	 *                   either not a single post row query or it's not for a Provisional Post.
	 */
	private function parse_query_post_id( string $query ) {
		global $wpdb;
		// Matches both WP_Post::get_instance() queries (with LIMIT 1) and wp_delete_post queries (without LIMIT).
		$post_row_pattern = "@^SELECT \\* FROM {$wpdb->posts} WHERE ID = (?<id>\d+)(?: LIMIT 1)?(?:\s|/\*.*?\*/)*$@";
		if ( ! preg_match( $post_row_pattern, $query, $matches ) || empty( $matches['id'] ) ) {
			return false;
		}

		return (int) $matches['id'];
	}

	/**
	 * This clears the occurrence cache stored for this occurrence ID.
	 *
	 * @since 6.0.12
	 *
	 * @param numeric $occurrence_id The occurrence ID to clear occurrence cache for.
	 */
	public function clear_occurrence_cache( $occurrence_id ) {
		$cache_key = 'occurrence_row_' . $occurrence_id;
		unset( $this->cache[ $cache_key ] );
	}

	/**
	 * Returns the full row for an Occurrence, read from the database.
	 *
	 * @since 6.0.0
	 * @since 7.3.0 The scope was switched private to public.
	 *
	 * @param int  $occurrence_id The Occurrence ID to return the row for.
	 * @param bool $refresh       Whether to refresh the cache or not.
	 *
	 * @return Model|null Either the Occurrence row or `null` if not found.
	 */
	public function get_occurrence_row( int $occurrence_id, bool $refresh = false ): ?Occurrence {
		$uid_column               = Occurrences::uid_column();
		$normalized_occurrence_id = $this->normalize_provisional_post_id( $occurrence_id );

		$cache_key = 'occurrence_row_' . $normalized_occurrence_id;
		$cached    = $this->cache[ $cache_key ];

		if (
			$refresh // Explicit refresh.
			|| ! ( $cached instanceof Occurrence || $cached === null ) // Compromised cache.
		) {
			$fetched = Occurrence::find( $normalized_occurrence_id, $uid_column );
			// Do not store an invalid value.
			$this->cache[ $cache_key ] = $fetched instanceof Occurrence ? $fetched : null;
		}

		return ( $this->cache[ $cache_key ] instanceof Occurrence )
			? $this->cache[ $cache_key ]
			: null;
	}

	/**
	 * Hydrates the post and meta caches for an arbitrary set of Occurrences Provisional posts.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int> $ids  An arbitrary set of Occurrences Provisional Post IDs to hydrate the caches
	 *                          for.
	 *
	 * @return bool|null
	 */
	public function hydrate_caches( array $ids = [] ): ?bool {
		$occurrences_ids = array_map(
			[ $this, 'normalize_provisional_post_id' ],
			array_filter( $ids, [ $this, 'is_provisional_post_id' ] )
		);

		if ( empty( $occurrences_ids ) ) {
			return null;
		}

		$this->queries->noop( true );
		$this->post_cache->hydrate_caches( $occurrences_ids );
		$this->queries->noop( false );

		return true;
	}

	/**
	 * If the provided ID is a provisional ID it has to be higher than the base.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The post ID to check.
	 *
	 * @return bool
	 */
	public function is_provisional_post_id( $post_id ): bool {
		return is_numeric( $post_id ) && $post_id > $this->post_cache->get_base();
	}

	/**
	 * Normalize the value of a Post ID removing the base out of the ID.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id  The post ID to normalize.
	 *
	 * @return int The normalized Provisional post ID.
	 */
	public function normalize_provisional_post_id( int $post_id ): int {
		$cache_key = 'occurrence_provisional_post_id_' . $post_id;

		if ( isset( $this->cache[ $cache_key ] ) ) {
			return $this->cache[ $cache_key ];
		}

		if ( $post_id < $this->post_cache->get_base() ) {
			$normal = $post_id;
		} else {
			$normal = $post_id - $this->post_cache->get_base();
		}

		$this->cache[ $cache_key ] = $normal;

		return $normal;
	}

	/**
	 * Modifies the input query to fetch the Provisional Post to redirect all of
	 * it, save for the `ID`, to the original Post row and still produce valid
	 * SQL.
	 *
	 * @since 6.0.0
	 *
	 * @param int $original_post_id    The original post ID; this is the ID of the Post
	 *                                 that "owns" the Occurrence.
	 * @param int $provisional_id      The Occurrence ID in the Occurrences table.
	 *
	 * @return string The complete SQL statement that will produce a realistic result
	 *                for the Occurrence post.
	 */
	private function occurrence_post_row_sql( int $original_post_id, int $provisional_id ): string {
		global $wpdb;

		/*
		 * We need to fetch all the `posts` table columns minus the `ID` one.
		 * MySQL does not support this in the `SELECT` clause, so we build a list of fields
		 * we require.
		 * The ID we'll replace with the Occurrence ID.
		 */
		$posts_columns_excl_id = array_diff( $this->get_posts_table_columns(), [ 'ID' ] );
		$posts_table           = $wpdb->posts;
		$other_post_fields     = implode(
			', ',
			array_map(
				static function ( $post_field ) use ( $posts_table ) {
					return $posts_table . '.' . $post_field;
				},
				$posts_columns_excl_id 
			) 
		);

		// Prepare a query that will return a realistic post row, the ID replaced by the Occurrence ID.
		return $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Field list and table name cannot be placeholders.
			"SELECT %d as ID, {$other_post_fields} FROM {$wpdb->posts} WHERE ID = %d LIMIT 1",
			$provisional_id,
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

		if ( ! $this->is_provisional_post_id( $object_id ) ) {
			return $meta_value;
		}

		$post = get_post( $object_id );
		// Maybe already hydrated? Use `get_object_vars` as `isset` will trigger the `WP_Post::__get` method.
		$occurrence_id = get_object_vars( $post )['_tec_occurrence_id'] ?? null;

		if ( empty( $occurrence_id ) ) {
			// Not already hydrated, let's do it now.
			$this->post_cache->hydrate_caches( [ $object_id ] );

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
		$fetched    = $occurrence;

		if ( $fetched instanceof Occurrence ) {
			$cache[ $cache_key ] = $occurrence;

			return $fetched;
		}

		return $meta_value;
	}

	/**
	 * Returns the Event post ID for an Occurrence `occurrence_id` or `provisional_id`.
	 *
	 * @since 6.0.1
	 *
	 * @param int $occurrence_id The Occurrence `occurrence_id` or `provisional_id` to get the Event ID for.
	 *
	 * @return int The Event ID, or the input value if not found.
	 */
	public function get_occurrence_post_id( int $occurrence_id ): int {
		$occurrence_row = $this->get_occurrence_row( $occurrence_id );

		if ( $occurrence_row instanceof Occurrence ) {
			return $occurrence_row->post_id;
		}

		return $occurrence_id;
	}
}
