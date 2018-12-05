<?php

class Tribe__Events__REST__V1__Endpoints__Archive_Tag
	extends Tribe__Events__REST__V1__Endpoints__Term_Archive_Base
	implements Tribe__REST__Endpoints__READ_Endpoint_Interface,
	Tribe__Documentation__Swagger__Provider_Interface {

	/**
	 * @var array An array mapping the REST request supported query vars to the args used in a WP_REST_Terms_Controller query.
	 */
	protected $supported_query_vars = array(
		'page'       => 'page',
		'per_page'   => 'per_page',
		'search'     => 'search',
		'exclude'    => 'exclude',
		'include'    => 'include',
		'order'      => 'order',
		'orderby'    => 'orderby',
		'hide_empty' => 'hide_empty',
		'post'       => 'post',
		'event'      => 'post',
		'slug'       => 'slug',
	);

	/**
	 * Returns an array in the format used by Swagger 2.0.
	 *
	 * While the structure must conform to that used by v2.0 of Swagger the structure can be that of a full document
	 * or that of a document part.
	 * The intelligence lies in the "gatherer" of informations rather than in the single "providers" implementing this
	 * interface.
	 *
	 * @since 4.6
	 *
	 * @link http://swagger.io/
	 *
	 * @return array An array description of a Swagger supported component.
	 */
	public function get_documentation() {
		return array(
			'get' => array(
				'parameters' => $this->swaggerize_args( $this->READ_args(), array( 'in' => 'query', 'default' => '' ) ),
				'responses'  => array(
					'200' => array(
						'description' => __( 'Returns all the event tags matching the search criteria', 'the-event-calendar' ),
						'schema'      => array(
							'title' => $this->get_data_key(),
							'type'  => 'array',
							'items' => array( '$ref' => '#/definitions/Term' ),
						),
					),
					'400' => array(
						'description' => __( 'One or more of the specified query variables has a bad format', 'the-events-calendar' ),
					),
					'404' => array(
						'description' => __( 'No event tags match the query or the requested page was not found.', 'the-events-calendar' ),
					),
				),
			),
		);
	}

	/**
	 * Returns the taxonomy of the terms handled by the endpoint.
	 *
	 * @since 4.6
	 *
	 * @return string
	 */
	public function get_taxonomy() {
		return 'post_tag';
	}

	/**
	 * Returns the archive base REST URL
	 *
	 * @since 4.6
	 *
	 * @return string
	 */
	protected function get_base_rest_url() {
		return tribe_events_rest_url( 'tags/' );
	}

	/**
	 * Returns the data key that will be used to store terms data in the response.
	 *
	 * @since 4.6
	 *
	 * @return string
	 */
	protected function get_data_key() {
		return 'tags';
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @since 4.6
	 *
	 * @return array
	 */
	public function READ_args() {
		return array(
			'page'       => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_positive_int' ),
				'default'           => 1,
				'description'       => __( 'The archive page to return', 'the-events-calendar' ),
				'type'              => 'integer',
			),
			'per_page'   => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_positive_int' ),
				'sanitize_callback' => array( $this, 'sanitize_per_page' ),
				'default'           => $this->get_default_posts_per_page(),
				'description'       => __( 'The number of event tags to return on each page', 'the-events-calendar' ),
				'type'              => 'integer',
			),
			'search'     => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'description'       => __( 'Limit results to those matching a string', 'the-events-calendar' ),
				'type'              => 'string',
			),
			'exclude'    => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_post_tag' ),
				'description'       => __( 'Ensure result set exclude specific IDs', 'the-events-calendar' ),
				'swagger_type'      => 'array',
				'items' => array( 'type' => 'integer' ),
			),
			'include'    => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_post_tag' ),
				'description'       => __( 'Limit result set to specific IDs', 'the-events-calendar' ),
				'swagger_type'      => 'array',
				'items' => array( 'type' => 'integer' ),
			),
			'order'      => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'description'       => __( 'Order sort attribute ascending or descending', 'the-events-calendar' ),
				'type'              => 'string',
			),
			'orderby'    => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'description'       => __( 'Sort collection by term attribute', 'the-events-calendar' ),
				'type'              => 'string',
			),
			'hide_empty' => array(
				'required'    => false,
				'description' => __( 'Whether to hide terms not assigned to any posts', 'the-events-calendar' ),
				'type'        => 'boolean',
				'default'     => '1',
			),
			'post'       => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_event_id' ),
				'description'       => __( 'Limit result set to terms assigned to a specific post', 'the-events-calendar' ),
				'type'              => 'integer',
			),
			'event'      => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_event_id' ),
				'description'       => __( 'Limit result set to terms assigned to a specific event (an alias of the "post" parameter)',
					'the-events-calendar' ),
				'type'              => 'integer',
			),
			'slug'       => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'description'       => __( 'Limit result set to terms with a specific slug', 'the-events-calendar' ),
				'type'              => 'string',
			),
		);
	}
}
