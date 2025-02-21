<?php

namespace TEC\Events\Category_Colors\Tests;

use Codeception\TestCase\WPTestCase;
use InvalidArgumentException;
use TEC\Events\Category_Colors\Event_Category_Meta as Meta;
use Tribe\Tests\Traits\With_Uopz;
use TypeError;
use WP_Term;

class EventCategoryMeta_Test extends WPTestCase {
	use With_Uopz;

	/**
	 * Sample category term for testing.
	 *
	 * @var WP_Term
	 */
	protected $test_term;

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

		$this->assertNull( $meta->get( 'primary' ) );
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

		$this->assertNull( $meta->get( 'background' ) );
		$this->assertNull( $meta->get( 'text' ) );
	}

	/** @test */
	public function it_should_chain_set_and_delete_calls_and_save() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$meta->set( 'primary', '#ff0000' )
			->set( 'secondary', '#00ff00' )
			->delete( 'primary' )
			->save();

		$this->assertNull( $meta->get( 'primary' ) );
		$this->assertEquals( '#00ff00', $meta->get( 'secondary' ) );
	}

	/** @test */
	public function it_should_not_persist_changes_until_save_is_called() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$meta->set( 'color', '#ff0000' );
		$this->assertNull( $meta->get( 'color' ) ); // Should not be saved yet

		$meta->save();
		$this->assertEquals( '#ff0000', $meta->get( 'color' ) );
	}

	/** @test */
	public function it_should_not_fail_when_deleting_non_existent_keys() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		$result = $meta->delete( 'non_existent_key' )->save();
		$this->assertInstanceOf( Meta::class, $result );
		$this->assertNull( $meta->get( 'non_existent_key' ) );
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
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Meta value cannot be null.' );

		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		// Attempt to set an invalid value, should throw an exception
		$meta->set( 'another_key', null );
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

	/** @test */
	public function it_should_properly_handle_empty_values_when_retrieving_meta() {
		$meta = tribe( Meta::class )->set_term( $this->test_term->term_id );

		// Set different types of values.
		$meta->set( 'zero_value', 0 )
			->set( 'integer_value', 5 )
			->set( 'false_value', false )
			->set( 'empty_string', '' )
			->set( 'empty_array', [] )
			->set( 'non_empty_array', [ 'value' ] )
			->set( 'non_empty_string', 'hello' )
			->save();

		// Assertions for expected behavior.
		$this->assertSame( '0', $meta->get( 'zero_value' ) );
		$this->assertSame( '5', $meta->get( 'integer_value' ) );
		$this->assertSame( '', $meta->get( 'false_value' ) );
		$this->assertSame( '', $meta->get( 'empty_string' ) );
		$this->assertSame( [], $meta->get( 'empty_array' ) );
		$this->assertSame( [ 'value' ], $meta->get( 'non_empty_array' ) );
		$this->assertSame( 'hello', $meta->get( 'non_empty_string' ) );
	}
}
