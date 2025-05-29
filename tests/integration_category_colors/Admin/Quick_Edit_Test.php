<?php
/**
 * Test the Quick Edit functionality for Category Colors.
 *
 * @since   TBD
 *
 * @package TEC\Events\Tests\Integration\Category_Colors
 */

namespace TEC\Events\Tests\Integration\Category_Colors\Admin;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\Admin\Quick_Edit;
use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys_Trait;
use Tribe__Events__Main;
use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Tests\Traits\With_Uopz;

class Quick_Edit_Test extends WPTestCase {
	use MatchesSnapshots;
	use Meta_Keys_Trait;
	use With_Uopz;

	/**
	 * @var Quick_Edit
	 */
	protected $quick_edit;

	/**
	 * @var Event_Category_Meta
	 */
	protected $meta;

	/**
	 * @var array
	 */
	protected $original_post;

	/**
	 * @before
	 */
	public function setup_test() {
		$this->quick_edit = tribe( Quick_Edit::class );
		$this->meta       = tribe( Event_Category_Meta::class );

		// Store original $_POST state
		$this->original_post = $_POST;
	}

	/**
	 * @after
	 */
	public function cleanup_test() {
		$this->quick_edit = null;
		$this->meta       = null;

		// Restore original $_POST state
		$_POST = $this->original_post;
	}

	/**
	 * Data provider for quick edit scenarios.
	 *
	 * @return \Generator
	 */
	public function quick_edit_scenarios() {
		yield 'should display valid category fields in columns' => [
			'initial_values'   => [
				'primary'   => '#ff0000',
				'secondary' => '#00ff00',
				'text'      => '#000000',
				'priority'  => '1',
			],
			'expected_columns' => [
				'category_priority' => '1',
				'category_color'    => [
					'class' => 'tec-events-taxonomy-table__category-color-preview',
					'style' => [
						'background-color' => '#00ff00',
						'border'           => '3px solid #ff0000',
					],
					'data'  => [
						'primary'   => '#ff0000',
						'secondary' => '#00ff00',
						'text'      => '#000000',
					],
				],
			],
		];

		yield 'should display dash when colors are missing' => [
			'initial_values'   => [
				'primary'   => '#ff0000',
				'secondary' => '', // Missing secondary color
				'text'      => '#000000',
				'priority'  => '1',
			],
			'expected_columns' => [
				'category_priority' => '1',
				'category_color'    => '-',
			],
		];

		yield 'should display dash when primary color is missing' => [
			'initial_values'   => [
				'primary'   => '', // Missing primary color
				'secondary' => '#00ff00',
				'text'      => '#000000',
				'priority'  => '1',
			],
			'expected_columns' => [
				'category_priority' => '1',
				'category_color'    => '-',
			],
		];

		yield 'should display zero priority when not set' => [
			'initial_values'   => [
				'primary'   => '#ff0000',
				'secondary' => '#00ff00',
				'text'      => '#000000',
				'priority'  => '', // Empty priority
			],
			'expected_columns' => [
				'category_priority' => '0',
				'category_color'    => [
					'class' => 'tec-events-taxonomy-table__category-color-preview',
					'style' => [
						'background-color' => '#00ff00',
						'border'           => '3px solid #ff0000',
					],
					'data'  => [
						'primary'   => '#ff0000',
						'secondary' => '#00ff00',
						'text'      => '#000000',
					],
				],
			],
		];

		yield 'should handle invalid color values' => [
			'initial_values'   => [
				'primary'   => 'invalid-color',    // Not a hex color format
				'secondary' => '#invalid',       // Invalid hex color but matches format
				'text'      => 'not-a-color',         // Not a hex color format
				'priority'  => '1',
			],
			'expected_columns' => [
				'category_priority' => '1',
				'category_color'    => [
					'class' => 'tec-events-taxonomy-table__category-color-preview',
					'style' => [
						'background-color' => '#invalid',
						'border'           => '3px solid invalid-color',
					],
					'data'  => [
						'primary'   => 'invalid-color',
						'secondary' => '#invalid',
						'text'      => 'not-a-color',
					],
				],
			],
		];

		yield 'should handle missing text color' => [
			'initial_values'   => [
				'primary'   => '#ff0000',
				'secondary' => '#00ff00',
				'text'      => '',                    // Missing text color
				'priority'  => '1',
			],
			'expected_columns' => [
				'category_priority' => '1',
				'category_color'    => [
					'class' => 'tec-events-taxonomy-table__category-color-preview',
					'style' => [
						'background-color' => '#00ff00',
						'border'           => '3px solid #ff0000',
					],
					'data'  => [
						'primary'   => '#ff0000',
						'secondary' => '#00ff00',
						'text'      => '',
					],
				],
			],
		];
	}

