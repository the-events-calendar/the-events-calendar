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

		// Just register if we need it.
		if ( isset( $_GET[ File_Download::DOWNLOAD_QUERY_PARAM ] ) ) {
			add_action( "admin_action_" . File_Download::DOWNLOAD_SLUG, [ $this, 'download_csv' ] );
		}
	}

	/**
	 * Remove hooks.
	 *
	 * @since TBD
	 */
	public function unregister() {
		remove_action( "admin_action_" . File_Download::DOWNLOAD_SLUG, [ $this, 'download_csv' ] );
	}

	/**
	 * Trigger the download CSV check.
	 *
	 * @since TBD
	 */
	public function download_csv() {
		$this->container->make( File_Download::class )->download_csv();
	}
}