<?php

class Tribe__Events__REST__V1__Endpoints__Single_Category
	extends Tribe__Events__REST__V1__Endpoints__Term_Single_Base
	implements Tribe__REST__Endpoints__READ_Endpoint_Interface,
	Tribe__REST__Endpoints__CREATE_Endpoint_Interface,
	Tribe__REST__Endpoints__DELETE_Endpoint_Interface,
	Tribe__REST__Endpoints__UPDATE_Endpoint_Interface,
	Tribe__Documentation__Swagger__Provider_Interface {

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @since 4.6
	 *
	 * @return array
	 */
	public function CREATE_args() {
		return [
			'name'        => [
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_string' ],
				'type'              => 'string',
				'description'       => __( 'The event category name', 'the-events-calendar' ),
			],
			'description' => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_string' ],
				'type'              => 'string',
				'description'       => __( 'The event category description', 'the-events-calendar' ),
			],
			'slug'        => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_string' ],
				'type'              => 'string',
				'description'       => __( 'The event category slug', 'the-events-calendar' ),
			],
			'parent'      => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_event_category' ],
				'type'              => 'integer',
				'description'       => __( 'The event category slug', 'the-events-calendar' ),
			],
		];
	}

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
	 * @link  http://swagger.io/
	 *
	 * @return array An array description of a Swagger supported component.
	 */
	public function get_documentation() {
		$GET_defaults  = $DELETE_defaults = [ 'in' => 'query', 'default' => '', 'type' => 'string' ];
		$POST_defaults = [ 'in' => 'formData', 'default' => '', 'type' => 'string' ];
		$post_args = array_merge( $this->READ_args(), $this->CREATE_args() );

		return [
			'get'    => [
				'parameters' => $this->swaggerize_args( $this->READ_args(), $GET_defaults ),
				'responses'  => [
					'200' => [
						'description' => __( 'Returns the data of the event category with the specified term ID', 'the-events-calendar' ),
						'schema'      => [
							'$ref' => '#/definitions/Term',
						],
					],
					'400' => [
						'description' => __( 'The event category term ID is missing.', 'the-events-calendar' ),
					],
					'404' => [
						'description' => __( 'An event category with the specified term ID does not exist.', 'the-events-calendar' ),
					],
				],
			],
			'post'   => [
				'consumes'   => [ 'application/x-www-form-urlencoded' ],
				'parameters' => $this->swaggerize_args( $post_args, $POST_defaults ),
				'responses'  => [
					'200' => [
						'description' => __( 'Returns the data of the updated event category', 'the-events-calendar' ),
						'schema'      => [
							'$ref' => '#/definitions/Term',
						],
					],
					'201' => [
						'description' => __( 'Returns the data of the created event category', 'the-events-calendar' ),
						'schema'      => [
							'$ref' => '#/definitions/Term',
						],
					],
					'400' => [
						'description' => __( 'A required parameter is missing or an input parameter is in the wrong format', 'the-events-calendar' ),
					],
					'403' => [
						'description' => __( 'The user is not authorized to create event categories', 'the-events-calendar' ),
					],
				],
			],
			'delete' => [
				'parameters' => $this->swaggerize_args( $this->DELETE_args(), $DELETE_defaults ),
				'responses'  => [
					'200' => [
						'description' => __( 'Deletes an event category and returns its data', 'the-events-calendar' ),
						'schema'      => [
							'$ref' => '#/definitions/Term',
						],
					],
					'400' => [
						'description' => __( 'The event category term ID is missing or does not exist.', 'the-events-calendar' ),
					],
					'403' => [
						'description' => __( 'The current user cannot delete the event category with the specified term ID.', 'the-events-calendar' ),
					],
					'410' => [
						'description' => __( 'The event category with the specified term ID has been deleted already.', 'the-events-calendar' ),
					],
					'500' => [
						'description' => __( 'The event category with the specified term ID could not be deleted.', 'the-events-calendar' ),
					],
				],
			],
		];
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
		return [
			'id' => [
				'in'                => 'path',
				'type'              => 'integer',
				'description'       => __( 'the event category term ID', 'the-events-calendar' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_event_category' ],
			],
		];
	}

	/**
	 * Returns the taxonomy of the terms handled by the endpoint.
	 *
	 * @since 4.6
	 *
	 * @return string
	 */
	public function get_taxonomy() {
		return Tribe__Events__Main::TAXONOMY;
	}

	/**
	 * Returns the term namespace used by the endpoint.
	 *
	 * @since 4.6
	 *
	 * @return string
	 */
	protected function get_term_namespace() {
		return 'categories';
	}
}
