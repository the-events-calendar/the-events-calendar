<?php
namespace Helper;

use Codeception\Module;
use Codeception\Module\WPDb;
use Codeception\TestInterface;

/**
 * Keeps the `db_version` option of an imported SQL dump in step with the installed WordPress.
 *
 * A dump older than core makes wp-admin redirect to `upgrade.php`, so pages never render. Syncing on every test
 * lets one dump work across the whole WordPress matrix. Enable it AFTER `WPDb` in a suite's module list, so the
 * dump is already imported when it runs.
 *
 * @since TBD
 */
class Db_Version_Sync extends Module {

	/**
	 * Sets the dump's `db_version` to the one of the installed WordPress, when they differ.
	 *
	 * @since TBD
	 *
	 * @param TestInterface $test The test about to run.
	 *
	 * @return void
	 */
	public function _before( TestInterface $test ): void {
		/* Codeception loads the env file into $_SERVER; $_ENV and getenv() are only fallbacks. */
		$root = $_SERVER['WP_ROOT_FOLDER'] ?? $_ENV['WP_ROOT_FOLDER'] ?? getenv( 'WP_ROOT_FOLDER' );

		if ( ! $root ) {
			return;
		}

		$version_file = rtrim( $root, '/' ) . '/wp-includes/version.php';

		if ( ! is_readable( $version_file ) || ! preg_match( '/\$wp_db_version\s*=\s*(\d+)/', file_get_contents( $version_file ), $matches ) ) {
			return;
		}

		/** @var WPDb $db */
		$db = $this->getModule( 'WPDb' );

		if ( (string) $db->grabOptionFromDatabase( 'db_version' ) !== $matches[1] ) {
			$db->haveOptionInDatabase( 'db_version', $matches[1] );
		}
	}
}
