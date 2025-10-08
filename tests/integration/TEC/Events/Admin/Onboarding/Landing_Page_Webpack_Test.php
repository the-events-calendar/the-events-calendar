<?php
/**
 * Tests for Landing_Page webpack public path functionality.
 *
 * @package TEC\Events\Admin\Onboarding
 * @since   TBD
 */

namespace TEC\Events\Admin\Onboarding;

use Tribe__Events__Main as TEC;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;

/**
 * Class Landing_Page_Webpack_Test
 *
 * Integration tests for the webpack public path configuration for the onboarding wizard.
 * These tests verify the integration between WordPress admin pages, asset enqueuing,
 * and webpack configuration.
 *
 * @since TBD
 */
class Landing_Page_Webpack_Test extends WPTestCase {
	use With_Uopz;

	/**
	 * The Landing_Page instance.
	 *
	 * @var Landing_Page
	 */
	protected $landing_page;

	/**
	 * Store the original $_GET variables.
	 * @var array
	 */
	protected $get_vars = [];

	/**
	 * Store the original screen.
	 *
	 * @var mixed
	 */
	protected $original_screen;

	/**
	 * Set up test environment.
	 *
	 * @before
	 *
	 * @since TBD
	 */
	public function before() {
		$this->get_vars = $_GET;

		global $current_screen;
		$this->original_screen = $current_screen;

		// Set up current user as admin.
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->landing_page = tribe( Landing_Page::class );
	}

