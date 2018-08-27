<?php
namespace Tribe\Events\Importer;

require_once 'File_Importer_EventsTest.php';

use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;
use org\bovigo\vfs\vfsStream;

class File_Importer_Events_ExcerptTest extends File_Importer_EventsTest {


	/**
	 * @test
	 * it should not mark record as invalid if excerpt entry is missing
	 */
	public function it_should_not_mark_record_as_invalid_if_excerpt_entry_is_missing() {
		$sut = $this->make_instance( 'excerpt' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
	}

	/**
	 * @test
	 * it should import the excerpt if defined in file
	 */
	public function it_should_import_the_excerpt_if_defined_in_file() {
		$this->data        = [
			'excerpt_1' => 'Excerpt 1',
		];
		$this->field_map[] = 'event_excerpt';

		$sut     = $this->make_instance( 'excerpt' );
		$post_id = $sut->import_next_row();

		$this->assertEquals( 'Excerpt 1', get_post( $post_id )->post_excerpt );
	}

	/**
	 * @test
	 * it should import the excerpt if set on file and not set on existing post
	 */
	public function it_should_import_the_excerpt_if_set_on_file_and_not_set_on_existing_post() {
		$this->data        = [
			'excerpt_1' => '',
		];
		$this->field_map[] = 'event_excerpt';

		$sut     = $this->make_instance( 'excerpt' );
		$post_id = $sut->import_next_row();

		$this->assertEquals( '', get_post( $post_id )->post_excerpt );

		$this->data       = [
			'excerpt_1' => 'The post excerpt',
		];
		$sut              = $this->make_instance( 'excerpt' );
		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( 'The post excerpt', get_post( $reimport_post_id )->post_excerpt );
	}

	/**
	 * @test
	 * it should not import the excerpt if defined in file but already set on post
	 */
	public function it_should_not_import_the_excerpt_if_defined_in_file_but_already_set_on_post() {
		$this->data        = [
			'excerpt_1' => 'A',
		];
		$this->field_map[] = 'event_excerpt';

		$sut     = $this->make_instance( 'excerpt' );
		$post_id = $sut->import_next_row();

		$this->assertEquals( 'A', get_post( $post_id )->post_excerpt );

		$this->data       = [
			'excerpt_1' => 'B',
		];
		$sut              = $this->make_instance( 'excerpt' );
		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( 'A', get_post( $reimport_post_id )->post_excerpt );
	}

	/**
	 * @test
	 * it should not import the excerpt if not defined in file
	 */
	public function it_should_not_import_the_excerpt_if_not_defined_in_file() {
		$this->data        = [
			'excerpt_1' => '',
		];
		$this->field_map[] = 'event_excerpt';

		$sut     = $this->make_instance( 'excerpt' );
		$post_id = $sut->import_next_row();

		$this->assertEquals( '', get_post( $post_id )->post_excerpt );
	}
}