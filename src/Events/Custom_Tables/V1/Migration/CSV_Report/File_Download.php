<?php
/**
 * Handles the creation and download of a CSV file of the migration report.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\CSV_Report;
 */

namespace TEC\Events\Custom_Tables\V1\Migration\CSV_Report;

use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;

/**
 * Class File_Download.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\CSV_Report;
 */
class File_Download {
	/**
	 * @var string The page slug.
	 */
	const DOWNLOAD_SLUG = 'migration-report-download';
	/**
	 * @var string The query var to check for our page slug.
	 */
	const DOWNLOAD_QUERY_PARAM = 'action';

	/**
	 * Get the download URL string.
	 *
	 * @since TBD
	 *
	 * @return string|void
	 */
	public static function get_download_url() {

		return admin_url( "?" . self::DOWNLOAD_QUERY_PARAM . "=" . self::DOWNLOAD_SLUG . '&wpnonce=' . wp_create_nonce() );
	}

	/**
	 * Whether this is a legitimate download request.
	 *
	 * @since TBD
	 *
	 * @return bool If the download should continue.
	 */
	public function should_download() {
		if ( ! isset( $_GET['wpnonce'] ) || ! isset( $_GET[ self::DOWNLOAD_QUERY_PARAM ] ) || ( $_GET[ self::DOWNLOAD_QUERY_PARAM ] !== self::DOWNLOAD_SLUG ) ) {
			return false;
		}

		return (bool) wp_verify_nonce( $_GET['wpnonce'] );
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
		if ( ! $this->should_download() ) {
			return false;
		}

		// Determine which reports we want. Different logic based on the current phase.
		$site_report = Site_Report::build();
		$state       = tribe( State::class );

		if ( State::PHASE_MIGRATION_FAILURE_COMPLETE === $state->get_phase() ) {
			$status_to_fetch = Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE;
			$reports         = $site_report->get_event_reports( - 1, 9999, $status_to_fetch );
		} else {
			$reports = $site_report->get_event_reports();
		}

		$delimiter = ',';
		$output    = fopen( 'php://output', 'wb' );
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
}