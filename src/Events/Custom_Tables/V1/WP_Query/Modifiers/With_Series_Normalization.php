<?php
/**
 * Provides methods to normalize Series names or IDs to a uniform set in the context of Queries.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query\Modifiers;

use TEC\Events\Custom_Tables\V1\Traits\With_Unbound_Queries;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use WP_Query;

/**
 * Trait With_Series_Normalization
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */
trait With_Series_Normalization {
	use With_Unbound_Queries;

	/**
	 * A map from query hashes to the normalized set of Series IDs.
	 *
	 * @since TBD
	 *
	 * @var array<string,array<int>>
	 */
	private $normalized_series_ids = [];

	/**
	 * Normalizes an input set of Series post IDs and names to a set of
	 * series post IDs.
	 *
	 * @since TBD
	 *
	 * @param WP_Query $query A reference to the Query object that is being filtered
	 *                        and for whose the normalization is being done.
	 *
	 * @return array<int> A normalized set of Series post IDs.
	 */
	private function normalize_query_series_ids( WP_Query $query ) {
		if ( isset( $this->normalized_series_ids[ $query->query_vars_hash ] ) ) {
			return $this->normalized_series_ids[ $query->query_vars_hash ];
		}

		$series_input = (array) $query->get( 'related_series', [] );

		$matching_names_ids = $this->get_all_posts( [
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'post_type'              => Series::POSTTYPE,
			'post_name__in'          => $series_input,
		] );

		$normalized_ids = array_values( array_filter(
			array_map(
				'absint',
				array_merge( $series_input, $matching_names_ids )
			)
		) );

		$this->normalized_series_ids[ $query->query_vars_hash ] = $normalized_ids;

		return $normalized_ids;
	}
}
