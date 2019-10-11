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
use Tribe\Events\Views\V2\Template\Title;
use Tribe\Events\Views\V2\Template\Excerpt;
use Tribe__Events__Main as TEC;
use Tribe__Rewrite as Rewrite;

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
	}

	/**
	 * Adds the filters required by each Views v2 component.
	 *
	 * @since 4.9.2
	 */
	protected function add_filters() {
		// Let's make sure to suppress query filters from the main query.
		add_filter( 'tribe_suppress_query_filters', '__return_true' );
		add_filter( 'template_include', [ $this, 'filter_template_include' ], 50 );
		add_filter( 'posts_pre_query', [ $this, 'filter_posts_pre_query' ], 20, 2 );
		add_filter( 'body_class', [ $this, 'filter_body_class' ] );
		add_filter( 'query_vars', [ $this, 'filter_query_vars' ], 15 );
		add_filter( 'tribe_rewrite_canonical_query_args', [ $this, 'filter_map_canonical_query_args' ], 15, 3 );
		add_filter( 'excerpt_length', [ $this, 'filter_excerpt_length' ] );
		add_filter( 'excerpt_more', [ $this, 'filter_excerpt_more' ], 999 );

		if ( tribe_context()->doing_php_initial_state() ) {
			add_filter( 'wp_title', [ $this, 'filter_wp_title' ], 10, 2 );
			add_filter( 'document_title_parts', [ $this, 'filter_document_title_parts' ] );
		}
	}

	/**
	 * Fires when common is loaded.
	 *
	 * @since 4.9.2
	 */
	public function on_tribe_common_loaded() {
		$this->container->make( Template_Bootstrap::class )->disable_v1();
		$this->container->make( Rest_Endpoint::class )->maybe_enable_ajax_fallback();
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
	public function on_tribe_events_pre_rewrite( Rewrite $rewrite ) {
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
	 */
	public function filter_posts_pre_query( $posts = null, \WP_Query $query = null ) {
		foreach ( $this->container->tagged( 'query_controllers' ) as $controller ) {
			/** @var Abstract_Query_Controller $controller */
			$controller->inject_posts( $posts, $query );
		}
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
		return $this->container->make( Theme_Compatibility::class )->filter_add_body_classes( $classes );
	}

	/**
	 * Filters the `wp_title` template tag.
	 *
	 * @since TBD
	 *
	 * @param      string $title The current title value.
	 * @param string|null $sep The separator char, or sequence, to use to separate the page title from the blog one.
	 *
	 * @return string The modified page title, if required.
	 */
	public function filter_wp_title( $title, $sep = null ) {
		return $this->container->make( Title::class )->filter_wp_title( $title, $sep );
	}

	/**
	 * Filters the `wp_get_document_title` template tag.
	 *
	 * This is the template tag introduced in WP 4.4 to get the page title.
	 *
	 * @since TBD
	 *
	 * @param string $title The page title.
	 *
	 * @return string The modified page title, if required.
	 */
	public function filter_document_title_parts( $title ) {
		return $this->container->make( Title::class )->filter_document_title_parts( $title );
	}

	/**
	 * Filters the `excerpt_length`.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @param string $link The excerpt read more link.
	 *
	 * @return int The modified excerpt read more link, if required.
	 */
	public function filter_excerpt_more( $link ) {
		return $this->container->make( Template\Excerpt::class )->maybe_filter_excerpt_more( $link );
	}
}