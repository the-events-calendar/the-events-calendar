<?php
/**
 * Test the Add Category functionality for Category Colors.
 *
 * @since   6.14.0
 *
 * @package TEC\Events\Tests\Integration\Category_Colors
 */

namespace TEC\Events\Tests\Integration\Category_Colors\Admin;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\Admin\Add_Category;
use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys_Trait;
use Tribe__Events__Main;

class Add_Category_Test extends WPTestCase {
	use Meta_Keys_Trait;

	/**
	 * @var Add_Category
	 */
	protected $add_category;

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
		$this->add_category = tribe( Add_Category::class );
		$this->meta         = tribe( Event_Category_Meta::class );

		// Store original $_POST state
		$this->original_post = $_POST;
	}

	/**
	 * @after
	 */
	public function cleanup_test() {
		$this->add_category = null;
		$this->meta         = null;

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
			'post_data'       => [
				'primary'   => '#ff0000',
				'secondary' => '#00ff00',
				'text'      => '#000000',
				'priority'  => '1',
			],
			'expected_values' => [
				'primary'   => '#ff0000',
				'secondary' => '#00ff00',
				'text'      => '#000000',
				'priority'  => '1',
			],
		];

		yield 'should sanitize color values according to WordPress behavior' => [
			'post_data'       => [
				'primary'   => 'invalid-color',
				'secondary' => '#invalid',
				'text'      => 'not-a-color',
				'priority'  => 'invalid',
			],
			'expected_values' => [
				'primary'   => '',
				'secondary' => '',
				'text'      => '',
				'priority'  => '0',
			],
		];

		yield 'should handle empty POST data' => [
			'post_data'       => [],
			'expected_values' => [
				'primary'   => '',
				'secondary' => '',
				'text'      => '',
				'priority'  => '',
			],
		];

		yield 'should handle missing keys in POST data' => [
			'post_data'       => [
				'primary'  => '#ff0000',
				// Missing secondary and text
				'priority' => '2',
			],
			'expected_values' => [
				'primary'   => '#ff0000',
				'secondary' => '',
				'text'      => '',
				'priority'  => '2',
			],
		];

		yield 'should handle priority overflow' => [
			'post_data'       => [
				'primary'   => '#ff0000',
				'secondary' => '#00ff00',
				'text'      => '#000000',
				'priority'  => (string) PHP_INT_MAX,
			],
			'expected_values' => [
				'primary'   => '#ff0000',
				'secondary' => '#00ff00',
				'text'      => '#000000',
				'priority'  => (string) PHP_INT_MAX,
			],
		];

		yield 'should handle negative priority' => [
			'post_data'       => [
				'primary'   => '#ff0000',
				'secondary' => '#00ff00',
				'text'      => '#000000',
				'priority'  => '-1',
			],
			'expected_values' => [
				'primary'   => '#ff0000',
				'secondary' => '#00ff00',
				'text'      => '#000000',
				'priority'  => '1',
			],
		];

		yield 'should handle non-numeric priority' => [
			'post_data'       => [
				'primary'   => '#ff0000',
				'secondary' => '#00ff00',
				'text'      => '#000000',
				'priority'  => 'abc',
			],
			'expected_values' => [
				'primary'   => '#ff0000',
				'secondary' => '#00ff00',
				'text'      => '#000000',
				'priority'  => '0',
			],
		];
	}

	/**
	 * @test
	 * @covers       \TEC\Events\Category_Colors\Admin\Add_Category::save_category_fields
	 * @dataProvider category_field_scenarios
	 */
	public function should_handle_category_field_scenarios( array $post_data, array $expected_values ) {
		// Create a test category
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Test Category',
			]
		);

		// Simulate POST data
		$_POST['tec_events_category-color'] = $post_data;
		// Generate a valid nonce.
		$_POST['tec_category_colors_nonce'] = wp_create_nonce( 'save_category_colors' );

		// Save the fields
		$this->add_category->save_category_fields( $term_id );

		// Verify the meta values
		$meta = $this->meta->set_term( $term_id );
		foreach ( $expected_values as $key => $expected_value ) {
			$this->assertEquals(
				$expected_value,
				$meta->get( $this->get_key( $key ) ),
				sprintf( 'Failed asserting that %s value matches expected value', $key )
			);
		}
	}
}
