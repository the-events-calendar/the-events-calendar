<?php
/**
 * Views v2 specific template tags.
 *
 * @since 4.9.4
 */

if ( ! function_exists( 'tribe_events_template_var' ) ) {
	/**
	 * Returns a value set on the current view template.
	 *
	 * This template tag should be used in View templates, after the View set up the loop.
	 *
	 * @since 4.9.4
	 *
	 * @param string|array $key     The key, or nested keys, to fetch the variable.
	 * @param mixed        $default The default value that will be returned if the value is not set in the template or the
	 *                              template is not set at all.
	 *
	 * @example
	 *        ```php
	 *        <?php
	 *        // Return the value of the `events` variable set on the template or an empty array if not found.
	 *        $events = tribe_events_template_var( 'events', [] );
	 *
	 * // Return the `keyword` value set in the `bar` array if the array `bar` variable is set and the `keyword` index
	 * // is set on it or an empty string
	 * $events = tribe_events_template_var( [ 'bar', 'keyword' ], '' );
	 * ```
	 *
	 * @return mixed The template variable value, or the default value if not found.
	 */
	function tribe_events_template_var( $key, $default = null ) {
		/** @var Tribe__Template $tribe_template */
		global $tribe_template;
		$value = $default;
		$view_slug  = false;

		if ( $tribe_template instanceof Tribe__Template ) {
			$value = $tribe_template->get( $key, $default );
			$view_slug = $tribe_template->get( 'view_slug', false );
		}

		/**
		 * Filters the value of a View template variable.
		 *
		 * @since 4.9.4
		 *
		 * @param mixed        $value     The View template value.
		 * @param string|array $key       The variable index, or indexes.
		 * @param mixed        $default   The default value that will be returned if the value was not found.
		 * @param string       $view_slug The current view view_slug, if any.
		 */
		$value = apply_filters( 'tribe_events_template_var', $value, $key, $default, $view_slug );

		if ( $view_slug ) {
			/**
			 * Filters the value of a specific View template variable.
			 *
			 * @since 4.9.4
			 *
			 * @param mixed        $value   The View template value.
			 * @param string|array $key     The  variable index, or indexes.
			 * @param mixed        $default The default value that will be returned if the value was not found.
			 */
			$value = apply_filters( "tribe_events_{$view_slug}_template_var", $value, $key, $default );
		}

		return $value;
	}
}
