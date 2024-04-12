<?php
namespace Tribe\Events\Importer;

use org\bovigo\vfs\vfsStream;
use Tribe__Events__Importer__File_Importer_Venues as Venues_Importer;
use function tad\WPBrowser\renderString;

class File_Importer_VenuesTest extends \Codeception\TestCase\WPTestCase {

	protected $field_map = [
		'venue_name',
		'venue_country',
		'venue_address',
		'venue_address2',
		'venue_city',
		'venue_state',
		'venue_zip',
		'venue_phone',
	];

	/**
	 * @var \Tribe__Events__Importer__File_Reader
	 */
	protected $file_reader;

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
	protected $template = 'venues';

	/**
	 * @var \Tribe__Events__Importer__Featured_Image_Uploader
	 */
	protected $featured_image_uploader;

	public function setUp(): void {
		// before
		parent::setUp();

		// your set up methods here
		$this->featured_image_uploader = $this->prophesize( 'Tribe__Events__Importer__Featured_Image_Uploader' );
	}

	public function tearDown(): void {
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

		$sut = new Venues_Importer( $this->file_reader, $this->featured_image_uploader->reveal() );
		$sut->set_map( $this->field_map );
		$sut->set_type( 'venues' );

		return $sut;
	}

	/**
	 * @param $template_dir
	 */
	protected function setup_file( $template_dir ) {
		if ( ! empty( $template_dir ) ) {
			$template_dir           = trim( $template_dir, '\\/' );
			$template_name          = trim( $this->template, '\\/' );
			$template_file          = codecept_data_dir( "csv-import-test-files/{$template_dir}/{$template_name}.handlebars" );
			$template_file_contents = file_get_contents( $template_file );

			if ( false === $template_file_contents ) {
				throw new \RuntimeException( "Could not read {$template_file} contents." );
			}

			$this->rendered_file_contents = renderString( $template_file_contents, $this->data );
			vfsStream::setup( 'csv_file_root', null, [ 'venues.csv' => $this->rendered_file_contents ] );
			$this->file_reader = new \Tribe__Events__Importer__File_Reader( vfsStream::url( 'csv_file_root/venues.csv' ) );
		} else {
			$this->file_reader = new \Tribe__Events__Importer__File_Reader( codecept_data_dir( 'csv-import-test-files/venues.csv' ) );
		}
		$this->file_reader->set_row( 1 );
	}

}
