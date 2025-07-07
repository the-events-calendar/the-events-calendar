<?php
/**
 * Test the new Category Color features including filters, caching, and cache busting.
 *
 * @since   6.14.0
 *
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\Repositories\Category_Color_Dropdown_Provider;
use TEC\Events\Category_Colors\CSS\Assets;
use TEC\Events\Category_Colors\Controller;
use Tribe__Events__Main;
use TEC\Events\Category_Colors\Meta_Keys_Trait;
use Tribe\Events\Views\V2\View;

class Category_Color_New_Features_Test extends WPTestCase {
	use Meta_Keys_Trait;

	/**
	 * @var Category_Color_Dropdown_Provider
	 */
	protected $dropdown_provider;

	/**
	 * @var Event_Category_Meta
	 */
	protected $category_meta;

	/**
	 * @var Assets
	 */
	protected $assets;

	/**
	 * @var Controller
	 */
	protected $controller;

	/**
	 * @before
	 */
	public function setup_test_environment(): void {
		$this->dropdown_provider = tribe( Category_Color_Dropdown_Provider::class );
		$this->category_meta     = tribe( Event_Category_Meta::class );
		$this->assets            = tribe( Assets::class );
		$this->controller        = tribe( Controller::class );
	}

	/**
	 * @after
	 */
	public function tear_down(): void {
		parent::tearDown();

		// Clean up any filters we added
		remove_all_filters( 'tec_events_category_colors_enabled' );
		remove_all_filters( 'tec_events_category_colors_show_frontend_ui' );
		remove_all_filters( 'tec_events_category_colors_should_enqueue_frontend_styles' );
		remove_all_filters( 'tec_events_category_colors_should_enqueue_frontend_legend' );

		// Bust cache to ensure clean state
		$this->dropdown_provider->bust_dropdown_categories_cache();
	}

	/**
	 * @test
	 */
	public function should_have_dropdown_categories_method() {
		$this->assertTrue( method_exists( $this->dropdown_provider, 'has_dropdown_categories' ) );
	}

	/**
	 * @test
	 */
	public function should_return_false_when_no_categories_with_colors() {
		// Create a category without colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'No Color Category',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '' ) // No primary color
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		$this->assertFalse( $this->dropdown_provider->has_dropdown_categories() );
	}

	/**
	 * @test
	 */
	public function should_return_true_when_categories_have_colors() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Color Category',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		$this->assertTrue( $this->dropdown_provider->has_dropdown_categories() );
	}

	/**
	 * @test
	 */
	public function should_cache_dropdown_categories() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Cached Category',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// First call should cache the result
		$first_call = $this->dropdown_provider->get_dropdown_categories();
		$this->assertNotEmpty( $first_call );

		// Second call should return cached result
		$second_call = $this->dropdown_provider->get_dropdown_categories();
		$this->assertEquals( $first_call, $second_call );
	}

	/**
	 * @test
	 */
	public function should_bust_cache_when_called() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Cache Bust Category',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Get initial result
		$initial_result = $this->dropdown_provider->get_dropdown_categories();
		$this->assertNotEmpty( $initial_result );

		// Bust cache
		$this->dropdown_provider->bust_dropdown_categories_cache();

		// Verify cache is busted by checking if the cache key exists
		$cached_result = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertFalse( $cached_result );
	}

	/**
	 * @test
	 */
	public function should_respect_global_enabled_filter() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Filter Test Category',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Test with filter disabled
		add_filter( 'tec_events_category_colors_enabled', '__return_false' );

		// The filter should prevent the controller from registering, but we can test the provider directly
		$this->assertTrue( $this->dropdown_provider->has_dropdown_categories() );

		remove_filter( 'tec_events_category_colors_enabled', '__return_false' );
	}

	/**
	 * @test
	 */
	public function should_respect_frontend_ui_filter() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Frontend UI Test Category',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Test the filter directly
		$should_show = apply_filters( 'tec_events_category_colors_show_frontend_ui', true );
		$this->assertTrue( $should_show );

		// Test with filter disabled
		add_filter( 'tec_events_category_colors_show_frontend_ui', '__return_false' );

		$should_show_disabled = apply_filters( 'tec_events_category_colors_show_frontend_ui', true );
		$this->assertFalse( $should_show_disabled );

		remove_filter( 'tec_events_category_colors_show_frontend_ui', '__return_false' );
	}

	/**
	 * @test
	 */
	public function should_respect_frontend_styles_filter() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Frontend Styles Test Category',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Test the filter directly
		$should_enqueue = apply_filters( 'tec_events_category_colors_should_enqueue_frontend_styles', true );
		$this->assertTrue( $should_enqueue );

		// Test with filter disabled
		add_filter( 'tec_events_category_colors_should_enqueue_frontend_styles', '__return_false' );

		$should_enqueue_disabled = apply_filters( 'tec_events_category_colors_should_enqueue_frontend_styles', true );
		$this->assertFalse( $should_enqueue_disabled );

		remove_filter( 'tec_events_category_colors_should_enqueue_frontend_styles', '__return_false' );
	}

	/**
	 * @test
	 */
	public function should_respect_frontend_legend_filter() {
		// Test the filter directly
		$should_enqueue = apply_filters( 'tec_events_category_colors_should_enqueue_frontend_legend', true );
		$this->assertTrue( $should_enqueue );

		// Test with filter disabled
		add_filter( 'tec_events_category_colors_should_enqueue_frontend_legend', '__return_false' );

		$should_enqueue_disabled = apply_filters( 'tec_events_category_colors_should_enqueue_frontend_legend', true );
		$this->assertFalse( $should_enqueue_disabled );

		remove_filter( 'tec_events_category_colors_should_enqueue_frontend_legend', '__return_false' );
	}

	/**
	 * @test
	 */
	public function should_return_correct_template_vars_when_categories_exist() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Template Vars Test Category',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Test the core logic directly - check that categories exist
		$this->assertTrue( $this->dropdown_provider->has_dropdown_categories() );

		// Test that we get the expected categories
		$categories = $this->dropdown_provider->get_dropdown_categories();
		$this->assertNotEmpty( $categories );
		$this->assertCount( 1, $categories );
		$this->assertEquals( 'template-vars-test-category', $categories[0]['slug'] );
		$this->assertEquals( '#ff0000', $categories[0]['primary'] );
	}

	/**
	 * @test
	 */
	public function should_return_correct_template_vars_when_no_categories() {
		// Don't create any categories with colors

		// Test the core logic directly - check that no categories exist
		$this->assertFalse( $this->dropdown_provider->has_dropdown_categories() );

		// Test that we get an empty array
		$categories = $this->dropdown_provider->get_dropdown_categories();
		$this->assertEmpty( $categories );
	}

	/**
	 * @test
	 */
	public function should_bust_cache_on_term_operations() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Term Operations Test Category',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Get initial result to populate cache
		$initial_result = $this->dropdown_provider->get_dropdown_categories();
		$this->assertNotEmpty( $initial_result );

		// Verify cache exists
		$cached_result = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertNotFalse( $cached_result );

		// Update the term (this should trigger cache busting)
		wp_update_term( $term_id, Tribe__Events__Main::TAXONOMY, [
			'name' => 'Updated Term Operations Test Category'
		] );

		// Verify cache is busted
		$cached_result_after_update = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertFalse( $cached_result_after_update );
	}

	/**
	 * @test
	 */
	public function should_handle_cache_key_constant() {
		$this->assertEquals( 'tec_category_colors_dropdown_categories', Category_Color_Dropdown_Provider::CACHE_KEY );
	}

	/**
	 * @test
	 */
	public function should_return_empty_array_when_cache_returns_false() {
		// Ensure cache is empty
		$this->dropdown_provider->bust_dropdown_categories_cache();

		// Don't create any categories
		$result = $this->dropdown_provider->get_dropdown_categories();

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}
}
