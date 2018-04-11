<?php

abstract class Tribe__Events__REST__V1__Endpoints__Archive_Base
	extends Tribe__Events__REST__V1__Endpoints__Base {
	/**
	 * @var string The post type managed by this archive
	 */
	protected $post_type = '';

	/**
	 * @var array An array mapping the REST request supported query vars to the args used in a TEC WP_Query.
	 */
	protected $supported_query_vars = array();

	/**
	 * @var Tribe__Events__REST__Interfaces__Post_Repository
	 */
	protected $repository;

	/**
	 * @var Tribe__Validator__Interface
	 */
	protected $validator;
	/**
	 * @var int The total number of posts according to the current request parameters and user access rights.
	 */
	protected $total;

	/**
	 * Tribe__Events__REST__V1__Endpoints__Archive_Event constructor.
	 *
	 * @param Tribe__REST__Messages_Interface                  $messages
	 * @param Tribe__Events__REST__Interfaces__Post_Repository $repository
	 * @param Tribe__Events__Validator__Interface              $validator
	 */
	public function __construct(
		Tribe__REST__Messages_Interface $messages,
		Tribe__Events__REST__Interfaces__Post_Repository $repository,
		Tribe__Events__Validator__Interface $validator
	) {
		parent::__construct( $messages );
		$this->repository = $repository;
		$this->validator = $validator;
	}

	/**
	 * Parses the `per_page` argument from the request.
	 *
	 * @param int $per_page The `per_page` param provided by the request.
	 *
	 * @return bool|int The `per_page` argument provided in the request or `false` if not set.
	 */
	public function sanitize_per_page( $per_page ) {
		return ! empty( $per_page ) ?
			min( $this->get_max_posts_per_page(), intval( $per_page ) )
			: false;
	}

	/**
	 * Returns the maximum number of posts per page fetched via the REST API.
	 *
	 * @return int
	 */
	abstract public function get_max_posts_per_page();

	/**
	 * Returns the total number of pages depending on the `per_page` setting.
	 *
	 * @param int $total
	 * @param int $per_page
	 *
	 * @return int
	 */
	protected function get_total_pages( $total, $per_page = null ) {
		$per_page = $per_page ? $per_page : get_option( 'posts_per_page' );
		$total_pages = (int) $total > 0
			? (int) ceil( $total / $per_page )
			: 0;

		return $total_pages;
	}

	/**
	 * Returns the archive base REST URL
	 *
	 * @return string
	 */
	abstract protected function get_base_rest_url();

	/**
	 * Builds and returns the current rest URL depending on the query arguments.
	 *
	 * @param array $args
	 * @param array $extra_args
	 *
	 * @return string
	 */
	protected function get_current_rest_url( array $args = array(), array $extra_args = array() ) {
		$url = $this->get_base_rest_url();

		$flipped = array_flip( $this->supported_query_vars );
		$values = array_intersect_key( $args, $flipped );
		$keys = array_intersect_key( $flipped, $values );

		if ( ! empty( $keys ) ) {
			$parameters = array_fill_keys( array_values( $keys ), '' );
			foreach ( $keys as $key => $value ) {
				$parameters[ $value ] = $args[ $key ];
			}

			$url = add_query_arg( $parameters, $url );
		}

		if ( ! empty( $extra_args ) ) {
			$url = add_query_arg( $extra_args, $url );
		}

		return $url;
	}

	/**
	 * Builds and returns the next page REST URL.
	 *
	 * @param string $rest_url
	 * @param int $page
	 *
	 * @return string
	 */
	protected function get_next_rest_url( $rest_url, $page ) {
		return add_query_arg( array( 'page' => $page + 1 ), remove_query_arg( 'page', $rest_url ) );
	}

	/**
	 * Builds and returns the previous page REST URL.
	 *
	 * @param string $rest_url
	 * @param int $page
	 *
	 * @return string
	 */
	protected function get_previous_rest_url( $rest_url, $page ) {
		$rest_url = remove_query_arg( 'page', $rest_url );

		return 2 === $page ? $rest_url : add_query_arg( array( 'page' => $page - 1 ), $rest_url );
	}

	/**
	 * Filters a list of post stati returning only those accessible by the current user for the post type
	 * managed by the endpoint.
	 *
	 * @since 4.6
	 *
	 * @param array|string $post_stati An array of post stati or a comma separated list of post stati.
	 *
	 * @return array|bool An array of post stati accessible by the current user or `false` if the no requested
	 *               stati are accessible by the user.
	 */
	public function filter_post_status_list( $post_stati = 'publish' ) {
		$stati         = Tribe__Utils__Array::list_to_array( $post_stati, ',' );
		$post_type_obj = get_post_type_object( $this->post_type );

		if ( ! current_user_can( $post_type_obj->cap->edit_posts ) ) {
			return $stati === array( 'publish' )
				? $stati
				: false;
		}

		global $wp_post_statuses;
		$valid_stati = array_keys( $wp_post_statuses );

		return count( array_intersect( $stati, $valid_stati ) ) === count( $stati )
			? $stati
			: false;
	}
}