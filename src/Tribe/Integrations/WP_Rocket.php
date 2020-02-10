<?php
/**
 * Handles compatibility with WP Rocket plugin.
 *
 * @package Tribe\Events\Integrations
 * @since 5.0.0.2
 */
namespace Tribe\Events\Integrations;

/**
 * Integrations with WP Rocket plugin.
 *
 * @package Tribe\Events\Integrations
 * @since 5.0.0.2
 */
class WP_Rocket {

	/**
	 * Hooks all the required methods for WP_Rocket usage on our code.
	 *
	 * @since 5.0.0.2
	 */
	public function hook() {
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return;
		}
		add_filter( 'rocket_excluded_inline_js_content', [ $this, 'filter_excluded_inline_js_concat' ] );
	}

	/**
	 * Filters the content of the WP Rocket excluded inline JS concat.
	 *
	 * @since 5.0.0.2
	 *
	 * @param array $excluded_inline Items to be excluded by WP Rocket.
	 *
	 * @return array Excluded inline scripts after adding the breakpoint code.
	 */
	public function filter_excluded_inline_js_concat( array $excluded_inline ) {
		$excluded_inline[] = 'data-view-breakpoint-pointer';
		return $excluded_inline;
	}
}
