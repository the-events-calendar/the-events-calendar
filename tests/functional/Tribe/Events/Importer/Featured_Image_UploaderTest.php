<?php
namespace Tribe\Events\Importer;

use Tribe__Events__Importer__Featured_Image_Uploader as Featured_Image_Uploader;

class Featured_Image_UploaderTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
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
		$this->assertInstanceOf( 'Tribe__Events__Importer__Featured_Image_Uploader', new Featured_Image_Uploader() );
	}

	/**
	 * @test
	 * it should return false if record does not contain featured image
	 */
	public function it_should_return_false_if_record_does_not_contain_featured_image() {
		$sut = new Featured_Image_Uploader( 'some_value' );

		$out = $sut->upload_and_get_attachment();

		$this->assertFalse( $out );
	}

	/**
	 * @test
	 * it should return false when trying to upload non ID and non URL
	 */
	public function it_should_return_false_when_trying_to_upload_non_id_and_non_url() {
		$image_url = 'redneck url';

		$sut = new Featured_Image_Uploader( $image_url );
		$id  = $sut->upload_and_get_attachment();

		$this->assertFalse( $id );
	}

	/**
	 * @test
	 * it should return false when trying to upload non int ID
	 */
	public function it_should_return_false_when_trying_to_upload_non_int_id() {
		$image_url = 33.2;

		$sut = new Featured_Image_Uploader( $image_url );
		$id  = $sut->upload_and_get_attachment();

		$this->assertFalse( $id );
	}

	/**
	 * @test
	 * it should return false when trying to upload non existing URL
	 */
	public function it_should_return_false_when_trying_to_upload_non_existing_url() {
		$image_url = plugins_url( '_data/csv-import-test-files/featured-image/images/non-existing.jpg', codecept_data_dir() );

		$sut = new Featured_Image_Uploader( $image_url );
		$id  = $sut->upload_and_get_attachment();

		$this->assertFalse( $id );
	}

	/**
	 * @test
	 * it should return false when trying to upload non supported file type
	 */
	public function it_should_return_false_when_trying_to_upload_non_supported_file_type() {
		$image_url = plugins_url( '_data/csv-import-test-files/featured-image/images/featured-image.raw', codecept_data_dir() );

		$sut = new Featured_Image_Uploader( $image_url );
		$id  = $sut->upload_and_get_attachment();

		$this->assertFalse( $id );
	}

	/**
	 * @test
	 * it should return false when trying to upload non existing attachment ID
	 */
	public function it_should_return_false_when_trying_to_upload_non_existing_attachment_id() {
		$image_url = $this->factory()->post->create();

		$sut = new Featured_Image_Uploader( $image_url );
		$id  = $sut->upload_and_get_attachment();

		$this->assertFalse( $id );
	}

	/**
	 * @test
	 * it should return attachment ID when uploading existing image URL
	 */
	public function it_should_return_attachment_id_when_uploading_existing_image_url() {
		$image_url = plugins_url( '_data/csv-import-test-files/featured-image/images/featured-image.jpg', codecept_data_dir() );

		$sut = new Featured_Image_Uploader( $image_url );
		$id  = $sut->upload_and_get_attachment();

		$this->assertNotFalse( $id );
		$this->assertEquals( 'attachment', get_post( $id )->post_type );
	}

	/**
	 * @test
	 * it should return attachment ID when uploading existing attachment ID
	 */
	public function it_should_return_attachment_id_when_uploading_existing_attachment_id() {
		$image_url              = plugins_url( '_data/csv-import-test-files/featured-image/images/featured-image.jpg', codecept_data_dir() );
		$existing_attachment_id = $this->factory()->attachment->create_upload_object( $image_url );

		$sut = new Featured_Image_Uploader( $existing_attachment_id );
		$id  = $sut->upload_and_get_attachment();

		$this->assertNotFalse( $id );
		$this->assertEquals( $existing_attachment_id, $id );
	}


}