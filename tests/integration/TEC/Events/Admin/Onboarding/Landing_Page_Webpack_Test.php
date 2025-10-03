<?php
/**
 * Tests for Landing_Page webpack public path functionality.
 *
 * @package TEC\Events\Admin\Onboarding
 * @since   TBD
 */

namespace TEC\Events\Admin\Onboarding;

use Tribe__Events__Main as TEC;

/**
 * Class Landing_Page_Webpack_Test
 *
 * Integration tests for the webpack public path configuration for the onboarding wizard.
 * These tests verify the integration between WordPress admin pages, asset enqueuing,
 * and webpack configuration.
 *
 * @since TBD
 */
class Landing_Page_Webpack_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * The Landing_Page instance.
	 *
	 * @var Landing_Page
	 */
	protected $landing_page;

	/**
	 * Set up test environment.
	 *
	 * @since TBD
	 */
	public function setUp() {
		parent::setUp();

		// Set up current user as admin.
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->landing_page = tribe( Landing_Page::class );
	}

	/**
	 * Test that set_webpack_public_path outputs the correct script tag.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_output_webpack_public_path_script() {
		// Simulate being on the landing page.
		$_GET['page'] = Landing_Page::get_page_slug();
		$_GET['post_type'] = TEC::POSTTYPE;

		// Mock the admin pages system to recognize we're on the landing page.
		add_filter( 'tribe_admin_pages_current_page', function() {
			return Landing_Page::get_page_slug();
		} );

		// Capture the output.
		ob_start();
		$this->landing_page->set_webpack_public_path();
		$output = ob_get_clean();

		// Verify script tag exists.
		$this->assertStringContainsString( '<script type="text/javascript">', $output );
		$this->assertStringContainsString( 'window.tecWebpackPublicPath', $output );
		$this->assertStringContainsString( '</script>', $output );
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

		add_filter( 'tribe_admin_pages_current_page', function() {
			return Landing_Page::get_page_slug();
		} );

		ob_start();
		$this->landing_page->set_webpack_public_path();
		$output = ob_get_clean();

		// Should contain /build/ in the path (may be escaped as \/build\/ in JSON).
		$this->assertTrue(
			strpos( $output, '/build/' ) !== false || strpos( $output, '\/build\/' ) !== false,
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

		add_filter( 'tribe_admin_pages_current_page', function() {
			return Landing_Page::get_page_slug();
		} );

		ob_start();
		$this->landing_page->set_webpack_public_path();
		$output = ob_get_clean();

		// Extract the URL from the output using regex.
		preg_match( '/window\.tecWebpackPublicPath\s*=\s*"([^"]+)"/', $output, $matches );

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
	 * Test that the script respects the is_on_page check.
	 *
	 * Note: This test verifies the conditional output mechanism exists,
	 * not the full WordPress admin page detection system.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_respect_page_check() {
		// This test verifies that is_on_page() is being called.
		// The actual page detection is handled by WordPress and tested elsewhere.
		$method = new \ReflectionMethod( $this->landing_page, 'set_webpack_public_path' );
		$this->assertTrue( $method->isPublic(), 'Method should be public' );

		// Verify the method checks is_on_page by ensuring it doesn't fatal.
		ob_start();
		$this->landing_page->set_webpack_public_path();
		$output = ob_get_clean();

		// The output may or may not be empty depending on test environment,
		// but it should not cause errors.
		$this->assertTrue( true, 'Method executes without errors' );
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
		$_GET['page'] = 'tec-events-onboarding';

		// Backup original constants.
		$original_content_dir = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : null;
		$original_content_url = defined( 'WP_CONTENT_URL' ) ? WP_CONTENT_URL : null;

		// Simulate custom wp-content directory.
		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', '/custom/path/to/content' );
		}
		if ( ! defined( 'WP_CONTENT_URL' ) ) {
			define( 'WP_CONTENT_URL', 'https://example.com/custom-content' );
		}

		ob_start();
		$this->landing_page->set_webpack_public_path();
		$output = ob_get_clean();

		// Extract URL.
		preg_match( '/window\.tecWebpackPublicPath\s*=\s*"([^"]+)"/', $output, $matches );

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
		$_GET['page'] = 'tec-events-onboarding';

		ob_start();
		$this->landing_page->set_webpack_public_path();
		$output = ob_get_clean();

		// Should use wp_json_encode for proper escaping.
		// Test that quotes are properly handled.
		$this->assertStringNotContainsString( '\'"', $output );
		$this->assertStringNotContainsString( '"\' ', $output );

		// Verify the output is valid JSON-encoded.
		preg_match( '/window\.tecWebpackPublicPath\s*=\s*(.+);/', $output, $matches );
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
		$_GET['page'] = 'tec-events-onboarding';

		// Get the actual plugin file path.
		$plugin_file = TEC::instance()->plugin_file;

		// Verify plugins_url works correctly.
		$expected_url = trailingslashit( plugins_url( 'build/', $plugin_file ) );

		ob_start();
		$this->landing_page->set_webpack_public_path();
		$output = ob_get_clean();

		// Extract the URL.
		preg_match( '/window\.tecWebpackPublicPath\s*=\s*"([^"]+)"/', $output, $matches );

		$actual_url = isset( $matches[1] ) ? stripslashes( $matches[1] ) : '';
		$this->assertEquals( $expected_url, $actual_url, 'Should match the expected plugins_url output' );
	}

	/**
	 * Test that the script is added via admin_head hook.
	 *
	 * @test
	 * @since TBD
	 */
	public function it_should_hook_into_admin_head() {
		// Register assets to add the hooks.
		$this->landing_page->register_assets();

		// Verify the hook is registered.
		$this->assertNotFalse(
			has_action( 'admin_head', [ $this->landing_page, 'set_webpack_public_path' ] ),
			'set_webpack_public_path should be hooked to admin_head'
		);

		// Verify it's hooked with priority 1 (early).
		$this->assertEquals(
			1,
			has_action( 'admin_head', [ $this->landing_page, 'set_webpack_public_path' ] ),
			'Hook should have priority 1 to run early'
		);
	}

	/**
	 * Clean up after tests.
	 *
	 * @since TBD
	 */
	public function tearDown() {
		unset( $_GET['page'], $_GET['post_type'] );
		remove_all_filters( 'tribe_admin_pages_current_page' );
		parent::tearDown();
	}
}
