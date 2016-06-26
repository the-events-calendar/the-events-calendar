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
		if ( ! self::$instance ) {
			self::$instance = new self( $aggregator );
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

		$this->get_origins();
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
		if ( preg_match( '/image/', $response['headers']['content-type'] ) ) {
			preg_match( '/filename="([^"]+)"/', $response['headers']['content-disposition'], $matches );

			if (
				preg_match( '/filename="([^"]+)"/', $response['headers']['content-disposition'], $matches )
				&& ! empty( $matches[1] )
			) {
				$filename = $matches[1];
			} else {
				$extension = str_replace( 'image/', '', $results['headers']['content-type'] );
				$filename = md5( $results['body'] ) . '.' . $extension;
			}

			$filename = sanitize_file_name( $filename );

			$upload_dir = wp_upload_dir();
			$filepath = $upload_dir['path'] . DIRECTORY_SEPARATOR . $filename;

			file_put_contents( $filepath, $results['body'] );

			return $filepath;
		} else {
			$response = json_decode( wp_remote_retrieve_body( $response ) );
		}

		return $response;
	}

	/**
	 * Performs a POST request against the Event Aggregator service
	 *
	 * @param string $url Endpoint URL
	 * @param array $data Array of parameters to send to the endpoint
	 *
	 * @return stdClass
	 */
	public function post( $endpoint, $data = array() ) {
		$url = $this->build_url( $endpoint );

		if ( empty( $data['body'] ) ) {
			$args = array( 'body' => $data );
		} else {
			$args = $data;
		}

		$response = wp_remote_post( $url, $args );
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
	 * Fetch import data from service
	 *
	 * @return array
	 */
	public function get_import( $import_id ) {
		// if the user doesn't have a license key, don't bother hitting the service
		if ( ! $this->api_key ) {
			return new WP_Error( 'invalid-ea-license', __( 'You must enter an Event Aggregator license key in Events > Settings > Licenses', 'the-events-calendar' ) );
		}

		$response = $this->get( 'import/' . $import_id );

		return $response;
	}

	/**
	 * Creates an import
	 *
	 * @param array $args {
	 *     Array of arguments. See REST docs for details. 1 excpetion listed below:
	 *
	 *     @type array $source_file Source file array using the $_FILES array values
	 * }
	 *
	 * @return string
	 */
	public function post_import( $args ) {
		// if the user doesn't have a license key, don't bother hitting the service
		if ( ! $this->api_key ) {
			return new WP_Error( 'invalid-ea-license', __( 'You must enter an Event Aggregator license key in Events > Settings > Licenses', 'the-events-calendar' ) );
		}

		$request_args = array(
			'body' => $args,
		);

		if ( isset( $args['source_file'] ) ) {
			$boundary = wp_generate_password( 24 );
			$headers = array(
				'content-type' => 'multipart/form-data; boundary=' . $boundary,
			);

			$payload = array();
			foreach ( $args as $name => $value ) {
				if ( 'source_file' === $name ) {
					continue;
				}

				if ( 'source' === $name ) {
					continue;
				}

				$payload[] = '--' . $boundary;
				$payload[] = 'Content-Disposition: form-data; name="' . $name . '"'. "\r\n";
				$payload[] = $value;
			}

			$payload[] = '--' . $boundary;
			$payload[] = 'Content-Disposition: form-data; name="source"; filename="' . basename( $args['source_file']['name'] ) . '"' . "\r\n";
			$payload[] = file_get_contents( $args['source_file']['tmp_name'] );
			$payload[] = '--' . $boundary . '--';

			$args = array(
				'headers' => $headers,
				'body' => implode( "\r\n", $payload ),
			);
		} else {
			$args = $request_args;
		}

		$response = $this->post( 'import', $args );
	}

	/**
	 * Fetches an image from the Event Aggregator service
	 *
	 * @param string $image_id Image ID to fetch
	 */
	public function get_image( $image_id ) {
		// if the user doesn't have a license key, don't bother hitting the service
		if ( ! $this->api_key ) {
			return new WP_Error( 'invalid-ea-license', __( 'You must enter an Event Aggregator license key in Events > Settings > Licenses', 'the-events-calendar' ) );
		}

		$response = $this->get( 'image/' . $image_id );

		return $response;
	}
}
