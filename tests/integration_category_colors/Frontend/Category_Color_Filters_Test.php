<?php
/**
 * Test the Category Color filter functionality.
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

class Category_Color_Filters_Test extends WPTestCase {
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
		remove_all_filters( 'tec_events_category_colors_enabled_views' );

		// Bust cache to ensure clean state
		$this->dropdown_provider->bust_dropdown_categories_cache();
	}

	/**
	 * @test
	 */
	public function should_respect_global_enabled_filter_default() {
		// By default, the filter should return true
		$enabled = apply_filters( 'tec_events_category_colors_enabled', true );
		$this->assertTrue( $enabled );
	}

	/**
	 * @test
	 */
	public function should_respect_global_enabled_filter_disabled() {
		add_filter( 'tec_events_category_colors_enabled', '__return_false' );

		$enabled = apply_filters( 'tec_events_category_colors_enabled', true );
		$this->assertFalse( $enabled );
	}

	/**
	 * @test
	 */
	public function should_respect_global_enabled_filter_enabled() {
		add_filter( 'tec_events_category_colors_enabled', '__return_true' );

		$enabled = apply_filters( 'tec_events_category_colors_enabled', false );
		$this->assertTrue( $enabled );
	}

	/**
	 * @test
	 */
	public function should_respect_frontend_ui_filter_default() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Frontend UI Default Test',
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

		// By default, the filter should return true when categories exist
		$show_ui = apply_filters( 'tec_events_category_colors_show_frontend_ui', $this->dropdown_provider->has_dropdown_categories() );
		$this->assertTrue( $show_ui );
	}

	/**
	 * @test
	 */
	public function should_respect_frontend_ui_filter_disabled() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Frontend UI Disabled Test',
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

		add_filter( 'tec_events_category_colors_show_frontend_ui', '__return_false' );

		$show_ui = apply_filters( 'tec_events_category_colors_show_frontend_ui', $this->dropdown_provider->has_dropdown_categories() );
		$this->assertFalse( $show_ui );
	}

	/**
	 * @test
	 */
	public function should_respect_frontend_styles_filter_default() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Frontend Styles Default Test',
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

		// By default, the filter should return true when categories exist
		$should_enqueue = apply_filters( 'tec_events_category_colors_should_enqueue_frontend_styles', $this->dropdown_provider->has_dropdown_categories() );
		$this->assertTrue( $should_enqueue );
	}

	/**
	 * @test
	 */
	public function should_respect_frontend_styles_filter_disabled() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Frontend Styles Disabled Test',
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

		add_filter( 'tec_events_category_colors_should_enqueue_frontend_styles', '__return_false' );

		$should_enqueue = apply_filters( 'tec_events_category_colors_should_enqueue_frontend_styles', $this->dropdown_provider->has_dropdown_categories() );
		$this->assertFalse( $should_enqueue );
	}

	/**
	 * @test
	 */
	public function should_respect_frontend_legend_filter_default() {
		// By default, the filter should return true
		$should_enqueue = apply_filters( 'tec_events_category_colors_should_enqueue_frontend_legend', true );
		$this->assertTrue( $should_enqueue );
	}

	/**
	 * @test
	 */
	public function should_respect_frontend_legend_filter_disabled() {
		add_filter( 'tec_events_category_colors_should_enqueue_frontend_legend', '__return_false' );

		$should_enqueue = apply_filters( 'tec_events_category_colors_should_enqueue_frontend_legend', true );
		$this->assertFalse( $should_enqueue );
	}

	/**
	 * @test
	 */
	public function should_respect_enabled_views_filter() {
		// Test default enabled views
		$enabled_views = apply_filters( 'tec_events_category_colors_enabled_views', [ 'list', 'month' ] );
		$this->assertContains( 'list', $enabled_views );
		$this->assertContains( 'month', $enabled_views );

		// Test custom enabled views
		add_filter( 'tec_events_category_colors_enabled_views', function() {
			return [ 'day', 'week' ];
		} );

		$custom_enabled_views = apply_filters( 'tec_events_category_colors_enabled_views', [ 'list', 'month' ] );
		$this->assertContains( 'day', $custom_enabled_views );
		$this->assertContains( 'week', $custom_enabled_views );
		$this->assertNotContains( 'list', $custom_enabled_views );
		$this->assertNotContains( 'month', $custom_enabled_views );
	}

	/**
	 * @test
	 */
	public function should_combine_multiple_filters() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Multiple Filters Test',
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

		// Add multiple filters
		add_filter( 'tec_events_category_colors_show_frontend_ui', '__return_false' );
		add_filter( 'tec_events_category_colors_should_enqueue_frontend_styles', '__return_false' );
		add_filter( 'tec_events_category_colors_should_enqueue_frontend_legend', '__return_false' );

		// Test all filters are respected
		$show_ui = apply_filters( 'tec_events_category_colors_show_frontend_ui', $this->dropdown_provider->has_dropdown_categories() );
		$should_enqueue_styles = apply_filters( 'tec_events_category_colors_should_enqueue_frontend_styles', $this->dropdown_provider->has_dropdown_categories() );
		$should_enqueue_legend = apply_filters( 'tec_events_category_colors_should_enqueue_frontend_legend', true );

		$this->assertFalse( $show_ui );
		$this->assertFalse( $should_enqueue_styles );
		$this->assertFalse( $should_enqueue_legend );
	}

	/**
	 * @test
	 */
	public function should_handle_filter_removal() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Filter Removal Test',
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

		// Add filter
		add_filter( 'tec_events_category_colors_show_frontend_ui', '__return_false' );

		$show_ui_with_filter = apply_filters( 'tec_events_category_colors_show_frontend_ui', $this->dropdown_provider->has_dropdown_categories() );
		$this->assertFalse( $show_ui_with_filter );

		// Remove filter
		remove_filter( 'tec_events_category_colors_show_frontend_ui', '__return_false' );

		$show_ui_without_filter = apply_filters( 'tec_events_category_colors_show_frontend_ui', $this->dropdown_provider->has_dropdown_categories() );
		$this->assertTrue( $show_ui_without_filter );
	}

	/**
	 * @test
	 */
	public function should_handle_dynamic_filter_values() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Dynamic Filter Test',
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

		// Add dynamic filter that depends on categories
		add_filter( 'tec_events_category_colors_show_frontend_ui', function( $default ) {
			$dropdown_provider = tribe( Category_Color_Dropdown_Provider::class );
			return $dropdown_provider->has_dropdown_categories() && $default;
		} );

		$show_ui = apply_filters( 'tec_events_category_colors_show_frontend_ui', $this->dropdown_provider->has_dropdown_categories() );
		$this->assertTrue( $show_ui );

		// Remove all colors (this will make the category not have dropdown categories)
		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '' ) // Remove primary color
			->set( $this->get_key( 'secondary' ), '' ) // Remove secondary color
			->set( $this->get_key( 'text' ), '' ) // Remove text color
			->save();

		// Manually bust the cache to ensure fresh data
		$this->dropdown_provider->bust_dropdown_categories_cache();

		$show_ui_after_removal = apply_filters( 'tec_events_category_colors_show_frontend_ui', $this->dropdown_provider->has_dropdown_categories() );
		$this->assertFalse( $show_ui_after_removal );
	}
}
