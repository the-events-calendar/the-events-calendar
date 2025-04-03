<?php
/**
 * An abstract base for the modifiers, to implement common methods.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query\Modifiers;

use TEC\Events\Custom_Tables\V1\Traits\With_Observable_Filtering;
use WP_Query;

/**
 * Class Base_Modifier
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */
abstract class Base_Modifier implements WP_Query_Modifier {
	use With_Observable_Filtering;

	/**
	 * A reference to the `WP_Query` instance this modifier is targeting, if any.
	 *
	 * @since 6.0.0
	 *
	 * @var WP_Query|null
	 */
	protected $query;

	/**
	 * Sets the reference to the `WP_Query` instance the Modifier should act on.
	 *
	 * @since 6.0.0
	 *
	 * @param  WP_Query  $query  A reference to the `WP_Query` instance this modifier is targeting.
	 */
	public function set_query( WP_Query $query ) {
		$this->query = $query;
		$this->hook();
	}

	/**
	 * Returns a reference to the `WP_Query` instance the modifier is attached to, if any.
	 *
	 * @since 6.0.0
	 *
	 * @return WP_Query|null A reference to the `WP_Query` instance the modifier is attached to, or `null`
	 *                       if the modifier is not currently attached to any query.
	 */
	public function get_query() {
		return $this->query;
	}

	/**
	 * Deems the modifier action as completed and triggers the callbacks on the "done"
	 * action.
	 *
	 * @since 6.0.0
	 */
	public function done() {
		$modifier_class = get_class( $this );

		/**
		 * Fires an action to signal this `WP_Query` modifier is done.
		 *
		 * Note: the modifier will pass a reference of itself to the callbacks, as such
		 * any callback that will store that reference will prevent the garbage collection
		 * of the Modifier and the instances (e.g. the `WP_Query` instance) it references!
		 *
		 * @since 6.0.0
		 *
		 * @param  Base_Modifier|WP_Query_Modifier  $this  A reference to the modifier that is
		 *                                                 triggering the action.
		 */
		do_action( "tec_events_custom_tables_v1_{$modifier_class}_done", $this );
	}

	/**
	 * Returns whether a query instance is the one this modifier should target or not.
	 *
	 * @since 6.0.0
	 *
	 * @param  WP_Query  $query  A reference to the `WP_Query` instance to check.
	 *
	 * @return bool Whether the `WP_Query` instance is the target one or not.
	 */
	protected function is_target_query( WP_Query $query = null ) {
		return $query === $this->query;
	}
}
