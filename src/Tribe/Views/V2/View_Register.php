<?php
/**
 * The View registration facade.
 *
 * @package Tribe\Events\Views\V2
 * @since   5.7.0
 */

namespace Tribe\Events\Views\V2;

use Tribe__Events__Main;
use Tribe__Events__Organizer;
use Tribe__Events__Venue;

/**
 * Class View_Register
 *
 * @package Tribe\Events\Views\V2
 * @since   5.7.0
 * @since   5.10.0 Added feature to define the route slug used for this view, decoupled from the view slug.
 */
class View_Register {
	/**
	 * Slug for locating the view file.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Name for the view.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Class name for the view.
	 *
	 * @var string
	 */
	protected $class;

	/**
	 * Priority order for the view registration.
	 *
	 * @var int
	 */
	protected $priority;

	/**
	 * The slug applied to the route for this view.
	 *
	 * @var string
	 */
	protected $route_slug;

	/**
	 * View_Register constructor.
	 *
	 * @param string $slug Slug for locating the view file.
	 * @param string $name Name for the view.
	 * @param string $class Class name for the view.
	 * @param int $priority Priority order for the view registration.
	 * @param string $route_slug The slug applied to the route for this view.
	 */
	public function __construct( $slug, $name, $class, $priority = 40, $route_slug = null ) {
		$this->slug       = $slug;
		$this->route_slug = $route_slug ?: $slug;
		$this->name       = $name;
		$this->class      = $class;
		$this->priority   = $priority;

		$this->add_actions();
		$this->add_filters();

		$asset_registration_object = call_user_func( $this->class . '::get_asset_origin', $this->slug );
		call_user_func( $this->class . '::register_assets', $asset_registration_object );
	}

	/**
	 * Adds actions for view registration.
	 *
	 * @since 5.7.0
	 */
	protected function add_actions() {
		add_action( 'tribe_events_pre_rewrite', [ $this, 'filter_add_routes' ], 5 );
		add_action( 'wp', [ $this, 'add_canonical_tag' ] );
	}

	/**
	 * Adds filters for view registration.
	 *
	 * @since 5.7.0
	 */
	protected function add_filters() {
		add_filter( 'tribe_events_views', [ $this, 'filter_events_views' ] );
		add_filter( 'tribe-events-bar-views', [ $this, 'filter_tec_bar_views' ], $this->priority );
		add_filter( 'tribe_events_rewrite_base_slugs', [ $this, 'filter_add_base_slugs' ], $this->priority );
		add_filter( 'tribe_events_rewrite_matchers_to_query_vars_map', [ $this, 'filter_add_matchers_to_query_vars_map' ], $this->priority, 1 );
	}

	/**
	 * Add rewrite routes for custom PRO stuff and views.
	 *
	 * @since 5.7.0
	 * @since 5.10.0 Adds optional decoupling of view name to route slug
	 *
	 * @param \Tribe__Events__Rewrite $rewrite The Tribe__Events__Rewrite object
	 *
	 * @return void
	 */
	public function filter_add_routes( $rewrite ) {
		// Setup base rewrite rules
		$rewrite
			->archive( [ '{{ ' . $this->slug . ' }}', '{{ page }}', '(\d+)' ], [ 'eventDisplay' => $this->slug, 'paged' => '%1' ] )
			->archive( [ '{{ ' . $this->slug . ' }}', '{{ featured }}', '{{ page }}', '(\d+)' ], [ 'eventDisplay' => $this->slug, 'featured' => true, 'paged' => '%1' ] )
			->archive( [ '{{ ' . $this->slug . ' }}' ], [ 'eventDisplay' => $this->slug ] )
			->archive( [ '{{ ' . $this->slug . ' }}', '{{ featured }}' ], [ 'eventDisplay' => $this->slug, 'featured' => true ] )
			->archive( [ '{{ ' . $this->slug . ' }}', '(\d{4}-\d{2}-\d{2})' ], [ 'eventDisplay' => $this->slug, 'eventDate' => '%1' ] )
			->archive( [ '{{ ' . $this->slug . ' }}', '(\d{4}-\d{2}-\d{2})', '{{ featured }}' ], [ 'eventDisplay' => $this->slug, 'eventDate' => '%1', 'featured' => true ] );

		// Setup taxonomy based rewrite rules.
		$rewrite
			->tax( [ '{{ ' . $this->slug . ' }}', '{{ page }}', '(\d+)' ], [ 'eventDisplay' => $this->slug, 'paged' => '%2' ] )
			->tax( [ '{{ ' . $this->slug . ' }}', '{{ featured }}', '{{ page }}', '(\d+)' ], [ 'eventDisplay' => $this->slug, 'featured' => true, 'paged' => '%2' ] )
			->tax( [ '{{ ' . $this->slug . ' }}', '{{ featured }}' ], [ 'eventDisplay' => $this->slug, 'featured' => true ] )
			->tax( [ '{{ ' . $this->slug . ' }}' ], [ 'eventDisplay' => $this->slug ] );

		// Setup post_tag rewrite rules.
		$rewrite
			->tag( [ '{{ ' . $this->slug . ' }}', '{{ page }}', '(\d+)' ], [ 'eventDisplay' => $this->slug, 'paged' => '%2' ] )
			->tag( [ '{{ ' . $this->slug . ' }}', '{{ featured }}', '{{ page }}', '(\d+)' ], [ 'eventDisplay' => $this->slug, 'featured' => true, 'paged' => '%2' ] )
			->tag( [ '{{ ' . $this->slug . ' }}', '{{ featured }}' ], [ 'eventDisplay' => $this->slug, 'featured' => true ] )
			->tag( [ '{{ ' . $this->slug . ' }}' ], [ 'eventDisplay' => $this->slug ] );
	}