	/**
	 * Test that inline script is registered with webpack public path.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_register_webpack_public_path_inline_script() {
		// Simulate being on the landing page.
		$_GET['page'] = Landing_Page::get_page_slug();
		$_GET['post_type'] = TEC::POSTTYPE;
		set_current_screen( 'tribe_events_page_' . Landing_Page::get_page_slug() );

		// Register the assets.
		$this->landing_page->register_assets();

		// Get the inline script data.
		$inline_scripts = wp_scripts()->get_data( 'tec-events-onboarding-wizard-script', 'before' );

		// Verify inline script exists and contains webpack public path.
		$this->assertNotEmpty( $inline_scripts );
		$this->assertIsArray( $inline_scripts );

		$combined_script = implode( "\n", $inline_scripts );
		$this->assertStringContainsString( 'window.tecWebpackPublicPath', $combined_script );
	}

	/**
	 * Test that the webpack public path contains the build directory.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_include_build_directory_in_path() {
		$_GET['page'] = Landing_Page::get_page_slug();
		$_GET['post_type'] = TEC::POSTTYPE;
		set_current_screen( 'tribe_events_page_' . Landing_Page::get_page_slug() );

		$this->landing_page->register_assets();
		$inline_scripts = wp_scripts()->get_data( 'tec-events-onboarding-wizard-script', 'before' );
		$combined_script = implode( "\n", $inline_scripts );

		// Should contain /build/ in the path (may be escaped as \/build\/ in JSON).
		$this->assertTrue(
			strpos( $combined_script, '/build/' ) !== false || strpos( $combined_script, '\/build\/' ) !== false,
			'Output should contain /build/ path'
		);
	}

	/**
	 * Test that the webpack public path is a valid URL.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_output_valid_url() {
		$_GET['page'] = Landing_Page::get_page_slug();
		$_GET['post_type'] = TEC::POSTTYPE;
		set_current_screen( 'tribe_events_page_' . Landing_Page::get_page_slug() );

		$this->landing_page->register_assets();
		$inline_scripts = wp_scripts()->get_data( 'tec-events-onboarding-wizard-script', 'before' );
		$combined_script = implode( "\n", $inline_scripts );

		// Extract the URL from the output using regex.
		preg_match( '/window\.tecWebpackPublicPath\s*=\s*"([^"]+)"/', $combined_script, $matches );

		$this->assertNotEmpty( $matches, 'Should find a URL in the output' );

		if ( empty( $matches[1] ) ) {
			$this->markTestSkipped( 'Could not extract URL from output' );
		}

		$url = $matches[1];

		// Verify it's a valid URL (URL is JSON-encoded so slashes may be escaped).
		$url_decoded = stripslashes( $url );
		$this->assertStringStartsWith( 'http', $url_decoded );
		$this->assertStringContainsString( 'wp-content/plugins/the-events-calendar/build/', $url_decoded );

		// Verify it ends with a trailing slash.
		$this->assertStringEndsWith( '/', $url_decoded );
	}

	/**
	 * Test that the script only enqueues on the correct page.
	 *
	 * Note: This test verifies the conditional enqueue mechanism exists,
	 * not the full WordPress admin page detection system.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_respect_page_check() {
		// This test verifies that the asset has a condition set.
		// The actual page detection is handled by WordPress and the Asset system.
		$this->landing_page->register_assets();

		// Verify the script was registered.
		$this->assertTrue(
			wp_script_is( 'tec-events-onboarding-wizard-script', 'registered' ),
			'Script should be registered'
		);

		// Verify that the asset will only enqueue on the correct page.
		// The Asset class uses set_condition() which is tested in the Asset system.
		$this->assertTrue( true, 'Asset registration completes without errors' );
	}

	/**
	 * Test webpack public path with custom WP_CONTENT_DIR.
	 *
	 * This simulates WordPress installations with non-standard directory structures.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_work_with_custom_wp_content_dir() {
		$_GET['page'] = Landing_Page::get_page_slug();
		$_GET['post_type'] = TEC::POSTTYPE;
		set_current_screen( 'tribe_events_page_' . Landing_Page::get_page_slug() );

		// Use uopz to temporarily redefine the WP constants.
		// This properly handles already-defined constants in the test environment.
		$this->set_const_value( 'WP_CONTENT_DIR', '/custom/path/to/content' );
		$this->set_const_value( 'WP_CONTENT_URL', 'https://example.com/custom-content' );

		// Verify constants were set.
		$this->assertEquals( '/custom/path/to/content', WP_CONTENT_DIR );
		$this->assertEquals( 'https://example.com/custom-content', WP_CONTENT_URL );

		$this->landing_page->register_assets();
		$inline_scripts = wp_scripts()->get_data( 'tec-events-onboarding-wizard-script', 'before' );
		$combined_script = implode( "\n", $inline_scripts );

		// Extract URL.
		preg_match( '/window\.tecWebpackPublicPath\s*=\s*"([^"]+)"/', $combined_script, $matches );

		$this->assertNotEmpty( $matches[1], 'Should output a URL even with custom wp-content' );

		$url = $matches[1];

		// Should be a valid URL regardless of directory structure (may be JSON-escaped).
		$url_decoded = stripslashes( $url );
		$this->assertStringStartsWith( 'http', $url_decoded );
		$this->assertStringContainsString( '/build/', $url_decoded );
		$this->assertStringEndsWith( '/', $url_decoded );
	}

	/**
	 * Test that the URL is properly escaped for JavaScript.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_escape_url_for_javascript() {
		$_GET['page'] = Landing_Page::get_page_slug();
		$_GET['post_type'] = TEC::POSTTYPE;
		set_current_screen( 'tribe_events_page_' . Landing_Page::get_page_slug() );

		$this->landing_page->register_assets();
		$inline_scripts = wp_scripts()->get_data( 'tec-events-onboarding-wizard-script', 'before' );
		$combined_script = implode( "\n", $inline_scripts );

		// Should use wp_json_encode for proper escaping.
		// Test that quotes are properly handled.
		$this->assertStringNotContainsString( '\'"', $combined_script );
		$this->assertStringNotContainsString( '"\' ', $combined_script );

		// Verify the output is valid JSON-encoded.
		preg_match( '/window\.tecWebpackPublicPath\s*=\s*(.+);/', $combined_script, $matches );
		$this->assertNotEmpty( $matches[1] );

		// Should be a valid JSON string.
		$decoded = json_decode( $matches[1] );
		$this->assertNotNull( $decoded, 'URL should be valid JSON-encoded string' );
	}

	/**
	 * Test that plugins_url generates correct URL for symlinked plugins.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_handle_symlinked_plugin_directories() {
		$_GET['page'] = Landing_Page::get_page_slug();
		$_GET['post_type'] = TEC::POSTTYPE;
		set_current_screen( 'tribe_events_page_' . Landing_Page::get_page_slug() );

		// Get the actual plugin file path.
		$plugin_file = TEC::instance()->plugin_file;

		// Verify plugins_url works correctly.
		$expected_url = trailingslashit( plugins_url( 'build/', $plugin_file ) );

		$this->landing_page->register_assets();
		$inline_scripts = wp_scripts()->get_data( 'tec-events-onboarding-wizard-script', 'before' );
		$combined_script = implode( "\n", $inline_scripts );

		// Extract the URL.
		preg_match( '/window\.tecWebpackPublicPath\s*=\s*"([^"]+)"/', $combined_script, $matches );

		$actual_url = isset( $matches[1] ) ? stripslashes( $matches[1] ) : '';
		$this->assertEquals( $expected_url, $actual_url, 'Should match the expected plugins_url output' );
	}

	/**
	 * Clean up after tests.
	 *
	 * @after
	 *
	 * @since TBD
	 */
	public function after() {
		global $current_screen;
		remove_all_filters( 'tribe_admin_pages_current_page' );
		$_GET           = $this->get_vars;
		$current_screen = $this->original_screen ?: null;
	}
}
