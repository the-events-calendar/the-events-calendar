<?php
/**
 * Test the Quick Edit functionality for Category Colors.
 *
 * @since TBD
 *
 * @package TEC\Events\Tests\Integration\Category_Colors
 */

namespace TEC\Events\Tests\Integration\Category_Colors\Admin;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\Admin\Quick_Edit;
use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys;
use Tribe__Events__Main;

class Quick_Edit_Test extends WPTestCase {
	/**
	 * @var Quick_Edit
	 */
	protected $quick_edit;

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
		$this->quick_edit = tribe( Quick_Edit::class );
		$this->meta = tribe( Event_Category_Meta::class );
		$this->meta_keys = tribe( Meta_Keys::class );

		// Store original $_POST state
		$this->original_post = $_POST;
	}

	/**
	 * @after
	 */
	public function cleanup_test() {
		$this->quick_edit = null;
		$this->meta = null;
		$this->meta_keys = null;

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
			'initial_values' => [
				'primary' => '#ff0000',
				'secondary' => '#00ff00',
				'text' => '#000000',
				'priority' => '1',
			],
			'expected_columns' => [
				'category_priority' => '1',
				'category_color' => [
					'class' => 'tec-events-taxonomy-table__category-color-preview',
					'style' => [
						'background-color' => '#00ff00',
						'border' => '3px solid #ff0000',
					],
					'data' => [
						'primary' => '#ff0000',
						'secondary' => '#00ff00',
						'text' => '#000000',
					],
				],
			],
		];

		yield 'should display dash when colors are missing' => [
			'initial_values' => [
				'primary' => '#ff0000',
				'secondary' => '', // Missing secondary color
				'text' => '#000000',
				'priority' => '1',
			],
			'expected_columns' => [
				'category_priority' => '1',
				'category_color' => '-',
			],
		];

		yield 'should display dash when primary color is missing' => [
			'initial_values' => [
				'primary' => '', // Missing primary color
				'secondary' => '#00ff00',
				'text' => '#000000',
				'priority' => '1',
			],
			'expected_columns' => [
				'category_priority' => '1',
				'category_color' => '-',
			],
		];

		yield 'should display zero priority when not set' => [
			'initial_values' => [
				'primary' => '#ff0000',
				'secondary' => '#00ff00',
				'text' => '#000000',
				'priority' => '', // Empty priority
			],
			'expected_columns' => [
				'category_priority' => '0',
				'category_color' => [
					'class' => 'tec-events-taxonomy-table__category-color-preview',
					'style' => [
						'background-color' => '#00ff00',
						'border' => '3px solid #ff0000',
					],
					'data' => [
						'primary' => '#ff0000',
						'secondary' => '#00ff00',
						'text' => '#000000',
					],
				],
			],
		];

		yield 'should handle invalid color values' => [
			'initial_values' => [
				'primary' => 'invalid-color',    // Not a hex color format
				'secondary' => '#invalid',       // Invalid hex color but matches format
				'text' => 'not-a-color',         // Not a hex color format
				'priority' => '1',
			],
			'expected_columns' => [
				'category_priority' => '1',
				'category_color' => [
					'class' => 'tec-events-taxonomy-table__category-color-preview',
					'style' => [
						'background-color' => '#invalid',
						'border' => '3px solid invalid-color',
					],
					'data' => [
						'primary' => 'invalid-color',
						'secondary' => '#invalid',
						'text' => 'not-a-color',
					],
				],
			],
		];

		yield 'should handle missing text color' => [
			'initial_values' => [
				'primary' => '#ff0000',
				'secondary' => '#00ff00',
				'text' => '',                    // Missing text color
				'priority' => '1',
			],
			'expected_columns' => [
				'category_priority' => '1',
				'category_color' => [
					'class' => 'tec-events-taxonomy-table__category-color-preview',
					'style' => [
						'background-color' => '#00ff00',
						'border' => '3px solid #ff0000',
					],
					'data' => [
						'primary' => '#ff0000',
						'secondary' => '#00ff00',
						'text' => '',
					],
				],
			],
		];
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_columns
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 * @dataProvider quick_edit_scenarios
	 */
	public function should_handle_quick_edit_scenarios( array $initial_values, array $expected_columns ) {
		// Create a test category
		$term_id = $this->factory()->term->create([
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'name' => 'Test Category',
		]);

		// Set initial meta values
		$meta = $this->meta->set_term($term_id);
		foreach ( $initial_values as $key => $value ) {
			$meta->set($this->meta_keys->get_key($key), $value);
		}
		$meta->save();

		// Test column headers
		$columns = $this->quick_edit->add_columns([]);
		$this->assertArrayHasKey('category_priority', $columns);
		$this->assertArrayHasKey('category_color', $columns);

		// Test column data
		foreach ( $expected_columns as $column_name => $expected_value ) {
			$content = $this->quick_edit->add_custom_column_data('', $column_name, $term_id);

			if ( $column_name === 'category_priority' ) {
				$this->assertEquals(
					$expected_value,
					$content,
					sprintf('Failed asserting that %s column content matches expected value', $column_name)
				);
			} else {
				if ( $expected_value === '-' ) {
					$this->assertEquals(
						'-',
						$content,
						'Failed asserting that invalid/missing colors display as dash'
					);
				} else {
					// Parse the HTML content
					$dom = new \DOMDocument();
					$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
					$span = $dom->getElementsByTagName('span')->item(0);

					// Check class
					$this->assertEquals(
						$expected_value['class'],
						$span->getAttribute('class'),
						'Failed asserting that span has correct class'
					);

					// Check style attributes
					$style = $span->getAttribute('style');
					foreach ( $expected_value['style'] as $property => $value ) {
						$this->assertStringContainsString(
							$value,
							$style,
							sprintf('Failed asserting that style contains %s: %s', $property, $value)
						);
					}

					// Check data attributes
					foreach ( $expected_value['data'] as $key => $value ) {
						$this->assertEquals(
							$value,
							$span->getAttribute('data-' . $key),
							sprintf('Failed asserting that data-%s attribute matches expected value', $key)
						);
					}
				}
			}
		}
	}
} 