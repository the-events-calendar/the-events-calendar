<?php
/**
 * Handles the enqueueing of category color CSS assets.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\CSS
 */

namespace TEC\Events\Category_Colors\CSS;

use TEC\Common\StellarWP\Assets\Asset;
use TEC\Events\Category_Colors\Repositories\Category_Color_Dropdown_Provider;
use Tribe__Events__Main;

/**
 * Class for managing CSS assets related to category colors.
 *
 * @since TBD
 */
class Assets {
	/**
	 * The Generator instance.
	 *
	 * @since TBD
	 *
	 * @var Generator
	 */
	protected Generator $generator;

	/**
	 * Constructor.
	 *
	 * @since TBD
	 *
	 * @param Generator $generator The Generator instance.
	 */
	public function __construct( Generator $generator ) {
		$this->generator = $generator;
	}

	/**
	 * Enqueues frontend styles and inline category color CSS.
	 *
	 * @since TBD
	 */
	public function enqueue_frontend_scripts(): void {
		/**
		 * Filters whether the Category Colors frontend UI should be displayed.
		 *
		 * @since TBD
		 *
		 * @param bool $show_frontend_ui Whether the frontend UI should be displayed.
		 */
		$show_frontend_ui = apply_filters( 'tec_events_category_colors_show_frontend_ui', true );

		// Early bail if frontend UI should not be displayed.
		if ( ! $show_frontend_ui ) {
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
		if ( ! empty( $css ) ) {
			wp_add_inline_style( 'tec-category-colors-frontend-styles', $css );
		}
	}

	/**
	 * Determines whether to enqueue the frontend styles for category colors.
	 *
	 * @since TBD
	 *
	 * @return bool True if frontend styles should be enqueued, false otherwise.
	 */
	public function should_enqueue_frontend_styles(): bool {
		/**
		 * Filter whether the category colors frontend styles should be enqueued.
		 *
		 * @since TBD
		 *
		 * @param bool   $should_enqueue Whether the styles should be enqueued.
		 * @param Assets $assets           The Assets instance.
		 */
		return (bool) apply_filters(
			'tec_events_category_colors_should_enqueue_frontend_styles',
			true,
			$this
		);
	}

	/**
	 * Determines whether to enqueue the frontend legend styles for category colors.
	 *
	 * @since TBD
	 *
	 * @return bool True if frontend legend styles should be enqueued, false otherwise.
	 */
	public function should_enqueue_frontend_legend(): bool {
		// Only enqueue legend styles if custom CSS is not enabled.
		$should_enqueue = ! tribe_get_option( 'category-color-custom-css', false );

		/**
		 * Filter whether the category colors frontend legend styles should be enqueued.
		 *
		 * @since TBD
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
