<?php
namespace Tribe\Events\Importer;
require 'File_Importer_EventsTest.php';

use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;
use org\bovigo\vfs\vfsStream;
use Tribe__Events__Importer__File_Importer_Events as Events_Importer;

class File_Importer_Events_BooleanFieldsTest extends File_Importer_EventsTest {

	public function boolean_fields() {
		return [
			[ 'event_allow_comments' ],
			[ 'event_allow_trackbacks' ],
			[ 'event_hide_from_listsings' ],
			[ 'event_sticky_in_month_view' ],
		];

	}

	/**
	 * @test
	 * it should not mark record as invalid if boolean field is missing
	 * @dataProvider boolean_fields
	 */
	public function it_should_not_mark_record_as_invalid_if_boolean_field_is_missing( $field_slug ) {
		$this->data        = [
			'value_1' => '',
		];
		$this->field_map[] = $field_slug;

		$sut = $this->make_instance( 'boolean-fields' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
	}

	public function truthy_boolean_values() {
		return [
			[ 'true' ],
			[ true ],
			[ '1' ],
			[ 1 ],
			[ 'yes' ],
		];
	}

	public function falsy_boolean_values() {
		return [
			[ 'false' ],
			[ false ],
			[ '0' ],
			[ 0 ],
			[ 'no' ],
		];

	}

	/**
	 * @test
	 * it should accept truthy values to allow comments
	 * @dataProvider truthy_boolean_values
	 */
	public function it_should_accept_various_valid_boolean_values_to_allow_comments( $truthy_boolean_value ) {
		$this->data        = [
			'value_1' => $truthy_boolean_value,
		];
		$this->field_map[] = 'event_comment_status';

		$sut = $this->make_instance( 'boolean-fields' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
		$this->assertEquals( 'open', get_post( $post_id )->comment_status );
	}

	/**
	 * @test
	 * it should accept falsy values to block comments
	 * @dataProvider falsy_boolean_values
	 */
	public function it_should_accept_falsy_values_to_block_comments( $falsy_boolean_value ) {
		$this->data        = [
			'value_1' => $falsy_boolean_value,
		];
		$this->field_map[] = 'event_comment_status';

		$sut = $this->make_instance( 'boolean-fields' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
		$this->assertEquals( 'closed', get_post( $post_id )->comment_status );
	}

	/**
	 * @test
	 * it should accept truthy values to allow trackbacks
	 * @dataProvider truthy_boolean_values
	 */
	public function it_should_accept_various_valid_boolean_values_to_allow_trackbacks( $truthy_boolean_value ) {
		$this->data        = [
			'value_1' => $truthy_boolean_value,
		];
		$this->field_map[] = 'event_ping_status';

		$sut = $this->make_instance( 'boolean-fields' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
		$this->assertEquals( 'open', get_post( $post_id )->ping_status );
	}

	/**
	 * @test
	 * it should accept falsy values to block trackbacks
	 * @dataProvider falsy_boolean_values
	 */
	public function it_should_accept_falsy_values_to_block_trackbacks( $falsy_boolean_value ) {
		$this->data        = [
			'value_1' => $falsy_boolean_value,
		];
		$this->field_map[] = 'event_ping_status';

		$sut = $this->make_instance( 'boolean-fields' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
		$this->assertEquals( 'closed', get_post( $post_id )->ping_status );
	}

}