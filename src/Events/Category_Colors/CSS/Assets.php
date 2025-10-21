<?php
/**
 * Handles the enqueueing of category color CSS assets.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\CSS
 */

namespace TEC\Events\Category_Colors\CSS;

use TEC\Common\StellarWP\Assets\Asset;
use TEC\Events\Category_Colors\Repositories\Category_Color_Dropdown_Provider;
use Tribe__Events__Main;
use TEC\Events\Category_Colors\Controller;

/**
 * Class for managing CSS assets related to category colors.
 *
 * @since 6.14.0
 */
class Assets {
	/**
	 * The Generator instance.
	 *
	 * @since 6.14.0
	 *
	 * @var Generator
	 */
	protected Generator $generator;

	/**
	 * Constructor.
	 *
	 * @since 6.14.0
	 *
	 * @param Generator $generator The Generator instance.
	 */
	public function __construct( Generator $generator ) {
		$this->generator = $generator;
	}

	/**
	 * Enqueues frontend styles and inline category color CSS.
	 *
	 * @since 6.14.0
	 * @since 6.15.9 - Added more robust conditional for inline styles.
	 */
	public function enqueue_frontend_scripts(): void {
		// Early bail if frontend UI should not be displayed.
		if ( ! tribe( Controller::class )->should_show_frontend_ui() ) {
			return;
		}

		// Check if there are categories with colors.
		$dropdown_provider = tribe( Category_Color_Dropdown_Provider::class );
		if ( ! $dropdown_provider->has_dropdown_categories() ) {
			return;
		}

		// Add main CSS file.
		Asset::add(
			'tec-category-colors-frontend-styles',
			'/css/category-colors/frontend-category.css',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( Tribe__Events__Main::class . '-packages' )
			->add_to_group( 'tec-events-category-colors' )
			->set_condition( [ $this, 'should_enqueue_frontend_styles' ] )
			->enqueue_on( 'tribe_events_views_v2_after_make_view' )
			->register();
		Asset::add(
			'tec-category-colors-frontend-legend-styles',
			'/css/category-colors/category-legend.css',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( Tribe__Events__Main::class . '-packages' )
			->add_to_group( 'tec-events-category-colors' )
			->set_condition( [ $this, 'should_enqueue_frontend_legend' ] )
			->enqueue_on( 'tribe_events_views_v2_after_make_view' )
			->register();
		Asset::add(
			'tec-category-colors-frontend-scripts',
			'/js/views/category-color-selector.js',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( Tribe__Events__Main::class . '-packages' )
			->add_to_group( 'tec-events-category-colors' )
			->set_condition( [ $this, 'should_enqueue_frontend_styles' ] )
			->enqueue_on( 'tribe_events_views_v2_after_make_view' )
			->register();

		// Retrieve the dynamically generated category color CSS.
		$css = get_option( $this->generator->get_option_key(), '' );

		// Add inline styles if available.
		if ( ! empty( $css ) && $this->should_enqueue_frontend_styles() ) {
			wp_add_inline_style( 'tec-category-colors-frontend-styles', $css );
		}
	}

	/**
	 * Determines whether to enqueue the frontend styles for category colors.
	 *
	 * @since 6.14.0
	 * @since 6.15.9 - Add logic to only enqueue on event archive pages.
	 *
	 * @return bool True if frontend styles should be enqueued, false otherwise.
	 */
	public function should_enqueue_frontend_styles(): bool {
		/*
		Allow enqueueing on valid TEC views or frontend pages (e.g., venues),
		but prevent it on single event or recurring event instance pages.
		 */
		$should_enqueue = ( ! is_singular( Tribe__Events__Main::POSTTYPE ) && ( tec_is_valid_view() || tribe_is_frontend() ) );

		/**
		 * Filter whether the category colors frontend styles should be enqueued.
		 *
		 * @since 6.14.0
		 *
		 * @param bool   $should_enqueue Whether the styles should be enqueued.
		 * @param Assets $assets           The Assets instance.
		 */
		return (bool) apply_filters(
			'tec_events_category_colors_should_enqueue_frontend_styles',
			$should_enqueue,
			$this
		);
	}

	/**
	 * Determines whether to enqueue the frontend legend styles for category colors.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if frontend legend styles should be enqueued, false otherwise.
	 */
	public function should_enqueue_frontend_legend(): bool {
		// Only enqueue legend styles if custom CSS is not enabled.
		$should_enqueue = ! tribe_get_option( 'category-color-custom-css', false ) && $this->should_enqueue_frontend_styles();

		/**
		 * Filter whether the category colors frontend legend styles should be enqueued.
		 *
		 * @since 6.14.0
		 *
		 * @param bool   $should_enqueue Whether the legend styles should be enqueued.
		 * @param Assets $assets           The Assets instance.
		 */
		return (bool) apply_filters(
			'tec_events_category_colors_should_enqueue_frontend_legend',
			$should_enqueue,
			$this
		);
	}
}
