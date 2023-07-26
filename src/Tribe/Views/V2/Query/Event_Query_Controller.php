<?php
/**
 * Controls an Event query connecting it with the Repository and Context.
 *
 * @since   4.9.2
 * @package Tribe\Events\Views\V2\Query
 */

namespace Tribe\Events\Views\V2\Query;

use Tribe__Events__Main as TEC;

/**
 * Class Event_Query_Controller
 *
 * @since   4.9.2
 * @package Tribe\Events\Views\V2\Query
 */
class Event_Query_Controller {
	/**
	 * The query object currently being filtered.
	 *
	 * @var \WP_Query
	 */
	private $filtering_query;

	/**
	 * Returns the name that will be used to build the controller filters.
	 *
	 * @since 4.9.2
	 *
	 * @return string The name that will be used to build the controller filters, a slug.
	 */
	public function get_filter_name() {
		return 'events';
	}

	/**
	 * Conditionally modify or populate the posts before a query runs.
	 *
	 * The injection, or modification, will only happen if the query is the main one and if the requested post types
	 * are all supported by the Event_Query_Controller.
	 *
	 * @since 4.9.2
	 *
	 * @param \WP_Query|null $query The query object currently being filtered, if any.
	 *
	 * @param null|array     $posts The array of posts to populate. By default empty when coming from the WP_Query
	 *                              class, it might have been pre-populated by other methods though.
	 *
	 * @return array|null A populated list of posts, or the original value if the filtering should not apply.
	 */
	public function inject_posts( $posts = null, \WP_Query $query = null ) {
		if ( ! $query instanceof \WP_Query ) {
			return $posts;
		}

		if ( ! ( empty( $query->tribe_controller ) && $query->is_main_query() ) ) {
			return $posts;
		}

		if ( ! $this->control_query( $query ) ) {
			return $posts;
		}

		$this->filtering_query = $query;
		// Let's flag the query as one we've handled.
		$query->tribe_controller = $this;

		$query_post_types     = (array) $query->get( 'post_type', [] );
		$supported_post_types = $this->get_supported_post_types();
		if ( count( array_intersect( $query_post_types, $supported_post_types ) ) !== count( $query_post_types ) ) {
			// Let's bail if not all the requested post types are supported by the Event_Query_Controller.
			return $posts;
		}

		$post__in = null;
		if ( null !== $query->posts ) {
			if ( ! is_array( $query->posts ) ) {
				// We don't know what's in there, let's bail.
				return $posts;
			}

			// If the query posts have been pre-filled already then let's use the information.
			$query_posts = $query->posts;

			$post__in = array_map( static function ( $post ): int {
				return is_object( $post ) ? $post->ID : (int) $post;
			}, $query_posts );
		}

		/**
		 * Allows pre-filling the injected posts to skip the Event_Query_Controller logic completely.
		 *
		 * This filter will only run if the query is the main query and the queries post types
		 * are all supported by the controller.
		 *
		 * @since 4.9.2
		 *
		 * @param array|null $injected_posts An array of posts that will be injected, defaults to `null`.
		 * @param array|null $posts          The array of posts as received from the Event_Query_Controller from the WP_Query filter.
		 * @param \WP_Query  $query          The query object that is being filtered.
		 */
		$injected_posts = apply_filters(
			"tribe_events_views_v2_{$this->get_filter_name()}_query_controller_posts",
			null,
			$posts,
			$query
		);

		if ( null !== $injected_posts ) {
			return $injected_posts;
		}

		// @todo here build the args via URL -> Context -> orm_args
		$orm_args = [];

		if ( null !== $post__in ) {
			/*
			 * Here the `post__in` might legitimately be empty.
			 * While we're usually restricting the post results we might want to search by completely different
			 * criteria; so let's move on.
			 */
			$orm_args = [ 'post__in', $post__in ];
		}

		/**
		 * Filters the arguments that will be set on the Repository/ORM to fetch the posts to inject.
		 *
		 * @since 4.9.2
		 *
		 * @param array     $orm_args
		 * @param \WP_Query $query
		 */
		$orm_args = apply_filters(
			"tribe_events_views_v2_{$this->get_filter_name()}_query_controller_orm_args",
			$orm_args,
			$query
		);

		/*
		 * Let's remove an empty `post__in` clause, after the filtering, as it will cause a SQL error.
		 */
		if ( empty( $orm_args['post__in'] ) ) {
			unset( $orm_args['post__in'] );
		}

		$repository     = $this->repository()->by_args( $orm_args );
		$injected_posts = $repository->all();

		/*
		 * The `found_posts` property would be set only if the query has `no_found_rows` unset or set to `false`.
		 * Since we have the information here, we set it now.
		 */
		$query->found_posts = $repository->found();
		// Again: we have the information now, it makes sense to set it.
		$query->post_count = count( $injected_posts );
		$query->post       = $query->post_count ? reset( $injected_posts ) : null;
		// Set the request (SQL code) the repository used to fetch the events.;
		$query->request       = $repository->get_query()->request;
		$query->max_num_pages = $query->post_count > 0
			? (int) ( ceil( $query->found_posts / $query->post_count ) )
			: 1;
		$query->is_single     = false;
		$query->is_singular   = false;
		$query->is_archive    = true;
		$query->is_page       = false;

		return $injected_posts;
	}

