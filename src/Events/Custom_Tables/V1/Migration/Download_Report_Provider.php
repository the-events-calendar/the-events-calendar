<?php
/**
 * Registers the implementations and hooks for the Download Report migration button.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use tad_DI52_ServiceProvider as Service_Provider;
use TEC\Events\Custom_Tables\V1\Migration\CSV_Report\File_Download;

/**
 * Class Download_Report_Provider.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Download_Report_Provider extends Service_Provider {
	/**
	 * @var string The page slug.
	 */
	const DOWNLOAD_SLUG = 'migration-report-download';

	/**
	 * Registers the required implementations and hooks into the required
	 * actions and filters.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register() {
		if ( ! is_admin() ) {
			return;
		}

		$this->container->singleton( File_Download::class, File_Download::class );

		add_action( 'admin_menu', [ $this, 'add_dashboard_page' ] );
		add_action( 'admin_head', [ $this, 'remove_submenu_page' ] );
	}

	public function add_dashboard_page() {
		// Build the object if and when required.
		$callback = $this->container->callback( File_Download::class, 'download_csv' );

		return add_dashboard_page(
			null,
			null,
			'manage_options',
			self::DOWNLOAD_SLUG,
			$callback
		);
	}

	public function remove_submenu_page() {
		return remove_submenu_page( 'index.php', self::DOWNLOAD_SLUG );
	}

	public static function get_download_url() {
		return admin_url( "?noheader=1&page=" . self::DOWNLOAD_SLUG );
	}
}