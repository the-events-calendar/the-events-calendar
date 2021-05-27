<?php
/**
 * Handles Elementor templates and their redirection.
 *
 * @since   5.7.0
 *
 * @package Tribe\Events\Integrations\Hello_Elementor
 */

namespace Tribe\Events\Integrations\Hello_Elementor;

/**
 * Class Templates
 *
 * @since   5.7.0
 *
 * @package Tribe\Events\Integrations\Hello_Elementor
 */
class Templates {
	/**
	 * Redirects an Elementor location to the correct one, if required.
	 *
	 * @since 5.7.0
	 *
	 * @param string $template The original Elementor location, e.g. `single` or `archive`.
	 *
	 * @return bool Whether the template location was redirected or not.
	 */
	public function theme_do_location( $template ) {
		if ( 'archive' !== $template ) {
			// Not a template we redirect, let Hello Elementor handle it.
			return false;
		}

		$view_slug = tribe_context()->get( 'view_request', false );

		if ( false === $view_slug ) {
			// Not a View request, let Hello Elementor handle it.
			return false;
		}

		$template_part = 'template-parts/single';

		/**
		 * Filters the Elementor template part as resolved by Views v2.
		 *
		 * Note that this filter will allow to both filter the template part and bail
		 * out of our logic completely by returning an empty value from the filter.
		 *
		 * @since 5.7.0
		 *
		 * @param string $template_part The template part path as resolved by our code, on behalf
		 *                              of Elementor.
		 * @param string $template      The template originally requested by Elementor, e.g. `single` or
		 *                              `archive`.
		 */
		$template_part = apply_filters(
			'tribe_events_views_v2_elementor_theme_do_location',
			$template_part,
			$template
		);

		if ( empty( $template_part ) ) {
			// Let Elementor resolve it.
			return false;
		}

		return false !== get_template_part( $template_part );
	}
}
