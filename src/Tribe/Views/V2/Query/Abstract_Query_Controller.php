<?php
/**
 * The base implementation for the Views v2 query controllers.
 *
 * @package Tribe\Events\Views\V2\Query
 * @since 4.9.2
 */

namespace Tribe\Events\Views\V2\Query;

/**
 * Class Abstract_Query_Controller
 *
 * @package Tribe\Events\Views\V2\Query
 * @since 4.9.2
 */
abstract class Abstract_Query_Controller {

	/**
	 * The query object currently being filtered.
	 *
	 * @var \WP_Query
	 */
	protected $filtering_query;

	/**
	 * Conditionally modify or populate the posts before a query runs.
	 *
	 * The injection, or modification, will only happen if the query is the main one and if the requested post types
	 * are all supported by the Event_Query_Controller.
	 *
	 * @param  null|array  $posts  The array of posts to populate. By default empty when coming from the WP_Query class,
	 *                            it might have been pre-populated by other methods though.
	 * @param  \WP_Query|null  $query  The query object currently being filtered, if any.
	 *
	 * @return array|null A populated list of posts, or the original value if the filtering should not apply.
	 * @since 4.9.2
	 *
	 */
	public function inject_posts( $posts = null, \WP_Query $query = null ) {
		if ( ! $query instanceof \WP_Query ) {
			return $posts;
		}

		if ( ! empty( $query->tribe_controller ) || ! $query->is_main_query() ) {
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

			$post__in = wp_list_pluck( $query_posts, 'ID' );
		}

		/**
		 * Allows pre-filling the injected posts to skip the Event_Query_Controller logic completely.
		 *
		 * This filter will only run if the query is the main query and the queries post types
		 * are all supported by the controller.
		 *
		 * @since 4.9.2
		 *
		 * @param  array|null  $injected_posts  An array of posts that will be injected, defaults to `null`.
		 * @param  array|null  $posts  The array of posts as received from the Event_Query_Controller from the WP_Query filter.
		 * @param  \WP_Query  $query  The query object that is being filtered.
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
		 * @param array $orm_args
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

		$repository           = $this->repository()->by_args( $orm_args );
		$injected_posts       = $repository->all();

		// @todo why are we setting these here when WP does it?
		$query->found_posts   = $repository->found();
		$query->post_count    = $repository->count();
		$query->post          = reset( $injected_posts );
		$query->max_num_pages = $query->post_count > 0
			? ceil( $query->found_posts / $query->post_count )
			: 1;

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
	public function get_supported_post_types(  ) {
		/**
		 * Filters the list of post types supported by the Event_Query_Controller.
		 *
		 * This list will be used to decide if a query post injection should be performed by the Event_Query_Controller or not.
		 * If not all the post types the query is for are supported then the Event_Query_Controller will not intervene.
		 *
		 * @param  array  $post_types  An array of post types supported by the Event_Query_Controller.
		 * @param  \WP_Query  $query  The query object currently being filtered, if set.
		 *
		 * @since 4.9.2
		 *
		 */
		return apply_filters(
			"tribe_events_views_v2_{$this->get_filter_name()}_query_controller_post_types",
			$this->get_default_post_types(),
			$this->filtering_query
		);
	}

	/**
	 * Returns the name that will be used to build the controller filters.
	 *
	 * @since 4.9.2
	 *
	 * @return string The name that will be used to build the controller filters, a slug.
	 */
	abstract protected function get_filter_name();

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
	abstract protected function get_default_post_types();

	/**
	 * Returns the repository the controller will use to fetch posts.
	 *
	 * @return \Tribe__Repository__Interface
	 * @since 4.9.2
	 *
	 */
	abstract protected function repository();
}