<?php

namespace Tribe\Events\Importer;

require_once 'functions.php';
require_once 'File_Importer_OrganizersTest.php';

class File_Importer_Organizers_FeaturedImageTest extends File_Importer_OrganizersTest {

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
		$image_url     = get_image_url();
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
}