	/**
	 * Add the required bases for the Pro Views
	 *
	 * @since 5.7.0
	 * @since 5.10.0 Using the decoupled route slug.
	 *
	 * @param array $bases Bases that are already set
	 *
	 * @return array         The modified version of the array of bases
	 */
	public function filter_add_base_slugs( $bases = [] ) {
		/** @var View_Interface $view */
		$view = tribe( $this->class );
		[ $en_slug, $localized_slug ] = $view->get_rewrite_slugs();

		// Support the original and translated forms for added robustness
		$bases[ $this->slug ] = [ $en_slug, $localized_slug ];

		return $bases;
	}

	/**
	 * Add the required bases for the Summary View.
	 *
	 * @since 5.7.0
	 * @since 5.10.0 Using the decoupled route slug.
	 * @since 6.0.7 Use the en_US slug as matcher key.
	 *
	 * @param array<string,string> $matchers A map from the matcher name to the query var name.
	 *
	 * @return array<string,string>         The modified version of the array of bases.
	 */
	public function filter_add_matchers_to_query_vars_map( array $matchers = [] ) {
		// Use the en_US slug as the matcher key, the rewrite resolution is based on en_US keys.
		$matchers[ $this->slug ] = 'eventDisplay';

		return $matchers;
	}

	/**
	 * Filters the available views.
	 *
	 * @since 5.7.0
	 *
	 * @param array $views An array of available Views.
	 *
	 * @return array The array of available views, including the PRO ones.
	 */
	public function filter_events_views( array $views = [] ) {
		$views[ $this->slug ] = $this->class;

		return $views;
	}

	/**
	 * Add the view to the views selector in the TEC bar.
	 *
	 * @since 5.7.0
	 * @since 5.10.0 Using the route slug to build the `url` element.
	 *
	 * @param array $views The current array of views registered to the tribe bar.
	 *
	 * @return array The views registered with photo view added.
	 */
	public function filter_tec_bar_views( $views ) {
		$views[] = [
			'displaying'     => $this->slug,
			'anchor'         => $this->name,
			'event_bar_hook' => 'tribe_events_before_template',
			'url'            => \tribe_get_view_permalink( $this->route_slug ),
		];

		return $views;
	}

	/**
	 * The canonical tag that should be added to the page header.
	 *
	 * @since 6.0.13
	 *
	 * @param string $current_url The URL of the page being currently viewed.
	 */
	public function set_canonical_tag() {
		global $wp;

		$current_url = trailingslashit( home_url( $wp->request ) );

		echo "\n" . '<link rel="canonical" id="tec-seo-meta-tag" href="' . esc_url( $current_url ) . '" />' . "\n";
	}

	/**
	 * Add canonical tag to the head of all calendar views.
	 *
	 * @since 6.0.7
	 * @since 6.0.13
	 *
	 * @param bool $add_canonical_tag Whether or not we should add the canonical tag to the current event view.
	 */
	public function add_canonical_tag() {
		$add_canonical_tag = true;

		if ( ! tribe( Template_Bootstrap::class )->should_load() ) {
			$add_canonical_tag = false;
		}

		if (
			is_singular(
				[
					Tribe__Events__Main::POSTTYPE,
					Tribe__Events__Organizer::POSTTYPE,
					Tribe__Events__Venue::POSTTYPE,
				]
			)
		) {
			$add_canonical_tag = false;
		}

		/**
		 * Determines whether or not a canonical tag will be added to the current event view.
		 *
		 * @since 6.0.13
		 *
		 * @var bool $add_canonical_tag Whether or not we should add the current event view.
		 */
		$add_canonical_tag = apply_filters( 'tribe_events_add_canonical_tag', $add_canonical_tag );

		if ( $add_canonical_tag ) {
			add_action( 'wp_head', [ $this, 'set_canonical_tag' ] );
		}
	}
}