	/**
	 * Returns the list of post types supported by the Event_Query_Controller.
	 *
	 * This list will be used to decide if a query post injection should be performed by the Event_Query_Controller or
	 * not. If not all the post types the query is for are supported then the Event_Query_Controller will not
	 * intervene.
	 *
	 * @since 4.9.2
	 *
	 * @return array An array of post types supported by the Event_Query_Controller.
	 */
	public
	function get_supported_post_types() {
		/**
		 * Filters the list of post types supported by the Event_Query_Controller.
		 *
		 * This list will be used to decide if a query post injection should be performed by the Event_Query_Controller or not.
		 * If not all the post types the query is for are supported then the Event_Query_Controller will not intervene.
		 *
		 * @since 4.9.2
		 *
		 * @param \WP_Query $query      The query object currently being filtered, if set.
		 *
		 * @param array     $post_types An array of post types supported by the Event_Query_Controller.
		 */
		return apply_filters(
			"tribe_events_views_v2_{$this->get_filter_name()}_query_controller_post_types",
			$this->get_default_post_types(),
			$this->filtering_query
		);
	}

	/**
	 * Returns the default list of post types supported by the query controller.
	 *
	 * This list will, usually, be filtered when getting the supported post types with the `get_supported_post_types`
	 * method.
	 *
	 * @since 4.9.2
	 *
	 * @return array An array of post types supported by default from the query controller.
	 */
	private function get_default_post_types() {
		return [
			TEC::POSTTYPE,
		];
	}

	/**
	 * Returns the repository the controller will use to fetch posts.
	 *
	 * @since 4.9.2
	 *
	 * @return \Tribe__Repository__Interface
	 */
	private function repository() {
		// @todo [BTRIA-594]: Refine this to handle order depending on the View.
		return tribe_events()->order_by( 'event_date', 'ASC' );
	}

	/**
	 * Parses the query to add/remove properties.
	 *
	 * @since 4.9.11
	 *
	 * @param \WP_Query $query The current WordPress query object.
	 */
	public function parse_query( \WP_Query $query ) {
		if ( ! $this->control_query( $query ) ) {
			return;
		}

		/*
		 * We should only inject posts if doing PHP initial state render and if this is the main query.
		 * We can correctly use the global context as that's the only context we're interested in.
		 * Else bail early and inexpensively.
		 */
		if ( ! $this->is_php_initial_state_main_query( $query ) ) {
			return;
		}

		/*
		 * If this method fires on the `tribe_events_parse_query` action, then the `Tribe__Events__Query::parse_query`
		 * method should have set a number of `tribe_` flag properties on the query.
		 * These allow us to know if we should suppress v1 query filters for this query or not.
		 */
		$suppress_filters = array_sum(
			[
				// It must be an event query.
				! empty( $query->tribe_is_event_query ),
				// It must be a query only for the events post type.
				empty( $query->tribe_is_multi_posttype ),
				// It must be a query for an archive of events.
				! empty( $query->is_archive ),
				// It must not be a feed query, we delegate feed filtering to v1 currently.
				empty( $query->is_feed ),
			]
		);

		if ( 4 === $suppress_filters || $query->is_embed ) {
			$query->set( 'tribe_suppress_query_filters', true );
		}
	}

	/**
	 * Checks whether the query controller should control the query or not.
	 *
	 * @since 5.0.3
	 *
	 * @param null|\WP_Query $query The current query object.
	 *
	 * @return bool Whether the query controller should control the query or not.
	 */
	private function control_query( $query = null ) {
		/**
		 * Toggle filter to control the query controller and, if required, deactivate it.
		 *
		 * @since 5.0.3
		 *
		 * @param bool                    $active Whether this query controller should be active or not.
		 * @param \WP_Query|null          $query  The current query object.
		 * @param \Event_Query_Controller $this   This query controller instance.
		 */
		return apply_filters(
			"tribe_views_v2_{$this->get_filter_name()}_query_controller_active",
			true,
			$query,
			$this
		);
	}

	/**
	 * Checks whether a query is the main query running in the context of PHP initial state or not.
	 *
	 * @since 6.0.0
	 *
	 * @param \WP_Query $query A reference to the `WP_Query` instance to check.
	 *
	 * @return bool Whether the query is the main query running in the context of the PHP initial state
	 *              render or not.
	 */
	private function is_php_initial_state_main_query( $query ) {
		return tribe_context()->doing_php_initial_state()
		       && $query instanceof \WP_Query
		       && $query->is_main_query();
	}
}
