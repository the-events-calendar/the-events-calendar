<?php
/**
 * Test the Event Category Meta functionality.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors;

use Codeception\TestCase\WPTestCase;
use Generator;
use InvalidArgumentException;
use TEC\Events\Category_Colors\Event_Category_Meta as Meta;
use Tribe\Tests\Traits\With_Uopz;
use TypeError;
use WP_Error;
use WP_Term;
use Tribe__Events__Main;

class Event_Category_Meta_Test extends WPTestCase {
	use With_Uopz;

	/**
	 * Sample category term for testing.
	 *
	 * @var WP_Term
	 */
	protected $test_term;

	/**
	 * @var Event_Category_Meta
	 */
	protected $category_meta;

	/**
	 * @before
	 */
	public function setup_test_environment(): void {
		$this->category_meta = tribe(Event_Category_Meta::class);
	}

	/**
	 * Creates a test category before each test.
	 *
	 * @before
	 */
	public function create_test_term(): void {
		$this->test_term = $this->factory()->term->create_and_get(
			[
				'taxonomy' => 'tribe_events_cat',
				'name'     => 'Test Category',
				'slug'     => 'test-category',
			]
		);

		$this->assertInstanceOf( WP_Term::class, $this->test_term, 'Failed to create test category' );
	}

	/**
	 * Deletes the test category after each test.
	 *
	 * @after
	 */
	public function delete_test_term(): void {
		wp_delete_term( $this->test_term->term_id, 'tribe_events_cat' );
	}

	/** @test */
	public function it_should_create_instance_for_valid_term() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );
		$this->assertInstanceOf( Meta::class, $meta );
	}

	/** @test */
	public function it_should_throw_an_error_when_set_is_called_without_set_term() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'set_term() must be called before using this method.' );

		$meta = tribe( Meta::class );
		$meta->set( 'primary', '#ff0000' );
	}

	/** @test */
	public function it_should_throw_an_error_when_get_is_called_without_set_term() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'set_term() must be called before using this method.' );

		$meta = tribe( Meta::class );
		$meta->get( 'primary' );
	}

	/** @test */
	public function it_should_throw_an_error_when_delete_is_called_without_set_term() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'set_term() must be called before using this method.' );

		$meta = tribe( Meta::class );
		$meta->delete( 'primary' );
	}

	/** @test */
	public function it_should_allow_setting_meta_after_set_term_is_called() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );
		$meta->set( 'primary', '#ff0000' )->save();

		$this->assertEquals( '#ff0000', $meta->get( 'primary' ) );
	}

	/** @test */
	public function it_should_allow_deleting_meta_after_set_term_is_called() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );
		$meta->set( 'primary', '#ff0000' )->save();
		$meta->delete( 'primary' )->save();

		$this->assertEmpty( $meta->get( 'primary' ) );
	}

	/** @test */
	public function it_should_allow_getting_meta_after_set_term_is_called() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );
		$meta->set( 'primary', '#ff0000' )->save();

		$this->assertEquals( '#ff0000', $meta->get( 'primary' ) );
	}

	/** @test */
	public function it_should_queue_meta_updates_and_save_them() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$meta->set( 'color', '#ff0000' )
			->set( 'border', '#00ff00' )
			->save();

		$this->assertEquals( '#ff0000', $meta->get( 'color' ) );
		$this->assertEquals( '#00ff00', $meta->get( 'border' ) );
	}

	/** @test */
	public function it_should_queue_meta_deletes_and_save_them() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$meta->set( 'background', '#123456' )
			->set( 'text', '#654321' )
			->save();

		$this->assertEquals( '#123456', $meta->get( 'background' ) );
		$this->assertEquals( '#654321', $meta->get( 'text' ) );

		$meta->delete( 'background' )
			->delete( 'text' )
			->save();

		$this->assertEmpty( $meta->get( 'background' ) );
		$this->assertEmpty( $meta->get( 'text' ) );
	}

	/** @test */
	public function it_should_chain_set_and_delete_calls_and_save() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$meta->set( 'primary', '#ff0000' )
			->set( 'secondary', '#00ff00' )
			->delete( 'primary' )
			->save();

		$this->assertEmpty( $meta->get( 'primary' ) );
		$this->assertEquals( '#00ff00', $meta->get( 'secondary' ) );
	}

	/** @test */
	public function it_should_not_persist_changes_until_save_is_called() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$meta->set( 'color', '#ff0000' );
		$this->assertEmpty( $meta->get( 'color' ) ); // Should not be saved yet

		$meta->save();
		$this->assertEquals( '#ff0000', $meta->get( 'color' ) );
	}

	/** @test */
	public function it_should_not_fail_when_deleting_non_existent_keys() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$result = $meta->delete( 'non_existent_key' )->save();
		$this->assertInstanceOf( Meta::class, $result );
		$this->assertEmpty( $meta->get( 'non_existent_key' ) );
	}

	/** @test */
	public function it_should_throw_an_error_when_invalid_key_is_passed() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Meta key cannot be empty.' );

		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		// Attempt to set an invalid key, should throw an exception
		$meta->set( '', '#00ff00' );
	}

	/** @test */
	public function it_should_throw_an_error_when_invalid_value_is_passed() {
		add_filter(
			'tec_events_category_validate_meta_value',
			function ( $value ) {
				if ( null === $value ) {
					return new WP_Error( 'invalid_meta', 'Meta value cannot be null.' );
				}

				return $value;
			}
		);

		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Meta value cannot be null.' );

		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		// Attempt to set an invalid value, should trigger the filter and throw an exception
		$meta->set( 'another_key', null );

		// Unhook filter after test
		remove_filter( 'tec_events_category_validate_meta_value', '__return_null' );
	}

	/** @test */
	public function it_should_handle_special_characters_in_keys_and_values() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$special_key   = 'some@key#with!special$chars';
		$special_value = '!@#$%^&*()_+={}[]|:;"\'<>,.?/~`';

		$meta->set( $special_key, $special_value )->save();
		$this->assertEquals( $special_value, $meta->get( $special_key ) );
	}

	/** @test */
	public function it_should_overwrite_existing_meta_values() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$meta->set( 'color', '#ff0000' )->save();
		$this->assertEquals( '#ff0000', $meta->get( 'color' ) );

		$meta->set( 'color', '#00ff00' )->save();
		$this->assertEquals( '#00ff00', $meta->get( 'color' ) );
	}

	/** @test */
	public function it_should_return_wp_error_for_invalid_term() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'does not exist in taxonomy' );
		$meta = tribe( Meta::class )->set_term( 999999 );
	}

	/** @test */
	public function it_should_throw_exception_for_zero_or_negative_term_id() {
		$this->expectException( InvalidArgumentException::class );
		tribe( Meta::class )->set_term( 0 );

		$this->expectException( InvalidArgumentException::class );
		tribe( Meta::class )->set_term( -5 );
	}

	/** @test */
	public function it_should_throw_exception_for_non_integer_term_id() {
		$this->expectException( TypeError::class );
		tribe( Meta::class )->set_term( 'not-an-id' );

		$this->expectException( TypeError::class );
		tribe( Meta::class )->set_term( null );

		$this->expectException( TypeError::class );
		tribe( Meta::class )->set_term( (object) [ 'id' => 123 ] );
	}

	/** @test */
	public function it_should_allow_array_values_and_serialize_them() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$array_value = [ 'red', 'blue', 'green' ];
		$meta->set( 'array_key', $array_value )->save();

		$this->assertEquals( $array_value, maybe_unserialize( $meta->get( 'array_key' ) ) );
	}

	/** @test */
	public function it_should_allow_object_values_and_serialize_them() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$object_value = (object) [ 'foo' => 'bar' ];
		$meta->set( 'object_key', $object_value )->save();

		$this->assertEquals( $object_value, maybe_unserialize( $meta->get( 'object_key' ) ) );
	}

	/** @test */
	public function it_should_not_fail_when_saving_without_changes() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		// Call save without setting anything
		$meta->save();

		// No assertion needed, just ensure no exception is thrown.
		$this->assertTrue( true );
	}

	/**
	 * We are testing WordPress's native `update_term_meta()` and `get_term_meta()` behavior.
	 * Ideally, WordPress should store and retrieve values exactly as they were saved.
	 * However, as of WordPress version `6.7.2`, data is being altered during storage and retrieval.
	 *
	 * This test ensures we have a baseline understanding of how WordPress processes meta values.
	 * If future WordPress versions fix this issue, these tests may need to be updated.
	 *
	 * @test
	 * @dataProvider meta_value_provider
	 */
	public function it_should_properly_store_and_retrieve_meta_values_using_wordpress( $key, $input, $expected ) {
		$term_id = $this->test_term->term_id;

		// Store the value using WordPress function
		update_term_meta( $term_id, $key, $input );

		// Retrieve the value
		$retrieved = get_term_meta( $term_id, $key, true );

		// Assert that the retrieved value matches the expected transformed value
		if ( is_object( $expected ) ) {
			$this->assertEquals( $expected, $retrieved, "Failed for key: $key in WordPress." );
		} else {
			$this->assertSame( $expected, $retrieved, "Failed for key: $key in WordPress." );
		}
	}

	/**
	 * This test ensures that our custom meta handling class behaves exactly like WordPress.
	 * Now that we know WordPress alters values, our class must do the same for consistency.
	 *
	 * @test
	 * @dataProvider meta_value_provider
	 */
	public function it_should_properly_store_and_retrieve_meta_values_using_our_class( $key, $input, $expected ) {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		// Store the value using our class
		$meta->set( $key, $input )->save();

		// Retrieve the value using our class
		$retrieved = $meta->get( $key );

		// Assert that our class matches WordPress behavior exactly
		if ( is_object( $expected ) ) {
			$this->assertEquals( $expected, $retrieved, "Failed for key: $key in our class." );
		} else {
			$this->assertSame( $expected, $retrieved, "Failed for key: $key in our class." );
		}
	}

	/**
	 * Data provider for meta value tests.
	 *
	 * @return Generator
	 */
	public function meta_value_provider() {
		yield 'bool_true' => [ 'bool_true', true, '1' ];
		yield 'bool_false' => [ 'bool_false', false, '' ];
		yield 'int_zero' => [ 'int_zero', 0, '0' ];
		yield 'int_positive' => [ 'int_positive', 123, '123' ];
		yield 'int_negative' => [ 'int_negative', -123, '-123' ];
		yield 'float_zero' => [ 'float_zero', 0.0, '0' ];
		yield 'float_positive' => [ 'float_positive', 3.14, '3.14' ];
		yield 'float_negative' => [ 'float_negative', -3.14, '-3.14' ];
		yield 'string_empty' => [ 'string_empty', '', '' ];
		yield 'string_numeric' => [ 'string_numeric', '123', '123' ];
		yield 'string_alpha' => [ 'string_alpha', 'abc', 'abc' ];
		yield 'string_bool' => [ 'string_bool', 'true', 'true' ];
		yield 'null_value' => [ 'null_value', null, '' ];
		yield 'empty_array' => [ 'empty_array', [], [] ];
		yield 'array_strings' => [ 'array_strings', [ 'one', 'two', 'three' ], [ 'one', 'two', 'three' ] ];
		yield 'array_mixed' => [ 'array_mixed', [ 1, 'one', 1.5, true, false ], [ 1, 'one', 1.5, true, false ] ];
		yield 'array_nested' => [ 'array_nested', [ [ 'nested' => 'array' ] ], [ [ 'nested' => 'array' ] ] ];
		yield 'json_encoded' => [
			'json_encoded',
			wp_json_encode(
				[
					'json' => 'object',
				]
			),
			'{"json":"object"}',
		];
		yield 'json_decoded' => [
			'json_decoded',
			json_decode(
				wp_json_encode(
					[
						'json' => 'object',
					]
				),
				true
			),
			[ 'json' => 'object' ],
		];
		yield 'array_assoc' => [
			'array_assoc',
			[
				'key' => 'value',
				'num' => 123,
			],
			[
				'key' => 'value',
				'num' => 123,
			],
		];
		yield 'object_std' => [ 'object_std', (object) [ 'prop' => 'value' ], (object) [ 'prop' => 'value' ] ];
	}

	/** @test */
	public function it_should_throw_an_error_when_setting_meta_for_a_shared_term() {
		// Mock wp_term_is_shared() to return true for this test.
		$this->set_fn_return( 'wp_term_is_shared', true );

		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage(
			sprintf( "Meta cannot be added to term ID %d because it's shared between taxonomies.", $this->test_term->term_id )
		);

		// Attempt to set a meta value, should trigger the shared term error.
		$meta->set( 'shared_key', '#123456' );
	}

	/** @test */
	public function it_should_treat_meta_keys_as_case_insensitive() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$meta->set( 'CaseTest', '#FF0000' )->save();
		$this->assertEquals( '#FF0000', $meta->get( 'casetest' ) ); // Lowercase retrieval
	}

	/** @test */
	public function it_should_not_fail_when_deleting_already_deleted_meta() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$meta->set( 'to_delete', '#ABCDEF' )->save();
		$meta->delete( 'to_delete' )->save();

		// Attempt to delete again
		$result = $meta->delete( 'to_delete' )->save();

		$this->assertInstanceOf( Meta::class, $result );
		$this->assertEmpty( $meta->get( 'to_delete' ) );
	}

	/** @test */
	public function it_should_handle_large_number_of_meta_entries() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$bulk_meta = [];
		for ( $i = 0; $i < 250; $i++ ) {
			$bulk_meta["key_{$i}"] = "value_{$i}";
		}

		foreach ( $bulk_meta as $key => $value ) {
			$meta->set( $key, $value );
		}

		$meta->save();

		foreach ( $bulk_meta as $key => $value ) {
			$this->assertEquals( $value, $meta->get( $key ) );
		}
	}

	/** @test */
	public function it_should_do_nothing_if_save_is_called_without_any_pending_operations() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		// Call save without any `set()` or `delete()`
		$result = $meta->save();

		// Ensure the object is still valid and save doesn't break anything
		$this->assertInstanceOf( Meta::class, $result );
	}

	/** @test */
	public function it_should_not_persist_meta_if_deleted_before_saving() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		// Set a meta key but delete it before save
		$meta->set( 'primary', '#FF0000' )->delete( 'primary' )->save();

		// The meta key should be gone, since it was deleted before being committed
		$this->assertEmpty( $meta->get( 'primary' ) );
	}

	/** @test */
	public function it_should_throw_an_error_when_deleting_an_empty_meta_key() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Meta key cannot be empty.' );

		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );
		$meta->delete( '' );
	}
}
