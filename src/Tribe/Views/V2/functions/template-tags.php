<?php
/**
 * Views v2 specific template tags.
 *
 * @since TBD
 */

if ( ! function_exists( 'tribe_template_var' ) ) {
	/**
	 * Returns a value set on the current view template.
	 *
	 * This template tag should be used in View templates, after the View set up the loop.
	 *
	 * @since TBD
	 *
	 * @param string|array $key     The key, or nested keys, to fetch the variable.
	 * @param mixed        $default The default value that will be returned if the value is not set in the template or the
	 *                              template is not set at all.
	 *
	 * @return mixed
	 * @example
	 *        ```php
	 *        <?php
	 *        // Return the value of the `events` variable set on the template or an empty array if not found.
	 *        $events = tribe_template_var( 'events', [] );
	 *
	 * // Return the `keyword` value set in the `bar` array if the array `bar` variable is set and the `keyword` index
	 * // is set on it or an empty string
	 * $events = tribe_template_var( [ 'bar', 'keyword' ], '' );
	 * ```
	 *
	 */
	function tribe_template_var( $key, $default = null ) {
		global /** @var Tribe__Template $tribe_template */
		$tribe_template;

		if ( ! $tribe_template instanceof Tribe__Template ) {
			return $default;
		}

		return $tribe_template->get( $key, $default );
	}
}
