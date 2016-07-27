<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
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
	 * API varibles stored in a single Object
	 *
	 * @var array $api {
	 *     @type string     $key         License key for the API (PUE)
	 *     @type string     $version     Which version of we are dealing with
	 *     @type string     $domain      Domain in which the API lies
	 *     @type string     $path        Path of the API on the domain above
	 * }
	 */
	protected $api = array(
		'key' => null,
		'version' => 'v1',
		'domain' => 'http://api.tri.be/',
		'path' => 'wp-json/event-aggregator/',
	);

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Events__Aggregator__Service
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Create a clean way of fetching API variables
	 *
	 * @return stdClass|WP_Error
	 */
	public function api() {
		// Make it an Object
		$api = (object) $this->api;

		if ( defined( 'EVENT_AGGREGATOR_API_BASE_URL' ) ) {
			$api->domain = EVENT_AGGREGATOR_API_BASE_URL;
		}

		// Since we don't need to fetch this key elsewhere
		$api->key = get_option( 'pue_install_key_event_aggregator' );

		/**
		 * Creates a clean way to filter and redirect to another API domain/path
		 * @var stdClass
		 */
		$api = (object) apply_filters( 'tribe_ea_api', $api );

		// The user doesn't have a license key
		if ( ! $api->key ) {
			return new WP_Error( 'invalid-ea-license', __( 'You must enter an Event Aggregator license key in Events > Settings > Licenses', 'the-events-calendar' ) );
		}

		return $api;
	}

	/**
	 * Builds an endpoint URL
	 *
	 * @param string $endpoint  Endpoint for the Event Aggregator service
	 * @param array  $data      Parameters to add to the URL
	 *
	 * @return string|WP_Error
	 */
	public function build_url( $endpoint, $data = array() ) {
		$api = $this->api();

		// If we have an WP_Error we return it here
		if ( is_wp_error( $api ) ) {
			return $api;
		}

		// Build the URL
		$url = "{$api->domain}{$api->path}{$api->version}/{$endpoint}";

		// Enforce Key on the Query Data
		$data['key'] = $api->key;

		// If we have data we add it
		$url = add_query_arg( $data, $url );

		return $url;
	}

	/**
	 * Performs a GET request against the Event Aggregator service
	 *
	 * @param string $endpoint   Endpoint for the Event Aggregator service
	 * @param array  $data       Parameters to send to the endpoint
	 *
	 * @return stdClass|WP_Error
	 */
	public function get( $endpoint, $data = array() ) {
		$url = $this->build_url( $endpoint, $data );

		// If we have an WP_Error we return it here
		if ( is_wp_error( $url ) ) {
			return $url;
		}

		$response = wp_remote_get( esc_url_raw( $url ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// if the response is not an image, let's json decode the body
		if ( ! preg_match( '/image/', $response['headers']['content-type'] ) ) {
			$response = json_decode( wp_remote_retrieve_body( $response ) );
		}

		return $response;
	}

	/**
	 * Performs a POST request against the Event Aggregator service
	 *
	 * @param string $endpoint   Endpoint for the Event Aggregator service
	 * @param array  $data       Parameters to send to the endpoint
	 *
	 * @return stdClass|WP_Error
	 */
	public function post( $endpoint, $data = array() ) {
		$url = $this->build_url( $endpoint );

		// If we have an WP_Error we return it here
		if ( is_wp_error( $url ) ) {
			return $url;
		}

		if ( empty( $data['body'] ) ) {
			$args = array( 'body' => $data );
		} else {
			$args = $data;
		}

		$response = wp_remote_post( esc_url_raw( $url ), $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response = json_decode( wp_remote_retrieve_body( $response ) );

		return $response;
	}

	/**
	 * Fetch origins from service
	 *
	 * @return array
	 */
	public function get_origins() {
		$origins = array(
			(object) array(
				'id' => 'csv',
				'name' => __( 'CSV File', 'the-events-calendar' ),
			),
		);

		$response = $this->get( 'origin' );

		// If we have an WP_Error we return only CSV
		if ( is_wp_error( $response ) ) {
			return $origins;
		}

		if ( $response && 'success' === $response->status ) {
			$origins = array_merge( $origins, $response->data->origin );
		}

		return $origins;
	}

	/**
	 * Fetch import data from service
	 *
	 * @param string   $import_id   ID of the Import Record
	 *
	 * @return stdClass|WP_Error
	 */
	public function get_import( $import_id ) {
		$response = $this->get( 'import/' . $import_id );

		return $response;
	}

	/**
	 * Creates an import
	 *
	 * @param array $args {
	 *     Array of arguments. See REST docs for details. 1 exception listed below:
	 *
	 *     @type array $source_file Source file array using the $_FILES array values
	 * }
	 *
	 * @return string
	 */
	public function post_import( $args ) {
		$api = $this->api();

		// if the user doesn't have a license key, don't bother hitting the service
		if ( is_wp_error( $api ) ) {
			return $api;
		}

		$request_args = array(
			'body' => $args,
		);

		if ( isset( $args['file'] ) ) {
			$boundary = wp_generate_password( 24 );
			$headers = array(
				'content-type' => 'multipart/form-data; boundary=' . $boundary,
			);

			$payload = array();
			foreach ( $args as $name => $value ) {
				if ( 'file' === $name ) {
					continue;
				}

				if ( 'source' === $name ) {
					continue;
				}

				$payload[] = '--' . $boundary;
				$payload[] = 'Content-Disposition: form-data; name="' . $name . '"'. "\r\n";
				$payload[] = $value;
			}

			$file_path = null;
			$file_name = null;

			if ( is_numeric( $args['file'] ) ) {
				$file_id = absint( $args['file'] );
				$file_path = get_attached_file( $file_id );

				if ( ! file_exists( $file_path ) ) {
					$file_path = null;
				} else {
					$file_name = basename( $file_path );
				}
			} elseif ( ! empty( $args['file']['tmp_name'] ) && ! empty( $args['file']['name'] ) ) {
				if ( file_exists( $args['file']['tmp_name'] ) ) {
					$file_path = $args['file']['tmp_name'];
					$file_name = basename( $args['file']['name'] );
				}
			}

			if ( $file_path && $file_name ) {
				$payload[] = '--' . $boundary;
				$payload[] = 'Content-Disposition: form-data; name="source"; filename="' . $file_name . '"' . "\r\n";
				$payload[] = file_get_contents( $file_path );
				$payload[] = '--' . $boundary . '--';
			}

			$args = array(
				'headers' => $headers,
				'body' => implode( "\r\n", $payload ),
			);
		} else {
			$args = $request_args;
		}

		$response = $this->post( 'import', $args );
		return $response;
	}

	/**
	 * Fetches an image from the Event Aggregator service
	 *
	 * @param string $image_id Image ID to fetch
	 *
	 * @return stdClass|WP_Error
	 */
	public function get_image( $image_id ) {
		$response = $this->get( 'image/' . $image_id );

		return $response;
	}
}
