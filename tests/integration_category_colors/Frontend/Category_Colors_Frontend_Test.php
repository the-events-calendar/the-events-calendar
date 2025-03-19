<?php
/**
 * Test the Category Colors frontend functionality.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\CSS\Assets;
use TEC\Events\Category_Colors\CSS\Controller as CSS_Controller;
use TEC\Events\Category_Colors\CSS\Generator;
use TEC\Events\Category_Colors\Repositories\Category_Color_Dropdown_Provider;
use TEC\Events\Category_Colors\Repositories\Category_Color_Priority_Category_Provider;
use Tribe__Events__Main;

class Category_Colors_Frontend_Test extends WPTestCase {
	/**
	 * @var Category_Colors
	 */
	protected $category_colors;

	/**
	 * @var CSS_Controller
	 */
	protected $css_controller;

	/**
	 * @var Generator
	 */
	protected $css_generator;

	/**
	 * @var Assets
	 */
	protected $css_assets;

	/**
	 * @var Category_Color_Dropdown_Provider
	 */
	protected $dropdown_provider;

	/**
	 * @var Category_Color_Priority_Category_Provider
	 */
	protected $priority_provider;

	/**
	 * @var Event_Category_Meta
	 */
	protected $category_meta;

	/**
	 * @before
	 */
	public function setup_test_environment(): void {
		$this->category_colors = tribe(Category_Colors::class);
		$this->css_controller = tribe(CSS_Controller::class);
		$this->css_generator = tribe(Generator::class);
		$this->css_assets = tribe(Assets::class);
		$this->dropdown_provider = tribe(Category_Color_Dropdown_Provider::class);
		$this->priority_provider = tribe(Category_Color_Priority_Category_Provider::class);
		$this->category_meta = tribe(Event_Category_Meta::class);
	}

	/**
	 * @test
	 * @dataProvider css_generator_data_provider
	 */
	public function css_generator_should_generate_correct_css($category_name, $foreground, $background, $text_color, $expected_css) {
		// Create a test category with color meta
		$term_id = $this->factory()->term->create([
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'name' => $category_name,
		]);

		// Set color meta using the meta class
		$this->category_meta
			->set_term($term_id)
			->set(Category_Colors::$meta_foreground_slug, $foreground)
			->set(Category_Colors::$meta_background_slug, $background)
			->set(Category_Colors::$meta_text_color_slug, $text_color)
			->save();

		// Generate CSS
		$css = $this->css_generator->generate_css();

		// Assert CSS contains the expected rules
		foreach ($expected_css as $expected) {
			$this->assertStringContainsString($expected, $css);
		}
	}

	/**
	 * @test
	 */
	public function css_assets_should_register_properly() {
		// Register assets
		$this->css_assets->register();

		// Check if the asset is registered
		$this->assertTrue(wp_style_is('tec-category-colors', 'registered'));
	}

	/**
	 * @test
	 */
	public function css_controller_should_register_hooks() {
		// Register the controller
		$this->css_controller->register();

		// Check if the required hooks are added
		$this->assertTrue(has_action('wp_enqueue_scripts', [$this->css_controller, 'enqueue_styles']));
	}

	/**
	 * @test
	 * @dataProvider dropdown_provider_data_provider
	 */
	public function dropdown_provider_should_return_correct_options($category_name, $colors, $expected_keys) {
		// Create test category
		$term_id = $this->factory()->term->create([
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'name' => $category_name,
		]);

		// Set color meta using the meta class
		$this->category_meta
			->set_term($term_id)
			->set(Category_Colors::$meta_foreground_slug, $colors['foreground'])
			->set(Category_Colors::$meta_background_slug, $colors['background'])
			->save();

		// Get dropdown options
		$options = $this->dropdown_provider->get_dropdown_options();

		// Assert options contain the expected data
		$this->assertIsArray($options);
		foreach ($expected_keys as $key) {
			$this->assertArrayHasKey($key, $options[$term_id]);
		}
	}

	/**
	 * @test
	 * @dataProvider priority_provider_data_provider
	 */
	public function priority_provider_should_sort_categories_correctly($categories, $expected_order) {
		// Create categories with priorities
		$term_ids = [];
		foreach ($categories as $category) {
			$term_id = $this->factory()->term->create([
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name' => $category['name'],
			]);

			$this->category_meta
				->set_term($term_id)
				->set(Category_Colors::$meta_priority_slug, $category['priority'])
				->save();

			$term_ids[] = $term_id;
		}

		// Get prioritized categories
		$prioritized_categories = $this->priority_provider->get_prioritized_categories();

		// Assert categories are in correct order
		$this->assertIsArray($prioritized_categories);
		foreach ($expected_order as $index => $term_id) {
			$this->assertEquals($term_id, $prioritized_categories[$index]->term_id);
		}
	}

	/**
	 * @test
	 * @dataProvider color_validation_data_provider
	 */
	public function color_validation_should_work_correctly($color, $expected) {
		$this->assertEquals($expected, Category_Colors::validate_hex_color($color));
	}

	/**
	 * @test
	 * @dataProvider category_meta_data_provider
	 */
	public function category_meta_should_store_and_retrieve_correctly($meta_data, $expected) {
		// Create a test category
		$term_id = $this->factory()->term->create([
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'name' => 'Test Category',
		]);

		// Set color meta using the meta class
		$this->category_meta
			->set_term($term_id);

		foreach ($meta_data as $key => $value) {
			$this->category_meta->set($key, $value);
		}

		$this->category_meta->save();

		// Get meta
		$meta = $this->category_colors->get_category_color_meta($term_id);

		// Assert meta values
		foreach ($expected as $key => $value) {
			$this->assertEquals($value, $meta[$key]);
		}
	}

	/**
	 * @test
	 * @dataProvider meta_validation_data_provider
	 */
	public function meta_validation_should_handle_errors_correctly($operation, $expected_exception) {
		$this->expectException($expected_exception);

		$operation($this->category_meta);
	}

	/**
	 * Data provider for CSS generator tests
	 */
	public function css_generator_data_provider() {
		yield 'basic category colors' => [
			'category_name' => 'Test Category',
			'foreground' => '#ff0000',
			'background' => '#00ff00',
			'text_color' => '#0000ff',
			'expected_css' => [
				'.tribe-events-category-test-category',
				'background-color: #00ff00',
				'color: #0000ff',
			],
		];
	}

	/**
	 * Data provider for dropdown provider tests
	 */
	public function dropdown_provider_data_provider() {
		yield 'category with colors' => [
			'category_name' => 'Test Category',
			'colors' => [
				'foreground' => '#ff0000',
				'background' => '#00ff00',
			],
			'expected_keys' => ['color', 'name'],
		];
	}

	/**
	 * Data provider for priority provider tests
	 */
	public function priority_provider_data_provider() {
		yield 'categories with different priorities' => [
			'categories' => [
				['name' => 'High Priority', 'priority' => 1],
				['name' => 'Low Priority', 'priority' => 2],
			],
			'expected_order' => [1, 2],
		];
	}

	/**
	 * Data provider for color validation tests
	 */
	public function color_validation_data_provider() {
		yield 'valid hex colors' => [
			'color' => '#ff0000',
			'expected' => '#ff0000',
		];
		yield 'invalid color' => [
			'color' => 'invalid',
			'expected' => '',
		];
		yield 'invalid hex' => [
			'color' => '#xyz',
			'expected' => '',
		];
		yield 'empty color' => [
			'color' => '',
			'expected' => '',
		];
	}

	/**
	 * Data provider for category meta tests
	 */
	public function category_meta_data_provider() {
		yield 'complete meta data' => [
			'meta_data' => [
				Category_Colors::$meta_foreground_slug => '#ff0000',
				Category_Colors::$meta_background_slug => '#00ff00',
				Category_Colors::$meta_text_color_slug => '#0000ff',
				Category_Colors::$meta_priority_slug => 1,
			],
			'expected' => [
				'foreground' => '#ff0000',
				'background' => '#00ff00',
				'text_color' => '#0000ff',
				'priority' => 1,
			],
		];
	}

	/**
	 * Data provider for meta validation tests
	 */
	public function meta_validation_data_provider() {
		yield 'invalid term ID' => [
			'operation' => fn($meta) => $meta->set_term(0),
			'expected_exception' => \InvalidArgumentException::class,
		];
		yield 'non-existent term' => [
			'operation' => fn($meta) => $meta->set_term(999999),
			'expected_exception' => \InvalidArgumentException::class,
		];
		yield 'set without term' => [
			'operation' => fn($meta) => $meta->set(Category_Colors::$meta_foreground_slug, '#ff0000'),
			'expected_exception' => \InvalidArgumentException::class,
		];
		yield 'get without term' => [
			'operation' => fn($meta) => $meta->get(Category_Colors::$meta_foreground_slug),
			'expected_exception' => \InvalidArgumentException::class,
		];
	}
} 