<?php
/**
 * Provides methods to make an object interaction with the WordPress Filters
 * API observable.
 *
 * @since 6.0.0
 */

namespace TEC\Events\Custom_Tables\V1\Traits;

/**
 * Trait With_Observable_Filtering
 *
 * @since 6.0.0
 */
trait With_Observable_Filtering {

	/**
	 * A list of the filters the modifier did act on.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string,bool>
	 */
	protected $done_filters = [];

	/**
	 * Returns whether the object did act on a specific WordPress filter or not.
	 *
	 * @since 6.0.0
	 *
	 * @param string $tag The name of the filter, e.g. `posts_pre_query`.
	 *
	 * @return bool Whether the object did act on a specific filter or not.
	 */
	public function did_filter( $tag ){
		return isset( $this->done_filters[ $tag ] );
	}

	/**
	 * Returns the value returned by the object for a specific filter, if any.
	 *
	 * Since `null` is a legitimate value the modifier could return in a filter,
	 * use  the `did_filter` method to correctly discriminate whether the modifier did
	 * intervene in a filter at all or not.
	 *
	 * @since 6.0.0
	 *
	 * @param string The filter name, e.g. `posts_pre_query`.
	 *
	 * @return mixed Either the value returned for the filter or `null` to indicate
	 *                          the object did not inject any value in the filter.
	 */
	public function get_filtered_value( $tag ) {
		return $this->did_filter( $tag ) ? $this->done_filters[ $tag ] : null;
	}
}
