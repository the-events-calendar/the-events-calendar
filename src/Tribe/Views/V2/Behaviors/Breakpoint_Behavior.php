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
	 * Returns the xsmall breakpoint value
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	protected function get_xsmall_breakpoint() {
		// Default value for xsmall breakpoint is 500px
		$bp_xsmall = 500;

		/**
		 * Filters the xsmall breakpoint value.
		 *
		 * @since TBD
		 *
		 * @param object $bp_xsmall The xsmall breakpoint value.
		 * @param View   $this      The current View instance being rendered.
		 */
		$bp_xsmall = apply_filters( "tribe_events_views_v2_view_breakpoint_xsmall", $bp_xsmall, $this );

		/**
		 * Filters the xsmall breakpoint value for a specific view.
		 *
		 * @since TBD
		 *
		 * @param object $bp_xsmall The xsmall breakpoint value.
		 * @param View   $this      The current View instance being rendered.
		 */
		$bp_xsmall = apply_filters( "tribe_events_views_v2_view_{$this->slug}_breakpoint_xsmall", $bp_xsmall, $this );

		return $bp_xsmall;
	}

	/**
	 * Returns the medium breakpoint value
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	protected function get_medium_breakpoint() {
		// Default value for medium breakpoint is 768px
		$bp_medium = 768;

		/**
		 * Filters the medium breakpoint value.
		 *
		 * @since TBD
		 *
		 * @param object $bp_medium The medium breakpoint value.
		 * @param View   $this      The current View instance being rendered.
		 */
		$bp_medium = apply_filters( "tribe_events_views_v2_view_breakpoint_medium", $bp_medium, $this );

		/**
		 * Filters the medium breakpoint value for a specific view.
		 *
		 * @since TBD
		 *
		 * @param object $bp_medium The medium breakpoint value.
		 * @param View   $this      The current View instance being rendered.
		 */
		$bp_medium = apply_filters( "tribe_events_views_v2_view_{$this->slug}_breakpoint_medium", $bp_medium, $this );

		return $bp_medium;
	}

	/**
	 * Returns the full breakpoint value
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	protected function get_full_breakpoint() {
		// Default value for full breakpoint is 960px
		$bp_full = 960;

		/**
		 * Filters the full breakpoint value.
		 *
		 * @since TBD
		 *
		 * @param object $bp_full The full breakpoint value.
		 * @param View   $this    The current View instance being rendered.
		 */
		$bp_full = apply_filters( "tribe_events_views_v2_view_breakpoint_full", $bp_full, $this );

		/**
		 * Filters the full breakpoint value for a specific view.
		 *
		 * @since TBD
		 *
		 * @param object $bp_full The full breakpoint value.
		 * @param View   $this    The current View instance being rendered.
		 */
		$bp_full = apply_filters( "tribe_events_views_v2_view_{$this->slug}_breakpoint_full", $bp_full, $this );

		return $bp_full;
	}
}
