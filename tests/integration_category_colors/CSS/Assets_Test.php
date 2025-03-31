<?php

namespace Tribe\Events\Category_Colors\CSS;

use TEC\Events\Category_Colors\CSS\Assets;
use TEC\Events\Category_Colors\CSS\Generator;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;

class Assets_Test extends WPTestCase {
    use With_Uopz;

    /**
     * @var Assets
     */
    protected $assets;

    /**
     * @var Generator
     */
    protected $generator;

    /**
     * @before
     */
    public function before() {
        $this->generator = tribe( Generator::class );
        $this->assets = tribe( Assets::class );
        
        // Reset WordPress styles and scripts
        global $wp_styles, $wp_scripts;
        $wp_styles = new \WP_Styles();
        $wp_scripts = new \WP_Scripts();
    }

    /**
     * @test
     */
    public function should_enqueue_frontend_styles_when_category_colors_enabled() {
        // Set up test data
        $this->set_fn_return( 'tribe_get_option', true );
        
        // Register and enqueue the assets
        $this->assets->enqueue_frontend_scripts();
        do_action( 'tribe_events_views_v2_after_make_view' );

        // Verify styles are enqueued
        $this->assertTrue( wp_style_is( 'tec-category-colors-frontend-styles', 'registered' ) );
        $this->assertTrue( wp_style_is( 'tec-category-colors-frontend-legend-styles', 'registered' ) );
        $this->assertTrue( wp_script_is( 'tec-category-colors-frontend-scripts', 'registered' ) );
    }

    /**
     * @test
     */
    public function should_not_enqueue_frontend_styles_when_category_colors_disabled() {
        // Set up test data
        $this->set_fn_return( 'tribe_get_option', false );
        
        // Register and enqueue the assets
        $this->assets->enqueue_frontend_scripts();
        do_action( 'tribe_events_views_v2_after_make_view' );

        // Verify styles are not enqueued
        $this->assertFalse( wp_style_is( 'tec-category-colors-frontend-styles', 'registered' ) );
        $this->assertFalse( wp_style_is( 'tec-category-colors-frontend-legend-styles', 'registered' ) );
        $this->assertFalse( wp_script_is( 'tec-category-colors-frontend-scripts', 'registered' ) );
    }

    /**
     * @test
     */
    public function should_add_inline_css_when_category_colors_exist() {
        // Set up test data
        $this->set_fn_return( 'tribe_get_option', true );
        $test_css = '.test-category { color: #ff0000; }';
        $this->set_fn_return( 'get_option', $test_css );
        
        // Register the style first
        wp_register_style( 'tec-category-colors-frontend-styles', '' );
        
        // Register and enqueue the assets
        $this->assets->enqueue_frontend_scripts();
        do_action( 'tribe_events_views_v2_after_make_view' );

        // Verify inline styles are added
        $inline_styles = wp_styles()->get_data( 'tec-category-colors-frontend-styles', 'after' );
        $this->assertIsArray( $inline_styles );
        $this->assertContains( $test_css, $inline_styles );
    }

    /**
     * @test
     */
    public function should_not_add_inline_css_when_no_category_colors_exist() {
        // Set up test data
        $this->set_fn_return( 'tribe_get_option', true );
        $this->set_fn_return( 'get_option', '' );
        
        // Register the style first
        wp_register_style( 'tec-category-colors-frontend-styles', '' );
        
        // Register and enqueue the assets
        $this->assets->enqueue_frontend_scripts();
        do_action( 'tribe_events_views_v2_after_make_view' );

        // Verify no inline styles are added
        $inline_styles = wp_styles()->get_data( 'tec-category-colors-frontend-styles', 'after' );
        $this->assertEmpty( $inline_styles );
    }

    /**
     * @test
     */
    public function should_respect_filter_for_frontend_styles() {
        // Set up test data
        $this->set_fn_return( 'tribe_get_option', true );
        
        // Add filter to disable frontend styles
        add_filter( 'tec_events_category_colors_should_enqueue_frontend_styles', '__return_false' );
        
        // Register and enqueue the assets
        $this->assets->enqueue_frontend_scripts();
        do_action( 'tribe_events_views_v2_after_make_view' );

        // Verify styles are not enqueued
        $this->assertFalse( wp_style_is( 'tec-category-colors-frontend-styles', 'registered' ) );
        $this->assertFalse( wp_script_is( 'tec-category-colors-frontend-scripts', 'registered' ) );
    }

    /**
     * @test
     */
    public function should_respect_filter_for_frontend_legend() {
        // Set up test data
        $this->set_fn_return( 'tribe_get_option', true );
        
        // Add filter to disable legend styles
        add_filter( 'tec_events_category_colors_should_enqueue_frontend_legend', '__return_false' );
        
        // Register and enqueue the assets
        $this->assets->enqueue_frontend_scripts();
        do_action( 'tribe_events_views_v2_after_make_view' );

        // Verify legend styles are not enqueued
        $this->assertFalse( wp_style_is( 'tec-category-colors-frontend-legend-styles', 'registered' ) );
    }

    /**
     * @test
     */
    public function should_enqueue_assets_on_correct_hook() {
        // Set up test data
        $this->set_fn_return( 'tribe_get_option', true );
        
        // Register the assets
        $this->assets->enqueue_frontend_scripts();

        // Add the action to verify it exists
        add_action( 'tribe_events_views_v2_after_make_view', [ $this->assets, 'enqueue_frontend_scripts' ] );

        // Verify assets are registered to the correct hook
        $this->assertNotFalse( has_action( 'tribe_events_views_v2_after_make_view', [ $this->assets, 'enqueue_frontend_scripts' ] ) );
    }
} 