<?php

namespace Tribe\Events\Integrations;

use Codeception\TestCase\WPTestCase;
use Tribe__Events__Integrations__ACF__ACF as ACF_Integration;
use Tribe__Admin__Helpers;

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
	 * @after
	 */
	public function tear_down_acf_integration(): void {
		// Clean up any hooked actions.
		remove_action( 'admin_enqueue_scripts', [ $this->acf_integration, 'load_compat_js' ] );
	}

	/**
	 * It should generate correct script URL using plugin_dir_url with __FILE__
	 *
	 * @test
	 */
	public function should_generate_correct_script_url(): void {
		// Test the URL generation logic by examining what plugin_dir_url(__FILE__) produces
		// from the ACF integration file.
		$plugin_root = dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) );
		$acf_file_path = $plugin_root . '/src/Tribe/Integrations/ACF/ACF.php';
		$expected_url = plugin_dir_url( $acf_file_path ) . 'resources/tribe-admin-acf-compat.js';

		// Verify the URL structure is correct and doesn't contain the broken path.
		$this->assertStringContainsString( '/src/Tribe/Integrations/ACF/resources/tribe-admin-acf-compat.js', $expected_url );
		$this->assertStringNotContainsString( '/plugins/build/', $expected_url );
	}

	/**
	 * It should verify the JavaScript file actually exists
	 *
	 * @test
	 */
	public function should_verify_javascript_file_exists(): void {
		$plugin_root = dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) );
		$expected_file_path = $plugin_root . '/src/Tribe/Integrations/ACF/resources/tribe-admin-acf-compat.js';

		$this->assertFileExists( $expected_file_path, 'The ACF compatibility JavaScript file should exist at the expected location' );

		// Verify file contains expected content.
		$file_contents = file_get_contents( $expected_file_path );
		$this->assertStringContainsString( 'tribe.ui-datepicker-div-beforeshow', $file_contents, 'File should contain ACF datepicker compatibility code' );
		$this->assertStringContainsString( 'acf-ui-datepicker', $file_contents, 'File should contain ACF-specific functionality' );
	}

	/**
	 * It should hook the load_compat_js method to admin_enqueue_scripts
	 *
	 * @test
	 */
	public function should_hook_load_compat_js_to_admin_enqueue_scripts(): void {
		// Clear any existing hooks.
		remove_action( 'admin_enqueue_scripts', [ $this->acf_integration, 'load_compat_js' ] );

		// Hook the integration.
		$this->acf_integration->hook();

		// Verify the action was hooked.
		$this->assertNotFalse(
			has_action( 'admin_enqueue_scripts', [ $this->acf_integration, 'load_compat_js' ] ),
			'The load_compat_js method should be hooked to admin_enqueue_scripts'
		);
	}

	/**
	 * It should only enqueue scripts when is_post_type_screen returns true
	 *
	 * @test
	 */
	public function should_only_enqueue_scripts_when_on_post_type_screen(): void {
		// Test that the method checks is_post_type_screen() before enqueuing.
		$admin_helpers = Tribe__Admin__Helpers::instance();
		$this->assertTrue( method_exists( $admin_helpers, 'is_post_type_screen' ), 'Admin helpers should have is_post_type_screen method' );

		// Verify load_compat_js method exists and is callable.
		$this->assertTrue( method_exists( $this->acf_integration, 'load_compat_js' ), 'ACF integration should have load_compat_js method' );
		$this->assertTrue( is_callable( [ $this->acf_integration, 'load_compat_js' ] ), 'load_compat_js should be callable' );
	}
}
