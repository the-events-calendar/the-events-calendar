<?php

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\CSV_Report\File_Download;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class Download_ReportTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;

	/**
	 * Check when you should be able to download the CSV.
	 *
	 * @test
	 */
	public function should_download_when_appropriate() {
		$downloader = tribe( File_Download::class );

		$this->assertFalse( $downloader->should_download() );

		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_PROMPT );

		$this->assertFalse( $downloader->should_download() );

		$_GET['wpnonce'] = wp_create_nonce();

		$this->assertFalse( $downloader->should_download() );

		$_GET[ File_Download::DOWNLOAD_QUERY_PARAM ] = File_Download::DOWNLOAD_SLUG;

		$this->assertTrue( $downloader->should_download() );

		unset( $_GET['wpnonce'], $_GET[ File_Download::DOWNLOAD_QUERY_PARAM ] );
	}

	/**
	 * @test
	 */
	public function should_see_event_in_csv() {
		// Setup a legit download.
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_PROMPT );
		$post         = $this->given_a_migrated_single_event();
		$event_report = new Event_Report( $post );
		$event_report->migration_success();
		$downloader                                  = tribe( File_Download::class );
		$_GET['wpnonce']                             = wp_create_nonce();
		$_GET[ File_Download::DOWNLOAD_QUERY_PARAM ] = File_Download::DOWNLOAD_SLUG;

		// Do download
		ob_start();
		$downloader->download_csv( false );
		$report = ob_get_clean();

		// Prep values for CSV format validation.
		$columns = File_Download::CSV_COLUMNS;
		$lines   = explode( "\n", $report );
		$lines   = array_filter( $lines, function ( $line ) {
			return ! empty( $line );
		} );

		// Should have our post in it.
		$this->assertContains( $post->post_title, $report );

		// Should have all the columns in it.
		foreach ( $columns as $column ) {
			$this->assertContains( $column, $report );
		}

		// Should only be one event report in it (and the columns).
		$this->assertCount( 2, $lines );
		foreach ( $lines as $csv_line ) {
			// Should be same number as our columns
			$csv_array = str_getcsv( $csv_line );
			$this->assertCount( count( $columns ), $csv_array );
		}
	}
}