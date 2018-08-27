<?php
namespace Tribe\Events\Importer;

require_once 'functions.php';
require_once 'File_Importer_EventsTest.php';

class File_Importer_Events_FeaturedImageTest extends File_Importer_EventsTest {

	/**
	 * @test
	 * it should not mark record as invalid if featured image entry is missing
	 */
	public function it_should_not_mark_record_as_invalid_if_featured_image_entry_is_missing() {
		$sut = $this->make_instance( 'featured-image' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
	}
}
