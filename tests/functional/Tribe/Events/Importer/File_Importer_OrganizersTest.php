<?php
namespace Tribe\Events\Importer;

use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;
use org\bovigo\vfs\vfsStream;
use Tribe__Events__Importer__File_Importer_Organizers as Organizers_Importer;

class File_Importer_OrganizersTest extends \Codeception\TestCase\WPTestCase {

	protected $field_map = [
		'organizer_name',
		'organizer_email',
		'organizer_website',
		'organizer_phone'
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
	protected $template = 'organizers';

	/**
	 * @var \Tribe__Events__Importer__Featured_Image_Uploader
	 */
	protected $featured_image_uploader;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->handlebars              = new Handlebars();
		$this->featured_image_uploader = $this->prophesize( 'Tribe__Events__Importer__Featured_Image_Uploader' );
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

		$sut = new Organizers_Importer( $this->file_reader, $this->featured_image_uploader->reveal() );
		$sut->set_map( $this->field_map );
		$sut->set_type( 'organizers' );

		return $sut;
	}

	/**
	 * @param $template_dir
	 */
	protected function setup_file( $template_dir ) {
		if ( ! empty( $template_dir ) ) {
			$this->handlebars->setLoader( new FilesystemLoader( codecept_data_dir( 'csv-import-test-files/' . $template_dir ) ) );
			$this->rendered_file_contents = $this->handlebars->loadTemplate( $this->template )->render( $this->data );
			vfsStream::setup( 'csv_file_root', null, [ 'organizers.csv' => $this->rendered_file_contents ] );
			$this->file_reader = new \Tribe__Events__Importer__File_Reader( vfsStream::url( 'csv_file_root/organizers.csv' ) );
		} else {
			$this->file_reader = new \Tribe__Events__Importer__File_Reader( codecept_data_dir( 'csv-import-test-files/organizers.csv' ) );
		}
		$this->file_reader->set_row( 1 );
	}

}