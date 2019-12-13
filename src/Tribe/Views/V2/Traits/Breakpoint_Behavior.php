<?php
/**
 * Provides methods for Views for breakpoint behavior.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Traits
 */

namespace Tribe\Events\Views\V2\Traits;

use Tribe\Events\Views\V2\View;

/**
 * Trait Breakpoint_Behavior
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Traits
 *
 * @property string $string The slug of the View instance.
 */
trait Breakpoint_Behavior {
	/**
	 * Default breakpoints used by TEC views.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected $default_breakpoints = [
		'xsmall' => 500,
		'medium' => 768,
		'full'   => 960,
	];

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
		$breakpoints = $this->default_breakpoints;

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
