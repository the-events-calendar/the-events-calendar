<?php
/**
 * Handles the custom tables updates integrating with the normal WordPress flow.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Updates
 */

namespace TEC\Events\Custom_Tables\V1\Updates;

use tad_DI52_ServiceProvider as Service_Provider;
use TEC\Events\Custom_Tables\V1\Provider_Contract;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Updates
 */
class Provider extends Service_Provider implements Provider_Contract {
	public function register() {
		// Make this provider available in the Service Locator by class and slug.
		$this->container->singleton( self::class, $this );
		$this->container->singleton( 'tec.events.custom-tables-v1.updates', $this );

		/*
		 * We need this to be a singleton to keep track of the Events to update in the WRITE phase
		 * of the request and update them before the READ phase of the request.
		 */
		$this->container->singleton( Controller::class, Controller::class );

		/*
		 * For the whole life cycle of any request, not only explicit update ones, watch for meta updates
		 * using an object that will need to be the same from the start to the end of the request.
		 */
		$this->container->singleton( Meta_Watcher::class, Meta_Watcher::class );

		// Other bindings are bound as singletons to save some resources.
		$this->container->singleton( Requests::class, Requests::class );
		$this->container->singleton( Post_Ops::class, Post_Ops::class );

		$this->hook_to_watch_for_post_updates();
		$this->hook_to_redirect_post_updates();
		$this->hook_to_commit_post_updates();
		$this->hook_to_delete_post_data();
	}

	/**
	 * Before an HTTP request is processed and updates an Event Post, we might need to redirect the update
	 * to either the real post ID or to a different post ID.
	 *
	 * @since 6.0.0
	 */
	private function hook_to_redirect_post_updates() {
		/*
		 * Classic Editor updates will come through the `wp-admin/post.php` file.
		 * This includes Trash and Delete requests.
		 * What will differ is the HTTP method used: POST for updates, GET for Trash and Delete requests.
		 * In both instances, this is a good place to hook before WordPress identifies the update target.
		 */
		if ( ! has_action( 'load-post.php', [ $this, 'redirect_classic_editor_post_id' ] ) ) {
			add_action( 'load-post.php', [ $this, 'redirect_classic_editor_post_id' ] );
		}

		/*
		 * Intercept requests coming from the REST API (thus including Blocks Editor).
		 */
		add_filter( 'rest_pre_dispatch', [ $this, 'redirect_rest_request_post_id' ], 5, 3 );
	}

	/**
	 * Following a request (that _might_ have been redirected to a different post ID), WordPress might
	 * have updated meta we care about (dates, duration ,timezone et al.).
	 * Running an update each time one of the meta we care about is updated would be inefficient
	 * and prone to errors.
	 * Just earmark Events whose meta has changed as possibly requiring an update, no **actual**
	 * update to the custom tables happened yet.
	 *
	 * @since 6.0.0
	 */
	private function hook_to_watch_for_post_updates() {
		add_action( 'updated_postmeta', [ $this, 'watch_for_meta_updates' ], 10, 3 );
		add_action( 'added_post_meta', [ $this, 'watch_for_meta_updates' ], 10, 3 );
		add_action( 'deleted_post_meta', [ $this, 'watch_for_meta_updates' ], 10, 3 );
	}

	/**
	 * At the end of the request, update the custom tables, if required, following updates
	 * to the meta we use to model Events.
	 * The "end of the request" will change depending on the kind of request.
	 * Requests coming from both the Classic and Blocks Editor will perform updates following
	 * a WRITE-THEN-READ model: the Event data is updated, then the updated Event data is read
	 * to build the response or redirect the user to the correct page.
	 * Other updates might happen programmatically, i.e. by means of calls to the `wp_insert_post`
	 * and similar functions.
	 *
	 * @since 6.0.0
	 *
	 */
	private function hook_to_commit_post_updates() {
		/*
		 * Performing updates on `shutdown` will work for any Event that was updated programmatically
		 * by means of a function like `wp_insert_post`. It's too late for any post that is the object
		 * of an Editor request, though.
		 */
		add_action( 'shutdown', [ $this, 'commit_updates' ] );

		/*
		 * This action fires in the context of the `wp-includes/post.php` file and will
		 * fire after the post has been updated (WRITE) and before it's redirected (READ).
		 */
		add_filter( 'redirect_post_location', [ $this, 'commit_and_redirect_classic_editor' ], 100, 2 );

		/*
		 * This action is documented in wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php
		 * It fires after the post has been updated (WRITE) following the REST request, and before the
		 * response is returned (READ).
		 */
		add_action( 'rest_after_insert_' . TEC::POSTTYPE, [ $this, 'commit_rest_update' ], 100, 2 );

		/**
		 * Hook into the logic that would rebuild the known range, setting the `earliest_date`
		 * and `latest_date` options, to parse Occurrences, not Events.
		 */
		add_filter( 'tribe_events_rebuild_known_range', [ $this, 'rebuild_known_range' ] );
	}

