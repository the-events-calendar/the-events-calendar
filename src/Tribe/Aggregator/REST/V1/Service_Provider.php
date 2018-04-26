<?php

/**
 * Class Tribe__Events__Aggregator__REST__V1__Service_Provider
 *
 * Provides the Event Aggregator batch process support functionality.
 *
 * @since TBD
 */
class Tribe__Events__Aggregator__REST__V1__Service_Provider extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 */
	public $namespace;

	/**
	 * Registers the classes and functionality needed to support batch imports.
	 *
	 * @since TBD
	 */
	public function register() {
		// @todo -- should we check for a valid license here? Usage?

		tribe_singleton( 'events-aggregator.rest-api.v1.endpoints.batch', 'Tribe__Events__Aggregator__REST__V1__Endpoints__Batch' );

		add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	}

	/**
	 * Registers the REST API endpoints needed to support batch imports.
	 *
	 * @since TBD
	 */
	public function register_endpoints() {
		/** @var Tribe__REST__Endpoints__CREATE_Endpoint_Interface $batch_endpoint */
		$batch_endpoint = tribe( 'events-aggregator.rest-api.v1.endpoints.batch' );

		// @todo -- should we check the specific length of the import ID here?
		$this->namespace = 'tribe/event-aggregator/v1';
		register_rest_route( $this->namespace, '/import/(?P<import_id>\w+)/batch', array(
			'methods' => WP_REST_Server::CREATABLE,
			'args' => $batch_endpoint->CREATE_args(),
			'permission_callback' => array( $batch_endpoint, 'can_create' ),
			'callback' => array( $batch_endpoint, 'create' ),
		) );
	}
}
