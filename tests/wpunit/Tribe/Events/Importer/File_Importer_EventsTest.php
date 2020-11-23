<?php
namespace Tribe\Events\Importer;

use Handlebars\Handlebars;
use org\bovigo\vfs\vfsStream;
use Tribe\Events\Test\Factories\Event;
use Tribe__Events__Importer__File_Importer_Events as Events_Importer;
use function tad\WPBrowser\renderString;

class File_Importer_EventsTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var \Tribe__Events__Importer__Featured_Image_Uploader
	 */
	protected $featured_image_uploader;

	protected $field_map = [
		'event_name',
		'event_description',
		'event_start_date',
		'event_start_time',
		'event_end_date',
		'event_end_time',
		'event_all_day',
		'event_venue_name',
		'event_organizer_name',
		'event_show_map_link',
		'event_show_map',
		'event_cost',
		'event_category',
		'event_website',
	];

	/**
	 * @var \Tribe__Events__Importer__File_Reader
	 */
	protected $file_reader;

	/**
	 * @var Handlebars
	 */
	protected $handlebars;

	/**
	 * @var array
	 */
	protected $data = [ ];

	/**
	 * @var string
	 */
	protected $rendered_file_contents;

	/**
	 * @var string
	 */
	protected $template = 'events';

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->handlebars              = new Handlebars();
		$this->featured_image_uploader = $this->prophesize( 'Tribe__Events__Importer__Featured_Image_Uploader' );
		$this->factory()->event = new Event();
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();
	}

	protected function make_instance( $template_dir = null ) {
		$this->setup_file( $template_dir );

		$sut = new Events_Importer( $this->file_reader, $this->featured_image_uploader->reveal() );
		$sut->set_map( $this->field_map );
		$sut->set_type( 'events' );

		return $sut;
	}

	protected function setup_file( $template_dir = null ) {
		if ( ! empty( $template_dir ) ) {
			$template_dir      = trim( $template_dir, '\\/' );
			$template_name     = trim( $this->template, '\\/' );
			$csv_file_template = codecept_data_dir( "csv-import-test-files/{$template_dir}/{$template_name}.handlebars" );

			if ( ! is_file( $csv_file_template ) ) {
				throw new \InvalidArgumentException( "Template file {$csv_file_template} does not exist or is not accessible." );
			}

			$template_contents = file_get_contents( $csv_file_template );

			if ( false === $template_contents ) {
				throw new \RuntimeException( "Could not read contents of {$csv_file_template}." );
			}

			$this->rendered_file_contents = renderString( $template_contents, $this->data );
			vfsStream::setup( 'csv_file_root', null, [ 'events.csv' => $this->rendered_file_contents ] );
			$this->file_reader = new \Tribe__Events__Importer__File_Reader( vfsStream::url( 'csv_file_root/events.csv' ) );
		} else {
			$this->file_reader = new \Tribe__Events__Importer__File_Reader( codecept_data_dir( 'csv-import-test-files/events.csv' ) );
		}
		$this->file_reader->set_row( 1 );
	}

	/**
	 * It should match existing events no matter the record and proposed status
	 *
	 * @test
	 */
	public function should_match_existing_events_no_matter_the_record_and_proposed_status() {
		$open_importer = $this->open_and_build_importer();

		$events = array_reduce( [ 'publish', 'draft', 'private' ], function ( array $acc, $status ) {
			$acc[] = $this->factory()->event->create( [ 'post_status' => $status ] );

			return $acc;
		}, [] );

		$ev0_start_frags = explode( ' ', get_post_meta( $events[0], '_EventStartDate', true ) );
		$ev0_end_frags   = explode( ' ', get_post_meta( $events[0], '_EventEndDate', true ) );

		$this->assertEquals( $events[0], $open_importer->match_existing_post( [
			get_post( $events[0] )->post_title,
			$ev0_start_frags[0],
			$ev0_start_frags[1],
			$ev0_end_frags[0],
			$ev0_end_frags[1],
		] ) );

		$ev1_start_frags = explode( ' ', get_post_meta( $events[1], '_EventStartDate', true ) );
		$ev1_end_frags   = explode( ' ', get_post_meta( $events[1], '_EventEndDate', true ) );

		$this->assertEquals( $events[1], $open_importer->match_existing_post( [
			get_post( $events[1] )->post_title,
			$ev1_start_frags[0],
			$ev1_start_frags[1],
			$ev1_end_frags[0],
			$ev1_end_frags[1],
		] ) );

		$ev2_start_frags = explode( ' ', get_post_meta( $events[2], '_EventStartDate', true ) );
		$ev2_end_frags   = explode( ' ', get_post_meta( $events[2], '_EventEndDate', true ) );

		$this->assertEquals( $events[2], $open_importer->match_existing_post( [
			get_post( $events[2] )->post_title,
			$ev2_start_frags[0],
			$ev2_start_frags[1],
			$ev2_end_frags[0],
			$ev2_end_frags[1],
		] ) );
	}

	/**
	 * It should match existing events no matter the date
	 *
	 * @test
	 */
	public function should_match_existing_events_no_matter_the_date() {
		$open_importer = $this->open_and_build_importer();

		$events = array_reduce( [ '-5 years', '-2 years', '-1 week' ], function ( array $acc, $event_date ) {
			$acc[] = $this->factory()->event->create( ['when' => $event_date] );

			return $acc;
		}, [] );

		$ev0_start_frags = explode( ' ', get_post_meta( $events[0], '_EventStartDate', true ) );
		$ev0_end_frags   = explode( ' ', get_post_meta( $events[0], '_EventEndDate', true ) );

		$this->assertEquals( $events[0], $open_importer->match_existing_post( [
			get_post( $events[0] )->post_title,
			$ev0_start_frags[0],
			$ev0_start_frags[1],
			$ev0_end_frags[0],
			$ev0_end_frags[1],
		] ) );

		$ev1_start_frags = explode( ' ', get_post_meta( $events[1], '_EventStartDate', true ) );
		$ev1_end_frags   = explode( ' ', get_post_meta( $events[1], '_EventEndDate', true ) );

		$this->assertEquals( $events[1], $open_importer->match_existing_post( [
			get_post( $events[1] )->post_title,
			$ev1_start_frags[0],
			$ev1_start_frags[1],
			$ev1_end_frags[0],
			$ev1_end_frags[1],
		] ) );

		$ev2_start_frags = explode( ' ', get_post_meta( $events[2], '_EventStartDate', true ) );
		$ev2_end_frags   = explode( ' ', get_post_meta( $events[2], '_EventEndDate', true ) );

		$this->assertEquals( $events[2], $open_importer->match_existing_post( [
			get_post( $events[2] )->post_title,
			$ev2_start_frags[0],
			$ev2_start_frags[1],
			$ev2_end_frags[0],
			$ev2_end_frags[1],
		] ) );
	}

	/**
	 * Creates an open version of the importer in terms of method visibility.
	 *
	 * @return Events_Importer
	 */
	protected function open_and_build_importer(): Events_Importer {
		$open_class = new class extends Events_Importer {
			public function __construct() {
			}

			public function match_existing_post( array $record ) {
				return parent::match_existing_post( $record );
			}
		};
		/** @var Events_Importer $open_importer */
		$open_importer = new $open_class;
		$open_importer->set_map( [
			'event_name',
			'event_start_date',
			'event_start_time',
			'event_end_date',
			'event_end_time',
		] );

		return $open_importer;
	}
}
