<?php
/**
 * Provides methods for Views for breakpoint behavior.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Behaviors
 */

namespace Tribe\Events\Views\V2\Behaviors;

use Tribe\Events\Views\V2\View;

/**
 * Trait Breakpoint_Behavior
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Behaviors
 *
 * @property string $string The slug of the View instance.
 */
trait Breakpoint_Behavior {

	/**
	 * Returns a given breakpoint.
	 *
	 * @since TBD
	 *
	 * @param string $name Which index we getting the breakpoint for.
	 *
	 * @return int   Returns the breakpoint with that given name or 0 when not available.
	 */
	public function get_breakpoint( $name ) {
		$breakpoints = $this->get_breakpoints();
		$breakpoint  = false;

		if ( isset( $breakpoints[ $name ] ) ) {
			$breakpoint = $breakpoints[ $name ];
		}

		/**
		 * Filters the breakpoint value.
		 *
		 * @since TBD
		 *
		 * @param int    $breakpoint The breakpoint value.
		 * @param View   $this       The current View instance being rendered.
		 */
		$breakpoint = apply_filters( "tribe_events_views_v2_view_breakpoint_{$name}", $breakpoint, $this );

		/**
		 * Filters the breakpoint value for a specific view.
		 *
		 * @since TBD
		 *
		 * @param int    $breakpoint The breakpoint value.
		 * @param View   $this       The current View instance being rendered.
		 */
		$breakpoint = apply_filters( "tribe_events_views_v2_view_{$this->slug}_breakpoint_{$name}", $breakpoint, $this );

		return absint( $breakpoint );
	}

	/**
	 * Returns all of the available breakpoints.
	 *
	 * @since TBD
	 *
	 * @return array Indexed array of all available breakpoints.
	 */
	public function get_breakpoints() {
		// Default breakpoints.
		$breakpoints = [
			'xsmall' => 500,
			'medium' => 768,
			'full'   => 960,
		];

		/**
		 * Filters all the breakpoints available.
		 *
		 * @since TBD
		 *
		 * @param array  $breakpoints All breapoints available.
		 * @param View   $this        The current View instance being rendered.
		 */
		$breakpoints = apply_filters( "tribe_events_views_v2_view_breakpoints", $breakpoints, $this );

		/**
		 * Filters the medium breakpoint value for a specific view.
		 *
		 * @since TBD
		 *
		 * @param array  $breakpoints All breapoints available.
		 * @param View   $this        The current View instance being rendered.
		 */
		$breakpoints = apply_filters( "tribe_events_views_v2_view_{$this->slug}_breakpoints", $breakpoints, $this );

		return $breakpoints;
	}
}
