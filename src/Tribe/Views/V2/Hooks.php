<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ tribe( Tribe\Events\Views\V2\Hooks::class ), 'some_filtering_method' ] );
 * remove_filter( 'some_filter', [ tribe( 'views-v2.filters' ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ tribe( Tribe\Events\Views\V2\Hooks::class ), 'some_method' ] );
 * remove_action( 'some_action', [ tribe( 'views-v2.hooks' ), 'some_method' ] );
 *
 * @since 4.9.2
 *
 * @package Tribe\Events\Views\V2
 */

namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Query\Abstract_Query_Controller;
use Tribe\Events\Views\V2\Query\Event_Query_Controller;
use Tribe\Events\Views\V2\Repository\Event_Period;
use Tribe\Events\Views\V2\Template\Title;
use Tribe__Events__Main as TEC;
use Tribe__Rewrite as TEC_Rewrite;
use Tribe__Utils__Array as Arr;
use Tribe__Date_Utils as Dates;

/**
 * Class Hooks
 *
 * @since 4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
class Hooks extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.9.2
	 */
	public function register() {
		$this->container->tag( [ Event_Query_Controller::class, ], 'query_controllers' );
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Adds the actions required by each Views v2 component.
	 *
	 * @since 4.9.2
	 */
	protected function add_actions() {
		add_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
		add_action( 'tribe_common_loaded', [ $this, 'on_tribe_common_loaded' ], 1 );
		add_action( 'wp_head', [ $this, 'on_wp_head' ], 1000 );
		add_action( 'tribe_events_pre_rewrite', [ $this, 'on_tribe_events_pre_rewrite' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'action_disable_assets_v1' ], 0 );
		add_action( 'tribe_events_pro_shortcode_tribe_events_after_assets', [ $this, 'action_disable_shortcode_assets_v1' ] );
		add_action( 'updated_option', [ $this, 'action_save_wplang' ], 10, 3 );
		add_action( 'the_post', [ $this, 'manage_sensitive_info' ] );
	}

	/**
	 * Adds the filters required by each Views v2 component.
	 *
	 * @since 4.9.2
	 */
	protected function add_filters() {
		add_filter( 'wp_redirect', [ $this, 'filter_redirect_canonical' ], 10, 2 );
		add_filter( 'redirect_canonical', [ $this, 'filter_redirect_canonical' ], 10, 2 );
		add_action( 'tribe_events_parse_query', [ $this, 'parse_query' ] );
		add_filter( 'template_include', [ $this, 'filter_template_include' ], 50 );
		add_filter( 'embed_template', [ $this, 'filter_template_include' ], 50 );
		add_filter( 'posts_pre_query', [ $this, 'filter_posts_pre_query' ], 20, 2 );
		add_filter( 'body_class', [ $this, 'filter_body_class' ] );
		add_filter( 'query_vars', [ $this, 'filter_query_vars' ], 15 );
		add_filter( 'tribe_rewrite_canonical_query_args', [ $this, 'filter_map_canonical_query_args' ], 15, 3 );
		add_filter( 'admin_post_thumbnail_html', [ $this, 'filter_admin_post_thumbnail_html' ] );
		add_filter( 'excerpt_length', [ $this, 'filter_excerpt_length' ] );
		add_filter( 'tribe_events_views_v2_after_make_view', [ $this, 'action_include_filters_excerpt' ] );
		// 100 is the WordPress cookie-based auth check.
		add_filter( 'rest_authentication_errors', [ Rest_Endpoint::class, 'did_rest_authentication_errors' ], 150 );
		add_filter( 'tribe_support_registered_template_systems', [ $this, 'filter_register_template_updates' ] );
		add_filter( 'tribe_events_event_repository_map', [ $this, 'add_period_repository' ], 10, 3 );

		add_filter( 'tribe_general_settings_tab_fields', [ $this, 'filter_general_settings_tab_live_update' ], 20 );
		add_filter( 'tribe_events_rewrite_i18n_slugs_raw', [ $this, 'filter_rewrite_i18n_slugs_raw' ], 50, 2 );
		add_filter( 'tribe_get_event_after', [ $this, 'filter_events_properties' ] );
		add_filter( 'tribe_template_file', [ $this, 'filter_template_file' ], 10, 3 );

		if ( tribe_context()->doing_php_initial_state() ) {
			add_filter( 'wp_title', [ $this, 'filter_wp_title' ], 10, 2 );
			add_filter( 'document_title_parts', [ $this, 'filter_document_title_parts' ] );
			add_filter( 'pre_get_document_title', [ $this, 'pre_get_document_title' ], 20 );
		}
	}

	/**
	 * Includes includes edge cases for filtering when we need to manually overwrite theme's read
	 * more link when excerpt is cut programatically.
	 *
	 * @see   tribe_events_get_the_excerpt
	 *
	 * @since 4.9.11
	 *
	 * @return void
	 */
	public function action_include_filters_excerpt() {
		add_filter( 'excerpt_more', [ $this, 'filter_excerpt_more' ], 50 );
	}

	/**
	 * Fires to deregister v1 assets correctly.
	 *
	 * @since 4.9.11
	 *
	 * @return void
	 */
	public function action_disable_assets_v1() {
		$assets = $this->container->make( Assets::class );
		if ( ! $assets->should_enqueue_frontend() ) {
			return;
		}

		$assets->disable_v1();
	}

	/**
	 * Fires to deregister v1 assets correctly for shortcodes.
	 *
	 * @since 4.9.11
	 *
	 * @return void
	 */
	public function action_disable_shortcode_assets_v1() {
		$assets = $this->container->make( Assets::class );
		$assets->disable_v1();
	}

	/**
	 * Fires when common is loaded.
	 *
	 * @since 4.9.2
	 */
	public function on_tribe_common_loaded() {
		$this->container->make( Template_Bootstrap::class )->disable_v1();
		$this->container->make( Rest_Endpoint::class )->enable_ajax_fallback();
	}

	/**
	 * Fires when WordPress head is printed.
	 *
	 * @since 4.9.2
	 */
	public function on_wp_head() {
		$this->container->make( Template\Page::class )->maybe_hijack_main_query();
	}

	/**
	 * Fires when Tribe rewrite rules are processed.
	 *
	 * @since 4.9.2
	 *
	 * @param  \Tribe__Events__Rewrite  $rewrite  An instance of the Tribe rewrite abstraction.
	 */
	public function on_tribe_events_pre_rewrite( TEC_Rewrite $rewrite ) {
		$this->container->make( Kitchen_Sink::class )->generate_rules( $rewrite );
	}

	/**
	 * Filters the template included file.
	 *
	 * @since 4.9.2
	 *
	 * @param  string  $template  The template included file, as found by WordPress.
	 *
	 * @return string The template file to include, depending on the query and settings.
	 */
	public function filter_template_include( $template ) {
		return $this->container->make( Template_Bootstrap::class )
		                       ->filter_template_include( $template );
	}

	/**
	 * Registers the REST endpoints that will be used to return the Views HTML.
	 *
	 * @since 4.9.2
	 */
	public function register_rest_endpoints() {
		$this->container->make( Rest_Endpoint::class )->register();
	}

	/**
	 * Filters the posts before the query runs but after its SQL and arguments are finalized to
	 * inject posts in it, if needed.
	 *
	 * @since 4.9.2
	 *
	 * @param  null|array  $posts The posts to filter, a `null` value by default or an array if set by other methods.
	 * @param  \WP_Query|null  $query The query object to (maybe) control and whose posts will be populated.
	 *
	 * @return array An array of injected posts, or the original array of posts if no post injection is required.
	 */
	public function filter_posts_pre_query( $posts = null, \WP_Query $query = null ) {
		if ( is_admin() ) {
			return $posts;
		}

		/*
		 * We should only inject posts if doing PHP initial state render and if this is the main query.
		 * We can correctly use the global context as that's the only context we're interested in.
		 * Else bail early and inexpensively.
		 */
		if ( ! (
			tribe_context()->doing_php_initial_state()
			&& $query instanceof \WP_Query
			&& $query->is_main_query()
		) ) {
			return $posts;
		}

		// Verifies and only applies it to the correct queries.
		if ( tribe( Template_Bootstrap::class )->should_load( $query ) ) {
			return $posts;
		}

		foreach ( $this->container->tagged( 'query_controllers' ) as $controller ) {
			/** @var Abstract_Query_Controller $controller */
			$posts = $controller->inject_posts( $posts, $query );
		}

		return $posts;
	}

	/**
	 * Filters the publicly available query variables to add the ones supported by Views v2.
	 *
	 * To keep back-compatibility with v1 we're registering the same query vars making this method
	 * a copy of the original `Tribe__Events__Main::eventQueryVars` one.
	 *
	 * @since 4.9.2
	 *
	 * @param  array  $query_vars  The list of publicly available query variables.
	 *
	 * @return array The filtered list of publicly available query variables.
	 */
	public function filter_query_vars( array $query_vars = [] ) {
		$query_vars[] = 'eventDisplay';
		$query_vars[] = 'eventDate';
		$query_vars[] = 'eventSequence';
		$query_vars[] = 'ical';
		$query_vars[] = 'start_date';
		$query_vars[] = 'end_date';
		$query_vars[] = 'featured';
		$query_vars[] = TEC::TAXONOMY;
		$query_vars[] = 'tribe_remove_date_filters';

		return $this->container->make( Kitchen_Sink::class )->filter_register_query_vars( $query_vars );
	}

	/**
	 * Include the The Events calendar mapping for query args, into to canonical url.
	 *
	 * @since 4.9.5
	 *
	 * @param array          $map  Associative array following the format: `[ 'eventDate' => [ 'event-date', 'event_date', 'tribe-bar-date' ], ]`.
	 * @param string         $url  The input URL to resolve to a canonical one.
	 * @param Tribe__Rewrite $this This rewrite object.
	 *
	 * @return  array
	 */
	public function filter_map_canonical_query_args( $map, $url, $rewrite ) {
		$map['eventDate'] = [ 'event-date', 'event_date', 'tribe-bar-date' ];
		return $map;
	}

	/**
	 * Filters the body classes to add theme compatibility ones.
	 *
	 * @since 4.9.3
	 *
	 * @param  array $classes Classes that are been passed to the body.
	 *
	 * @return array $classes
	 */
	public function filter_body_class( $classes ) {
		$classes = $this->container->make( Theme_Compatibility::class )->filter_add_body_classes( $classes );
		$classes = $this->container->make( Template_Bootstrap::class )->filter_add_body_classes( $classes );

		return $classes;
	}

	/**
	 * Filters the `wp_title` template tag.
	 *
	 * @since 4.9.10
	 *
	 * @param      string $title The current title value.
	 * @param string|null $sep The separator char, or sequence, to use to separate the page title from the blog one.
	 *
	 * @return string The modified page title, if required.
	 */
	public function filter_wp_title( $title, $sep = null ) {
		$bootstrap = $this->container->make( Template_Bootstrap::class );
		if ( ! $bootstrap->should_load() || $bootstrap->is_single_event() ) {
			return $title;
		}

		return $this->container->make( Title::class )->filter_wp_title( $title, $sep );
	}

	/**
	 * Filters the `pre_get_document_title` to prevent conflicts when other plugins
	 * modify this initial value on our pages.
	 *
	 * @since 5.0.0
	 *
	 * @param string $title The current title value.
	 *
	 * @return string The current title or empty string.
	 */
	public function pre_get_document_title( $title ) {
		$bootstrap = $this->container->make( Template_Bootstrap::class );
		if ( ! $bootstrap->should_load() || $bootstrap->is_single_event() ) {
			return $title;
		}

		return '';
	}

	/**
	 * Filters the `wp_get_document_title` template tag.
	 *
	 * This is the template tag introduced in WP 4.4 to get the page title.
	 *
	 * @since 4.9.10
	 *
	 * @param string $title The page title.
	 *
	 * @return string The modified page title, if required.
	 */
	public function filter_document_title_parts( $title ) {
		$bootstrap = $this->container->make( Template_Bootstrap::class );
		if ( ! $bootstrap->should_load() || $bootstrap->is_single_event() ) {
			return $title;
		}


		return $this->container->make( Title::class )->filter_document_title_parts( $title );
	}

	/**
	 * Filters the `excerpt_length`.
	 *
	 * @since 4.9.10
	 *
	 * @param int $length The excerpt length.
	 *
	 * @return int The modified excerpt length, if required.
	 */
	public function filter_excerpt_length( $length ) {
		return $this->container->make( Template\Excerpt::class )->maybe_filter_excerpt_length( $length );
	}

	/**
	 * Filters the `excerpt_more`.
	 *
	 * @since 4.9.10
	 *
	 * @param string $link The excerpt read more link.
	 *
	 * @return string The modified excerpt read more link, if required.
	 */
	public function filter_excerpt_more( $link ) {
		return $this->container->make( Template\Excerpt::class )->maybe_filter_excerpt_more( $link );
	}

	/**
	 * Filters the `admin_post_thumbnail_html` to add image aspect ratio recommendation.
	 *
	 * @since 4.9.11
	 *
	 * @param string $html The HTML for the featured image box.
	 *
	 * @return string The modified html, if required.
	 */
	public function filter_admin_post_thumbnail_html( $html ) {

		if ( TEC::POSTTYPE !== get_current_screen()->post_type ) {
			return $html;
		}

		return $html . '<p class="hide-if-no-js howto">' . __( 'We recommend a 16:9 aspect ratio for featured images.', 'the-events-calendar' ) . '</p>';
	}

	/**
	 * Filters the `redirect_canonical` to prevent any redirects on embed URLs.
	 *
	 * @since 4.9.13
	 *
	 * @param mixed $redirect_url URL which we will redirect to.
	 *
	 * @return string A redirection URL, or `false` to prevent redirection.
	 */
	public function filter_redirect_canonical( $redirect_url = null, $original_url = null ) {
		if ( trailingslashit( $original_url ) === trailingslashit( $redirect_url ) ) {
			return $redirect_url;
		}

		$context = tribe_context();

		$view = $context->get( 'view_request', null );

		if ( 'embed' === $view ) {
			// Do not redirect embedded Views.
			return false;
		}

		if ( empty( $view ) || 'single-event' === $view ) {
			// Let the redirection go on.
			return $redirect_url;
		}

		$parsed = \Tribe__Events__Rewrite::instance()->parse_request( $redirect_url );

		if (
			empty( $parsed['tribe_redirected'] )
			&& $view !== Arr::get( (array) $parsed, 'eventDisplay' )
		) {

			/*
			 * If we're here we know we should be looking at a View URL.
			 * If the proposed URL does not resolve to a View, do not redirect.
			 */
			return false;
		}

		return $redirect_url;
	}

	/**
	 * Modifies the Live update tooltip properly.
	 *
	 * @since  4.9.13
	 *
	 * @param  array $fields  Fields that were passed for the Settigns tab.
	 *
	 * @return array          Fields after changing the tooltip.
	 */
	public function filter_general_settings_tab_live_update( $fields ) {
		if ( empty( $fields['liveFiltersUpdate'] ) ) {
			return $fields;
		}

		$fields['liveFiltersUpdate']['tooltip'] .= '<br/>' . esc_html__( 'Recommended for all sites using the updated calendar views.', 'the-events-calendar' );

		return $fields;
	}

 	/**
	 * Registers The Events Calendar with the views/overrides update checker.
	 *
	 * @since  4.9.13
	 *
	 * @param array $plugins List of plugisn to be checked.
	 *
	 * @return array
	 */
	public function filter_register_template_updates( array $plugins = [] ) {
		$plugins[ __( 'The Events Calendar - View V2', 'the-events-calendar' ) ] = [
			TEC::VERSION,
			TEC::instance()->pluginPath . 'src/views/v2',
			trailingslashit( get_stylesheet_directory() ) . 'tribe/events',
		];

		return $plugins;
	}

	/**
	 * Suppress v1 query filters on a per-query basis, if required.
	 *
	 * @since 4.9.11
	 *
	 * @param \WP_Query $query The current WordPress query object.
	 */
	public function parse_query( $query ) {
		if ( ! $query instanceof \WP_Query ) {
			return;
		}

		$event_query = $this->container->make( Event_Query_Controller::class );
		$event_query->parse_query( $query );
	}

	/**
	 * Adds the period repository to the map of available repositories.
	 *
	 * @since 4.9.13
	 *
	 * @param array $repository_map The current repository map.
	 *
	 * @return array The filtered repository map.
	 */
	public function add_period_repository( array $repository_map, $repository, array $args = [] ) {
		if ( 'period' === $repository ) {
			// This is a new instance on each run, by design. Making this a singleton would create dangerous dependencies.
			$event_period_repository                = $this->container->make( Event_Period::class );
			$event_period_repository->cache_results = in_array( 'caching', $args, true );
			$repository_map['period']               = $event_period_repository;
		}

		return $repository_map;
	}

	/**
	 * Flush rewrite rules after the site language setting changes.
	 *
	 * @since 4.9.13
	 *
	 * @param string $option The option name that was updated.
	 * @param string $old    The option old value.
	 * @param string $new    The option updated value.
	 */
	public function action_save_wplang( $option, $old, $new ) {

		if ( 'WPLANG' !== $option ) {
			return;
		}

		// Deleting `rewrite_rules` given that this is being executed after `init`
		// And `flush_rewrite_rules()` doesn't take effect.
		delete_option( 'rewrite_rules' );
	}

	/**
	 * Filters rewrite rules to modify and update them for Views V2.
	 *
	 * @since 5.0.0
	 *
	 * @param array  $bases  An array of rewrite bases that have been generated.
	 * @param string $method The method that's being used to generate the bases; defaults to `regex`.
	 *
	 * @return array<string,array> An array of rewrite rules. Modified, if required, to support Views V2.
	 */
	public function filter_rewrite_i18n_slugs_raw( $bases, $method ) {
		if ( ! is_array( $bases ) ) {
			return $bases;
		}

		return $this->container->make( Rewrite::class )->filter_raw_i18n_slugs( $bases, $method );
	}

	/**
	 * Fires to manage sensitive information on password protected posts.
	 *
	 * @since 5.0.0
	 *
	 * @param \WP_Post|int $post The event post ID or object currently being decorated.
	 */
	public function manage_sensitive_info( $post ) {
		if ( $this->container->make( Template_Bootstrap::class )->is_single_event() ) {
			$this->container->make( Template\Event::class )->manage_sensitive_info( $post );
		}
	}

	/**
	 * Updates and modifies the properties added to the event post object by the `tribe_get_event` function to
	 * hide some sensitive information, if required.
	 *
	 * @since 5.0.0
	 *
	 * @param \WP_Post $event The event post object, decorated w/ properties added by the `tribe_get_event` function.
	 *
	 * @return \WP_Post The event post object, decorated w/ properties added by the `tribe_get_event` function, some of
	 *                  them updated to hide sensitive information, if required.
	 */
	public function filter_events_properties( $event ) {
		if ( ! $event instanceof \WP_Post ) {
			return $event;
		}

		return $this->container->make( Template\Event::class )->filter_event_properties( $event );
	}

	/**
	 * Filter the template file in case we're in single event
	 * and we need to use the theme overrides.
	 *
	 * @see   tribe_template_file
	 *
	 * @since 5.0.0
	 *
	 * @param string $file      Complete path to include the PHP File
	 * @param array  $name      Template name
	 * @param object $template  Instance of the Tribe__Template
	 *
	 * @return string
	 */
	public function filter_template_file( $file, $name, $template ) {
		return $this->container->make( Template_Bootstrap::class )->filter_template_file( $file, $name, $template );
	}
}
