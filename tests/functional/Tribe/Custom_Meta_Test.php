<?php
namespace Tribe\Events\Pro\Tests;

use Codeception\TestCase\WPTestCase as WP_Test_Case,
    Tribe__Events__API as Events_API,
    Tribe__Events__Pro__Custom_Meta as Meta_API,
    Tribe__Settings_Manager as Settings;


class Custom_Meta_Test extends WP_Test_Case {
	protected $test_event_id;

	public function setUp() {
		$this->setup_test_post();
	}

	protected function setup_test_post() {
		$this->test_event_id = Events_API::createEvent( [
			'post_title'       => 'Test Custom Meta',
			'post_content'     => 'Test Custom Meta',
			'EventStartDate'   => date_i18n( 'Y-m-d' ),
			'EventEndDate'     => date_i18n( 'Y-m-d' ),
			'EventStartHour'   => 16,
			'EventEndHour'     => 17,
			'EventStartMinute' => 0,
			'EventEndMinute'   => 0,
		] );
	}

	public function test_saves_strings() {
		$this->setup_simple_text_field( 'I am a string' );
		$saved_value = Meta_API::get_custom_field_by_label( 'simple text field', $this->test_event_id );
		$this->assertEquals( 'I am a string', $saved_value, 'The correct value was saved and returned' );
	}

	public function test_does_not_save_empty_values() {
		$this->setup_simple_text_field( '' );
		$saved_value = Meta_API::get_custom_field_by_label( 'simple text field', $this->test_event_id );
		$this->assertEmpty( $saved_value, 'The empty value was rejected' );
	}

	public function test_string_zero_not_deemed_empty() {
		$this->setup_simple_text_field( '0' );
		$saved_value = Meta_API::get_custom_field_by_label( 'simple text field', $this->test_event_id );
		$this->assertEquals( '0', $saved_value, '(string) "0" was successfully saved' );
	}

	/**
	 * Creates a custom field labelled 'simple text field' and sets it to the
	 * specified value.
	 *
	 * @param mixed $value
	 */
	protected function setup_simple_text_field( $value ) {
		$this->setup_custom_fields( [ [ 'simple text field', 'text', '' ] ] );
		$field_name = $this->get_internal_field_name( 'simple text field' );
		Meta_API::save_single_event_meta( $this->test_event_id, [ $field_name => $value ] );
	}

	/**
	 * Tries to save a set of additional fields for use in tests.
	 *
	 * The desired fields should be an array formatted as follows:
	 *
	 *     [
	 *         [ 'field 1', 'type', 'default options' ],
	 *         [ 'field 2', 'other-type', 'default options' ],
	 *         ...
	 *     ]
	 *
	 * @param array $fields
	 */
	protected function setup_custom_fields( array $fields ) {
		// We're going to need to specify our set of additional fields required
		// for testing by adjusting/creating the $_POST superglobal
		$post_global_was_set = isset( $_POST );
		$original_post_data  = @$_POST;

		// Arrange the fields into a format that the custom meta API can understand
		$_POST = [
			'custom-field'         => [],
			'custom-field-type'    => [],
			'custom-field-options' => [],
		];

		foreach ( $fields as $set ) {
			if ( ! is_array( $set ) || count( $set ) !== 3 ) {
				continue;
			}

			$_POST[ 'custom-field' ][]         = $set[0];
			$_POST[ 'custom-field-type' ][]    = $set[1];
			$_POST[ 'custom-field-options' ][] = $set[2];
		}

		// Ensure 'disable_metabox_custom_fields' is set to avoid notices
		$_POST[ 'disable_metabox_custom_fields' ] = 1;

		// It looks odd, but this reflects the way additional fields are currently set and updated
		Settings::set_options( Meta_API::save_meta_options( Settings::get_options() ) );

		// Undo the changes we made to the $_POST superglobal
		if ( $post_global_was_set ) {
			$_POST = $original_post_data;
		} else {
			unset( $_POST );
		}
	}

	/**
	 * Attempts to return the (internal) name of whichever custom field has the
	 * specified label.
	 *
	 * For example, given "Some test field" it may return "_ecp_custom_3".
	 *
	 * @param string $label
	 *
	 * @return string|false
	 */
	protected function get_internal_field_name( $label ) {
		$fields = (array) tribe_get_option( 'custom-fields' );

		foreach ( $fields as $definition ) {
			if ( $label === $definition[ 'label' ] ) {
				return $definition[ 'name' ];
			}
		}

		return false;
	}
}