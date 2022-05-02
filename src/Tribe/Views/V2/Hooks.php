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
use Tribe\Events\Views\V2\Template\Featured_Title;
use Tribe\Events\Views\V2\Template\Title;
use Tribe\Events\Views\V2\Utils\View as View_Utils;
use Tribe__Context as Context;
use Tribe__Customizer__Section as Customizer_Section;
use Tribe__Events__Main as TEC;
use Tribe__Rewrite as TEC_Rewrite;
use Tribe__Utils__Array as Arr;
use WP_Post;

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
		add_action( 'parse_query', [ $this, 'add_body_classes' ], 55 );
		add_action( 'wp_head', [ $this, 'on_wp_head' ], 1000 );
		add_action( 'tribe_events_pre_rewrite', [ $this, 'on_tribe_events_pre_rewrite' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'action_disable_assets_v1' ], 0 );
		add_action( 'tribe_events_pro_shortcode_tribe_events_after_assets', [ $this, 'action_disable_shortcode_assets_v1' ] );
		add_action( 'updated_option', [ $this, 'action_save_wplang' ], 10, 3 );
		add_action( 'the_post', [ $this, 'manage_sensitive_info' ] );
		add_action( 'get_header', [ $this, 'print_single_json_ld' ] );
		add_action( 'tribe_template_after_include:events/v2/components/after', [ $this, 'action_add_promo_banner' ], 10, 3 );
		add_action( 'tribe_events_parse_query', [ $this, 'parse_query' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_customizer_in_block_editor' ] );
	}

	/**
	 * Enqueue Customizer styles for the single event block editor screen.
	 *
	 * @since 5.14.1
	 */
	public function enqueue_customizer_in_block_editor() {
		// Make sure we're on the block edit screen
		if ( ! is_admin() || ! get_current_screen()->is_block_editor ) {
			return;
		}

		if ( ! tribe( 'admin.helpers' )->is_post_type_screen() ) {
			return;
		}

		global $post;
		// Make sure we're editing an Event post.
		if ( empty( $post ) || ! $post instanceof WP_Post || ! tribe_is_event( $post ) ) {
			return;
		}

		// Append the customizer styles to the single block stylesheet
		add_filter( 'tribe_customizer_inline_stylesheets', static function( $sheets ) {
			$sheets[] = 'tribe-admin-v2-single-blocks';

			return $sheets;
		} );

		// Print the styles!
		tribe( 'customizer' )->inline_style( true );
	}

	/**
	 * Adds the filters required by each Views v2 component.
	 *
	 * @since 4.9.2
	 */
	protected function add_filters() {
		add_filter( 'tec_system_information', [ $this, 'filter_system_information' ] );
		add_filter( 'wp_redirect', [ $this, 'filter_redirect_canonical' ], 10, 2 );
		add_filter( 'redirect_canonical', [ $this, 'filter_redirect_canonical' ], 10, 2 );
		add_filter( 'template_include', [ $this, 'filter_template_include' ], 50 );
		add_filter( 'embed_template', [ $this, 'filter_template_include' ], 50 );
		add_filter( 'posts_pre_query', [ $this, 'filter_posts_pre_query' ], 20, 2 );
		add_filter( 'body_class', [ $this, 'filter_body_classes' ] );
		add_filter( 'tribe_body_class_should_add_to_queue', [ $this, 'body_class_should_add_to_queue' ], 10, 3 );
		add_filter( 'tribe_body_classes_should_add', [ $this, 'body_classes_should_add' ], 10, 3 );
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
		add_filter( 'tribe_get_option', [ $this, 'filter_get_stylesheet_option' ], 10, 2 );
		add_filter( 'option_liveFiltersUpdate', [ $this, 'filter_live_filters_option_value' ], 10, 2 );
		add_filter( 'tribe_get_option', [ $this, 'filter_live_filters_option_value' ], 10, 2 );
		add_filter( 'tribe_field_value', [ $this, 'filter_live_filters_option_value' ], 10, 2 );

		if ( tribe_context()->doing_php_initial_state() ) {
			add_filter( 'tribe_events_filter_views_v2_wp_title_plural_events_label', [ $this, 'filter_wp_title_plural_events_label' ], 10, 2 );
			add_filter( 'wp_title', [ $this, 'filter_wp_title' ], 10, 2 );
			add_filter( 'document_title_parts', [ $this, 'filter_document_title_parts' ] );
			add_filter( 'pre_get_document_title', [ $this, 'pre_get_document_title' ], 20 );
		}

		// Replace the `pubDate` in event feeds.
		if ( ! has_filter( 'get_post_time', [ 'Tribe__Events__Templates', 'event_date_to_pubDate' ], 10 ) ) {
			add_filter( 'get_post_time', [ 'Tribe__Events__Templates', 'event_date_to_pubDate' ], 10, 3 );
		}

		add_filter( 'tribe_events_views_v2_view_data', [ View_Utils::class, 'clean_data' ] );

		// Customizer.
		add_filter( 'tribe_customizer_print_styles_action', [ $this, 'print_inline_styles_in_footer' ] );
		add_filter( 'tribe_customizer_global_elements_css_template', [ $this, 'filter_global_elements_css_template' ], 10, 3 );
		add_filter( 'tribe_customizer_single_event_css_template', [ $this, 'filter_single_event_css_template' ], 10, 3 );

		// Add filters to change the display of website links on the Single Event template.
		add_filter( 'tribe_get_event_website_link_label', [ $this, 'filter_single_event_details_event_website_label' ], 10, 2 );
		add_filter( 'tribe_events_get_event_website_title', '__return_empty_string' );

		add_filter( 'tribe_get_venue_website_link_label', [ $this, 'filter_single_event_details_venue_website_label' ], 10, 2 );
		add_filter( 'tribe_events_get_venue_website_title', '__return_empty_string' );

		add_filter( 'tribe_get_organizer_website_link_label', [ $this, 'filter_single_event_details_organizer_website_label' ], 10, 2 );
		add_filter( 'tribe_events_get_organizer_website_title', '__return_empty_string' );

		// iCalendar export request handling.
		add_filter( 'tribe_ical_template_event_ids', [ $this, 'inject_ical_event_ids' ] );

		add_filter( 'tec_events_query_default_view', [ $this, 'filter_tec_events_query_default_view' ] );
	}

	/**
	 * Includes includes edge cases for filtering when we need to manually overwrite theme's read
	 * more link when excerpt is cut programmatically.
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
		if ( tec_is_full_site_editor() ) {
			return;
		}

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
	public function filter_body_classes( $classes ) {
		$classes = $this->container->make( Template_Bootstrap::class )->filter_add_body_classes( $classes );

		return $classes;
	}

	/**
	 * Add body classes.
	 *
	 * @since 5.1.5
	 *
	 * @return void
	 */
	public function add_body_classes() {
		$this->container->make( Theme_Compatibility::class )->add_body_classes();
		$this->container->make( Template_Bootstrap::class )->add_body_classes();
	}

	/**
	 * Contains hooks to the logic for if this object's classes should be added to the queue.
	 *
	 * @since 5.1.5
	 *
	 * @param boolean $add   Whether to add the class to the queue or not.
	 * @param array   $class The array of body class names to add.
	 * @param string  $queue The queue we want to get 'admin', 'display', 'all'.
	 * @return boolean
	 */
	public function body_class_should_add_to_queue( $add, $class, $queue ) {
		$add = $this->container->make( Template_Bootstrap::class )->should_add_body_class_to_queue( $add, $class, $queue );
		$add = $this->container->make( Theme_Compatibility::class )->should_add_body_class_to_queue( $add, $class, $queue );

		return $add;
	}

	/**
	 * Logic for if body classes should be added.
	 *
	 * @since 5.1.5
	 *
	 * @param boolean $add   Whether to add classes or not.
	 * @param string  $queue The queue we want to get 'admin', 'display', 'all'.
	 *
	 * @return boolean Whether to add classes or not.
	 */
	public function body_classes_should_add( $add, $queue ) {
		$context = tribe_context();

		if (
			$context->get( 'event_post_type', false )
			|| $context->get( 'shortcode', false )
		) {
			return true;
		}

		return $add;
	}

	/**
	 * Filter the plural events label for Featured V2 Views.
	 *
	 * @since 5.1.5
	 *
	 * @param string  $label   The plural events label as it's been generated thus far.
	 * @param Context $context The context used to build the title, it could be the global one, or one externally
	 *                         set.
	 *
	 * @return string the original label or updated label for virtual archives.
	 */
	public function filter_wp_title_plural_events_label( $label, Context $context ) {
		return $this->container->make( Featured_Title::class )->filter_views_v2_wp_title_plural_events_label( $label, $context );
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

		$screen = get_current_screen();

		if ( ! $screen instanceof \WP_Screen ) {
			return $html;
		}

		if ( TEC::POSTTYPE !== $screen->post_type ) {
			return $html;
		}

		return $html . '<p class="hide-if-no-js howto">' . __( 'We recommend a 16:9 aspect ratio for featured images.', 'the-events-calendar' ) . '</p>';
	}

	/**
	 * Filters the `redirect_canonical` to prevent any redirects on embed URLs.
	 *
	 * @since 4.9.13
	 *
	 * @param mixed      $redirect_url URL which we will redirect to.
	 * @param string|int $original_url The original URL if this method runs on the `redirect_canonical` filter, else
	 *                                 the redirect status (e.g. `301`) if this method runs in the context of the
	 *                                 `wp_redirect` filter.
	 *
	 * @return string A redirection URL, or `false` to prevent redirection.
	 */
	public function filter_redirect_canonical( $redirect_url = null, $original_url = null ) {
		if ( doing_filter( 'redirect_canonical' ) ) {
			/*
			 * If we're not running in the context of the `redirect_canonical` filter, skip this check
			 * as it would happen between a string (`$redirect_url`) and an integer (the redirect HTTP
			 * status code).
			 */
			if ( trailingslashit( $original_url ) === trailingslashit( $redirect_url ) ) {
				return $redirect_url;
			}
		}

		$context = tribe_context();

		// Bail with the original redirect if we are not dealing with a CPT from TEC.
		if ( ! $context->is( 'tec_post_type' ) ) {
			return $redirect_url;
		}

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
	 * @param  array $fields  Fields that were passed for the Settings tab.
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
	 * @param array $plugins List of plugins to be checked.
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
	 * Include the promo banner after the after component.
	 *
	 * @since 5.1.5
	 *
	 * @param string   $file     Complete path to include the PHP File.
	 * @param array    $name     Template name.
	 * @param Template $template Current instance of the Template.
	 *
	 * @return void  Template render has no return.
	 */
	public function action_add_promo_banner( $file, $name, $template ) {
		$this->container->make( Template\Promo::class )->action_add_promo_banner( $file, $name, $template );
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

	/**
	 * Filter the stylesheet option to do some switching for V2
	 *
	 * @since  5.0.2
	 *
	 * @param  string $value The option value.
	 * @param  string $key   The option key.
	 *
	 * @return string Which value we are converting to.
	 */
	public function filter_get_stylesheet_option( $value, $key ) {
		// Bail early if possible. No need to do the shuffle below if
		if (
			'stylesheetOption' !== $key
			&& ( 'stylesheet_mode' !== $key )
		) {
			return $value;
		}

		// Remove this filter so we don't loop infinitely.
		remove_filter( 'tribe_get_option', [ $this, 'filter_get_stylesheet_option' ], 10 );

		$default = 'tribe';

		if ( 'stylesheetOption' === $key ) {
			$value = tribe_get_option( 'stylesheet_mode', $default );
		} else if ( 'stylesheet_mode' === $key && empty( $value ) ) {
			$value = tribe_get_option( 'stylesheetOption', $default );
			if ( 'full' === $value ) {
				$value = $default;
			}
		}

		// Add the filter back
		add_filter( 'tribe_get_option', [ $this, 'filter_get_stylesheet_option' ], 10, 2 );

		return $value;
	}

	/**
	 * Filter the liveFiltersUpdate option to do some switching for V2.
	 * Note: this triggers on option_liveFiltersUpdate, tribe_get_option, AND tribe_field_value. We
	 * don't have to add/remove filters because we don't need to get the value - it's already provided.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $value  The option value.
	 * @param  string $key    The option key.
	 *
	 * @return string Converted value of the Live Filters string.
	 */
	public function filter_live_filters_option_value( $value, $key ) {
		if ( 'liveFiltersUpdate' !== $key ) {
			return $value;
		}

		return $this->live_filters_maybe_convert( $value );
	}

	/**
	 * Converts old (boolean) values to the new string values.
	 *
	 * @since 5.0.3
	 *
	 * @param  mixed  $value The value to maybe convert.
	 *
	 * @return string Modified value of Live filters Update.
	 */
	public function live_filters_maybe_convert( $value ) {
		$return_value = 'automatic';

		if ( empty( $value ) || 'manual' === $value ) {
			$return_value = 'manual';
		}

		/**
		 * Allow filtering of the new value for Live Filters.
		 *
		 * @since 5.0.3
		 *
		 * @param string $return_value Which value we are going to return as the conversion.
		 * @param string $value        Which value was previously used.
		 */
		$return_value = apply_filters( 'tribe_events_option_convert_live_filters', $return_value, $value );

		return $return_value;
	}

	/**
	 * Print Single Event JSON-LD.
	 *
	 * @since 5.0.3
	 */
	public function print_single_json_ld() {

		$this->container->make( Template\JSON_LD::class )->print_single_json_ld();
	}

	/**
	 * Changes the action the Customizer should use to try and print inline styles to print the inline
	 * styles in the footer.
	 *
	 * @since 5.3.1
	 *
	 * @return string The action the Customizer should use to print inline styles.
	 */
	public function print_inline_styles_in_footer() {
		return 'wp_print_footer_scripts';
	}

	/**
	 * Filter the website link label and change it for Single Event Classic Editor.
	 * Use the following in functions.php to disable:
	 * remove_filter( 'tribe_get_venue_website_link_label', [ tribe( 'events.views.v2.hooks' ), 'filter_single_event_details_website_label' ] );
	 *
	 * @since 5.5.0
	 *
	 * @param string     $label The filtered label.
	 * @param null|string|int $post_id The current post ID.
	 *
	 * @return string
	 */
	public function filter_single_event_details_event_website_label( $label, $post_id = null ) {
		// If not V2 or not Classic Editor, return the website url.
		if ( $this->is_v1_or_blocks( $post_id ) ) {
			return $label;
		}

		if ( 'Website' !== $label ) {
			return $label;
		}

		return sprintf(
			_x(
				'View %s Website',
				'Capitalized label for the event website link.',
				'the-events-calendar'
			),
			tribe_get_event_label_singular()
		);
	}

	/**
	 * Filter the website link label and change it for Single Event Classic Editor.
	 * Use the following in functions.php to disable:
	 * remove_filter( 'tribe_get_venue_website_link_label', [ tribe( 'events.views.v2.hooks' ), 'filter_single_event_details_venue_website_label' ] );
	 *
	 * @since 5.5.0
	 *
	 * @param string     $label The filtered label.
	 * @param null|string|int $post_id The current post ID.
	 *
	 * @return string
	 */
	public function filter_single_event_details_venue_website_label( $label, $post_id = null ) {
		// If not V2 or not Classic Editor, return the website url.
		if ( $this->is_v1_or_blocks( $post_id ) ) {
			return $label;
		}

		return sprintf(
			_x(
				'View %s Website',
				'Capitalized label for the venue website link.',
				'the-events-calendar'
			),
			tribe_get_venue_label_singular()
		);
	}

	/**
	 * Filter the website link label and change it for Single Event Classic Editor.
	 * Use the following in functions.php to disable:
	 * remove_filter( 'tribe_get_organizer_website_link_label', [ tribe( 'events.views.v2.hooks' ), 'filter_single_event_details_organizer_website_label' ] );
	 *
	 * @since 5.5.0
	 *
	 * @param string     $label The filtered label.
	 * @param null|string|int $post_id The current post ID.
	 *
	 * @return string
	 */
	public function filter_single_event_details_organizer_website_label( $label, $post_id = null ) {
		// If not V2 or not Classic Editor, return the website url.
		if ( $this->is_v1_or_blocks( $post_id ) ) {
			return $label;
		}

		return sprintf(
			_x(
				'View %s Website',
				'Capitalized label for the organizer website link.',
				'the-events-calendar'
			),
			tribe_get_organizer_label_singular()
		);
	}

	public function filter_tec_events_query_default_view( $default_view ) {
		return tribe( Manager::class )->get_default_view();
	}

	/**
	 * Sugar function for the above that determines if the labels should be filtered.
	 *
	 * @since 4.6.0
	 *
	 * @param null|string|int $post_id The current post ID.
	 *
	 * @return boolean
	 */
	public function is_v1_or_blocks( $post_id = null ) {
		return is_null( $post_id )
				|| ! tribe_events_single_view_v2_is_enabled()
				|| tribe( 'editor' )->should_load_blocks() && has_blocks( $post_id );
	}

	/**
	 * Overrides the default iCalendar export link logic to inject a list of event
	 * post IDs fitting the Views V2 criteria.
	 *
	 * @since 4.6.0
	 *
	 * @param array<int>|false $event_ids Either a list of event post IDs that has been
	 *                                    explicitly requested or `false` to indicate the
	 *                                    iCalendar export link did not indicate a specific
	 *                                    set of event post IDs.
	 *
	 * @return array<int> Either the original input value if a specific set of event post IDs
	 *                    was requested as part of the iCalendar export link, or a filtered
	 *                    set of event post IDs compiled depending on the current View context
	 *                    and request arguments.
	 */
	public function inject_ical_event_ids( $event_ids = null ) {
		if ( false !== $event_ids ) {
			// The request already specifies a set of Event post IDs to return, bail.
			return $event_ids;
		}

		return $this->container->make( iCalendar\Request::class )->get_event_ids();
	}

	/* DEPRECATED */

	/**
	 * Adds new Global Elements settings via the hook in common.
	 *
	 * @since 5.3.1
	 * @deprecated 5.9.0
	 *
	 * @param \Tribe__Customizer__Section $section    The Global Elements Customizer section.
	 * @param WP_Customize_Manager        $manager    The settings manager.
	 * @param \Tribe__Customizer          $customizer The Customizer object.
	 */
	public function action_include_global_elements_settings( $section, $manager, $customizer ) {
		_deprecated_function( __METHOD__, '5.9.0' );
		tribe( 'customizer' )->include_global_elements_settings( $section, $manager, $customizer );
	}

	/**
	 * Adds new Single Event settings via the hook in common.
	 *
	 * @since 5.3.1
	 * @deprecated 5.9.0
	 *
	 * @param \Tribe__Customizer__Section $section    The Single Event Customizer section.
	 * @param WP_Customize_Manager        $manager    The settings manager.
	 * @param \Tribe__Customizer          $customizer The Customizer object.
	 */
	public function action_include_single_event_settings( $section, $manager, $customizer ) {
		_deprecated_function( __METHOD__, '5.9.0' );
		tribe( 'customizer' )->include_single_event_settings( $section, $manager, $customizer );
	}

	/**
	 * Filters the Global Elements section CSS template to add Views v2 related style templates to it.
	 *
	 * @since 5.3.1
	 * @deprecated 5.9.0
	 *
	 * @param string                      $css_template The CSS template, as produced by the Global Elements.
	 * @param \Tribe__Customizer__Section $section      The Global Elements section.
	 * @param \Tribe__Customizer          $customizer   The current Customizer instance.
	 *
	 * @return string The filtered CSS template.
	 */
	public function filter_global_elements_css_template( $css_template, $section ) {
		_deprecated_function( __METHOD__, '5.9.0' );
		if ( ! ( is_string( $css_template ) && $section instanceof Customizer_Section ) ) {
			return $css_template;
		}

		return tribe( 'customizer' )->filter_global_elements_css_template( $css_template, $section );
	}

	/**
	 * Filters the Single Event section CSS template to add Views v2 related style templates to it.
	 *
	 * @since 5.3.1
	 * @deprecated 5.9.0
	 *
	 * @param string                      $css_template The CSS template, as produced by the Global Elements.
	 * @param \Tribe__Customizer__Section $section      The Single Event section.
	 * @param \Tribe__Customizer          $customizer   The current Customizer instance.
	 *
	 * @return string The filtered CSS template.
	 */
	public function filter_single_event_css_template( $css_template, $section ) {
		_deprecated_function( __METHOD__, '5.9.0' );
		if ( ! ( is_string( $css_template ) && $section instanceof Customizer_Section ) ) {
			return $css_template;
		}

		return tribe( 'customizer' )->filter_single_event_css_template( $css_template, $section );
	}

	/**
	 * Add the views v2 status in a more prominent way in the Troubleshooting page system info panel.
	 *
	 * @since 5.12.4
	 *
	 * @param array $info Existing information that will be displayed.
	 *
	 * @return array
	 */
	public function filter_system_information( array $info = [] ) {
		$views_v2_status = [
			'Views V2 Status' => tribe_events_views_v2_is_enabled() ? esc_html__( 'Enabled', 'the-events-calendar' ) : esc_html__( 'Disabled', 'the-events-calendar' ),
		];
		return \Tribe__Main::array_insert_before_key( 'Settings', $info, $views_v2_status );
	}
}
