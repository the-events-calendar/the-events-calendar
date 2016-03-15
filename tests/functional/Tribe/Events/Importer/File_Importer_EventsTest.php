<?php
namespace Tribe\Events\Importer;

use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;
use org\bovigo\vfs\vfsStream;
use Tribe__Events__Importer__File_Importer_Events as Events_Importer;

class File_Importer_EventsTest extends \Codeception\TestCase\WPTestCase {

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

	/**
	 * @test
	 * it should not mark record as invalid if featured image entry is missing
	 */
	public function it_should_not_mark_record_as_invalid_if_featured_image_entry_is_missing() {
		$sut = $this->make_instance( 'featured-image' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
	}

	/**
	 * @test
	 * it should import and attach featured image if featured image is ok
	 */
	public function it_should_import_and_attach_featured_image_if_featured_image_is_ok() {
		$image_url     = plugins_url( '_data/csv-import-test-files/featured-image/images/featured-image.jpg', codecept_data_dir() );
		$attachment_id = $this->factory()->attachment->create_upload_object( $image_url );
		$this->featured_image_uploader->upload_and_get_attachment()->willReturn( $attachment_id );

		$sut = $this->make_instance( 'featured-image' );

		$post_id = $sut->import_next_row();

		$this->assertEquals( $attachment_id, get_post_thumbnail_id( $post_id ) );
	}

	/**
	 * @test
	 * it should not import and attach featured image if featured image is not ok
	 */
	public function it_should_not_import_and_attach_featured_image_if_featured_image_is_not_ok() {
		$this->featured_image_uploader->upload_and_get_attachment()->willReturn( false );

		$sut = $this->make_instance( 'featured-image' );

		$post_id = $sut->import_next_row();

		$has_thumbnail = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) );
		$this->assertFalse( $has_thumbnail );
	}

	private function make_instance( $template_dir = null ) {
		$this->setup_file( $template_dir );

		$sut = new Events_Importer( $this->file_reader, $this->featured_image_uploader->reveal() );
		$sut->set_map( [
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
		] );
		$sut->set_type( 'events' );

		return $sut;
	}

	private function setup_file( $template_dir = null ) {
		if ( ! empty( $template_dir ) ) {
			$this->handlebars->setLoader( new FilesystemLoader( codecept_data_dir( trailingslashit( 'csv-import-test-files' ) . $template_dir ) ) );
			$this->rendered_file_contents = $this->handlebars->loadTemplate( $this->template )->render( $this->data );
			vfsStream::setup( 'csv_file_root', null, [ 'events.csv' => $this->rendered_file_contents ] );
			$this->file_reader = new \Tribe__Events__Importer__File_Reader( vfsStream::url( 'csv_file_root/events.csv' ) );
		} else {
			$this->file_reader = new \Tribe__Events__Importer__File_Reader( codecept_data_dir( 'csv-import-test-files/events.csv' ) );
		}
		$this->file_reader->set_row( 1 );
	}

}