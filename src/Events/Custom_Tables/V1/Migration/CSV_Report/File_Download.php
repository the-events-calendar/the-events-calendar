<?php
/**
 * Handles the creation and download of a CSV file of the migration report.
 *
 * @since   6.0.0
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
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\CSV_Report;
 */
class File_Download {
	/**
	 * @var string The page slug.
	 */
	const DOWNLOAD_SLUG = 'ct1-migration-report-download';

	/**
	 * @var string The query var to check for our page slug.
	 */
	const DOWNLOAD_QUERY_PARAM = 'action';

	/**
	 * @var array The list of columns that are output.
	 */
	const CSV_COLUMNS = [ 'Event Name', 'Admin URL', 'Status', 'Has Error' ];

	/**
	 * Get the download URL string.
	 *
	 * @since 6.0.0
	 *
	 * @return string|void
	 */
	public static function get_download_url() {
		return admin_url( "?" . self::DOWNLOAD_QUERY_PARAM . "=" . urlencode( self::DOWNLOAD_SLUG ) . '&wpnonce=' . wp_create_nonce() );
	}

	/**
	 * Whether this is a legitimate download request.
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
	 *
	 * @param bool $should_exit Whether the downloader should exit automatically or continue.
	 *
	 * @return false|void
	 */
	public function download_csv( $should_exit = true ) {
		// Check if we are in WP-Admin
		if ( ! $this->should_download() ) {
			return false;
		}

		// Determine which reports we want. Different logic based on the current phase.
		$site_report = Site_Report::build();
		$state       = tribe( State::class );

		if ( State::PHASE_MIGRATION_FAILURE_COMPLETE === $state->get_phase() ) {
			$filter  = [ Event_Report::META_KEY_MIGRATION_PHASE => Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE ];
			$reports = $site_report->get_event_reports( - 1, 9999, $filter );
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

		fputcsv( $output, self::CSV_COLUMNS, $delimiter );
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
		if ( $should_exit ) {
			exit;
		}
	}
}