	/**
	 * @test
	 * @covers       \TEC\Events\Category_Colors\Admin\Quick_Edit::add_columns
	 * @covers       \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 * @dataProvider quick_edit_scenarios
	 */
	public function should_handle_quick_edit_scenarios( array $initial_values, array $expected_columns ) {
		// Create a test category
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Test Category',
			]
		);

		// Set initial meta values
		$meta = $this->meta->set_term( $term_id );
		foreach ( $initial_values as $key => $value ) {
			$meta->set( $this->get_key( $key ), $value );
		}
		$meta->save();

		// Test column headers
		$columns = $this->quick_edit->add_columns( [] );
		$this->assertArrayHasKey( 'category_priority', $columns );
		$this->assertArrayHasKey( 'category_color', $columns );

		// Test column data
		foreach ( $expected_columns as $column_name => $expected_value ) {
			$content = $this->quick_edit->add_custom_column_data( '', $column_name, $term_id );

			if ( $column_name === 'category_priority' ) {
				$this->assertEquals(
					$expected_value,
					$content,
					sprintf( 'Failed asserting that %s column content matches expected value', $column_name )
				);
			} else {
				if ( $expected_value === '-' ) {
					$this->assertEquals(
						'transparent',
						$content,
						'Failed asserting that invalid/missing colors display as dash'
					);
				} else {
					$this->assertMatchesSnapshot( $content );
				}
			}
		}
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_columns
	 */
	public function should_add_correct_column_headers() {
		$columns = $this->quick_edit->add_columns( [] );

		$this->assertArrayHasKey( 'category_priority', $columns );
		$this->assertArrayHasKey( 'category_color', $columns );

		$this->assertEquals( 'Priority', $columns['category_priority'] );
		$this->assertEquals( 'Category Color', $columns['category_color'] );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_quick_edit_fields
	 */
	public function should_add_quick_edit_fields() {
		// Create a test category
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Test Category',
			]
		);

		// Mock the nonce for consistent snapshot testing
		$this->set_fn_return( 'wp_create_nonce', '12345678' );

		// Capture the output
		ob_start();
		$this->quick_edit->add_quick_edit_fields( 'category_color', 'edit-tags' );
		$output = ob_get_clean();

		// Create snapshot
		$this->assertMatchesSnapshot( $output );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 */
	public function should_handle_invalid_term_id() {
		// Use a non-existent term ID
		$term_id = 999999;

		// Test that we get default values for invalid term
		$content = $this->quick_edit->add_custom_column_data( '', 'category_priority', $term_id );
		$this->assertEquals( '', $content, 'Should return empty string for invalid term ID' );

		$content = $this->quick_edit->add_custom_column_data( '', 'category_color', $term_id );
		$this->assertEquals( '', $content, 'Should return empty string for invalid term ID' );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 */
	public function should_handle_invalid_column_name() {
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Test Category',
			]
		);

		$content = $this->quick_edit->add_custom_column_data( '', 'invalid_column', $term_id );
		$this->assertEquals( '', $content, 'Should return empty string for invalid column name' );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_quick_edit_fields
	 */
	public function should_not_add_quick_edit_fields_for_wrong_screen() {
		// Set up the screen with wrong taxonomy
		$screen = 'post_tag';

		// Capture the output
		ob_start();
		$this->quick_edit->add_quick_edit_fields( 'category_color', $screen );
		$output = ob_get_clean();

		// Verify no output was generated
		$this->assertEmpty( trim( $output ), 'Should not output anything for wrong screen' );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_quick_edit_fields
	 */
	public function should_not_add_quick_edit_fields_for_wrong_column() {
		// Capture the output
		ob_start();
		$this->quick_edit->add_quick_edit_fields( 'invalid_column', 'edit-tags' );
		$output = ob_get_clean();

		// Verify no output was generated
		$this->assertEmpty( trim( $output ), 'Should not output anything for wrong column' );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_quick_edit_fields
	 */
	public function should_add_quick_edit_fields_for_category_color_column() {
		// Mock the nonce for consistent snapshot testing
		$this->set_fn_return( 'wp_create_nonce', '12345678' );

		// Capture the output
		ob_start();
		$this->quick_edit->add_quick_edit_fields( 'category_color', 'edit-tags' );
		$output = ob_get_clean();

		// Create snapshot
		$this->assertMatchesSnapshot( $output );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 */
	public function should_handle_edge_case_priority_values() {
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Test Category',
			]
		);

		$meta         = $this->meta->set_term( $term_id );
		$priority_key = $this->get_key( 'priority' );

		// Test with PHP_INT_MAX
		$meta->set( $priority_key, PHP_INT_MAX );
		$meta->save();
		$content = $this->quick_edit->add_custom_column_data( '', 'category_priority', $term_id );
		$this->assertEquals( (string) PHP_INT_MAX, $content, 'Should handle PHP_INT_MAX priority value' );

		// Test with negative value
		$meta->set( $priority_key, -1 );
		$meta->save();
		$content = $this->quick_edit->add_custom_column_data( '', 'category_priority', $term_id );
		$this->assertEquals( '1', $content, 'Should convert negative priority to positive' );

		// Test with non-numeric value
		$meta->set( $priority_key, 'not_a_number' );
		$meta->save();
		$content = $this->quick_edit->add_custom_column_data( '', 'category_priority', $term_id );
		$this->assertEquals( '0', $content, 'Should handle non-numeric priority value' );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 */
	public function should_handle_html_entities_in_color_values() {
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Test Category',
			]
		);

		$meta          = $this->meta->set_term( $term_id );
		$primary_key   = $this->get_key( 'primary' );
		$secondary_key = $this->get_key( 'secondary' );
		$text_key      = $this->get_key( 'text' );

		// Set colors with HTML entities
		$meta->set( $primary_key, '#ff&gt;00' );
		$meta->set( $secondary_key, '#00&lt;ff' );
		$meta->set( $text_key, '#000&quot;00' );
		$meta->save();

		$content = $this->quick_edit->add_custom_column_data( '', 'category_color', $term_id );

		// Since the colors are invalid, we should get a dash
		$this->assertEquals( 'transparent', $content, 'Should return transparent for invalid color values' );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 */
	public function should_handle_empty_color_values() {
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Test Category',
			]
		);

		$meta          = $this->meta->set_term( $term_id );
		$primary_key   = $this->get_key( 'primary' );
		$secondary_key = $this->get_key( 'secondary' );
		$text_key      = $this->get_key( 'text' );

		// Set empty color values
		$meta->set( $primary_key, '' );
		$meta->set( $secondary_key, '' );
		$meta->set( $text_key, '' );
		$meta->save();

		$content = $this->quick_edit->add_custom_column_data( '', 'category_color', $term_id );
		$this->assertEquals( 'transparent', $content, 'Should display transparent for empty color values' );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 */
	public function should_handle_valid_color_values() {
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Test Category',
			]
		);

		$meta          = $this->meta->set_term( $term_id );
		$primary_key   = $this->get_key( 'primary' );
		$secondary_key = $this->get_key( 'secondary' );
		$text_key      = $this->get_key( 'text' );

		// Set valid color values
		$meta->set( $primary_key, '#ff0000' );
		$meta->set( $secondary_key, '#00ff00' );
		$meta->set( $text_key, '#0000ff' );
		$meta->save();

		$content = $this->quick_edit->add_custom_column_data( '', 'category_color', $term_id );

		$this->assertMatchesSnapshot( $content );
	}

	/**
	 * Test that the color preview returns 'transparent' when no color is assigned.
	 *
	 * @since TBD
	 */
	public function should_return_transparent_when_no_color_assigned() {
		// Create a category without any color meta.
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'No Color Category',
			]
		);

		// Get the color preview HTML.
		$preview = $this->quick_edit->get_column_category_color_preview(
			tribe( Event_Category_Meta::class )->set_term( $term_id ),
			$term_id
		);

		// Verify 'transparent' is returned.
		$this->assertEquals( 'transparent', $preview, 'Should return "transparent" when no colors are set' );
	}

	/**
	 * @test
	 * Ensures the column outputs 'transparent' (no <span>) when no color is set.
	 *
     * @test
	 * @since TBD
	 */
	public function category_color_column_outputs_transparent_snapshot() {
		$term_id = $this->factory()->term->create([
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'name'     => 'No Color Category',
		]);
		$output = $this->call_protected_method(
			$this->quick_edit,
			'get_column_category_color_preview',
			[tribe(Event_Category_Meta::class)->set_term($term_id), $term_id]
		);
		$this->assertMatchesSnapshot($output);
	}

	/**
	 * @test
	 * Ensures the column outputs a <span> when a color is set.
	 *
     * @test
     * 
	 * @since TBD
	 */
	public function category_color_column_outputs_span_snapshot() {
		$term_id2 = $this->factory()->term->create([
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'name'     => 'Colored Category',
		]);
		$meta = tribe(Event_Category_Meta::class)->set_term($term_id2);
		$meta->set($this->get_key('primary'), '#ff0000');
		$meta->set($this->get_key('secondary'), '#00ff00');
		$meta->save();
		$output2 = $this->call_protected_method(
			$this->quick_edit,
			'get_column_category_color_preview',
			[$meta, $term_id2]
		);
		$this->assertMatchesSnapshot($output2);
	}

    protected function call_protected_method($object, $method, array $args = []) {
		$reflection = new \ReflectionClass($object);
		$refMethod = $reflection->getMethod($method);
		$refMethod->setAccessible(true);
		return $refMethod->invokeArgs($object, $args);
	}
}
