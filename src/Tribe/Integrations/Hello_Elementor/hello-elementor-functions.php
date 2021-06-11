<?php
/**
 * Hello Elementor API functions.
 *
 * @since 5.7.0
 */

use Tribe\Events\Integrations\Hello_Elementor\Templates;

if ( ! function_exists( 'elementor_theme_do_location' ) ) {
	/**
	 * If this function is defined, then Hello Elementor will call it to allow filtering
	 * the template location.
	 *
	 * This function works a bit like the `do_parse_request` filter in WordPress Core: it
	 * allows our code to either take charge of handling the template discovery or let
	 * Elementor go through its own resolution.
	 *
	 * @since 5.7.0
	 *
	 * @param string $template The template that Elementor is currently filtering; e.g.
	 *                         `single` or `archive`.
	 *
	 * @return bool Whether the template was correctly handled or not.
	 */
	function elementor_theme_do_location( $template ) {
		return tribe( Templates::class )->theme_do_location( $template );
	}
}
