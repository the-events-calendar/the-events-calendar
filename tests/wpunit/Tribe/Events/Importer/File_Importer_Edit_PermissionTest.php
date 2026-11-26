<?php

namespace Tribe\Events\Importer;

use org\bovigo\vfs\vfsStream;
use Tribe__Events__Aggregator__Record__Activity as Activity;
use Tribe__Events__Aggregator__Record__CSV as CSV_Record;
use Tribe__Events__Aggregator__Records as Records;
use Tribe__Events__Importer__File_Importer as File_Importer;
use Tribe__Events__Importer__File_Importer_Events as Events_Importer;
use Tribe__Events__Importer__File_Importer_Organizers as Organizers_Importer;
use Tribe__Events__Importer__File_Importer_Venues as Venues_Importer;
use Tribe__Events__Importer__File_Reader as File_Reader;

/**
 * A CSV row that matches an existing post updates it only when the user running the import can edit that post.
 *
 * @group csv
 */
class File_Importer_Edit_PermissionTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var int
	 */
	protected $admin_id;

	/**
	 * @var int
	 */
	protected $author_id;

	/**
	 * @var \Prophecy\Prophecy\ObjectProphecy
	 */
	protected $featured_image_uploader;

	/**
	 * @var int
	 */
	protected $csv_files = 0;

	public function setUp() {
		parent::setUp();

		$this->featured_image_uploader = $this->prophesize( 'Tribe__Events__Importer__Featured_Image_Uploader' );
		$this->admin_id                = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		$this->author_id               = $this->factory()->user->create( [ 'role' => 'author' ] );
	}

	/**
	 * @test
	 */
	public function it_should_not_update_a_venue_matched_by_id_the_user_cannot_edit() {
		wp_set_current_user( $this->admin_id );
		$venue_id = $this->import_venue( 'Original Venue', 'Original address' );

		wp_set_current_user( $this->author_id );
		$importer = $this->make_venues_importer( [ [ $venue_id, 'Changed address' ] ] );

		$this->assertFalse( $importer->import_next_row() );
		$this->assertEquals( 'Original address', get_post_meta( $venue_id, '_VenueAddress', true ) );
		$this->assertEquals( 'Original Venue', get_the_title( $venue_id ) );
		$this->assertEquals( 0, $importer->get_updated_post_count() );
		$this->assertEquals( 1, $importer->get_skipped_row_count() );
	}

	/**
	 * @test
	 */
	public function it_should_not_update_a_venue_matched_by_title_the_user_cannot_edit() {
		wp_set_current_user( $this->admin_id );
		$venue_id = $this->import_venue( 'Original Venue', 'Original address' );

		wp_set_current_user( $this->author_id );
		$importer = $this->make_venues_importer( [ [ 'Original Venue', 'Changed address' ] ] );

		$this->assertFalse( $importer->import_next_row() );
		$this->assertEquals( 'Original address', get_post_meta( $venue_id, '_VenueAddress', true ) );
		$this->assertEquals( 0, $importer->get_new_post_count() );
		$this->assertEquals( 1, $importer->get_skipped_row_count() );
		/* A title match must not tell the user the ID of a post they cannot edit. */
		$this->assertStringNotContainsString( "{$venue_id}", implode( ' ', $importer->get_log_messages() ) );
	}

	/**
	 * @test
	 */
	public function it_should_update_a_venue_the_user_owns() {
		wp_set_current_user( $this->author_id );
		$venue_id = $this->import_venue( 'Original Venue', 'Original address' );

		$importer = $this->make_venues_importer( [ [ $venue_id, 'Changed address' ] ] );

		$this->assertEquals( $venue_id, $importer->import_next_row() );
		$this->assertEquals( 'Changed address', get_post_meta( $venue_id, '_VenueAddress', true ) );
		$this->assertEquals( 1, $importer->get_updated_post_count() );
	}

	/**
	 * @test
	 */
	public function it_should_update_another_users_venue_when_the_user_can_edit_it() {
		wp_set_current_user( $this->author_id );
		$venue_id = $this->import_venue( 'Original Venue', 'Original address' );

		wp_set_current_user( $this->admin_id );
		$importer = $this->make_venues_importer( [ [ $venue_id, 'Changed address' ] ] );

		$this->assertEquals( $venue_id, $importer->import_next_row() );
		$this->assertEquals( 'Changed address', get_post_meta( $venue_id, '_VenueAddress', true ) );
	}

	/**
	 * @test
	 */
	public function it_should_not_update_an_organizer_matched_by_id_the_user_cannot_edit() {
		wp_set_current_user( $this->admin_id );
		$seed = $this->make_organizers_importer( [ [ 'Original Organizer', 'Original phone' ] ] );
		$organizer_id = $seed->import_next_row();

		wp_set_current_user( $this->author_id );
		$importer = $this->make_organizers_importer( [ [ $organizer_id, 'Changed phone' ] ] );

		$this->assertFalse( $importer->import_next_row() );
		$this->assertEquals( 'Original phone', get_post_meta( $organizer_id, '_OrganizerPhone', true ) );
		$this->assertEquals( 'Original Organizer', get_the_title( $organizer_id ) );
		$this->assertEquals( 1, $importer->get_skipped_row_count() );
	}

	/**
	 * @test
	 */
	public function it_should_not_update_an_event_the_user_cannot_edit() {
		$row = [ 'Original Event', '2030-01-15', '10:00', '2030-01-15', '12:00', 'Original description' ];

		wp_set_current_user( $this->admin_id );
		$event_id = $this->make_events_importer( [ $row ] )->import_next_row();

		wp_set_current_user( $this->author_id );
		$row[5]   = 'Changed description';
		$importer = $this->make_events_importer( [ $row ] );

		$this->assertFalse( $importer->import_next_row() );
		$this->assertEquals( 'Original description', get_post( $event_id )->post_content );
		$this->assertEquals( 1, $importer->get_skipped_row_count() );
	}

	/**
	 * Background batches of a CSV import run with no logged-in user, so they act as the user who started the import.
	 *
	 * @test
	 */
	public function it_should_check_the_import_record_author_when_no_user_is_logged_in() {
		wp_set_current_user( $this->admin_id );
		$venue_id = $this->import_venue( 'Original Venue', 'Original address' );

		wp_set_current_user( 0 );
		$denied = $this->make_venues_importer( [ [ $venue_id, 'Changed by the author' ] ] );
		$this->attach_record( $denied, $this->author_id );

		$this->assertFalse( $denied->import_next_row() );
		$this->assertEquals( 'Original address', get_post_meta( $venue_id, '_VenueAddress', true ) );

		$allowed = $this->make_venues_importer( [ [ $venue_id, 'Changed by the admin' ] ] );
		$this->attach_record( $allowed, $this->admin_id );

		$this->assertEquals( $venue_id, $allowed->import_next_row() );
		$this->assertEquals( 'Changed by the admin', get_post_meta( $venue_id, '_VenueAddress', true ) );
	}

	/**
	 * A batch processed while someone else is logged in still acts as the user who started the import.
	 *
	 * @test
	 */
	public function it_should_check_the_import_record_author_over_the_logged_in_user() {
		wp_set_current_user( $this->admin_id );
		$venue_id = $this->import_venue( 'Original Venue', 'Original address' );

		$denied = $this->make_venues_importer( [ [ $venue_id, 'Changed by the author' ] ] );
		$this->attach_record( $denied, $this->author_id );

		$this->assertFalse( $denied->import_next_row() );
		$this->assertEquals( 'Original address', get_post_meta( $venue_id, '_VenueAddress', true ) );

		wp_set_current_user( $this->author_id );
		$allowed = $this->make_venues_importer( [ [ $venue_id, 'Changed by the admin' ] ] );
		$this->attach_record( $allowed, $this->admin_id );

		$this->assertEquals( $venue_id, $allowed->import_next_row() );
		$this->assertEquals( 'Changed by the admin', get_post_meta( $venue_id, '_VenueAddress', true ) );
	}

	/**
	 * The ticket importers report a match as `true`, which names no post to check.
	 *
	 * @test
	 */
	public function it_should_pass_a_match_that_names_no_post_to_the_importer() {
		wp_set_current_user( $this->author_id );

		$importer = new class( $this->make_reader( [ 'Ticket Name' ], [ [ 'General admission' ] ] ) ) extends File_Importer {
			/**
			 * @var array<array<string>> The records `update_post()` received.
			 */
			public $updated_records = [];

			protected function match_existing_post( array $record ) {
				return true;
			}

			protected function update_post( $post_id, array $record ) {
				$this->updated_records[] = $record;
			}

			protected function create_post( array $record ) {
				return 0;
			}
		};
		$importer->set_map( [ 'ticket_name' ] );

		$this->assertTrue( $importer->import_next_row() );
		$this->assertEquals( [ [ 'General admission' ] ], $importer->updated_records );
		$this->assertEquals( 0, $importer->get_skipped_row_count() );
	}

	/**
	 * The venue is owned by the current user, which is what each test's permission check turns on.
	 *
	 * @param string $name    The venue name.
	 * @param string $address The venue address.
	 *
	 * @return int The ID of the created venue.
	 */
	protected function import_venue( string $name, string $address ): int {
		$venue_id = $this->make_venues_importer( [ [ $name, $address ] ] )->import_next_row();

		$this->assertEquals( $address, get_post_meta( $venue_id, '_VenueAddress', true ) );

		return $venue_id;
	}

	/**
	 * @param array<array<int|string>> $rows The CSV rows below the header, each a venue name or ID and an address.
	 *
	 * @return Venues_Importer The importer, positioned on the first row.
	 */
	protected function make_venues_importer( array $rows ): Venues_Importer {
		$importer = new Venues_Importer( $this->make_reader( [ 'Venue Name', 'Venue Address' ], $rows ), $this->featured_image_uploader->reveal() );
		$importer->set_map( [ 'venue_name', 'venue_address' ] );
		$importer->set_type( 'venues' );

		return $importer;
	}

	/**
	 * @param array<array<int|string>> $rows The CSV rows below the header, each an organizer name or ID and a phone.
	 *
	 * @return Organizers_Importer The importer, positioned on the first row.
	 */
	protected function make_organizers_importer( array $rows ): Organizers_Importer {
		$importer = new Organizers_Importer( $this->make_reader( [ 'Organizer Name', 'Organizer Phone' ], $rows ), $this->featured_image_uploader->reveal() );
		$importer->set_map( [ 'organizer_name', 'organizer_phone' ] );
		$importer->set_type( 'organizers' );

		return $importer;
	}

	/**
	 * @param array<array<string>> $rows The CSV rows below the header, each a name, start date and time, end date
	 *                                   and time, and description.
	 *
	 * @return Events_Importer The importer, positioned on the first row.
	 */
	protected function make_events_importer( array $rows ): Events_Importer {
		$map    = [ 'event_name', 'event_start_date', 'event_start_time', 'event_end_date', 'event_end_time', 'event_description' ];
		$header = [ 'Event Name', 'Event Start Date', 'Event Start Time', 'Event End Date', 'Event End Time', 'Event Description' ];

		$importer = new Events_Importer( $this->make_reader( $header, $rows ), $this->featured_image_uploader->reveal() );
		$importer->set_map( $map );
		$importer->set_type( 'events' );

		return $importer;
	}

	/**
	 * Writes the rows to an in-memory CSV file and returns a reader positioned past its header.
	 *
	 * @param array<string>        $header The CSV header.
	 * @param array<array<int|string>> $rows   The CSV rows.
	 *
	 * @return File_Reader The reader.
	 */
	protected function make_reader( array $header, array $rows ): File_Reader {
		$handle = fopen( 'php://temp', 'r+' );
		fputcsv( $handle, $header );
		foreach ( $rows as $row ) {
			fputcsv( $handle, $row );
		}
		rewind( $handle );
		$contents = stream_get_contents( $handle );
		fclose( $handle );

		$file = 'import-' . ++$this->csv_files . '.csv';
		vfsStream::setup( 'csv_file_root', null, [ $file => $contents ] );

		$reader = new File_Reader( vfsStream::url( "csv_file_root/{$file}" ) );
		$reader->set_row( 1 );

		return $reader;
	}

	/**
	 * Attaches an import record created by the given user, as a background batch of an import carries.
	 *
	 * @param File_Importer $importer  The importer.
	 * @param int           $author_id The ID of the user who created the import record.
	 *
	 * @return void
	 */
	protected function attach_record( File_Importer $importer, int $author_id ): void {
		$record                   = new CSV_Record();
		$record->post             = $this->factory()->post->create_and_get(
			[
				'post_type'   => Records::$post_type,
				'post_author' => $author_id,
			]
		);
		$record->meta['activity'] = new Activity();

		$importer->is_aggregator     = true;
		$importer->aggregator_record = $record;
	}
}
