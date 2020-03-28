<?php
/**
 * Provides methods for Views for breakpoint behavior.
 *
 * @since   5.0.0
 *
 * @package Tribe\Events\Views\V2\Views\Traits
 */

namespace Tribe\Events\Views\V2\Views\Traits;

use Tribe\Events\Views\V2\View;

/**
 * Trait Breakpoint_Behavior
 *
 * @since   5.0.0
 *
 * @package Tribe\Events\Views\V2\Views\Traits
 *
 * @property string $string The slug of the View instance.
 */
trait Breakpoint_Behavior {
	/**
	 * Default breakpoints used by TEC views.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * Returns a given breakpoint pointer to a safer inline JS execution.
	 *
	 * @since 5.0.0.2
	 *
	 * @return int   Returns the breakpoint with that given name or 0 when not available.
	 */
	public function get_breakpoint_pointer() {
		$pointer = wp_generate_uuid4();

		/**
		 * Filters the pointer ID for all views.
		 *
		 * @since 5.0.0.2
		 *
		 * @param string $breakpoints Current pointer value.
		 * @param View   $this        The current View instance being rendered.
		 */
		$pointer = apply_filters( "tribe_events_views_v2_view_breakpoint_pointer", $pointer, $this );

		/**
		 * Filters the pointer ID for a specific view.
		 *
		 * @since 5.0.0.2
		 *
		 * @param string $pointer   Current pointer value.
		 * @param View   $this      The current View instance being rendered.
		 */
		$pointer = apply_filters( "tribe_events_views_v2_view_{$this->slug}_breakpoint_pointer", $pointer, $this );

		return $pointer;
	}

	/**
	 * Returns all of the available breakpoints.
	 *
	 * @since 5.0.0
	 *
	 * @return array Indexed array of all available breakpoints.
	 */
	public function get_breakpoints() {
		$breakpoints = $this->default_breakpoints;

		/**
		 * Filters all the breakpoints available.
		 *
		 * @since 5.0.0
		 *
		 * @param array  $breakpoints All breakpoints available.
		 * @param View   $this        The current View instance being rendered.
		 */
		$breakpoints = apply_filters( "tribe_events_views_v2_view_breakpoints", $breakpoints, $this );

		/**
		 * Filters the breakpoints value for a specific view.
		 *
		 * @since 5.0.0
		 *
		 * @param array  $breakpoints All breakpoints available.
		 * @param View   $this        The current View instance being rendered.
		 */
		$breakpoints = apply_filters( "tribe_events_views_v2_view_{$this->slug}_breakpoints", $breakpoints, $this );

		return $breakpoints;
	}
}
