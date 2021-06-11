<?php

namespace Tribe\Events\Importer;

require_once 'File_Importer_EventsTest.php';


class File_Importer_Events_ContentTest extends File_Importer_EventsTest {

	/**
	 * @test
	 * it should not mark record as invalid if missing content
	 */
	public function it_should_not_mark_record_as_invalid_if_missing_content() {
		$this->data        = [
			'description_1' => '',
		];
		$this->field_map[] = 'event_description';

		$sut = $this->make_instance();

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
	}

	/**
	 * @test
	 * it should insert event post content if description is provided
	 */
	public function it_should_insert_event_post_content_if_description_is_provided() {
		$this->data = [
			'description_1' => 'Some description',
		];

		$sut = $this->make_instance( 'event-description' );

		$post_id = $sut->import_next_row();

		$this->assertEquals( 'Some description', get_post( $post_id )->post_content );
	}

	/**
	 * @test
	 * it should overwrite post content when reimporting
	 */
	public function it_should_overwrite_post_content_when_reimporting() {
		$this->data = [
			'description_1' => 'First description',
		];

		$sut = $this->make_instance( 'event-description' );

		$post_id = $sut->import_next_row();

		$this->assertEquals( 'First description', get_post( $post_id )->post_content );

		$this->data = [
			'description_1' => 'New description',
		];

		$sut = $this->make_instance( 'event-description' );

		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( 'New description', get_post( $post_id )->post_content );
	}

	/**
	 * @test
	 * it should restore an event description that has been emptied
	 */
	public function it_should_restore_an_event_description_that_has_been_emptied() {
		$this->data = [
			'description_1' => 'First description',
		];

		$sut = $this->make_instance( 'event-description' );

		$post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, wp_update_post( [ 'ID' => $post_id, 'post_content' => '' ] ) );

		$this->data = [
			'description_1' => 'New description',
		];

		$sut = $this->make_instance( 'event-description' );

		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( 'New description', get_post( $post_id )->post_content );
	}

	/**
	 * @test
	 */
	public function it_should_not_overwrite_the_description_if_description_does_not_import() {
		$this->data = [
			'description_1' => 'A description',
		];

		$sut     = $this->make_instance( 'event-description' );
		$post_id = $sut->import_next_row();

		$this->assertEquals( 'A description', get_post( $post_id )->post_content );

		$this->data = [
			'description_1' => 'B description',
		];

		// remove event description from field map.
		if ( ( $key = array_search( 'event_description', $this->field_map ) ) !== false ) {
			unset( $this->field_map[ $key ] );
		}

		$sut              = $this->make_instance( 'event-description' );
		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( 'A description', get_post( $reimport_post_id )->post_content );
	}

	/**
	 * @test
	 */
	public function it_should_overwrite_the_description_if_description_import_is_empty() {
		$this->data = [
			'description_1' => 'A Description',
		];

		$sut     = $this->make_instance( 'event-description' );
		$post_id = $sut->import_next_row();

		$this->assertEquals( 'A Description', get_post( $post_id )->post_content );

		$this->data = [
			'description_1' => '',
		];

		$sut              = $this->make_instance( 'event-description' );
		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( '', get_post( $reimport_post_id )->post_content );
	}
}