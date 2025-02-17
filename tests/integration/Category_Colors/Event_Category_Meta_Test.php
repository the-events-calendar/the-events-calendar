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
		$meta = new Meta( $this->test_term->term_id );
		$this->assertInstanceOf( Meta::class, $meta );
	}

	/** @test */
	public function it_should_queue_meta_updates_and_save_them() {
		$meta = new Meta( $this->test_term->term_id );

		$meta->set( 'color', '#ff0000' )
			->set( 'border', '#00ff00' )
			->save();

		$this->assertEquals( '#ff0000', $meta->get( 'color' ) );
		$this->assertEquals( '#00ff00', $meta->get( 'border' ) );
	}

	/** @test */
	public function it_should_queue_meta_deletes_and_save_them() {
		$meta = new Meta( $this->test_term->term_id );

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
		$meta = new Meta( $this->test_term->term_id );

		$meta->set( 'primary', '#ff0000' )
			->set( 'secondary', '#00ff00' )
			->delete( 'primary' )
			->save();

		$this->assertNull( $meta->get( 'primary' ) );
		$this->assertEquals( '#00ff00', $meta->get( 'secondary' ) );
	}

	/** @test */
	public function it_should_not_persist_changes_until_save_is_called() {
		$meta = new Meta( $this->test_term->term_id );

		$meta->set( 'color', '#ff0000' );
		$this->assertNull( $meta->get( 'color' ) ); // Should not be saved yet

		$meta->save();
		$this->assertEquals( '#ff0000', $meta->get( 'color' ) );
	}

	/** @test */
	public function it_should_not_fail_when_deleting_non_existent_keys() {
		$meta = new Meta( $this->test_term->term_id );

		$result = $meta->delete( 'non_existent_key' )->save();
		$this->assertInstanceOf( Meta::class, $result );
		$this->assertNull( $meta->get( 'non_existent_key' ) );
	}

	/** @test */
	public function it_should_ignore_invalid_keys_but_continue_chaining() {
		$meta = new Meta( $this->test_term->term_id );

		// Attempt to set an invalid key
		$meta->set( 'valid_key', '#ff0000' )
			->set( '', '#00ff00' ) // Invalid, should not affect the chain
			->save();

		// Ensure valid key was saved
		$this->assertEquals( '#ff0000', $meta->get( 'valid_key' ) );

		// Ensure invalid key was ignored (not present)
		$this->assertNull( $meta->get( 'text_color' ) );
	}

	/** @test */
	public function it_should_ignore_invalid_values_but_continue_chaining() {
		$meta = new Meta( $this->test_term->term_id );

		// Attempt to set an invalid value
		$meta->set( 'valid_key', '#ff0000' )
			->set( 'another_key', null ) // Invalid, should be ignored
			->save();

		// Ensure valid key was saved
		$this->assertEquals( '#ff0000', $meta->get( 'valid_key' ) );

		// Ensure invalid value was ignored (not present)
		$this->assertNull( $meta->get( 'another_key' ) );
	}

	/** @test */
	public function it_should_handle_special_characters_in_keys_and_values() {
		$meta = new Meta( $this->test_term->term_id );

		$special_key   = 'some@key#with!special$chars';
		$special_value = '!@#$%^&*()_+={}[]|:;"\'<>,.?/~`';

		$meta->set( $special_key, $special_value )->save();
		$this->assertEquals( $special_value, $meta->get( $special_key ) );
	}

	/** @test */
	public function it_should_overwrite_existing_meta_values() {
		$meta = new Meta( $this->test_term->term_id );

		$meta->set( 'color', '#ff0000' )->save();
		$this->assertEquals( '#ff0000', $meta->get( 'color' ) );

		$meta->set( 'color', '#00ff00' )->save();
		$this->assertEquals( '#00ff00', $meta->get( 'color' ) );
	}

	/** @test */
	public function it_should_return_wp_error_for_invalid_term() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'does not exist in taxonomy' );

		new Meta( 999999 );
	}

	/** @test */
	public function it_should_throw_exception_for_zero_or_negative_term_id() {
		$this->expectException( InvalidArgumentException::class );
		new Meta( 0 );

		$this->expectException( InvalidArgumentException::class );
		new Meta( -5 );
	}

	/** @test */
	public function it_should_throw_exception_for_non_integer_term_id() {
		$this->expectException( TypeError::class );
		new Meta( 'not-an-id' );

		$this->expectException( TypeError::class );
		new Meta( null );

		$this->expectException( TypeError::class );
		new Meta( (object) [ 'id' => 123 ] );
	}

	/** @test */
	public function it_should_allow_array_values_and_serialize_them() {
		$meta = new Meta( $this->test_term->term_id );

		$array_value = [ 'red', 'blue', 'green' ];
		$meta->set( 'array_key', $array_value )->save();

		$this->assertEquals( $array_value, maybe_unserialize( $meta->get( 'array_key' ) ) );
	}

	/** @test */
	public function it_should_allow_object_values_and_serialize_them() {
		$meta = new Meta( $this->test_term->term_id );

		$object_value = (object) [ 'foo' => 'bar' ];
		$meta->set( 'object_key', $object_value )->save();

		$this->assertEquals( $object_value, maybe_unserialize( $meta->get( 'object_key' ) ) );
	}

	/** @test */
	public function it_should_not_fail_when_saving_without_changes() {
		$meta = new Meta( $this->test_term->term_id );

		// Call save without setting anything
		$meta->save();

		// No assertion needed, just ensure no exception is thrown.
		$this->assertTrue( true );
	}
}
