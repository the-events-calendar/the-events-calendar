<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// bail if already defined
if ( class_exists( 'Tribe__Events__Aggregator__Service' ) ) {
	return;
}

class Tribe__Events__Aggregator__Service {
	/**
	 * @var Tribe__Events__Aggregator__Service Event Aggregator Service class
	 */
	protected static $instance;

	/**
	 * @var Tribe__Events__Aggregator Event Aggregator object
	 */
	protected $aggregator;

	/**
	 * @var string Event Aggregator API version
	 */
	protected $api_version = 'v1';

	/**
	 * @var string Event Aggregator URL
	 */
	protected $api_base_url = 'http://api.tri.be/';

	/**
	 * @var string Event Aggregator API root
	 */
	protected $api_root = 'wp-json/event-aggregator/';

	/**
	 * @var string Event Aggregator API key
	 */
	protected $api_key;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Events__Aggregator__Service
	 */
	public static function instance( Tribe__Events__Aggregator $aggregator ) {
		if ( ! isset( self::$instance ) ) {
			$className      = __CLASS__;
			self::$instance = new $className( $aggregator );
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct( $aggregator ) {
		$this->aggregator = $aggregator;
		$this->api_key = $this->aggregator->get_license_key();

		if ( defined( 'EVENT_AGGREGATOR_API_BASE_URL' ) ) {
			$this->api_base_url = EVENT_AGGREGATOR_API_BASE_URL;
		}

		$this->fetch_origins();
	}

	/**
	 * Fetch origins from service
	 *
	 * @return array
	 */
	public function fetch_origins() {
		$origins = array(
			(object) array(
				'id' => 'csv',
				'name' => __( 'CSV File', 'the-events-calendar' ),
			),
		);

		// if the user doesn't have a license key, don't bother hitting the service
		if ( ! $this->api_key ) {
			return $origins;
		}

		$response = $this->get( 'origin' );

		if ( $response && 'success' === $response->status ) {
			$origins = array_merge( $origins, $response->data->origin );
		}

		return $origins;
	}

	/**
	 * Performs a GET request against the Event Aggregator service
	 *
	 * @param string $url Endpoint URL
	 * @param array $data Array of parameters to send to the endpoint
	 *
	 * @return stdClass
	 */
	public function get( $endpoint, $data = array() ) {
		$url = $this->build_url( $endpoint );
		$url = esc_url_raw( add_query_arg( $data, $url ) );

		$response = wp_remote_get( $url );
		$response = json_decode( wp_remote_retrieve_body( $response ) );

		return $response;
	}

	/**
	 * Builds an endpoint URL
	 *
	 * @param string $endpoint Endpoint for the Event Aggregator service
	 *
	 * @return string
	 */
	public function build_url( $endpoint ) {
		$url = "{$this->api_base_url}{$this->api_root}{$this->api_version}/{$endpoint}";
		$url = add_query_arg( 'key', $this->api_key, $url );

		return $url;
	}
}