	/**
	 * Unregisters, from the Filters API, the actions and filters added by this provider.
	 *
	 * @since 6.0.0
	 */
	public function unregister() {
		remove_action( 'updated_postmeta', [ $this, 'watch_for_meta_updates' ] );
		remove_action( 'added_post_meta', [ $this, 'watch_for_meta_updates' ] );
		remove_action( 'deleted_post_meta', [ $this, 'watch_for_meta_updates' ] );
		remove_action( 'load-post.php', [ $this, 'redirect_classic_editor_post_id' ] );
		remove_filter( 'rest_pre_dispatch', [ $this, 'redirect_rest_request_post_id' ], 5 );
		remove_action( 'rest_after_insert_' . TEC::POSTTYPE, [ $this, 'commit_rest_update' ], 100 );
		remove_action( 'delete_post', [ $this, 'delete_custom_tables_data' ] );
	}

	/**
	 * This plugin will, by default, not redirect the post ID at all.
	 * It will, instead, fire an action allowing other plugins to
	 * intervene and redirect the post ID.
	 *
	 * @since 6.0.0
	 */
	public function redirect_classic_editor_post_id() {
		/**
		 * Fires when a Classic Editor request is ready to be redirected, before
		 * any update specified by the request is applied to the post ID.
		 *
		 * @since 6.0.0
		 */
		do_action( 'tec_events_custom_tables_v1_redirect_classic_editor_event_post' );
	}

	/**
	 * Redirect the REST Request, modifying it.
	 *
	 * By default, this method will NOT redirect the REST request and will
	 * only provide other plugins the chance to do so.
	 *
	 * @since 6.0.0
	 *
	 * @param null|mixed      $result  The result of the dispatch, as filtered by
	 *                                 WordPress and previous filters.
	 * @param WP_REST_Server  $server  A reference to the REST Server instance
	 *                                 currently handling the request.
	 * @param WP_REST_Request $request A reference to the REST Request that is
	 *                                 going to be handled.
	 *
	 * @return null|mixed The input result value: this method will not modify
	 *                    the result and will just use the filter as an action.
	 */
	public function redirect_rest_request_post_id( $result, $server, $request ) {
		/**
		 * Fires when a REST request for an Event is ready to be processed and has not
		 * yet, been dispatched.
		 * Plugins that need to redirect the request should do so here.
		 *
		 * @since 6.0.0
		 *
		 * @param WP_REST_Request $request A reference to the REST Request that is
		 *                                 going to be handled.
		 * @param WP_REST_Server  $server  A reference to the REST Server instance
		 *                                 currently handling the request.
		 */
		do_action( 'tec_events_custom_tables_v1_redirect_rest_event_post', $request, $server );

		return $result;
	}

	/**
	 * Watches the updates to the post objects meta values to detect and keep track
	 * of changes that might require the data in the custom tables to be updated.
	 *
	 * @since 6.0.0
	 *
	 * @param array|int $meta_ids Either a meta id or an array of meta ids that
	 *                            are being updated.
	 * @param int       $post_id  The id of the post that is being updated.
	 * @param string    $meta_key The meta key that is being updated.
	 */
	public function watch_for_meta_updates( $meta_ids, $post_id, $meta_key ) {
		$this->container->make( Meta_Watcher::class )->mark_for_update( $post_id, $meta_key );
	}

	/**
	 * Iterates over the list of Event posts that had their Event-related meta
	 * updated to update their custom tables data.
	 *
	 * @since 6.0.0
	 */
	public function commit_updates() {
		$this->container->make( Controller::class )->commit_updates();
	}

	/**
	 * Commits custom tables updates for an Event post that might require it in the
	 * context of a REST API request (including Blocks Editor).
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Post         $post          A reference to the Event post object
	 *                                       that is being updated.
	 * @param WP_REST_Request $request       A reference to the REST Request that is being
	 *                                       processed.
	 */
	public function commit_rest_update( $post, $request ) {
		if ( ! ( $post instanceof WP_Post && $request instanceof WP_REST_Request ) ) {
			return;
		}

		$this->container->make( Controller::class )->commit_post_rest_update( $post, $request );
	}

	/**
	 * Commits custom tables updates for an Event post that might require it in the
	 * context of a Classic Editor request.
	 *
	 * The filter is used as an action, the input `$location` value is not changed.
	 *
	 * @since 6.0.0
	 *
	 * @param string $location The location the post will be redirected to. Unused by the method.
	 * @param int    $post_id  The post ID of the post that is being updated.
	 */
	public function commit_and_redirect_classic_editor( $location, $post_id ) {
		$controller = $this->container->make( Controller::class );
		$controller->commit_post_updates( $post_id );

		return $controller->redirect_post_location( $location, $post_id );
	}

	/**
	 * Hooks on the actions that will be fired when a post is deleted (NOT trashed)
	 * from the database to, then, remove the custom tables data.
	 *
	 * @since 6.0.0
	 */
	private function hook_to_delete_post_data() {
		add_action( 'delete_post', [ $this, 'delete_custom_tables_data' ] );
	}

	/**
	 * Hooked on the post delete action, this method will clear all the custom
	 * tables information related to the Event.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The deleted Event post ID.
	 */
	public function delete_custom_tables_data( $post_id ) {
		if ( ! is_int( $post_id ) ) {
			return;
		}

		$this->container->make( Controller::class )->delete_custom_tables_data( $post_id );
	}

	/**
	 * Rebuild the known range of Events from the Occurrences information.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the method did take care of rebuilding the known range or not.
	 */
	public function rebuild_known_range() {
		return $this->container->make( Events::class )->rebuild_known_range();
	}
}
