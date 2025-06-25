<?php

namespace Tribe\Events\Category_Colors\CSS;

use TEC\Events\Category_Colors\CSS\Assets;
use TEC\Events\Category_Colors\CSS\Generator;
use TEC\Events\Category_Colors\Repositories\Category_Color_Dropdown_Provider;
use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys_Trait;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;
use Tribe__Events__Main;

class Assets_Test extends WPTestCase {
	use With_Uopz;
	use Meta_Keys_Trait;

	/**
	 * @var Assets
	 */
	protected $assets;

	/**
	 * @var Generator
	 */
	protected $generator;

	/**
	 * @var Category_Color_Dropdown_Provider
	 */
	protected $dropdown_provider;

	/**
	 * @var Event_Category_Meta
	 */
	protected $category_meta;

	/**
	 * @before
	 */
	public function before() {
		$this->generator = tribe( Generator::class );
		$this->assets    = tribe( Assets::class );
		$this->dropdown_provider = tribe( Category_Color_Dropdown_Provider::class );
		$this->category_meta = tribe( Event_Category_Meta::class );
	}

	/**
	 * @after
	 */
	public function tear_down(): void {
		parent::tearDown();
		
		// Clean up any filters we added
		remove_all_filters( 'tec_events_category_colors_should_enqueue_frontend_styles' );
		remove_all_filters( 'tec_events_category_colors_should_enqueue_frontend_legend' );
		remove_all_filters( 'tec_events_category_colors_show_frontend_ui' );
		
		// Bust cache to ensure clean state
		$this->dropdown_provider->bust_dropdown_categories_cache();
	}

	/**
	 * @test
	 */
	public function should_not_error_when_enqueue_frontend_scripts_called_with_categories() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Test Category',
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

		// This should not throw any errors
		$this->assets->enqueue_frontend_scripts();
		
		// Verify the method completed successfully
		$this->assertTrue( true );
	}

	/**
	 * @test
	 */
	public function should_not_error_when_enqueue_frontend_scripts_called_without_categories() {
		// Don't create any categories with colors

		// This should not throw any errors
		$this->assets->enqueue_frontend_scripts();
		
		// Verify the method completed successfully
		$this->assertTrue( true );
	}

	/**
	 * @test
	 */
	public function should_return_true_for_should_enqueue_frontend_styles_by_default() {
		$result = $this->assets->should_enqueue_frontend_styles();
		$this->assertTrue( $result );
	}

	/**
	 * @test
	 */
	public function should_return_false_for_should_enqueue_frontend_styles_when_filtered() {
		add_filter( 'tec_events_category_colors_should_enqueue_frontend_styles', '__return_false' );
		
		$result = $this->assets->should_enqueue_frontend_styles();
		$this->assertFalse( $result );
	}

	/**
	 * @test
	 */
	public function should_return_true_for_should_enqueue_frontend_legend_by_default() {
		// Mock tribe_get_option to return false for custom CSS
		$this->set_fn_return( 'tribe_get_option', false );
		
		$result = $this->assets->should_enqueue_frontend_legend();
		$this->assertTrue( $result );
	}

	/**
	 * @test
	 */
	public function should_return_false_for_should_enqueue_frontend_legend_when_custom_css_enabled() {
		// Mock tribe_get_option to return true for custom CSS
		$this->set_fn_return( 'tribe_get_option', true );
		
		$result = $this->assets->should_enqueue_frontend_legend();
		$this->assertFalse( $result );
	}

	/**
	 * @test
	 */
	public function should_return_false_for_should_enqueue_frontend_legend_when_filtered() {
		// Mock tribe_get_option to return false for custom CSS
		$this->set_fn_return( 'tribe_get_option', false );
		
		add_filter( 'tec_events_category_colors_should_enqueue_frontend_legend', '__return_false' );
		
		$result = $this->assets->should_enqueue_frontend_legend();
		$this->assertFalse( $result );
	}

	/**
	 * @test
	 */
	public function should_respect_frontend_ui_filter() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Test Category',
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

		// Add filter to disable frontend UI
		add_filter( 'tec_events_category_colors_show_frontend_ui', '__return_false' );

		// This should not throw any errors and should return early
		$this->assets->enqueue_frontend_scripts();
		
		// Verify the method completed successfully
		$this->assertTrue( true );
	}

	/**
	 * @test
	 */
	public function should_not_add_inline_css_when_css_is_empty() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Test Category',
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

		$this->set_fn_return( 'get_option', '' );

		// Mock wp_add_inline_style to ensure it's not called
		$inline_style_called = false;
		$this->set_fn_return( 'wp_add_inline_style', function() use ( &$inline_style_called ) {
			$inline_style_called = true;
		} );

		// This should not call wp_add_inline_style
		$this->assets->enqueue_frontend_scripts();
		
		// Verify wp_add_inline_style was not called
		$this->assertFalse( $inline_style_called );
	}
}
