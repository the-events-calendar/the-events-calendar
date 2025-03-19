<?php
/**
 * Test the CSS Generator functionality.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\CSS\Generator;
use Tribe__Events__Main;

class CSS_Generator_Test extends WPTestCase {
	/**
	 * @var Generator
	 */
	protected $css_generator;

	/**
	 * @var Event_Category_Meta
	 */
	protected $category_meta;

	/**
	 * @before
	 */
	public function setup_test_environment(): void {
		$this->css_generator = tribe(Generator::class);
		$this->category_meta = tribe(Event_Category_Meta::class);
	}

	/**
	 * @test
	 * @dataProvider css_generator_data_provider
	 */
	public function should_generate_correct_css($category_name, $foreground, $background, $text_color, $expected_css) {
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
} 