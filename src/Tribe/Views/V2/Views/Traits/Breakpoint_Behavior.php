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
	 * Associative array of default breakpoints used by TEC views keyed by breakpoints.
	 *
	 * @since 5.0.0
	 *
	 * @var array<string, int>
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
	 * @param string $name Which index we are getting the breakpoint for.
	 *
	 * @return int The breakpoint with that given name or 0 when not available.
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
	 * @return string Breakpoint pointer as a random UUID (version 4).
	 */
	public function get_breakpoint_pointer() {
		$pointer = wp_generate_uuid4();

		/**
		 * Filters the pointer ID for all views.
		 *
		 * @since 5.0.0.2
		 *
		 * @param string $pointer Current pointer value (UUID4).
		 * @param View   $this        The current View instance being rendered.
		 */
		$pointer = apply_filters( "tribe_events_views_v2_view_breakpoint_pointer", $pointer, $this );

		$view_slug = static::get_view_slug();

		/**
		 * Filters the pointer ID for a specific view.
		 *
		 * @since 5.0.0.2
		 *
		 * @param string $pointer   Current pointer value (UUID4).
		 * @param View   $this      The current View instance being rendered.
		 */
		$pointer = apply_filters( "tribe_events_views_v2_view_{$view_slug}_breakpoint_pointer", $pointer, $this );

		return $pointer;
	}

	/**
	 * Returns all the available breakpoints.
	 *
	 * @since 5.0.0
	 *
	 * @return array<string, int> Associative array of all breakpoints available keyed by breakpoint name.
	 */
	public function get_breakpoints() {
		$breakpoints = $this->default_breakpoints;

		/**
		 * Filters all the breakpoints available.
		 *
		 * @since 5.0.0
		 *
		 * @param array<string, int> $breakpoints Associative array of all breakpoints available keyed by breakpoint name.
		 * @param View               $this        The current View instance being rendered.
		 */
		$breakpoints = apply_filters( "tribe_events_views_v2_view_breakpoints", $breakpoints, $this );

		$view_slug = static::get_view_slug();

		/**
		 * Filters the breakpoints value for a specific view.
		 *
		 * @since 5.0.0
		 *
		 * @param array<string, int> $breakpoints Associative array of all breakpoints available keyed by breakpoint name.
		 * @param View               $this        The current View instance being rendered.
		 */
		$breakpoints = apply_filters( "tribe_events_views_v2_view_{$view_slug}_breakpoints", $breakpoints, $this );

		return $breakpoints;
	}
}
