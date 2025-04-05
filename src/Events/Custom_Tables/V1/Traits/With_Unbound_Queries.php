<?php
/**
 * Provides methods to run unbound queries for posts
 * without actually running unbound queries.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits
 */

namespace TEC\Events\Custom_Tables\V1\Traits;

use WP_Post;

/**
 * Trait With_Unbound_Queries
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits
 */
trait With_Unbound_Queries {
	protected $unbound_query_batch_size = 200;

	/**
	 * Runs an unbound `get_posts` query batching the query to make sure it will not kill the database.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,mixed> $args A set of arguments that should be used to run the query. Pagination,
	 *                                  offset and posts-per-page settings will be overridden in each single
	 *                                  query request.
	 *
	 * @return array<int|WP_Post,array<int,array<int>>> The results of the unbound query, the format depending
	 *                                                  on the specified fields.
	 */
	protected function get_all_posts( array $args = [] ) {
		$batch_size     = $this->unbound_query_batch_size;
		$matching_count = null;
		$fetched_count  = 0;
		$result_sets    = [];

		do {
			$query_args = array_replace( $args, [
				'offset'         => $fetched_count,
				'posts_per_page' => $batch_size,
				'nopaging'       => false,
				'no_found_rows'  => false,
			] );

			if ( null !== $matching_count ) {
				// We already have the total, no reason to run another query.
				$query_args['no_found_rows'] = true;
			}

			$query          = new \WP_Query( $query_args );
			$query_posts    = $query->get_posts();
			$fetched_count  += (int) $query->post_count;
			$result_sets[]  = $query_posts;

			if ( null === $matching_count ) {
				$matching_count = $query->found_posts;
			}

		} while ( (int) $matching_count > $fetched_count );

		return count( $result_sets ) ? array_merge( ...$result_sets ) : [];
	}

	/**
	 * Run a potentially unbound direct query in limited size batches.
	 *
	 * Note: the method will add the `LIMIT` clause to the original query
	 * SQL, for this reason the original query SQL should not contain the
	 * `LIMIT` clause.
	 *
	 * @since 6.0.0
	 *
	 * @param string       $query  The prepared SQL query to run.
	 * @param false|string $column A column to pluck from the result set, if
	 *                             `null`, then the result set will be returned
	 *                             in `ARRAY_A` format.
	 *
	 * @todo test!
	 *
	 * @return array An array of all the available results for the query.
	 */
	private function get_all_results( $query, $column = null ) {
		$batch_size     = $this->unbound_query_batch_size;
		$matching_count = null;
		$fetched_count  = 0;
		$result_sets    = [];
		global $wpdb;

		do {
			$this_query    = $query . " LIMIT $fetched_count, $batch_size";
			$results       = (array) $wpdb->get_results( $this_query, ARRAY_A );
			$fetched_count += count( $results );
			$result_sets[] = $results;

			if ( null === $matching_count ) {
				$matching_count = $wpdb->num_rows;
			}
		} while ( (int) $matching_count > $fetched_count );

		$all_results = count( $result_sets ) ? array_merge( ...$result_sets ) : [];

		if ( ! empty( $column ) ) {
			$all_results = wp_list_pluck( $all_results, $column );
		}

		return $all_results;
	}
}
