<?php
/**
 * The API implemented by objects that modify a WP Query.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query\Modifiers;

use WP_Query;

/**
 * Interface WP_Query_Modifier
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */
interface WP_Query_Modifier {
	/**
	 * Sets the `WP_Query` the Modifier instance is attached to.
	 *
	 * @since 6.0.0
	 *
	 * @param  WP_Query  $query  A reference to the `WP_Query` instance the modifier is attached to.
	 */
	public function set_query( WP_Query $query );

	/**
	 * Returns whether the object did act on a specific WordPress filter or not.
	 *
	 * @since 6.0.0
	 *
	 * @param  string  $tag  The name of the filter, e.g. `posts_pre_query`.
	 *
	 * @return bool Whether the object did act on a specific filter or not.
	 */
	public function did_filter( $tag );

	/**
	 * Returns the value returned by the object for a specific filter, if any.
	 *
	 * Since `null` is a legitimate value the modifier could return in a filter,
	 * use  the `did_filter` method to correctly discriminate whether the modifier did
	 * intervene in a filter at all or not.
	 *
	 * @since 6.0.0
	 *
	 * @param  string The filter name, e.g. `posts_pre_query`.
	 *
	 * @return mixed Either the value returned for the filter or `null` to indicate
	 *                          the object did not inject any value in the filter.
	 */
	public function get_filtered_value( $tag );

	/**
	 * Register all the hooks with this Query Monitor.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function hook();

	/**
	 * Unhooks the query modifier from any filter or action it hooked itself,
	 * or was hooked, to.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value.
	 */
	public function unhook();

	/**
	 * Define if this modifier should be applied or not.
	 *
	 * @since 6.0.0
	 *
	 * @param  WP_Query|null  $query A reference to the query object
	 *                               the modifier should be applied to.
	 *
	 * @return bool If the current modifier should be applied or not.
	 */
	public function applies_to( WP_Query $query = null );
}
