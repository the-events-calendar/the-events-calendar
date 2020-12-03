<?php

/**
 * Class Tribe__Events__Aggregator__REST__V1__Service_Provider
 *
 * Provides the Event Aggregator batch process support functionality.
 *
 * @since 4.6.15
 */
class Tribe__Events__Aggregator__REST__V1__Service_Provider extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 */
	public $namespace;

	/**
	 * Registers the classes and functionality needed to support batch imports.
	 *
	 * @since 4.6.15
	 */
	public function register() {
		tribe_singleton( 'events-aggregator.rest-api.v1.endpoints.batch', 'Tribe__Events__Aggregator__REST__V1__Endpoints__Batch' );
		tribe_singleton( 'events-aggregator.rest-api.v1.endpoints.state', 'Tribe__Events__Aggregator__REST__V1__Endpoints__State' );

		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Registers the REST API endpoints needed to support batch imports.
	 *
	 * @since 4.6.15
	 */
	public function register_endpoints() {
		/** @var Tribe__REST__Endpoints__CREATE_Endpoint_Interface $batch_endpoint */
		$batch_endpoint = tribe( 'events-aggregator.rest-api.v1.endpoints.batch' );
		/** @var Tribe__REST__Endpoints__CREATE_Endpoint_Interface $batch_endpoint */
		$state_endpoint = tribe( 'events-aggregator.rest-api.v1.endpoints.state' );

		$this->namespace = 'tribe/event-aggregator/v1';

		register_rest_route(
			$this->namespace,
			'/import/(?P<import_id>\w+)/batch',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'args'                => $batch_endpoint->CREATE_args(),
				'permission_callback' => [ $batch_endpoint, 'can_create' ],
				'callback'            => [ $batch_endpoint, 'create' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/import/(?P<import_id>\w+)/state',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'args'                => $state_endpoint->CREATE_args(),
				'permission_callback' => [ $state_endpoint, 'can_create' ],
				'callback'            => [ $state_endpoint, 'create' ],
			]
		);
	}
}
