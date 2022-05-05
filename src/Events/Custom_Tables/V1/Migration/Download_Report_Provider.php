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
use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;
use TEC\Events\Custom_Tables\V1\Migration\State;

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
		if ( is_admin() ) {
			add_action( 'admin_menu', function () {
				add_dashboard_page(
					null,
					null,
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

	/**
	 * Outputs the CSV file for the current event report.
	 *
	 * @since TBD
	 *
	 * @return false|void
	 */
	public function download_csv() {

		// Check if we are in WP-Admin
		if ( ! is_admin() || empty( $_GET['noheader'] ) ) {
			return false;
		}

		$site_report = Site_Report::build();
		$reports     = $site_report->get_event_reports();
		$delimiter   = ',';
		$output      = fopen( 'php://output', 'w' );
		$charset     = get_option( 'blog_charset' );

		header( "Content-Type: text/csv; charset=$charset;" );
		header( 'Content-Disposition: attachment; filename="migration_event_report.csv"' );
		header( "Cache-Control: no-cache, must-revalidate" );
		header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" );

		// Determine which reports we want. Different logic based on the current phase.
		// @todo

		fputcsv( $output, [ 'Event Name', 'Admin URL', 'Status', 'Has Error' ], $delimiter );
		foreach ( $reports as $report ) {
			$message   = str_replace( [ "\n", "\t" ], " ", strip_tags( $report->error ) );
			$has_error = ! $report->error ? "No" : "Yes";

			$item = [
				$report->source_event_post->post_title,
				get_edit_post_link( $report->source_event_post->ID, 'url' ),
				$message,
				$has_error
			];

			fputcsv( $output, $item, $delimiter );
		}
		fclose( $output );
		exit;
	}

}