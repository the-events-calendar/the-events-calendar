<?php
/**
 * Tests for the Plugin_Manager class.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use Codeception\TestCase\WPTestCase;

/**
 * Class Plugin_Manager_Test
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Plugin_Manager_Test extends WPTestCase {
	/**
	 * Main file of the retired standalone plugin, relative to the plugins directory (the constant in Plugin_Manager is private).
	 */
	private const OLD_PLUGIN_FILE = 'the-events-calendar-category-colors/the-events-calendar-category-colors.php';

	/**
	 * Number of plugin files parsed by `get_plugins()` since the counter was last reset.
	 *
	 * `get_file_data()` applies the `extra_plugin_headers` filter once per file it reads.
	 *
	 * @var int
	 */
	private int $plugin_files_read = 0;

	/**
	 * Absolute path of the stand-in for the retired Category Colors plugin, when a test creates it.
	 *
	 * @var string
	 */
	private string $stand_in_file = '';

	/**
	 * @before
	 */
	public function set_up_counter(): void {
		$this->plugin_files_read = 0;
		add_filter(
			'extra_plugin_headers',
			function ( $headers ) {
				++$this->plugin_files_read;

				return $headers;
			}
		);
		// `get_plugins()` keeps its result in a non-persistent cache group; start every test from a cold cache.
		wp_cache_delete( 'plugins', 'plugins' );
	}

	/**
	 * @after
	 */
	public function remove_stand_in(): void {
		if ( $this->stand_in_file && file_exists( $this->stand_in_file ) ) {
			unlink( $this->stand_in_file );
			rmdir( dirname( $this->stand_in_file ) );
		}

		wp_cache_delete( 'plugins', 'plugins' );
	}

	/**
	 * Creates a minimal stand-in for the retired standalone plugin so it can be "installed" and "active".
	 */
	private function install_stand_in(): void {
		$this->stand_in_file = WP_PLUGIN_DIR . '/' . self::OLD_PLUGIN_FILE;
		wp_mkdir_p( dirname( $this->stand_in_file ) );
		file_put_contents( $this->stand_in_file, "<?php\n/* Plugin Name: The Events Calendar Category Colors (test stand-in) */\n" );
		wp_cache_delete( 'plugins', 'plugins' );
	}

	/**
	 * @test
	 */
	public function it_should_not_scan_the_plugins_directory_when_the_old_plugin_is_not_active(): void {
		$should_show = ( new Plugin_Manager() )->should_show_migration_controller();

		$this->assertFalse( $should_show );
		$this->assertSame(
			0,
			$this->plugin_files_read,
			'The plugins directory must not be scanned on a request just to look for a plugin that is not active.'
		);
	}

	/**
	 * @test
	 */
	public function it_should_still_detect_the_old_plugin_when_it_is_installed_and_active(): void {
		$this->install_stand_in();
		// The WPLoader module pins the active plugins through a `pre_option` filter, so add the stand-in with a later one.
		add_filter(
			'pre_option_active_plugins',
			static function ( $active ) {
				return array_merge( (array) $active, [ self::OLD_PLUGIN_FILE ] );
			},
			20
		);

		// Sanity check the fixture before relying on it, then start counting from a cold cache again.
		$this->assertTrue( is_plugin_active( self::OLD_PLUGIN_FILE ) );
		$this->assertArrayHasKey( self::OLD_PLUGIN_FILE, get_plugins() );
		$this->plugin_files_read = 0;
		wp_cache_delete( 'plugins', 'plugins' );

		$should_show = ( new Plugin_Manager() )->should_show_migration_controller();

		$this->assertTrue( $should_show );
		$this->assertGreaterThan( 0, $this->plugin_files_read, 'With the old plugin active, the installed check still has to look for it.' );
	}
}
