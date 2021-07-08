<?php
/**
 * Handles setting up the configuration data for v2 Customizer.
 *
 * @package Tribe\Events\Views\V2\Customizer
 * @since   TBD
 */

namespace Tribe\Events\Views\V2\Customizer;

/**
 * Class managing Configuration for the Views V2_1.
 *
 * @package Tribe\Events\Filterbar\Views\V2_1
 * @since   TBD
 */
class Configuration {
	/**
	 * Return the variables to be localized.
	 * localized under `tribe_events_customizer_live_preview_js_config` in Hooks.php
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function localize() {

		$data = [
			'selector' => self::get_selector(),
			'default_font' => '"Helvetica Neue", Helvetica, -apple-system, BlinkMacSystemFont, Roboto, Arial, sans-serif',
		];

		return apply_filters( 'tribe_customizer_live_preview_js_config', $data );
	}

	public static function get_selector() {
		$tribe_events = '#tribe-events-pg-template, .tribe-events';

		/**
		 * Allows filtering to enforce applying Customizer styles to shortcode views.
		 *
		 * @since TBD
		 *
		 * @param boolean $apply_to_shortcode Whether to apply Customizer styles to shortcodes (default = false).
		 */
		$apply_to_shortcode = apply_filters( 'tribe_customizer_should_print_shortcode_customizer_styles', false );

		if ( ! $apply_to_shortcode ) {
			$tribe_events .= ':not( .tribe-events-view--shortcode )';
		}

		/**
		 * Allows filtering to enforce NOT applying Customizer styles to widgets.
		 *
		 * @since TBD
		 *
		 * @param boolean $apply_to_widget Whether to apply Customizer styles to widgets (default = true).
		 */
		$apply_to_widget = apply_filters( 'tribe_customizer_should_print_widget_customizer_styles', true );

		if ( ! $apply_to_widget ) {
			$tribe_events .= ':not( .tribe-events-widget )';
		}

		return $tribe_events;
	}
}
