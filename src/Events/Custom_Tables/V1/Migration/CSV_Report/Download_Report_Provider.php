<?php
/**
 * Registers the implementations and hooks for the Download Report migration button.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\CSV_Report;
 */

namespace TEC\Events\Custom_Tables\V1\Migration\CSV_Report;

use tad_DI52_ServiceProvider as Service_Provider;

/**
 * Class Download_Report_Provider.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\CSV_Report;
 */
class Download_Report_Provider extends Service_Provider {
	/**
	 * Registers the required implementations and hooks into the required
	 * actions and filters.
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
	 */
	public function unregister() {
		remove_action( "admin_action_" . File_Download::DOWNLOAD_SLUG, [ $this, 'download_csv' ] );
	}

	/**
	 * Trigger the download CSV check.
	 *
	 * @since 6.0.0
	 */
	public function download_csv() {
		$this->container->make( File_Download::class )->download_csv();
	}
}