<?php

use Spatie\Snapshots\MatchesSnapshots;
use Tribe__Customizer as Customizer;

class CustomizerSettingsTest extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;

	/**
	 * @var array<string,string>
	 */
	private $collected_inline_styles = [];

	/**
	 * It should allow taking a CSS template snapshot
	 *
	 * @test
	 */
	public function should_allow_taking_a_css_template_snapshot() {
		$css_template = $this->get_css_template_for_settings( [ 'global_elements' => [ 'accent_color' => '#238923', 'link_color' => '#238923' ] ] );

		$this->assertMatchesSnapshot( $css_template );
	}

	/**
	 * Emulates a Customizer render where the specified settings, and only those settings, are set to the
	 * specified values.
	 *
	 * @since TBD
	 *
	 * @param array<string<array<string,int|string|float>> $settings A map of the customizer Sections, each
	 *                                                               one a map of customizer Settings.
	 *
	 * @return array<string,string> A map from each sheet handle to its rendered content.
	 */
	private function get_css_template_for_settings( array $settings = [] ) {
		add_filter( 'pre_option_tribe_customizer', static function () use ( $settings ) {
			return $settings;
		}, PHP_INT_MAX );
		// All sheets should count as enqueued.
		add_filter( 'tribe_customizer_inline_stylesheets', static function ( array $sheets ) {
			global $wp_styles;
			foreach ( $sheets as $sheet ) {
				if ( ! isset( $wp_styles->registered[ $sheet ] ) ) {
					$wp_styles->registered[ $sheet ] = new _WP_Dependency( $sheet, __FILE__, [], '1.0.0', [] );
					$wp_styles->queue[]              = $sheet;
				}
			}

			return $sheets;
		}, PHP_INT_MAX );

		/** @var Customizer $customizer */
		$customizer       = tribe( 'customizer' );
		$styles_collector = function ( string $sheet, string $inline_style ) {
			$this->collected_inline_styles[ $sheet ] = $inline_style;
		};
		add_action( 'tribe_customizer_before_inline_style', $styles_collector, 100, 2 );
		$customizer->inline_style( $force = true );
		remove_action( 'tribe_customizer_before_inline_style', $styles_collector, 100 );

		return $this->collected_inline_styles;
	}
}

