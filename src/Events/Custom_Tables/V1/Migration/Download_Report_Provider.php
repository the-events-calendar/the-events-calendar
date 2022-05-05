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

/**
 * Class Download_Report_Provider.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Download_Report_Provider extends Service_Provider {

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

		if ( is_admin() ) {
			add_action( 'admin_menu', function () {
				add_dashboard_page(
					__( 'Welcome', 'textdomain' ),
					__( 'Welcome', 'textdomain' ),
					'manage_options',
					self::DOWNLOAD_SLUG,
					[ $this, 'download_csv' ]
				);
			} );
			add_action( 'admin_head', function () {
				remove_submenu_page( 'index.php', self::DOWNLOAD_SLUG );
			} );
		}
	}

	public function download_csv() {
		// Check for current user privileges
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Check if we are in WP-Admin
		if ( ! is_admin() ) {
			return false;
		}

		// Nonce Check
		$nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'download_csv' ) ) {
			die( 'Security check error' );
		}


		header( 'Content-Type: text/csv; charset=UTF-8;' );

		header( 'Content-Disposition: attachment; filename="downloaded.pdf"' ); // Supply a file name to save
		header( "Cache-Control: no-cache, must-revalidate" );
		header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Date in the past

		exit;
	}

}