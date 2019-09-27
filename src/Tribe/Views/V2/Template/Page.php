<?php
/**
 * Initializer for The Events Calendar for the template structure using Page
 *
 * Can be changed on Events > Settings > Display
 *
 * @since   4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2\Template;

use Tribe\Events\Views\V2\Kitchen_Sink;
use Tribe\Events\Views\V2\Template_Bootstrap;
use Tribe__Events__Main as TEC;
use Tribe__Utils__Array as Arr;

class Page {
	/**
	 * Determines the Path for the PHP file to be used as the main template.
	 * For Page base template setting it will select from theme or child theme.
	 *
	 * @since  4.9.2
	 *
	 * @return string
	 */
	public function get_path() {
		// Fetches the WP default path for Page.
		$template = get_page_template();

		// If there wasn't any defined we fetch the Index.
		if ( empty( $template ) ) {
			$template = get_index_template();
		}

		return $template;
	}

	/**
	 * Fires when the loop starts, and tries to hijack the loop for post.
	 *
	 * @since  TBD
	 *
	 * @param  \WP_Query  $query
	 */
	public function hijack_on_loop_start( \WP_Query $query ) {
		// After attaching itself it will prevent it from happening again.
		remove_action( 'loop_start', [ $this, 'hijack_on_loop_start' ], 1000 );

		$this->maybe_hijack_page_template( $query );
	}

	/**
	 * When using Page template we need to specifically hijack the WordPress templating
	 * system at a specific point after `loop_start`.
	 *
	 * @since  4.9.2
	 *
	 * @param  \WP_Query $query WordPress query executed to get here.
	 *
	 * @return boolean
	 */
	public function maybe_hijack_page_template( \WP_Query $query ) {
		if ( ! $this->should_hijack_page_template( $query ) ) {
			return false;
		}

		$mock_page = $this->get_mocked_page();

		// don't query the database for the spoofed post.
		wp_cache_set( $mock_page->ID, $mock_page, 'posts' );
		wp_cache_set( $mock_page->ID, [ true ], 'post_meta' );

		// on loop start, unset the global post so that template tags don't work before the_content().
		add_action( 'the_post', [ $this, 'hijack_the_post' ], 25 );

		// Load our page Content.
		add_filter( 'the_content', [ $this, 'filter_hijack_page_content' ], 25 );

		// Prevent edit link from showing.
		add_filter( 'get_edit_post_link', [ $this, 'filter_prevent_edit_link' ], 25, 2 );

		// Makes sure Comments are not active.
		add_filter( 'comments_template', [ $this, 'filter_remove_comments' ], 25 );
	}

	/**
	 * Remove any possible comments template from Page that the theme might have.
	 *
	 * @todo  Take in consideration tribe_get_option( 'showComments', false ) values later on.
	 *
	 * @since  4.9.2
	 */
	public function filter_remove_comments( $template ) {
		remove_filter( 'comments_template', [ $this, 'filter_remove_comments' ], 25 );

		return false;
	}

	/**
	 * Prevents the Edit link to ever be displayed on any well designed theme.
	 * Ideally this method is here to return an empty string for the Mock Page.
	 *
	 * @since  4.9.2
	 *
	 * @param  string     $url     Old URL for editing the post
	 * @param  string|int $post_id Post ID in question
	 *
	 * @return string
	 */
	public function filter_prevent_edit_link( $url, $post_id ) {
		$query = tribe_get_global_query_object();

		// Bail in case of any other page template.
		if ( ! $this->should_hijack_page_template( $query ) ) {
			return $url;
		}

		$mock_page = $this->get_mocked_page();

		// If passed ID is not the Mock page one bail.
		if ( (int) $post_id !== (int) $mock_page->ID ) {
			return $url;
		}

		// Return empty edit link.
		return '';
	}

	/**
	 * Inject a Ghost Post into `the_post`
	 *
	 * @since  4.9.2
	 *
	 * @return void
	 */
	public function hijack_the_post() {
		remove_filter( 'the_post', [ $this, 'hijack_the_post' ], 25 );

		$GLOBALS['post'] = $this->get_mocked_page();
	}

	/**
	 * Depending on params from Default templating for events we will Hijack
	 * the main query for events to mimick a ghost page element so the theme
	 * can propely run `the_content` so we can hijack the content of that page
	 * as well as `the_title`.
	 *
	 * @since  4.9.2
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

		$mocked_post = $this->get_mocked_page();

		// Replace the Mocked post in a couple of places
		$GLOBALS['post']      = $mocked_post;
		$wp_query->posts      = [ $mocked_post ];
		$wp_query->post_count = count( $wp_query->posts );

		// re-do counting
		$wp_query->rewind_posts();

		add_action( 'loop_start', [ $this, 'hijack_on_loop_start' ], 1000 );
	}

	/**
	 * Restored the Hijacked posts from the main query so that we can run
	 * the template method properly with a fully populated WP_Query object
	 *
	 * @since 4.9.2
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
	 * @since 4.9.2
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
	 * @todo  Integrate with Template + Context classes
	 *
	 * @since  4.9.2
	 *
	 * @param  string $content Default content of the page we hijacked
	 *
	 * @return string
	 */
	public function filter_hijack_page_content( $content = '' ) {
		remove_filter( 'the_content', [ $this, 'filter_hijack_page_content' ], 25 );

		$this->restore_main_query();

		$html = tribe( Template_Bootstrap::class )->get_view_html();

		$this->prevent_page_looping();

		return $html;
	}

	/**
	 * When using Page template we need to specifically hijack the WordPress templating
	 * system at a specific point after `loop_start`.
	 *
	 * @since  4.9.2
	 *
	 * @param  \WP_Query $query WordPress query executed to get here.
	 *
	 * @return boolean
	 */
	public function should_hijack_page_template( \WP_Query $query ) {
		$should_hijack = true;

		// Dont hijack non-page event based
		if ( 'page' !== tribe( Template_Bootstrap::class )->get_template_setting() ) {
			$should_hijack = false;
		}

		// We dont want the main Query
		if ( ! $query->is_main_query() ) {
			$should_hijack = false;
		}

		// We wont hijack in case we are not dealing with a Post Type query
		if ( ! in_array( TEC::POSTTYPE, (array) $query->get( 'post_type' ) ) ) {
			$should_hijack = false;
		}

		/**
		 * Allows third-party to influence when we will hijack the page template
		 *
		 * @since  4.9.2
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
	 * @since  4.9.2
	 *
	 * @return object A Mocked stdClass that mimicks a WP_Post
	 */
	protected function get_mocked_page() {
		$page = [
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

		return (object) $page;
	}
}