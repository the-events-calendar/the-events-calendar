<?php
// Don't load directly
defined( 'WPINC' ) or die;

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
	 * @var object
	 */
	protected $origins = false;

	/**
	 * Codes and strings from the EA Service. These only exist here so that they can be translated
	 * @var array
	 */
	private $service_messages = array();

	/**
	 * @var string
	 */
	public static $auth_transient = 'tribe_aggregator_has_eventbrite_authorized_response';

	/**
	 * The name of the transient containing the Meetup authorization response.
	 *
	 * @since 4.9.6
	 *
	 * @var string
	 */
	public static $auth_transient_meetup = 'tribe_aggregator_has_meetup_authorized_response';

	/**
	 * API varibles stored in a single Object
	 *
	 * @var array $api {
	 *     @type string     $key         License key for the API (PUE)
	 *     @type string     $version     Which version of we are dealing with
	 *     @type string     $domain      Domain in which the API lies
	 *     @type string     $path        Path of the API on the domain above
	 *     @type array      $licenses    Array with plugins and licenses that we will pass to EA
	 * }
	 */
	public $api = [
		'key'      => null,
		'version'  => 'v1',
		'domain'   => 'https://ea.theeventscalendar.com/',
		'path'     => 'api/aggregator/',
		'licenses' => array(),
	];

	/**
	 * @var Tribe__Events__Aggregator__API__Requests
	 */
	protected $requests;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Events__Aggregator__Service
	 */
	public static function instance() {
		return tribe( 'events-aggregator.service' );
	}

	/**
	 * Constructor!
	 */
	public function __construct( Tribe__Events__Aggregator__API__Requests $requests ) {
		$this->register_messages();
		$this->requests = $requests;
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
		if ( is_multisite() ) {
			$network_key = get_network_option( null, 'pue_install_key_event_aggregator' );
			$api->key = ! empty( $api->key ) && $network_key !== $api->key ? $api->key : $network_key;
		}

		/**
		 * Creates a clean way to filter and redirect to another API domain/path
		 * @param  stdClass API object
		 */
		$api = (object) apply_filters( 'tribe_aggregator_api', $api );

		// Allows Eventbrite and others to skip ea license check
		if ( ! empty( $api->licenses ) ) {
			foreach ( $api->licenses as $plugin => $key ) {
				// If empty Key was passed we skip
				if ( empty( $key ) ) {
					continue;
				}

				$aggregator = tribe( 'events-aggregator.main' );
				$plugin_name = $aggregator->filter_pue_plugin_name( '', $plugin );

				$pue_notices = Tribe__Main::instance()->pue_notices();
				$has_notice = $pue_notices->has_notice( $plugin_name );

				// Means that we have a license and no notice - Valid Key
				if ( ! $has_notice ) {
					return $api;
				}
			}
		}

		// The user doesn't have a license key
		if ( empty( $api->key ) ) {
			return tribe_error( 'core:aggregator:invalid-service-key' );
		}

		$aggregator = tribe( 'events-aggregator.main' );
		$plugin_name = $aggregator->filter_pue_plugin_name( '', 'event-aggregator' );

		$pue_notices = Tribe__Main::instance()->pue_notices();
		$has_notice = $pue_notices->has_notice( $plugin_name );

		// The user doesn't have a valid license key
		if ( empty( $api->key ) || $has_notice ) {
			return tribe_error( 'core:aggregator:invalid-service-key' );
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

		/**
		 * Length of time to wait when initially connecting to Event Aggregator before abandoning the attempt.
		 * default is 60 seconds. We set this high so large files can be transfered on slow connections
		 *
		 * @var int $timeout_in_seconds
		 */
		$timeout_in_seconds = (int) apply_filters( 'tribe_aggregator_connection_timeout', 60 );

		$response = $http_response = $this->requests->get(
			esc_url_raw( $url ),
			array( 'timeout' => $timeout_in_seconds )
		);

		if ( is_wp_error( $response ) ) {
			if ( isset( $response->errors['http_request_failed'] ) ) {
				$response->errors['http_request_failed'][0] = __( 'Connection timed out while transferring the feed. If you are dealing with large feeds you may need to customize the tribe_aggregator_connection_timeout filter.', 'the-events-calendar' );
			}

			return $response;
		}

		if ( 403 == wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error(
				'core:aggregator:request-denied',
				esc_html__( 'Event Aggregator server has blocked your request. Please try your import again later or contact support to know why.', 'the-events-calendar' )
			);
		}

		// we know it is not a 404 or 403 at this point
		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error(
				'core:aggregator:bad-response',
				esc_html__( 'There may be an issue with the Event Aggregator server. Please try your import again later.', 'the-events-calendar' )
			);
		}

		if ( isset( $response->data ) && isset( $response->data->status ) && '404' === $response->data->status ) {
			return new WP_Error(
				'core:aggregator:daily-limit-reached',
				esc_html__( 'There may be an issue with the Event Aggregator server. Please try your import again later.', 'the-events-calendar' )
			);
		}

		// if the response is not an image, let's json decode the body
		if ( ! preg_match( '/image/', $response['headers']['content-type'] ) ) {
			$response = json_decode( wp_remote_retrieve_body( $response ) );
		}

		// It's possible that the json_decode() operation will have failed
		if ( null === $response ) {
			return new WP_Error(
				'core:aggregator:bad-json-response',
				esc_html__( 'The response from the Event Aggregator server was badly formed and could not be understood. Please try again.', 'the-events-calendar' ),
				$http_response
			);
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
			$args = [ 'body' => $data ];
		} else {
			$args = $data;
		}

		// if not timeout was set we pass it as 15 seconds
		if ( ! isset( $args['timeout'] ) ) {
			$args['timeout'] = 15;
		}

		$response = $this->requests->post( esc_url_raw( $url ), $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$json = json_decode( wp_remote_retrieve_body( $response ) );

		if ( empty( $json ) ) {
			return tribe_error( 'core:aggregator:invalid-json-response', [ 'response' => $response ], [ 'response' => $response ] );
		}

		return $json;
	}

	/**
	 * Fetch origins from service
	 *
	 * @param bool $return_error Whether response errors should be returned, if any.
	 *
	 * @return array The origins array of an array containing the origins first and an error second if `return_error` is set to `true`.
	 */
	public function get_origins( $return_error = false ) {
		$origins = $this->get_default_origins();

		$response = $this->get( 'origin' );
		$error = null;

		// If we have an WP_Error or a bad response we return only CSV and set some error data
		if ( is_wp_error( $response ) || empty( $response->status ) ) {
			$error = $response;

			return $return_error ? [ $origins, $error ] : $origins;
		}

		if ( $response && 'success' === $response->status ) {
			$origins = array_merge( $origins, (array) $response->data );
		}

		return $return_error
			? [ $origins, $error ]
			: $origins;
	}

	/**
	 * Get Eventbrite Arguments for EA
	 *
	 * @since 4.6.18
	 *
	 * @return mixed|void
	 */
	public function get_eventbrite_args( ) {
		$args = [
			'referral'   => urlencode( home_url() ),
			'url'        => urlencode( site_url() ),
			'secret_key' => tribe( 'events-aggregator.settings' )->get_eb_security_key()->security_key,
		];

		/**
		 *	Allow filtering for which params we are sending to EA for Token callback
		 *
		 * @since 4.6.18
		 *
		 * @param array $args Which arguments are sent to Token Callback
		 */
		return apply_filters( 'tribe_aggregator_eventbrite_token_callback_args', $args );
	}

	/**
	 * Fetch Eventbrite Extended Token from the Service
	 *
	 * @since 4.6.18
	 *
	 *  @return stdClass|WP_Error
	 */
	public function has_eventbrite_authorized() {

		$args = $this->get_eventbrite_args();

		$cached_response = get_transient( self::$auth_transient );

		if ( false !== $cached_response ) {
			return $cached_response;
		}

		$response = $this->get( 'eventbrite/validate', $args );

		// If we have an WP_Error we return only CSV
		if ( $response instanceof WP_Error ) {
			$response = tribe_error( 'core:aggregator:invalid-eventbrite-token', array(), array( 'response' => $response ) );
		} elseif (
			false === $cached_response
			&& isset( $response->status )
			&& 'error' !== $response->status
		) {
			// Check this each 15 minutes.
			set_transient( self::$auth_transient, $response, 900 );
		}

		return $response;
	}

	/**
	 * Disconnect Eventbrite Token on EA
	 *
	 * @since 4.6.18
	 *
	 * @return stdClass|WP_Error
	 */
	public function disconnect_eventbrite_token() {

		$args = $this->get_eventbrite_args();

		$response = $this->get( 'eventbrite/disconnect', $args );

		// If we have an WP_Error we return only CSV
		if ( is_wp_error( $response ) ) {
			return tribe_error( 'core:aggregator:invalid-eventbrite-token', array(), [ 'response' => $response ] );
		} else {
			delete_transient( self::$auth_transient );
		}

		return $response;
	}

	/**
	 * Fetch import data from service
	 *
	 * @param string   $import_id   ID of the Import Record
	 *
	 * @return stdClass|WP_Error
	 */
	public function get_import( $import_id, $data = array() ) {
		$response = $this->get( 'import/' . $import_id, $data );

		return $response;
	}

	/**
	 * Creates an import
	 *
	 * Note: This method exists because WordPress by default doesn't allow multipart/form-data
	 *       with boundaries to happen
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

		$args = $this->apply_import_limit( $args );

		/**
		 * Allows filtering to add a PUE key to be passed to the EA service
		 *
		 * @since 4.6.18
		 *
		 * @param  bool|string $pue_key PUE key
		 * @param  array       $args    Arguments to queue the import
		 * @param  self        $record  Which record we are dealing with
		 */
		$licenses = apply_filters( 'tribe_aggregator_service_post_pue_licenses', array(), $args, $this );

		// If we have a key we add that to the Arguments
		if ( ! empty( $licenses ) ) {
			$args['licenses'] = $licenses;
		}

		/**
		 * Allows filtering to add other arguments to be passed to the EA service.
		 *
		 * @since 4.6.24
		 *
		 * @param array $args   Arguments to queue the import.
		 * @param self  $record Which record we are dealing with.
		 */
		$args = apply_filters( 'tribe_aggregator_service_post_import_args', $args, $this );

		$request_args = [
			'body' => $args,
		];

		if ( isset( $args['file'] ) ) {
			$boundary = wp_generate_password( 24 );
			$headers = [
				'content-type' => 'multipart/form-data; boundary=' . $boundary,
			];

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
	 * @param  Tribe__Events__Aggregator__Record__Abstract $record    Record Object
	 *
	 * @return stdClass|WP_Error
	 */
	public function get_image( $image_id, $record ) {
		/**
		 * Allow filtering of the Image data Request Args
		 *
		 * @since 4.6.18
		 *
		 * @param  array  $data      Which Arguments
		 * @param  strng  $image_id  Image ID
		 */
		$data = apply_filters( 'tribe_aggregator_get_image_data_args', array(), $record, $image_id );

		$response = $this->get( 'image/' . $image_id, $data );

		return $response;
	}

	/**
	 * Returns a service message based on key
	 *
	 * @param string $key     Service Message index
	 * @param array  $args    An array of arguments that will be fed to a `sprintf` like function to replace
	 *                        placeholders.
	 * @param string $default A default message that should be returned should the message code not be found; defaults
	 *                        to the unknown message.
	 *
	 * @return string
	 */
	public function get_service_message( $key, $args = array(), $default = null ) {
		if ( empty( $this->service_messages[ $key ] ) ) {
			// Get error message if this is a registered Tribe_Error key.
			$error = tribe_error( $key );

			if ( is_wp_error( $error ) && 'unknown' !== $error->get_error_code() ) {
				return $error->get_error_message();
			}

			// Use default message if set.
			if ( null !== $default ) {
				return $default;
			}

			return $this->get_unknow_message();
		}

		return vsprintf( $this->service_messages[ $key ], $args );
	}

	/**
	 * Returns usage limits
	 *
	 * @param string $type Type of limits to return
	 * @param boolean $ignore_cache Whether or not cache should be ignored when fetching the value
	 *
	 * @return array|int Either an array detailing the limit information (used, remaining) or `0` if
	 *                   the limit for the specified type could not be determined.
	 */
	public function get_limit( $type, $ignore_cache = false ) {
		if ( false === $this->origins || $ignore_cache ) {
			$this->origins = ( (object) $this->get_origins() );
		}

		if ( ! isset( $this->origins->limit->$type ) ) {
			return 0;
		}

		return $this->origins->limit->$type;
	}

	/**
	 * Returns limit usage
	 *
	 * @param string $type Type of usage to return
	 * @param boolean $ignore_cache Whether or not cache should be ignored when fetching the value
	 *
	 * @return array
	 */
	public function get_usage( $type, $ignore_cache = false ) {
		static $origins;

		if ( ! $origins || $ignore_cache ) {
			$origins = (object) $this->get_origins();
		}

		if ( ! isset( $origins->usage->$type ) ) {
			return array(
				'used' => 0,
				'remaining' => 0,
			);
		}

		return $origins->usage->$type;
	}

	/**
	 * Returns whether or not the limit has been exceeded
	 *
	 * @param boolean $ignore_cache Whether or not cache should be ignored when fetching the value
	 *
	 * @return boolean
	 */
	public function is_over_limit( $ignore_cache = false ) {
		$limits = $this->get_usage( 'import', $ignore_cache );

		return isset( $limits->remaining ) && 0 >= $limits->remaining;
	}

	/**
	 * Returns the currently used imports for the day
	 *
	 * @param boolean $ignore_cache Whether or not cache should be ignored when fetching the value
	 *
	 * @return int
	 */
	public function get_limit_usage( $ignore_cache = false ) {
		$limits = (object) $this->get_usage( 'import', $ignore_cache );

		if ( isset( $limits->used ) ) {
			return $limits->used;
		}

		return 0;
	}

	/**
	 * Returns the remaining imports for the day
	 *
	 * @param boolean $ignore_cache Whether or not cache should be ignored when fetching the value
	 *
	 * @return int
	 */
	public function get_limit_remaining( $ignore_cache = false ) {
		$limits = (object) $this->get_usage( 'import', $ignore_cache );

		if ( isset( $limits->remaining ) ) {
			return $limits->remaining;
		}

		return 0;
	}

	/**
	 * Registers the message map used to translate message slugs returned from EA service into localized strings.
	 *
	 * These messages are delivered by the EA service and don't need to be registered. They just need to exist
	 * here so that they can be translated.
	 */
	protected function register_messages() {
		$ical_uid_specification_link = sprintf(
			'<a target="_blank" href="https://tools.ietf.org/html/rfc5545#section-3.8.4.7">%s</a>',
			esc_html__( 'the UID part of the iCalendar Specification', 'the-events-calendar' )
		);

		$facebook_restriction_link = sprintf(
			'<a href="https://theeventscalendar.com/knowledgebase/import-errors/" target="_blank">%s</a>',
			esc_html__( 'read more about Facebook restrictions in our knowledgebase', 'the-events-calendar')
		);

		$meetup_api_changes_link = sprintf(
			'<a href="https://m.tri.be/1afb">%s</a>',
			esc_html__( 'https://m.tri.be/1afb', 'the-events-calendar' )
		);

		$this->service_messages = [
			/* Error */
			'error:create-import-failed'              => __('Sorry, but something went wrong. Please try again.', 'the-events-calendar'),
			'error:create-import-invalid-params'      => __('Events could not be imported. The import parameters were invalid.', 'the-events-calendar'),
			'error:eb-permissions'                    => __('Events cannot be imported because Eventbrite has returned an error. This could mean that the event ID does not exist, the event or source is marked as Private, or the event or source has been otherwise restricted by Eventbrite. You can <a href="https://theeventscalendar.com/knowledgebase/import-errors/" target="_blank">read more about Eventbrite restrictions in our knowledgebase</a>.', 'the-events-calendar'),
			'error:eb-no-results'                     => __('No upcoming Eventbrite events found.', 'the-events-calendar'),
			'error:fetch-404'                         => __('The URL provided could not be reached.', 'the-events-calendar'),
			'error:fetch-failed'                      => __('The URL provided failed to load.', 'the-events-calendar'),
			'error:get-image'                         => __('The image associated with your event could not be imported.', 'the-events-calendar'),
			'error:get-image-bad-association'         => __('The image associated with your event is not accessible with your API key.', 'the-events-calendar'),
			'error:import-failed'                     => __('The import failed for an unknown reason. Please try again. If the problem persists, please contact support.', 'the-events-calendar'),
			'error:invalid-ical-url'                  => __('Events could not be imported. The URL provided did not have events in the proper format.', 'the-events-calendar'),
			'error:invalid-ics-file'                  => __('The file provided could not be opened. Please confirm that it is a properly formatted .ics file.', 'the-events-calendar'),
			'error:meetup-api-key'                    => __('Your Meetup API key is invalid.', 'the-events-calendar'),
			'error:meetup-api-quota'                  => __('Event Aggregator cannot reach Meetup.com because you exceeded the request limit for your Meetup API key.', 'the-events-calendar'),
			'error:usage-limit-exceeded'              => __('The daily limit of %d import requests to the Event Aggregator service has been reached. Please try again later.', 'the-events-calendar'),
			/* Fetching */
			'fetching'                                => __('The import is in progress.', 'the-events-calendar'),
			/* Queued */
			'queued'                                  => __('The import will be starting soon.', 'the-events-calendar'),
			/* Success */
			'success'                                 => __('Success', 'the-events-calendar'),
			'success:create-import'                   => __('Import created', 'the-events-calendar'),
			'success:eventbrite-get-token'            => __('Successfully fetched Eventbrite Token', 'the-events-calendar'),
			'success:get-origin'                      => __('Successfully loaded import origins', 'the-events-calendar'),
			'success:import-complete'                 => __('Import is complete', 'the-events-calendar'),
			'success:queued'                          => __('Import queued', 'the-events-calendar'),
			'error:invalid-other-url'                 => __('Events could not be imported. The URL provided could not be reached.', 'the-events-calendar'),
			'error:no-results'                        => __('The requested source does not have any upcoming and published events matching the search criteria.', 'the-events-calendar'),
			'error:ical-missing-uids-schedule'        => sprintf(
				_x(
					'Some events at the requested source are missing the UID attribute required by the iCalendar Specification. Creating a scheduled import would generate duplicate events on each import. Instead, please use a One-Time import or contact the source provider to fix the UID issue; linking them to %s may help them more quickly resolve their feed\'s UID issue.',
					'The placeholder is for the localized version of the iCal UID specification link',
					'the-events-calendar'
				),
				$ical_uid_specification_link
			),
			/* Warning */
			'warning:ical-missing-uids-manual'        => sprintf(
				_x(
					'Some events at the requested source are missing the UID attribute required by the iCalendar Specification. One-Time and ICS File imports are allowed but successive imports will create duplicated events on your site. Please contact the source provider to fix the UID issue; linking them to %s may help them more quickly resolve their feed\'s UID issue.',
					'The placeholder is for the localized version of the iCal UID specification link',
					'the-events-calendar'),
				$ical_uid_specification_link
			),
			'success:facebook-get-token'              => __('Successfully fetched Facebook Token', 'the-events-calendar'),
			'success:eb-token-valid'                  => __('Successfully connected to Eventbrite', 'the-events-calendar'),
			'success:eb-token-disconnected'           => __('Successfully disconnected Eventbrite', 'the-events-calendar'),
			'success:eb-webhook-success'              => __('Successfully marked event for import from Eventbrite', 'the-events-calendar'),
			'success:eb-event-synced'                 => __('Successfully synced event to Eventbrite', 'the-events-calendar'),
			'error:import-id-not-queued'              => __('The import being fetched is not queued up for importing. Please try the import again.', 'the-events-calendar'),
			'error:fb-permissions'                    => sprintf(
				_x(
					'Events cannot be imported because Facebook has returned an error. This could mean that the event ID does not exist, the event or source is marked as Private, or the event or source has been otherwise restricted by Facebook. You can %1$s.',
					'Placeholder used for the facebook restriction link',
					'the-events-calendar'
				),
				$facebook_restriction_link
			),
			'error:fb-error'                          => __('Events cannot be imported because we received an error from Facebook: ', 'the-events-calendar'),
			'error:eb-error'                          => __('Events cannot be imported because we received an error from Eventbrite: ', 'the-events-calendar'),
			'error:eb-sync-error'                     => __('Event cannot be synced to Eventbrite because we received an error from Eventbrite.', 'the-events-calendar'),
			'error:eb-token-not-valid'                => __('Eventbrite token is not valid.', 'the-events-calendar'),
			'error:eb-parsed-object-empty'            => __('Eventbrite parsed object is empty.', 'the-events-calendar'),
			'error:eb-parsed-object-type-empty'       => __('Eventbrite parsed object type is empty.', 'the-events-calendar'),
			'error:eb-parsed-object-id-empty'         => __('Eventbrite parsed object ID is empty.', 'the-events-calendar'),
			'error:eb-object-empty'                   => __('Eventbrite parsed object is empty.', 'the-events-calendar'),
			'error:eb-event-not-found'                => __('Eventbrite event not found.', 'the-events-calendar'),
			'error:eb-organizer-not-found'            => __('Eventbrite organizer not found.', 'the-events-calendar'),
			'error:eb-venue-not-found'                => __('Eventbrite venue not found.', 'the-events-calendar'),
			'error:eb-user-not-found'                 => __('Eventbrite user not found.', 'the-events-calendar'),
			'error:eb-sync-data-invalid'              => __('Eventbrite sync data invalid.', 'the-events-calendar'),
			'error:eb-token-not-found'                => __('You do not have an active connection to Eventbrite through your account and Event Aggregator.', 'the-events-calendar'),
			'error:eb-webhook-not-registered'         => __('Webhook not registered properly.', 'the-events-calendar'),
			'error:eb-action-not-supported'           => __('This webhook action is not currently supported.', 'the-events-calendar'),
			'error:eb-event-not-owned'                => __('Event not owned, you cannot edit it.', 'the-events-calendar'),
			'warning:meetup-api-key-deprecated-plain' => sprintf(
				_x(
					'Meetup is no longer supporting API keys, and will restrict access using your existing key starting from August 2019. As an alternative, you should use OAuth2 and update The Events Calendar to the latest version. Learn more at %1$s',
					'Placeholder used for the meetup API changes',
					'the-events-calendar'
				),
				$meetup_api_changes_link
			),
			'error:meetup-api-key-deprecated-plain'   => sprintf(
				_x(
					'Meetup is no longer supporting API keys, and has restricted access using your existing key starting from August 2019. As an alternative, you must use OAuth2 and update The Events Calendar to the latest version. Learn more at %1$s.',
					'Placeholder used for the meetup API changes link when the KEY is plain',
					'the-events-calendar'
				),
				$meetup_api_changes_link
			),
			'error:meetup-token-not-found'            => __('You do not have an active connection to Meetup through your account and Event Aggregator.', 'the-events-calendar'),
		];

		/**
		 * Filters the service messages map to allow addition and removal of messages.
		 *
		 * @param array $service_messages An associative array of service messages in the `[ <slug> => <localized text> ]` format.
		 */
		$this->service_messages = apply_filters( 'tribe_aggregator_service_messages', $this->service_messages );
	}

	/**
	 * Returns the message used for unknown message codes.
	 *
	 * @return string
	 */
	public function get_unknow_message() {
		return __( 'Unknown service message', 'the-events-calendar' );
	}

	/**
	 * Confirms an import with Event Aggregator Service.
	 *
	 * @param array $args
	 *
	 * @return bool Whether the import was confirmed or not.
	 */
	public function confirm_import( $args ) {
		$keys = [ 'origin', 'source', 'type' ];
		$keys = array_combine( $keys, $keys );
		$confirmation_args = array_intersect_key( $args, $keys );
		$confirmation_args = array_merge( $confirmation_args, [
				'eventbrite_token' => '1',
				'meetup_api_key'   => '1',
			]
		);

		// Set site for origin(s) that need it for new token handling.
		if ( 'eventbrite' === $confirmation_args['origin'] ) {
			$confirmation_args['site'] = site_url();
		}

		$response = $this->post_import( $confirmation_args );

		$confirmed = ! empty( $response->status ) && 0 !== strpos( $response->status, 'error' );

		return $confirmed;
	}

	/**
	 * Returns the default origins array.
	 *
	 * @since 4.5.11
	 *
	 * @return array
	 */
	protected function get_default_origins() {
		$origins = array(
			'origin' => array(
				(object) array(
					'id'   => 'csv',
					'name' => __( 'CSV File', 'the-events-calendar' ),
				),
			),
		);

		return $origins;
	}

	/**
	 * Applies a limit to the import request.
	 *
	 * @since 4.5.13
	 *
	 * @param array $args An array of request arguments.
	 *
	 * @return mixed
	 */
	protected function apply_import_limit( $args ) {
		if ( isset( $args['limit_type'], $args['limit'] ) ) {
			return $args;
		}

		$is_other_url = isset( $args['origin'] ) && $args['origin'] === 'url';
		if ( $is_other_url ) {
			$limit_type = 'range';
		} else {
			$limit_type = tribe_get_option( 'tribe_aggregator_default_import_limit_type', false );
		}

		/** @var \Tribe__Events__Aggregator__Settings $settings */
		$settings = tribe( 'events-aggregator.settings' );

		$limit_args = array();
		switch ( $limit_type ) {
			case 'no_limit':
				break;
			case 'count':
				$limit_args['limit_type'] = 'count';
				$default                  = $settings->get_import_limit_count_default();
				$limit_args['limit']      = tribe_get_option( 'tribe_aggregator_default_import_limit_number', $default );
				break;
			default:
			case 'range':
				$limit_args['limit_type'] = 'range';
				$default                  = $settings->get_import_range_default();
				$limit_args['limit']      = $is_other_url
					? tribe_get_option( 'tribe_aggregator_default_url_import_range', $default )
					: tribe_get_option( 'tribe_aggregator_default_import_limit_range', $default );
				break;
		}

		/**
		 * Filters the limit arguments before applying them to the import request arguments.
		 *
		 * @since 4.5.13
		 *
		 * @param array                              $limit_args The limit arguments.
		 * @param array                              $args       The import request arguments.
		 * @param Tribe__Events__Aggregator__Service $service    The service instance handling the import request..
		 */
		$limit_args = apply_filters( 'tribe_aggregator_limit_args', $limit_args, $args, $this );

		if ( is_array( $limit_args ) ) {
			$args = array_merge( $args, $limit_args );
		}

		return $args;
	}

	/**
	 * Get Meetup Arguments for EA
	 *
	 * @since 4.9.6
	 *
	 * @return mixed|void
	 */
	public function get_meetup_args() {
		$args = [
			'referral'   => urlencode( home_url() ),
			'url'        => urlencode( site_url() ),
			'secret_key' => tribe( 'events-aggregator.settings' )->get_meetup_security_key()->security_key,
		];

		/**
		 *	Allow filtering for which params we are sending to EA for Token callback
		 *
		 * @since 4.9.6
		 *
		 * @param array $args Which arguments are sent to Token Callback
		 */
		return apply_filters( 'tribe_aggregator_meetup_token_callback_args', $args );
	}

	/**
	 * Fetch Meetup Extended Token from the Service.
	 *
	 * @since 4.9.6
	 *
	 * @param bool $request_security_key Whether to explicitly request the Meetup security key in the response or not.
	 *
	 * @return stdClass|WP_Error Either the Event Aggregator Service response or a `WP_Error` on failure.
	 */
	public function has_meetup_authorized( $request_security_key = false ) {

		$args = $this->get_meetup_args();

		if ( $request_security_key ) {
			$args['secret_key'] = 'request';
		}

		$cached_response = get_transient( self::$auth_transient_meetup );

		if ( false !== $cached_response ) {
			return $cached_response;
		}

		$response = $this->get( 'meetup/validate', $args );

		// If we have an WP_Error we return only CSV.
		if ( $response instanceof WP_Error ) {
			$response = tribe_error( 'core:aggregator:invalid-meetup-token', array(), [ 'response' => $response ] );
		} elseif (
			false === $cached_response
			&& isset( $response->status )
			&& 'error' !== $response->status
		) {
			// Check this each 15 minutes.
			set_transient( self::$auth_transient_meetup, $response, 900 );
		}

		return $response;
	}

	/**
	 * Disconnect Meetup Token on EA
	 *
	 * @since 4.9.6
	 *
	 * @return stdClass|WP_Error
	 */
	public function disconnect_meetup_token() {

		$args = $this->get_meetup_args();

		$response = $this->get( 'meetup/disconnect', $args );

		// If we have an WP_Error we return only CSV
		if ( is_wp_error( $response ) ) {
			return tribe_error( 'core:aggregator:invalid-meetup-token', array(), [ 'response' => $response ] );
		} else {
			delete_transient( self::$auth_transient_meetup );
		}

		return $response;
	}

	/**
	 * Fetch Facebook Extended Token from the Service
	 *
	 * @deprecated 4.6.23
	 *
	 * @return array
	 */
	public function get_facebook_token() {
		_deprecated_function( __FUNCTION__, '4.6.23', 'Importing from Facebook is no longer supported in Event Aggregator.' );

		$args = array(
			'referral' => urlencode( home_url() ),
		);
		$response = $this->get( 'facebook/token', $args );

		// If we have an WP_Error we return only CSV
		if ( is_wp_error( $response ) ) {
			return tribe_error( 'core:aggregator:invalid-facebook-token', array(), array( 'response' => $response ) );
		}

		return $response;
	}
}
