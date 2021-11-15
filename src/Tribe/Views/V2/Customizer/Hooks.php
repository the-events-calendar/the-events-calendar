<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ tribe( Tribe\Events\Views\V2\Customizer\Hooks::class ), 'some_filtering_method' ] );
 * remove_filter( 'some_filter', [ tribe( 'views.v2.customizer.filters' ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ tribe( Tribe\Events\Views\V2\Customizer\Hooks::class ), 'some_method' ] );
 * remove_action( 'some_action', [ tribe( 'views.v2.customizer.hooks' ), 'some_method' ] );
 *
 * @since 5.7.0
 *
 * @package Tribe\Events\Views\V2\Customizer
 */

namespace Tribe\Events\Views\V2\Customizer;

use Tribe__Events__Main as TEC;

/**
 * Class Hooks
 *
 * @since 5.7.0
 *
 * @package Tribe\Events\Views\V2\Customizer
 */
class Hooks extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.7.0
	 */
	public function register() {
		// Register the Views V2 Customizer controls assets.
		tribe_asset(
			TEC::instance(),
			'tribe-customizer-views-v2-controls',
			'customizer-views-v2-controls.css'
		);

		tribe_asset(
			TEC::instance(),
			'tribe-customizer-views-v2-controls-js',
			'customizer-views-v2-controls.js'
		);

		tribe_asset(
			TEC::instance(),
			'tribe-customizer-views-v2-live-preview-js',
			'customizer-views-v2-live-preview.js',
			[],
			'customize_preview_init',
			[
				'localize'     => [
					'name' => 'tribe_events_customizer_live_preview_js_config',
					'data' => [ $this->container->make( Configuration::class ), 'localize' ],
				],
			]
		);

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Register any actions for the Customizer
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function add_actions() {
		add_action( 'customize_controls_enqueue_scripts', [ $this, 'enqueue_customizer_control_scripts'] );
	}

	/**
	 * Register any filters for the Customizer
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function add_filters() {
		// Register the assets for Customizer controls.
		add_action( 'customize_controls_print_styles', [ $this, 'enqueue_customizer_controls_styles' ] );
		// Register assets for Customizer styles.
		add_filter( 'tribe_customizer_inline_stylesheets', [ $this, 'customizer_inline_stylesheets' ], 12, 2 );
		add_filter( 'tribe_customizer_print_styles_action', [ $this, 'print_inline_styles_in_footer' ] );

		add_filter( 'body_class', [ $this, 'body_class' ] );
	}

	/**
	 * Add an identifying class to the body - but only when inside the Customizer preview.
	 *
	 * @since 5.11.0
	 *
	 * @param array<string> $classes The list of body classes to be applied.
	 *
	 * @return array<string> $classes The modified list of body classes to be applied.
	 */
	public function body_class( $classes ) {
		if ( is_customize_preview() ) {
			$classes[] = 'tec-customizer';
		}

		return $classes;
	}

	/**
	 * Enqueues the js for our v2 Customizer controls.
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function enqueue_customizer_control_scripts() {
		tribe_asset_enqueue( 'tribe-customizer-views-v2-controls-js' );
		tribe_asset_enqueue( 'tribe-customizer-views-v2-live-preview-js' );

	}

	/**
	 * Enqueues Customizer controls styles specific to Views v2 components.
	 *
	 * @since 5.9.0
	 */
	public function enqueue_customizer_controls_styles() {
		tribe_asset_enqueue( 'tribe-customizer-views-v2-controls' );
	}

	/**
	 * Add views stylesheets to customizer styles array to check.
	 * Remove unused legacy stylesheets.
	 *
	 * @since 5.1.1
	 *
	 * @param array<string> $sheets Array of sheets to search for.
	 * @param string        $css_template String containing the inline css to add.
	 *
	 * @return array Modified array of sheets to search for.
	 */
	public function customizer_inline_stylesheets( $sheets, $css_template ) {
		$v2_sheets = [ 'tribe-events-views-v2-full' ];

		// Dequeue legacy sheets.
		$keys = array_keys( $sheets, 'tribe-events-calendar-style' );
		if ( ! empty( $keys ) ) {
			foreach ( $keys as $key ) {
				unset( $sheets[ $key ] );
			}
		}

		return array_merge( $sheets, $v2_sheets );
	}

	/**
	 * Changes the action the Customizer should use to try and print inline styles to print the inline
	 * styles in the footer.
	 *
	 * @since 5.7.0
	 *
	 * @return string The action the Customizer should use to print inline styles.
	 */
	public function print_inline_styles_in_footer() {
		return 'wp_print_footer_scripts';
	}
}
