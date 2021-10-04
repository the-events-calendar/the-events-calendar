<?php
/**
 * Handles the generation of a provisional post ID given an occurrence ID.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models
 */

namespace TEC\Custom_Tables\V1\Models;

use TEC\Custom_Tables\V1\Tables\Occurrences;
use TEC\Custom_Tables\V1\Traits\With_Core_Tables;
use TEC\Custom_Tables\V1\WP_Query\Provider as WP_Queries;
use TEC\Pro\Custom_Tables\V1\Edits\Event\Unstable_Occurrence;

/**
 * Class Provisional_Post
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models
 */
class Provisional_Post {
	use With_Core_Tables;

	private $post_cache;

	/**
	 * Reference to the WP_Queries.
	 *
	 * @since TBD
	 *
	 * @var WP_Queries queries
	 */
	private $queries;
	/**
	 * Reference to the unstable occurrence.
	 *
	 * @since TBD
	 *
	 * @var Unstable_Occurrence unstable_occurrence
	 */
	private $unstable_occurrence;

	/**
	 * Provisional_Post constructor.
	 *
	 * @param  Provisional_Post_Cache  $post_cache
	 * @param  WP_Queries              $queries
	 */
	public function __construct( Provisional_Post_Cache $post_cache, WP_Queries $queries, Unstable_Occurrence $unstable_occurrence ) {
		$this->queries    = $queries;
		$this->post_cache = $post_cache;
		$this->unstable_occurrence = $unstable_occurrence;
	}

	/**
	 * Returns the current base value.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @param  string  $query  The input query to parse.
	 *
	 * @return string The filtered query.
	 */
	public function hydrate_provisional_post( $query ) {
		if ( empty( $query ) ) {
			return $query;
		}

		$occurrence_id = $this->parse_query_post_id( $query );

		if ( false === $occurrence_id || ! $this->is_provisional_post_id( $occurrence_id ) ) {
			return $query;
		}

		$occurrence = $this->get_occurrence_row( $occurrence_id );

		if ( ! $occurrence instanceof Occurrence ) {
			// If this occurrence no longer exists, maybe was cached for a bit.
			$occurrence_query = $this->unstable_occurrence->replace_query( $occurrence_id );
			if ( $occurrence_query === null ) {
				return $query;
			}

			return $occurrence_query;
		}

		if ( ! $this->post_cache->already_cached( $occurrence_id ) ) {
			$this->hydrate_caches( [ $occurrence_id ] );
		}

		return $this->occurrence_post_row_sql( $occurrence->post_id, $occurrence_id );
	}

	/**
	 * Parses the input SQL statement to check if it's one to fetch a single post
	 * row as the one generated from the `WP_Post::get_instance` method. If the
	 * SQL matches and the requested post ID is provisional, then the required Occurrence
	 * ID is returned.
	 *
	 * @since TBD
	 *
	 * @param  string  $query  The SQL query to parse.
	 *
	 * @return false|int Either the requested Occurrence ID or `false` to indicate this is
	 *                   either not a single post row query or it's not for a Provisional Post.
	 */
	private function parse_query_post_id( $query ) {
		global $wpdb;
		// Update signature for wp_delete_post uses: SELECT * FROM $wpdb->posts WHERE ID = %d without the LIMIT.
		$post_row_pattern = "@^SELECT \\* FROM {$wpdb->posts} WHERE ID = (?<id>\d+)(?: LIMIT 1$|$)@";
		if ( ! preg_match( $post_row_pattern, $query, $matches ) || empty( $matches['id'] ) ) {
			return false;
		}

		return (int) $matches['id'];
	}

	/**
	 * Returns the full row for an Occurrence, read from the database.
	 *
	 * @since TBD
	 *
	 * @param  int  $occurrence_id  The Occurrence ID to return the row for.
	 *
	 * @return Model|null
	 *                    to indicate the Occurrence was not found in the database.
	 */
	private function get_occurrence_row( $occurrence_id ) {
		$uid_column = Occurrences::uid_column();

		// TODO: Save the value in the cache.
		return Occurrence::find( $this->normalize_provisional_post_id( $occurrence_id ), $uid_column );
	}

	/**
	 * Hydrates the post and meta caches for an arbitrary set of Occurrences Provisional posts.
	 *
	 * @since TBD
	 *
	 * @param  array<int>  $ids  An arbitrary set of Occurrences Provisional Post IDs to hydrate the caches
	 *                           for.
	 *
	 * @return bool|null
	 */
	public function hydrate_caches( array $ids = [] ) {
		$occurrences_ids = array_map(
			[ $this, 'normalize_provisional_post_id' ],
			array_filter( $ids, [ $this, 'is_provisional_post_id' ] )
		);

		if ( empty( $occurrences_ids ) ) {
			return null;
		}

		$this->queries->unregister();
		$this->post_cache->hydrate_caches( $occurrences_ids );
		$this->queries->register();

		return true;
	}

	/**
	 * If the provided ID is a provisional ID it has to be higher than the base.
	 *
	 * @since TBD
	 *
	 * @param $post_id
	 *
	 * @return bool
	 */
	public function is_provisional_post_id( $post_id ) {
		return is_numeric( $post_id ) && $post_id > $this->post_cache->get_base();
	}

	/**
	 * Normalize the value of a Post ID removing the base out of the ID.
	 *
	 * @since TBD
	 *
	 * @param  int  $post_id  The post ID to normalize.
	 *
	 * @return int The normalizes Provisional post ID.
	 */
	public function normalize_provisional_post_id( $post_id ) {
		if ( $post_id < $this->post_cache->get_base() ) {
			return $post_id;
		}

		return $post_id - $this->post_cache->get_base();
	}

	/**
	 * Modifies the input query to fetch the Provisional Post to redirect all of
	 * it, save for the `ID`, to the original Post row and still produce valid
	 * SQL.
	 *
	 * @since TBD
	 *
	 * @param  int  $original_post_id  The original post ID; this is the ID of the Post
	 *                                 that "owns" the Occurrence.
	 * @param  int  $occurrence_id     The Occurrence ID in the Occurrences table.
	 *
	 * @return string The complete SQL statement that will produce a realistic result
	 *                for the Occurrence post.
	 */
	private function occurrence_post_row_sql( $original_post_id, $occurrence_id ) {
		global $wpdb;

		/*
		 * We need to fetch all the `posts` table columns minus the `ID` one.
		 * MySQL does not support this in the `SELECT` clause, so we build a list of fields
		 * we require.
		 * The ID we'll replace with the Occurrence ID.
		 */
		$posts_columns_excl_id = array_diff( $this->get_posts_table_columns(), [ 'ID' ] );
		$posts_table           = $wpdb->posts;
		$other_post_fields     = implode( ', ', array_map( static function ( $post_field ) use ( $posts_table ) {
			return $posts_table . '.' . $post_field;
		}, $posts_columns_excl_id ) );

		// Prepare a query that will return a realistic post row, the ID replaced by the Occurrence ID.
		$original_post_row_query = $wpdb->prepare(
			"SELECT {$occurrence_id} as ID, {$other_post_fields} FROM {$wpdb->posts} WHERE ID = %d LIMIT 1",
			$original_post_id
		);

		return $original_post_row_query;
	}
}
