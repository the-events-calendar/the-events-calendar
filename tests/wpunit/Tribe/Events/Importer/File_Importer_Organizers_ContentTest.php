<?php

namespace Tribe\Events\Importer;
require_once 'File_Importer_OrganizersTest.php';


class File_Importer_Organizers_ContentTest extends File_Importer_OrganizersTest {

	/**
	 * @test
	 * it should not mark record as invalid if missing content
	 */
	public function it_should_not_mark_record_as_invalid_if_missing_content() {
		$this->data        = [
			'description_1' => '',
		];
		$this->field_map[] = 'organizer_description';

		$sut = $this->make_instance( 'organizers-description' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
	}

	/**
	 * @test
	 * it should insert organizer post content if description is provided
	 */
	public function it_should_insert_organizer_post_content_if_description_is_provided() {
		$this->data        = [
			'description_1' => 'Some description',
		];
		$this->field_map[] = 'organizer_description';

		$sut = $this->make_instance( 'organizers-description' );

		$post_id = $sut->import_next_row();

		$this->assertEquals( 'Some description', get_post( $post_id )->post_content );
	}

	/**
	 * @test
	 * it should overwrite post content when reimporting
	 */
	public function it_should_overwrite_post_content_when_reimporting() {
		$this->data        = [
			'description_1' => 'First description',
		];
		$this->field_map[] = 'organizer_description';

		$sut = $this->make_instance( 'organizers-description' );

		$post_id = $sut->import_next_row();

		$this->assertEquals( 'First description', get_post( $post_id )->post_content );

		$this->data = [
			'description_1' => 'New description',
		];

		$sut = $this->make_instance( 'organizers-description' );

		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( 'New description', get_post( $post_id )->post_content );
	}

	/**
	 * @test
	 * it should restore an organizer description that has been emptied
	 */
	public function it_should_restore_an_organizer_description_that_has_been_emptied() {
		$this->data        = [
			'description_1' => 'First description',
		];
		$this->field_map[] = 'organizer_description';

		$sut = $this->make_instance( 'organizers-description' );

		$post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, wp_update_post( [ 'ID' => $post_id, 'post_content' => '' ] ) );

		$this->data = [
			'description_1' => 'New description',
		];

		$sut = $this->make_instance( 'organizers-description' );

		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( 'New description', get_post( $post_id )->post_content );
	}
}