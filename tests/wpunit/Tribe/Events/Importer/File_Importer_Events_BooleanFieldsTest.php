<?php
namespace Tribe\Events\Importer;
require_once 'File_Importer_EventsTest.php';

use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;
use org\bovigo\vfs\vfsStream;

class File_Importer_Events_BooleanFieldsTest extends File_Importer_EventsTest {

	public function boolean_fields() {
		return [
			[ 'event_allow_comments' ],
			[ 'event_allow_trackbacks' ],
			[ 'event_hide_from_listsings' ],
			[ 'event_sticky_in_month_view' ],
			[ 'event_show_map' ],
			[ 'event_show_map_link' ],
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

	/**
	 * @test
	 * it should accept truthy values for hide from upcoming
	 * @dataProvider truthy_boolean_values
	 */
	public function it_should_accept_various_valid_boolean_values_for_hide_from_upcoming( $truthy_boolean_value ) {
		$this->data        = [
			'value_1' => $truthy_boolean_value,
		];
		$this->field_map[] = 'event_hide';

		$sut = $this->make_instance( 'boolean-fields' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
		$this->assertCount( 1, get_post_meta( $post_id, '_EventHideFromUpcoming', false ) );
		$this->assertEquals( 'yes', get_post_meta( $post_id, '_EventHideFromUpcoming', true ) );
	}

	/**
	 * @test
	 * it should accept falsy values for hide from upcoming
	 * @dataProvider falsy_boolean_values
	 */
	public function it_should_accept_falsy_values_for_hide_from_upcoming( $falsy_boolean_value ) {
		$this->data        = [
			'value_1' => $falsy_boolean_value,
		];
		$this->field_map[] = 'event_hide';

		$sut = $this->make_instance( 'boolean-fields' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
		$this->assertCount( 0, get_post_meta( $post_id, '_EventHideFromUpcoming', false ) );
		$this->assertEquals( '', get_post_meta( $post_id, '_EventHideFromUpcoming', true ) );
	}

	/**
	 * @test
	 * it should accept truthy values to make sticky
	 * @dataProvider truthy_boolean_values
	 */
	public function it_should_accept_various_valid_boolean_values_to_make_sticky( $truthy_boolean_value ) {
		$this->data        = [
			'value_1' => $truthy_boolean_value,
		];
		$this->field_map[] = 'event_sticky';

		$sut = $this->make_instance( 'boolean-fields' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
		$this->assertEquals( - 1, get_post( $post_id )->menu_order );
	}

	/**
	 * @test
	 * it should accept falsy values to make sticky
	 * @dataProvider falsy_boolean_values
	 */
	public function it_should_accept_falsy_values_to_make_sticky( $falsy_boolean_value ) {
		$this->data        = [
			'value_1' => $falsy_boolean_value,
		];
		$this->field_map[] = 'event_sticky';

		$sut = $this->make_instance( 'boolean-fields' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
		$this->assertEquals( 0, get_post( $post_id )->menu_order );
	}

	/**
	 * @test
	 * it should accept truthy values to show map
	 * @dataProvider truthy_boolean_values
	 */
	public function it_should_accept_various_valid_boolean_values_to_show_map( $truthy_boolean_value ) {
		$this->data = [
			'show_map_link' => $truthy_boolean_value,
			'show_map'      => $truthy_boolean_value,
		];

		$sut = $this->make_instance( 'show-map-settings' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
		$this->assertEquals( '1', get_post_meta( $post_id, '_EventShowMap', true ) );
		$this->assertEquals( '1', get_post_meta( $post_id, '_EventShowMapLink', true ) );
	}

	/**
	 * @test
	 * it should accept falsy values to show map
	 * @dataProvider falsy_boolean_values
	 */
	public function it_should_accept_falsy_values_to_show_map( $falsy_boolean_value ) {
		$this->data = [
			'show_map_link' => $falsy_boolean_value,
			'show_map'      => $falsy_boolean_value,
		];

		$sut = $this->make_instance( 'show-map-settings' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
		$this->assertEquals( '', get_post_meta( $post_id, '_EventShowMapLink', true ) );
	}

	public function currency_positions() {
		return [
			[ 'prefix', 'prefix' ],
			[ 'prefix', 'Prefix' ],
			[ 'prefix', 'PREFIX' ],
			[ 'suffix', 'suffix' ],
			[ 'suffix', 'Suffix' ],
			[ 'suffix', 'SUFFIX' ],
			[ 'prefix', 'before' ],
			[ 'prefix', 'Before' ],
			[ 'prefix', 'BEFORE' ],
			[ 'suffix', 'after' ],
			[ 'suffix', 'After' ],
			[ 'suffix', 'AFTER' ],
		];
	}

	/**
	 * @test
	 * it should accept varied values for currency position setting
	 * @dataProvider currency_positions
	 */
	public function it_should_accept_varied_values_for_currency_position_setting( $expected_currency_position, $currency_position ) {
		$this->data        = [
			'value_1' => $currency_position,
		];
		$this->field_map[] = 'event_currency_position';

		$sut = $this->make_instance( 'boolean-fields' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
		$this->assertEquals( $expected_currency_position, get_post_meta( $post_id, '_EventCurrencyPosition', true ) );
	}

}