<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe\Tests\Traits\With_Uopz;

class Category_Color_PickerTest extends HtmlPartialTestCase {
	use With_Uopz;

	protected $partial_path = 'components/top-bar/category-color-picker';

	/**
	 * @before
	 */
	public function before() {
		// Always return the same value when creating nonces.
		$this->set_fn_return( 'wp_create_nonce', '1234567890' );
	}

	/**
	 * Test render with category colors enabled in Month view with superpowers enabled
	 */
	public function test_render_with_category_colors_enabled_month_view_superpowers_enabled() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'category_colors_enabled'           => true,
			'category_colors_category_dropdown' => [
				[
					'slug'     => 'test-category-1',
					'name'     => 'Test Category 1',
					'priority' => 1,
					'primary'  => '#ff0000',
					'hidden'   => false,
				],
				[
					'slug'     => 'test-category-2',
					'name'     => 'Test Category 2',
					'priority' => 2,
					'primary'  => '#00ff00',
					'hidden'   => false,
				],
			],
			'category_colors_super_power'       => true,
			'category_colors_show_reset_button' => true,
		] ) );
	}

	/**
	 * Test render with category colors enabled in Month view with superpowers disabled
	 */
	public function test_render_with_category_colors_enabled_month_view_superpowers_disabled() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'category_colors_enabled'           => true,
			'category_colors_category_dropdown' => [
				[
					'slug'     => 'test-category-1',
					'name'     => 'Test Category 1',
					'priority' => 1,
					'primary'  => '#ff0000',
					'hidden'   => false,
				],
				[
					'slug'     => 'test-category-2',
					'name'     => 'Test Category 2',
					'priority' => 2,
					'primary'  => '#00ff00',
					'hidden'   => false,
				],
			],
			'category_colors_super_power'       => false,
			'category_colors_show_reset_button' => false,
		] ) );
	}

	/**
	 * Test render with category colors enabled in List view with superpowers enabled
	 */
	public function test_render_with_category_colors_enabled_list_view_superpowers_enabled() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'category_colors_enabled'           => true,
			'category_colors_category_dropdown' => [
				[
					'slug'     => 'test-category-1',
					'name'     => 'Test Category 1',
					'priority' => 1,
					'primary'  => '#ff0000',
					'hidden'   => false,
				],
				[
					'slug'     => 'test-category-2',
					'name'     => 'Test Category 2',
					'priority' => 2,
					'primary'  => '#00ff00',
					'hidden'   => false,
				],
			],
			'category_colors_super_power'       => true,
			'category_colors_show_reset_button' => true,
		] ) );
	}

	/**
	 * Test render with category colors enabled in List view with superpowers disabled
	 */
	public function test_render_with_category_colors_enabled_list_view_superpowers_disabled() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'category_colors_enabled'           => true,
			'category_colors_category_dropdown' => [
				[
					'slug'     => 'test-category-1',
					'name'     => 'Test Category 1',
					'priority' => 1,
					'primary'  => '#ff0000',
					'hidden'   => false,
				],
				[
					'slug'     => 'test-category-2',
					'name'     => 'Test Category 2',
					'priority' => 2,
					'primary'  => '#00ff00',
					'hidden'   => false,
				],
			],
			'category_colors_super_power'       => false,
			'category_colors_show_reset_button' => false,
		] ) );
	}

	/**
	 * Test render with category colors enabled in Day view with superpowers enabled
	 */
	public function test_render_with_category_colors_enabled_day_view_superpowers_enabled() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'category_colors_enabled'           => true,
			'category_colors_category_dropdown' => [
				[
					'slug'     => 'test-category-1',
					'name'     => 'Test Category 1',
					'priority' => 1,
					'primary'  => '#ff0000',
					'hidden'   => false,
				],
				[
					'slug'     => 'test-category-2',
					'name'     => 'Test Category 2',
					'priority' => 2,
					'primary'  => '#00ff00',
					'hidden'   => false,
				],
			],
			'category_colors_super_power'       => true,
			'category_colors_show_reset_button' => true,
		] ) );
	}

	/**
	 * Test render with category colors enabled in Day view with superpowers disabled
	 */
	public function test_render_with_category_colors_enabled_day_view_superpowers_disabled() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'category_colors_enabled'           => true,
			'category_colors_category_dropdown' => [
				[
					'slug'     => 'test-category-1',
					'name'     => 'Test Category 1',
					'priority' => 1,
					'primary'  => '#ff0000',
					'hidden'   => false,
				],
				[
					'slug'     => 'test-category-2',
					'name'     => 'Test Category 2',
					'priority' => 2,
					'primary'  => '#00ff00',
					'hidden'   => false,
				],
			],
			'category_colors_super_power'       => false,
			'category_colors_show_reset_button' => false,
		] ) );
	}

	/**
	 * Test render with category colors disabled
	 */
	public function test_render_with_category_colors_disabled() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'category_colors_enabled'           => false,
			'category_colors_category_dropdown' => [],
			'category_colors_super_power'       => false,
			'category_colors_show_reset_button' => false,
		] ) );
	}
} 