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
				'category_color' => '-',         // Invalid colors should display as dash
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

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_columns
	 */
	public function should_add_correct_column_headers() {
		$columns = $this->quick_edit->add_columns([]);
		
		$this->assertArrayHasKey('category_priority', $columns);
		$this->assertArrayHasKey('category_color', $columns);
		
		$this->assertEquals('Priority', $columns['category_priority']);
		$this->assertEquals('Category Color', $columns['category_color']);
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_quick_edit_fields
	 */
	public function should_add_quick_edit_fields() {
		// Create a test category
		$term_id = $this->factory()->term->create([
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'name' => 'Test Category',
		]);

		// Set up the screen
		$screen = new \stdClass();
		$screen->taxonomy = Tribe__Events__Main::TAXONOMY;

		// Capture the output
		ob_start();
		$this->quick_edit->add_quick_edit_fields('category_color', $screen);
		$output = ob_get_clean();

		// Verify the output contains the expected elements
		$this->assertStringContainsString('tec-events-category-colors__container', $output);
		$this->assertStringContainsString('tec-events-category-colors__grid', $output);
		$this->assertStringContainsString('tec-events-category-colors__group', $output);
		$this->assertStringContainsString('tec-events-category-colors__preview', $output);
		$this->assertStringContainsString('tec-events-category-colors__priority', $output);
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 */
	public function should_handle_invalid_term_id() {
		// Use a non-existent term ID
		$term_id = 999999;

		// Test that we get default values for invalid term
		$content = $this->quick_edit->add_custom_column_data('', 'category_priority', $term_id);
		$this->assertEquals('', $content, 'Should return empty string for invalid term ID');

		$content = $this->quick_edit->add_custom_column_data('', 'category_color', $term_id);
		$this->assertEquals('', $content, 'Should return empty string for invalid term ID');
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 */
	public function should_handle_invalid_column_name() {
		$term_id = $this->factory()->term->create([
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'name' => 'Test Category',
		]);

		$content = $this->quick_edit->add_custom_column_data('', 'invalid_column', $term_id);
		$this->assertEquals('', $content, 'Should return empty string for invalid column name');
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
		$this->quick_edit->add_quick_edit_fields('category_color', $screen);
		$output = ob_get_clean();

		// Verify no output was generated
		$this->assertEmpty(trim($output), 'Should not output anything for wrong screen');
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_quick_edit_fields
	 */
	public function should_not_add_quick_edit_fields_for_wrong_column() {
		// Set up the screen
		$screen = new \stdClass();
		$screen->taxonomy = Tribe__Events__Main::TAXONOMY;

		// Capture the output
		ob_start();
		$this->quick_edit->add_quick_edit_fields('invalid_column', $screen);
		$output = ob_get_clean();

		// Verify no output was generated
		$this->assertEmpty(trim($output), 'Should not output anything for wrong column');
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_quick_edit_fields
	 */
	public function should_add_quick_edit_fields_for_category_color_column() {
		// Set up the screen
		$screen = new \stdClass();
		$screen->taxonomy = Tribe__Events__Main::TAXONOMY;

		// Capture the output
		ob_start();
		$this->quick_edit->add_quick_edit_fields('category_color', $screen);
		$output = ob_get_clean();

		// Verify the output contains the expected elements
		$this->assertStringContainsString('tec-events-category-colors__container', $output);
		$this->assertStringContainsString('tec-events-category-colors__grid', $output);
		$this->assertStringContainsString('tec-events-category-colors__group', $output);
		$this->assertStringContainsString('tec-events-category-colors__preview', $output);
		$this->assertStringContainsString('tec-events-category-colors__priority', $output);
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_quick_edit_fields
	 */
	public function should_handle_invalid_screen_parameters() {
		// Test with non-object screen
		ob_start();
		$this->quick_edit->add_quick_edit_fields('category_color', 'not_an_object');
		$output = ob_get_clean();
		$this->assertEmpty(trim($output), 'Should not output anything for non-object screen');

		// Test with screen missing taxonomy
		$screen = new \stdClass();
		ob_start();
		$this->quick_edit->add_quick_edit_fields('category_color', $screen);
		$output = ob_get_clean();
		$this->assertEmpty(trim($output), 'Should not output anything for screen missing taxonomy');

		// Test with empty screen
		ob_start();
		$this->quick_edit->add_quick_edit_fields('category_color', null);
		$output = ob_get_clean();
		$this->assertEmpty(trim($output), 'Should not output anything for empty screen');
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 */
	public function should_handle_edge_case_priority_values() {
		$term_id = $this->factory()->term->create([
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'name' => 'Test Category',
		]);

		$meta = $this->meta->set_term($term_id);
		$priority_key = $this->meta_keys->get_key('priority');

		// Test with PHP_INT_MAX
		$meta->set($priority_key, PHP_INT_MAX);
		$meta->save();
		$content = $this->quick_edit->add_custom_column_data('', 'category_priority', $term_id);
		$this->assertEquals((string)PHP_INT_MAX, $content, 'Should handle PHP_INT_MAX priority value');

		// Test with negative value
		$meta->set($priority_key, -1);
		$meta->save();
		$content = $this->quick_edit->add_custom_column_data('', 'category_priority', $term_id);
		$this->assertEquals('1', $content, 'Should convert negative priority to positive');

		// Test with non-numeric value
		$meta->set($priority_key, 'not_a_number');
		$meta->save();
		$content = $this->quick_edit->add_custom_column_data('', 'category_priority', $term_id);
		$this->assertEquals('0', $content, 'Should handle non-numeric priority value');
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 */
	public function should_handle_html_entities_in_color_values() {
		$term_id = $this->factory()->term->create([
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'name' => 'Test Category',
		]);

		$meta = $this->meta->set_term($term_id);
		$primary_key = $this->meta_keys->get_key('primary');
		$secondary_key = $this->meta_keys->get_key('secondary');
		$text_key = $this->meta_keys->get_key('text');

		// Set colors with HTML entities
		$meta->set($primary_key, '#ff&gt;00');
		$meta->set($secondary_key, '#00&lt;ff');
		$meta->set($text_key, '#000&quot;00');
		$meta->save();

		$content = $this->quick_edit->add_custom_column_data('', 'category_color', $term_id);
		
		// Since the colors are invalid, we should get a dash
		$this->assertEquals('-', $content, 'Should return dash for invalid color values');
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 */
	public function should_handle_empty_color_values() {
		$term_id = $this->factory()->term->create([
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'name' => 'Test Category',
		]);

		$meta = $this->meta->set_term($term_id);
		$primary_key = $this->meta_keys->get_key('primary');
		$secondary_key = $this->meta_keys->get_key('secondary');
		$text_key = $this->meta_keys->get_key('text');

		// Set empty color values
		$meta->set($primary_key, '');
		$meta->set($secondary_key, '');
		$meta->set($text_key, '');
		$meta->save();

		$content = $this->quick_edit->add_custom_column_data('', 'category_color', $term_id);
		$this->assertEquals('-', $content, 'Should display dash for empty color values');
	}

	/**
	 * @test
	 * @covers \TEC\Events\Category_Colors\Admin\Quick_Edit::add_custom_column_data
	 */
	public function should_handle_valid_color_values() {
		$term_id = $this->factory()->term->create([
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'name' => 'Test Category',
		]);

		$meta = $this->meta->set_term($term_id);
		$primary_key = $this->meta_keys->get_key('primary');
		$secondary_key = $this->meta_keys->get_key('secondary');
		$text_key = $this->meta_keys->get_key('text');

		// Set valid color values
		$meta->set($primary_key, '#ff0000');
		$meta->set($secondary_key, '#00ff00');
		$meta->set($text_key, '#0000ff');
		$meta->save();

		$content = $this->quick_edit->add_custom_column_data('', 'category_color', $term_id);

		// Parse the HTML content
		$dom = new \DOMDocument();
		$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		$span = $dom->getElementsByTagName('span')->item(0);

		// Check that colors are properly set
		$this->assertEquals('#ff0000', $span->getAttribute('data-primary'), 'Should set primary color correctly');
		$this->assertEquals('#00ff00', $span->getAttribute('data-secondary'), 'Should set secondary color correctly');
		$this->assertEquals('#0000ff', $span->getAttribute('data-text'), 'Should set text color correctly');
	}
} 