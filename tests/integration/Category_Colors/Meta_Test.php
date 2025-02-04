<?php

namespace TEC\Events\Category_Colors\Tests;

use Codeception\TestCase\WPTestCase;
use InvalidArgumentException;
use TEC\Events\Category_Colors\Meta;
use Tribe\Tests\Traits\With_Uopz;
use WP_Error;
use WP_Term;

class Meta_Test extends WPTestCase {
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
	public function it_should_store_and_retrieve_meta_data() {
		$meta = new Meta( $this->test_term->term_id );

		$meta->set( 'color', '#ff0000' );
		$this->assertEquals( '#ff0000', $meta->get( 'color' ) );

		$this->assertNull( $meta->get( 'non_existent_key' ) );
	}

	/** @test */
	public function it_should_delete_a_specific_meta_key() {
		$meta = new Meta( $this->test_term->term_id );

		$meta->set( 'background', '#00ff00' );
		$this->assertEquals( '#00ff00', $meta->get( 'background' ) );

		$meta->delete( 'background' );
		$this->assertNull( $meta->get( 'background' ) );
	}

	/** @test */
	public function it_should_delete_all_meta_data_for_a_term() {
		$meta = new Meta( $this->test_term->term_id );

		$meta->set( 'foreground', '#ff0000' );
		$meta->set( 'background', '#00ff00' );

		$this->assertEquals( '#ff0000', $meta->get( 'foreground' ) );
		$this->assertEquals( '#00ff00', $meta->get( 'background' ) );

		$meta->delete();
		$this->assertNull( $meta->get( 'foreground' ) );
		$this->assertNull( $meta->get( 'background' ) );
	}

	/** @test */
	public function it_should_return_wp_error_for_invalid_term() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'does not exist in taxonomy' );

		new Meta( 999999 );
	}

	/** @test */
	public function it_should_return_wp_error_for_invalid_key() {
		$meta = new Meta( $this->test_term->term_id );

		$result = $meta->set( '', '#ff0000' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/** @test */
	public function it_should_return_wp_error_for_null_value() {
		$meta = new Meta( $this->test_term->term_id );

		$result = $meta->set( 'color', null );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/** @test */
	public function it_should_return_all_metadata_if_no_key_is_provided() {
		$meta = new Meta( $this->test_term->term_id );

		$meta->set( 'color', '#ff0000' );
		$meta->set( 'priority', 5 );

		$all_meta = $meta->get();
		$this->assertArrayHasKey( 'color', $all_meta );
		$this->assertArrayHasKey( 'priority', $all_meta );
		$this->assertEquals( '#ff0000', $all_meta['color'] );
		$this->assertEquals( 5, $all_meta['priority'] );
	}

	/** @test */
	public function it_should_allow_chaining_set_calls() {
		$meta = new Meta( $this->test_term->term_id );

		$meta->set( 'primary_color', '#ff0000' )
			->set( 'secondary_color', '#00ff00' )
			->set( 'text_color', '#ffffff' );

		$this->assertEquals( '#ff0000', $meta->get( 'primary_color' ) );
		$this->assertEquals( '#00ff00', $meta->get( 'secondary_color' ) );
		$this->assertEquals( '#ffffff', $meta->get( 'text_color' ) );
	}

	/** @test */
	public function it_should_allow_chaining_delete_calls() {
		$meta = new Meta( $this->test_term->term_id );

		$meta->set( 'primary_color', '#ff0000' )
			->set( 'secondary_color', '#00ff00' );

		$meta->delete( 'primary_color' )->delete( 'secondary_color' );

		$this->assertNull( $meta->get( 'primary_color' ) );
		$this->assertNull( $meta->get( 'secondary_color' ) );
	}

	/** @test */
	public function it_should_allow_chaining_set_and_delete_calls() {
		$meta = new Meta( $this->test_term->term_id );

		$meta->set( 'color', '#ff0000' )
			->set( 'priority', 'high' )
			->delete( 'color' );

		$this->assertNull( $meta->get( 'color' ) );
		$this->assertEquals( 'high', $meta->get( 'priority' ) );
	}

	/** @test */
	public function it_should_stop_chaining_on_invalid_key() {
		$meta = new Meta( $this->test_term->term_id );

		$result = $meta->set( 'valid_key', '#ff0000' )
			->set( '', '#00ff00' );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( '#ff0000', $meta->get( 'valid_key' ) );
		$this->assertNull( $meta->get( 'text_color' ) );
	}

	/** @test */
	public function it_should_stop_chaining_on_invalid_value() {
		$meta = new Meta( $this->test_term->term_id );

		$result = $meta->set( 'valid_key', '#ff0000' )
			->set( 'another_key', null );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( '#ff0000', $meta->get( 'valid_key' ) );
		$this->assertNull( $meta->get( 'text_color' ) );
	}

	/** @test */
	public function it_should_handle_special_characters_in_keys_and_values() {
		$meta = new Meta( $this->test_term->term_id );

		$special_key   = 'some@key#with!special$chars';
		$special_value = '!@#$%^&*()_+={}[]|:;"\'<>,.?/~`';

		$meta->set( $special_key, $special_value );
		$this->assertEquals( $special_value, $meta->get( $special_key ) );
	}

	/** @test */
	public function it_should_handle_non_string_values() {
		$meta = new Meta( $this->test_term->term_id );

		$meta->set( 'integer_key', 123 );
		$this->assertEquals( 123, $meta->get( 'integer_key' ) );

		$meta->set( 'bool_key', true );
		$this->assertEquals( true, $meta->get( 'bool_key' ) );

		$array_value = [ 'red', 'blue', 'green' ];
		$meta->set( 'array_key', $array_value );
		$this->assertEquals( $array_value, maybe_unserialize( $meta->get( 'array_key' ) ) );

		$object_value = (object) [ 'foo' => 'bar' ];
		$meta->set( 'object_key', $object_value );
		$this->assertEquals( $object_value, maybe_unserialize( $meta->get( 'object_key' ) ) );
	}

	/** @test */
	public function it_should_overwrite_existing_meta_values() {
		$meta = new Meta( $this->test_term->term_id );

		$meta->set( 'color', '#ff0000' );
		$this->assertEquals( '#ff0000', $meta->get( 'color' ) );

		$meta->set( 'color', '#00ff00' );
		$this->assertEquals( '#00ff00', $meta->get( 'color' ) );
	}

	/** @test */
	public function it_should_not_fail_when_deleting_a_non_existent_key() {
		$meta = new Meta( $this->test_term->term_id );

		$result = $meta->delete( 'non_existent_key' );
		$this->assertInstanceOf( Meta::class, $result );
		$this->assertNull( $meta->get( 'non_existent_key' ) );
	}
}
