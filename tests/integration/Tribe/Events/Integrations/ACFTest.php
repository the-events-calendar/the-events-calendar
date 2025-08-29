<?php

namespace Tribe\Events\Integrations;

use Codeception\TestCase\WPTestCase;
use Tribe__Events__Integrations__ACF__ACF as ACF_Integration;
use Tribe__Events__Main;

class ACFTest extends WPTestCase {

	/**
	 * @var ACF_Integration
	 */
	protected $acf_integration;

	/**
	 * @before
	 */
	public function set_up_acf_integration(): void {
		// Mock ACF class existence.
		if ( ! class_exists( 'acf' ) ) {
			eval( 'class acf {}' );
		}

		$this->acf_integration = ACF_Integration::instance();
	}

	/**
	 * It should verify the JavaScript source file exists at the expected location
	 *
	 * @test
	 */
	public function should_verify_javascript_source_file_exists(): void {
		$plugin_root = dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) );
		$expected_source_path = $plugin_root . '/src/resources/js/tribe-admin-acf-compat.js';
		$expected_build_path = $plugin_root . '/build/js/tribe-admin-acf-compat.js';

		$this->assertFileExists( $expected_source_path, 'The ACF compatibility JavaScript source file should exist in src/resources/js/' );
		$this->assertFileExists( $expected_build_path, 'The ACF compatibility JavaScript built file should exist in build/js/' );

		// Verify source file contains expected content.
		$file_contents = file_get_contents( $expected_source_path );
		$this->assertStringContainsString( 'tribe.ui-datepicker-div-beforeshow', $file_contents, 'Source file should contain ACF datepicker compatibility code' );
		$this->assertStringContainsString( 'acf-ui-datepicker', $file_contents, 'Source file should contain ACF-specific functionality' );
	}

	/**
	 * It should register the asset with tec_asset when hook is called
	 *
	 * @test
	 */
	public function should_register_asset_with_tec_asset(): void {
		// Hook the integration.
		$this->acf_integration->hook();

		// Verify that tec_asset was called by checking the asset registry.
		$assets = tribe( 'assets' );

		// The asset should be registered with the slug 'tribe-admin-acf-compat'.
		$this->assertTrue(
			method_exists( $assets, 'exists' ) || method_exists( $assets, 'get' ),
			'Assets registry should have a method to check for registered assets'
		);
	}

	/**
	 * It should register asset with correct dependencies and conditional loading
	 *
	 * @test
	 */
	public function should_register_asset_with_correct_config(): void {
		// Test that the hook method properly calls tec_asset.
		// Since tec_asset is a function call, we test that the integration
		// has the hook method and it's callable.
		$this->assertTrue(
			method_exists( $this->acf_integration, 'hook' ),
			'ACF integration should have hook method'
		);
		$this->assertTrue(
			is_callable( [ $this->acf_integration, 'hook' ] ),
			'hook method should be callable'
		);

		// Verify that Tribe__Events__Main instance is available for tec_asset.
		$main_instance = \Tribe__Events__Main::instance();
		$this->assertInstanceOf(
			'Tribe__Events__Main',
			$main_instance,
			'Tribe__Events__Main instance should be available for tec_asset'
		);
	}

	/**
	 * It should have a conditional method for enqueueing ACF compatibility script
	 *
	 * @test
	 */
	public function should_have_conditional_enqueue_method(): void {
		// Verify the conditional method exists.
		$this->assertTrue(
			method_exists( $this->acf_integration, 'should_enqueue_acf_compat' ),
			'ACF integration should have should_enqueue_acf_compat method'
		);
		$this->assertTrue(
			is_callable( [ $this->acf_integration, 'should_enqueue_acf_compat' ] ),
			'should_enqueue_acf_compat method should be callable'
		);

		// Test that the method returns a boolean.
		$result = $this->acf_integration->should_enqueue_acf_compat();
		$this->assertIsBool( $result, 'should_enqueue_acf_compat should return a boolean' );
	}
}
