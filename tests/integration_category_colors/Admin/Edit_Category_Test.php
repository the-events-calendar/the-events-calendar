<?php
/**
 * Test the Edit Category functionality for Category Colors.
 *
 * @since TBD
 *
 * @package TEC\Events\Tests\Integration\Category_Colors
 */

namespace TEC\Events\Tests\Integration\Category_Colors\Admin;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\Admin\Edit_Category;
use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys;
use Tribe__Events__Main;

class Edit_Category_Test extends WPTestCase {
	/**
	 * @var Edit_Category
	 */
	protected $edit_category;

	/**
	 * @var Event_Category_Meta
	 */
	protected $meta;

	/**
	 * @var Meta_Keys
	 */
	protected $meta_keys;

	/**
	 * @var array
	 */
	protected $original_post;

	/**
	 * @before
	 */
	public function setup_test() {
		$this->edit_category = tribe( Edit_Category::class );
		$this->meta = tribe( Event_Category_Meta::class );
		$this->meta_keys = tribe( Meta_Keys::class );

		// Store original $_POST state
		$this->original_post = $_POST;
	}

	/**
	 * @after
	 */
	public function cleanup_test() {
		$this->edit_category = null;
		$this->meta = null;
		$this->meta_keys = null;

		// Restore original $_POST state
		$_POST = $this->original_post;
	}

	/**
	 * Data provider for category field scenarios.
	 *
	 * @return \Generator
	 */
	public function category_field_scenarios() {
		yield 'should save valid category fields' => [
			'initial_values' => [
				'primary' => '#ff0000',
				'secondary' => '#00ff00',
				'text' => '#000000',
				'priority' => '1',
			],
			'post_data' => [
				'primary' => '#0000ff',
				'secondary' => '#00ffff',
				'text' => '#ffffff',
				'priority' => '2',
			],
			'expected_values' => [
				'primary' => '#0000ff',
				'secondary' => '#00ffff',
				'text' => '#ffffff',
				'priority' => '2',
			],
		];

		yield 'should sanitize color values according to WordPress behavior' => [
			'initial_values' => [
				'primary' => '#ff0000',
				'secondary' => '#00ff00',
				'text' => '#000000',
				'priority' => '1',
			],
			'post_data' => [
				'primary' => 'invalid-color',    // Not a hex color format, should be empty
				'secondary' => '#invalid',       // Invalid hex color but matches format, should pass through
				'text' => 'not-a-color',         // Not a hex color format, should be empty
				'priority' => 'invalid',         // Invalid priority, should default to 0
			],
			'expected_values' => [
				'primary' => '',                 // Not a hex format
				'secondary' => '',       		// Expected to be empty
				'text' => '',                    // Not a hex format
				'priority' => '0',               // Invalid priority defaults to 0
			],
		];

		yield 'should preserve existing values when POST data is empty' => [
			'initial_values' => [
				'primary' => '#ff0000',
				'secondary' => '#00ff00',
				'text' => '#000000',
				'priority' => '1',
			],
			'post_data' => [],
			'expected_values' => [
				'primary' => '#ff0000',
				'secondary' => '#00ff00',
				'text' => '#000000',
				'priority' => '1',
			],
		];

		yield 'should handle new category with no existing meta' => [
			'initial_values' => [], // No initial values
			'post_data' => [
				'primary' => '#ff0000',
				'secondary' => '#00ff00',
				'text' => '#000000',
				'priority' => '1',
			],
			'expected_values' => [
				'primary' => '#ff0000',
				'secondary' => '#00ff00',
				'text' => '#000000',
				'priority' => '1',
			],
		];

		yield 'should handle missing keys in POST data' => [
			'initial_values' => [
				'primary' => '#ff0000',
				'secondary' => '#00ff00',
				'text' => '#000000',
				'priority' => '1',
			],
			'post_data' => [
				'primary' => '#0000ff',
				// Missing secondary and text
				'priority' => '2',
			],
			'expected_values' => [
				'primary' => '#0000ff',
				'secondary' => '',               // Missing key defaults to empty
				'text' => '',                    // Missing key defaults to empty
				'priority' => '2',
			],
		];

		yield 'should handle priority overflow' => [
			'initial_values' => [
				'primary' => '#ff0000',
				'secondary' => '#00ff00',
				'text' => '#000000',
				'priority' => '1',
			],
			'post_data' => [
				'primary' => '#0000ff',
				'secondary' => '#00ffff',
				'text' => '#ffffff',
				'priority' => (string) PHP_INT_MAX, // Maximum integer value
			],
			'expected_values' => [
				'primary' => '#0000ff',
				'secondary' => '#00ffff',
				'text' => '#ffffff',
				'priority' => (string) PHP_INT_MAX, // Should handle max integer value
			],
		];
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Edit_Category::save_category_fields
	 * @dataProvider category_field_scenarios
	 */
	public function should_handle_category_field_scenarios( array $initial_values, array $post_data, array $expected_values ) {
		// Create a test category
		$term_id = $this->factory()->term->create([
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'name' => 'Test Category',
		]);

		// Set initial meta values if any
		if ( ! empty( $initial_values ) ) {
			$meta = $this->meta->set_term($term_id);
			foreach ( $initial_values as $key => $value ) {
				$meta->set($this->meta_keys->get_key($key), $value);
			}
			$meta->save();
		}

		// Simulate POST data
		$_POST['tec_events_category-color'] = $post_data;

		// Save the fields
		$this->edit_category->save_category_fields($term_id);

		// Verify the meta values
		$meta = $this->meta->set_term($term_id);
		foreach ( $expected_values as $key => $expected_value ) {
			$this->assertEquals(
				$expected_value,
				$meta->get($this->meta_keys->get_key($key)),
				sprintf('Failed asserting that %s value matches expected value', $key)
			);
		}
	}
} 