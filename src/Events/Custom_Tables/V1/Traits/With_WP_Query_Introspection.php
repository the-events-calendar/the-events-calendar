<?php
/**
 * Provides methods to gather information about a `WP_Query` instance.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits
 */

namespace TEC\Events\Custom_Tables\V1\Traits;

use Tribe__Repository as Repository;
use Tribe__Repository__Query_Filters as Query_Filters;
use WP_Query;

/**
 * Trait With_WP_Query_Introspection
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits
 */
trait With_WP_Query_Introspection {

	/**
	 * Checks whether a `WP_Query` instance is using any of the specified meta keys in
	 * the meta query or not.
	 *
	 * @since 6.0.0
	 *
	 * @param  WP_Query       $query      A reference to the `WP_Query` instance to check.
	 * @param  array<string>  $meta_keys  A list of meta keys to check: if the query
	 *                                    meta query contains at least one, then that
	 *                                    is considered a match.
	 *
	 * @return bool Whether a `WP_Query` instance is date-bound by its meta query
	 *              arguments or not.
	 */
	protected function is_query_using_meta_keys( WP_Query $query, array $meta_keys ) {
		$meta_query = $query->get( 'meta_query', [] );

		if (
			empty( $meta_query )
			|| ( isset( $meta_query['relation'] ) && count( $meta_query ) === 1 )
		) {
			return false;
		}

		if ( isset( $meta_query['relation'] ) && count( $meta_query ) === 1 ) {
			return false;
		}

		if ( isset( $meta_query['starts-after']['key'] ) && is_array( $meta_query['starts-after'] ) ) {
			return array_key_exists( $meta_query['starts-after']['key'], array_fill_keys( $meta_keys, true ) );
		}

		$escaped_date_keys = array_map( static function ( $date_key ) {
			return preg_quote( $date_key, '@' );
		}, $meta_keys );

		$date_keys_pattern = '@"key":"(' . implode( '|', $escaped_date_keys ) . '")@';
		$meta_query_json   = wp_json_encode( $meta_query );
		$is_date_bound     = preg_match( $date_keys_pattern, $meta_query_json );

		return (bool) $is_date_bound;
	}

	/**
	 * Returns whether a `WP_Query` is only querying the specified post type or not.
	 *
	 * @since 6.0.0
	 *
	 * @param  WP_Query  $query      A reference to the `WP_Query` instance to check.
	 * @param  string    $post_type  The post type to check the `WP_Query` for.
	 *
	 * @return bool Whether a `WP_Query` is only querying the Event post type or not.
	 */
	protected function is_query_for_post_type( WP_Query $query = null, $post_type = '' ) {
		return $query instanceof WP_Query && array_values( array_filter( (array) $query->get( 'post_type' ) ) ) === [ $post_type ];
	}

	/**
	 * Checks whether a `WP_Query` instance was build by the TEC Repository
	 * or not.
	 *
	 * @since 6.0.0
	 *
	 * @param  WP_Query  $query  A reference to the `WP_Query` instance to check.
	 *
	 * @return bool Whether a `WP_Query` instance was build by the TEC Repository
	 *              or not.
	 */
	protected function is_repository_query( WP_Query $query ) {
		return isset( $query->builder->filter_query )
		       && $query->builder instanceof Repository
		       && $query->builder->filter_query instanceof Query_Filters;
	}
}
