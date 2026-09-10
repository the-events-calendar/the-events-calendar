<?php

namespace Tribe\Events\Aggregator\Record;

use Tribe\Events\Test\Testcases\Events_TestCase;
use Tribe__Events__Aggregator__Record__CSV as CSV_Record;

/**
 * Regression tests for `Tribe__Events__Aggregator__Record__CSV::prep_import_data()`.
 *
 * `prep_import_data()` persists the submitted column mapping to
 * `tribe_events_import_column_mapping_{content_type}` before validating that all
 * required fields are mapped. A rejected submission (missing a required field) used
 * to leave that bad mapping saved as the site-wide default, so the very next preview
 * would pre-select "Do Not Import" for the required column and fail again - forever,
 * for every content type (events, venues, organizers, rsvp_tickets), until someone
 * manually corrected the option.
 *
 * These tests cover the V1 "events" content type specifically, to confirm the fix
 * (roll back to the previous mapping on validation failure) does not change
 * behaviour for existing event/venue/organizer imports - only RSVP was reported
 * broken, but the fix lives in the shared method all content types go through.
 *
 * @group aggregator
 * @group csv
 */
class CSV_Prep_Import_Data_Test extends Events_TestCase {

	private $option_key = 'tribe_events_import_column_mapping_events';

	/** @var string|null Path of a copy of the fixture placed inside uploads/; cleaned up in tearDown. */
	private $uploads_copy;

	public function tearDown() {
		delete_option( $this->option_key );
		if ( $this->uploads_copy && file_exists( $this->uploads_copy ) ) {
			@unlink( $this->uploads_copy );
		}
		parent::tearDown();
	}

	/**
	 * Builds a finalized CSV record pointed at a copy of the bundled events.csv fixture
	 * placed inside the uploads directory - get_file_path() only accepts files there -
	 * without persisting the record itself, since prep_import_data() only reads the
	 * in-memory $meta.
	 */
	private function make_record(): CSV_Record {
		$upload_info        = wp_upload_dir();
		$this->uploads_copy = trailingslashit( $upload_info['basedir'] ) . 'tec-prep-import-data-test-events.csv';
		copy( codecept_data_dir( 'csv-import-test-files/events.csv' ), $this->uploads_copy );

		$record                        = new CSV_Record();
		$record->meta['finalized']     = true;
		$record->meta['content_type']  = 'tribe_events';
		$record->meta['file']          = $this->uploads_copy;

		return $record;
	}

	/**
	 * @test
	 */
	public function it_should_accept_a_valid_events_column_map() {
		$record = $this->make_record();

		$data = [
			'origin'     => 'csv',
			'column_map' => [ 'event_name', 'event_description', 'event_start_date', 'event_start_time', 'event_end_date', 'event_end_time' ],
		];

		$importer = $record->prep_import_data( $data );

		$this->assertInstanceOf( \Tribe__Events__Importer__File_Importer_Events::class, $importer );
		$this->assertEquals( $data['column_map'], get_option( $this->option_key ) );
	}

	/**
	 * @test
	 */
	public function it_should_reject_an_events_column_map_missing_a_required_field() {
		$record = $this->make_record();

		// event_start_date is required but not mapped.
		$data = [
			'origin'     => 'csv',
			'column_map' => [ 'event_name', 'event_description' ],
		];

		$result = $record->prep_import_data( $data );

		$this->assertTrue( tribe_is_error( $result ), 'A column map missing a required field must return an error.' );
	}

	/**
	 * @test
	 */
	public function it_should_not_corrupt_the_saved_mapping_when_validation_fails() {
		$good_map = [ 'event_name', 'event_description', 'event_start_date', 'event_start_time', 'event_end_date', 'event_end_time' ];
		update_option( $this->option_key, $good_map );

		$record = $this->make_record();

		// event_start_date is dropped here - this submission must be rejected.
		$bad_map = [ 'event_name', 'event_description' ];
		$result  = $record->prep_import_data( [ 'origin' => 'csv', 'column_map' => $bad_map ] );

		$this->assertTrue( tribe_is_error( $result ) );
		$this->assertEquals(
			$good_map,
			get_option( $this->option_key ),
			'A rejected column map must not overwrite the last known-good saved mapping.'
		);
	}
}
