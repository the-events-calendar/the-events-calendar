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
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
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

		// Determine which reports we want. Different logic based on the current phase.
		$site_report = Site_Report::build();
		$state       = tribe( State::class );
		switch ( $state->get_phase() ) {
			case State::PHASE_MIGRATION_FAILURE_COMPLETE:
				$reports = $site_report->get_event_reports( - 1, 9999, Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE );
				break;
			default:
				$reports = $site_report->get_event_reports();;
				break;
		}

		$delimiter = ',';
		$output    = fopen( 'php://output', 'w' );
		$charset   = get_option( 'blog_charset' );

		header( "Content-Type: text/csv; charset=$charset;" );
		header( 'Content-Disposition: attachment; filename="migration_event_report.csv"' );
		header( "Cache-Control: no-cache, must-revalidate" );
		header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" );

		fputcsv( $output, [ 'Event Name', 'Admin URL', 'Status', 'Has Error' ], $delimiter );
		foreach ( $reports as $report ) {
			$has_error = (bool) $report->error;
			if ( $has_error ) {
				$message = str_replace( [ "\n", "\t" ], " ", strip_tags( $report->error ) );
			} else {
				$message = $report->get_migration_strategy_text();
			}

			$item = [
				$report->source_event_post->post_title,
				get_edit_post_link( $report->source_event_post->ID, 'url' ),
				$message,
				$has_error ? "Yes" : "No"
			];

			fputcsv( $output, $item, $delimiter );
		}
		fclose( $output );
		exit;
	}

	/**
	 * @since TBD
	 *
	 * @return string The admin url to the file.
	 */
	public static function get_download_url() {
		return admin_url( "?noheader=1&page=" . self::DOWNLOAD_SLUG );
	}

}