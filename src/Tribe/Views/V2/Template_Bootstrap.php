<?php
/**
 * Bootstrap Events Templating system, which by default will hook into
 * the WordPress normal template workflow to allow the injection the Events
 * archive.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2;

use Tribe__Events__Main as TEC;

class Template_Bootstrap {

	/**
	 * Hook the Template Hijack into WordPress methods
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function hook() {
		add_filter( 'template_include', [ $this, 'filter_template_include' ], 50 );
		add_action( 'loop_start', [ $this, 'maybe_hijack_page_template' ], 25 );
		add_action( 'wp_head', [ $this, 'maybe_hijack_main_query' ], PHP_INT_MAX );

		add_action( 'tribe_common_loaded', [ $this, 'disable_v1' ], 1 );
	}

	/**
	 * Disables the Views V1 implementation of a Template Hijack
	 *
	 * @todo   use a better method to remove Views V1 from been initialized
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function disable_v1() {
		remove_action( 'plugins_loaded', [ 'Tribe__Events__Templates', 'init' ] );
	}

	/**
	 * When using Page template we need to specifically hijack the WordPress templating
	 * system at a specific point after `loop_start`.
	 *
	 * @since  TBD
	 *
	 * @param  WP_Query $query WordPress query excuted to get here
	 *
	 * @return boolean
	 */
	public function should_hijack_page_template( $query ) {
		$should_hijack = true;

		// Dont hijack non-page event based
		if ( 'page' !== $this->get_template_base() ) {
			$should_hijack = false;
		}

		// We dont want the main Query
		if ( ! $query->is_main_query() ) {
			$should_hijack = false;
		}

		if ( is_single() ) {
			$should_hijack = false;
		}

		// We wont hijack in case we are not dealing with a Post Type query
		if ( ! in_array( TEC::POSTTYPE, (array) $query->get( 'post_type' ) ) ) {
			$should_hijack = false;
		}

		/**
		 * Allows third-party to influence when we will hijack the page template
		 *
		 * @since  TBD
		 *
		 * @param  boolean  $should_hijack  Will we hijack and include our page template
		 * @param  WP_Query $query          WordPress query excuted to get here
		 */
		return apply_filters( 'tribe_events_views_v2_should_hijack_page_template', $should_hijack, $query );
	}

	/**
	 * Object to allow the Bootstrap to manipulate page Requests and avoid 404s when
	 * no events are available by default.
	 *
	 * @since  TBD
	 *
	 * @return object A Mocked stdClass that mimicks a WP_Post
	 */
	protected function get_mocked_post() {
		$post = [
			'ID'                    => 0,
			'post_status'           => 'publish',
			'post_author'           => 0,
			'post_parent'           => 0,
			'post_type'             => 'page',
			'post_date'             => 0,
			'post_date_gmt'         => 0,
			'post_modified'         => 0,
			'post_modified_gmt'     => 0,
			'post_content'          => '',
			'post_title'            => '',
			'post_excerpt'          => '',
			'post_content_filtered' => '',
			'post_mime_type'        => '',
			'post_password'         => '',
			'post_name'             => '',
			'guid'                  => '',
			'menu_order'            => 0,
			'pinged'                => '',
			'to_ping'               => '',
			'ping_status'           => '',
			'comment_status'        => 'closed',
			'comment_count'         => 0,
		];

		return (object) $post;
	}

	/**
	 * When using Page template we need to specifically hijack the WordPress templating
	 * system at a specific point after `loop_start`.
	 *
	 * @since  TBD
	 *
	 * @param  WP_Query $query WordPress query excuted to get here
	 *
	 * @return boolean
	 */
	public function maybe_hijack_page_template( $query ) {
		if ( ! $this->should_hijack_page_template( $query ) ) {
			return false;
		}

		// on loop start, unset the global post so that template tags don't work before the_content()
		add_action( 'the_post', [ $this, 'hijack_the_post' ], 25 );

		// Load our page Content
		add_filter( 'the_content', [ $this, 'filter_hijack_page_content' ], 25 );

		// Makes sure Comments are not active
		// add_filter( 'comments_template', [ $this, 'fitler_remove_comments' ] );
	}

	/**
	 * Determines with backwards compatibility in mind, which template user has selected
	 * on the Events > Settings page as their base Default template
	 *
	 * @since  TBD
	 *
	 * @return string Either 'event' or 'page' based templates
	 */
	public function get_template_base() {
		$template = 'event';
		$default_value = 'default';
		$setting = tribe_get_option( 'tribeEventsTemplate', $default_value );

		if ( $default_value === $setting ) {
			$template = 'page';
		}

		return $template;
	}

	/**
	 * Inject a Ghost Post into `the_post`
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function hijack_the_post() {
		remove_filter( 'the_content', [ $this, 'hijack_the_post' ], 25 );

		$GLOBALS['post'] = $this->get_mocked_post();
	}

	/**
	 * Depending on params from Default templating for events we will Hijack
	 * the main query for events to mimick a ghost page element so the theme
	 * can propely run `the_content` so we can hijack the content of that page
	 * as well as `the_title`.
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function maybe_hijack_main_query() {
		$wp_query = tribe_get_global_query_object();

		if ( ! $this->should_hijack_page_template( $wp_query ) ) {
			return false;
		}

		// Store old posts
		$wp_query->tribe_hijacked_posts = $wp_query->posts;

		$mocked_post = $this->get_mocked_post();

		// Replace the Mocked post in a couple of places
		$GLOBALS['post']      = $mocked_post;
		$wp_query->posts      = [ $mocked_post ];
		$wp_query->post_count = count( $wp_query->posts );

		// re-do counting
		$wp_query->rewind_posts();
	}

	/**
	 * Restored the Hijacked posts from the main query so that we can run
	 * the template method properly with a fully populated WP_Query object
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function restore_main_query() {
		$wp_query = tribe_get_global_query_object();

		// If the query doesnt have hijacked posts
		if ( ! isset( $wp_query->tribe_hijacked_posts ) ) {
			return;
		}

		$wp_query->posts = $wp_query->tribe_hijacked_posts;
		$wp_query->post_count = count( $wp_query->posts );

		// If we have other posts besides the spoof, rewind and reset
		if ( $wp_query->post_count > 0 ) {
			$wp_query->rewind_posts();
			wp_reset_postdata();
		}
		// If there are no other posts, unset the $post property
		elseif ( 0 === $wp_query->post_count ) {
			$wp_query->current_post = -1;
			unset( $wp_query->post );
		}

		// Unset the Posts Prop
		unset( $wp_query->tribe_hijacked_posts );
	}

	/**
	 * Prevents Looping multiple pages when including Page templates
	 * by modifing the global WP_Query object by pretending there are
	 * no posts to loop
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function prevent_page_looping() {
		$wp_query = tribe_get_global_query_object();

		$wp_query->current_post = -1;
		$wp_query->post_count   = 0;
	}

	/**
	 * Include our own Page template into `the_content` of their Page template
	 *
	 * @since  TBD
	 *
	 * @param  string $content Default content of the page we hijacked
	 *
	 * @return string
	 */
	public function filter_hijack_page_content( $content = '' ) {
		remove_filter( 'the_content', [ $this, 'filter_hijack_page_content' ], 25 );

		$this->restore_main_query();

		$context = [
			'query' => tribe_get_global_query_object(),
		];

		$html = tribe( 'events.views.v2.kitchen-sink' )->template( 'page', $context, false );

		$this->prevent_page_looping();

		return $html;
	}

	/**
	 * Filters the `template_include` filter to return the Views router template if required..
	 *
	 * @since TBD
	 *
	 * @param string $template The template located by WordPress.
	 *
	 * @return string The Views router file if required or the input template.
	 */
	public function filter_template_include( $template ) {
		$wp_query = tribe_get_global_query_object();

		/**
		 * Bail if we are not dealing with our Post Type
		 * @todo  needs support for Venues and Template
		 */
		if ( ! in_array( TEC::POSTTYPE, (array) $wp_query->get( 'post_type' ) ) ) {
			return $template;
		}

		if ( 'page' === $this->get_template_base() ) {
			// Fetches the WP default path for Page
			$template = get_page_template();

			// If there wasn't any defined we fetch the Index
			if ( empty( $template ) ) {
				$template = get_index_template();
			}

			return $template;
		}

		// Loads Event Template Setting
		$index = ( new Index() )->get_template_file();

		return $index ? $index : $template;
	}
}