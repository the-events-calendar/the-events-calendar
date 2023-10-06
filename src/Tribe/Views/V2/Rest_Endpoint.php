<?php
/**
 * The AJAX and REST API request handler.
 *
 * @since 4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2;

use WP_REST_Request as Request;
use WP_REST_Server as Server;
use WP_User;

/**
 * Class Rest_Endpoint
 *
 * @since   4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
class Rest_Endpoint {

	/**
	 * The action for this nonce.
	 *
	 * @since 6.1.4
	 *
	 * @var string
	 */
	const NONCE_ACTION = '_view_rest';

	/**
	 * The field name for the primary nonce.
	 *
	 * @since 6.1.4
	 *
	 * @var string
	 */
	const PRIMARY_NONCE_KEY = '_tec_view_rest_nonce_primary';

	/**
	 * The field name for the secondary nonce.
	 *
	 * @since 6.1.4
	 *
	 * @var string
	 */
	const SECONDARY_NONCE_KEY = '_tec_view_rest_nonce_secondary';

	/**
	 * Rest Endpoint namespace
	 *
	 * @since  4.9.7
	 *
	 * @var  string
	 */
	const ROOT_NAMESPACE = 'tribe/views/v2';

	/**
	 * AJAX action for the fallback when REST is inactive.
	 *
	 * @since  4.9.7
	 *
	 * @var  string
	 */
	public static $ajax_action = 'tribe_events_views_v2_fallback';

	/**
	 * A flag, set on a per-request basis, to indicate if the `rest_authentication_errors` filter fired or not.
	 *
	 * @since 4.9.12
	 *
	 * @var bool
	 */
	protected static $did_rest_authentication_errors;

	/**
	 * When in a REST request, store the authenticated user ID for use later.
	 *
	 * @since 6.2.3
	 *
	 * @var null|int The authenticated user ID.
	 */
	protected static $user_id;

	/**
	 * Due to our custom nonce usage on the REST auth, the _wpnonce is missing and WP core
	 * will fail to retain the authenticated user and removes it.
	 *
	 * This stores the user (if authenticated) for use when we check that our custom nonce(s) are valid.
	 *
	 * @since 6.2.3
	 *
	 * @param bool $send_nocache_headers
	 *
	 * @return bool
	 * @see   rest_cookie_check_errors()
	 *
	 */
	public static function preserve_user_for_custom_nonces( $send_nocache_headers ) {
		if ( ! is_user_logged_in() ) {
			return $send_nocache_headers;
		}

		$user = wp_get_current_user();
		if ( ! $user instanceof WP_User || ! $user->ID ) {
			return $send_nocache_headers;
		}

		// Save user for our nonce checks.
		self::$user_id = (int) $user->ID;

		return $send_nocache_headers;
	}

	/**
	 * Returns the user ID, if we successfully stored it during a REST request.
	 *
	 * @since 6.2.3
	 *
	 * @return int|null The user ID or null if none stored.
	 */
	public static function get_stored_user_id(): ?int {
		return self::$user_id;
	}

	/**
	 * Ensures the nonce(s) are valid.
	 *
	 * @since 6.2.3
	 *
	 * @param Request $request
	 *
	 * @return bool
	 */
	public function is_valid_request( Request $request ): bool {
		/*
		* Since WordPress 4.7 the REST API cannot be disabled completely.
		* The "disabling" happens by returning falsy or error values from the `rest_authentication_errors`
		* filter.
		* If false or error, we follow through and and do not authorize the callback.
		* If null, the site is using alternate authentication such as SAML
		*/
		$auth = apply_filters( 'rest_authentication_errors', null );

		if ( self::$user_id && ! is_user_logged_in() ) {
			/**
			 * This user was set but lost, because we use custom nonces which can not be handled by Wordpress auth.
			 *
			 */
			wp_set_current_user( self::$user_id );
			// We have a valid user, we should not fail on the cookie check anymore.
			$user_valid = static function ( $valid ) {
				if ( is_null( $valid ) ) {
					return true;
				}

				return $valid;
			};
			if ( ! has_filter( 'rest_authentication_errors', $user_valid ) ) {
				add_filter( 'rest_authentication_errors', $user_valid );
			}
		}

		// Did either our unauth or authed nonce pass? If neither, something is fishy.
		$nonce_check = wp_verify_nonce( $request->get_param( static::PRIMARY_NONCE_KEY ), static::NONCE_ACTION )
		               || wp_verify_nonce( $request->get_param( static::SECONDARY_NONCE_KEY ), static::NONCE_ACTION );

		return ( $auth || is_null( $auth ) )
		       && ! is_wp_error( $auth )
		       && $nonce_check;
	}

	/**
	 * Get the nonces being passed to the V2 views used for our REST requests.
	 *
	 * @since 6.1.4
	 *
	 * @return array<string,string> The field => nonce array.
	 */
	public static function get_rest_nonces(): array {
		$generated_nonces = [];

		/*
		 * Some plugins, like WooCommerce, will modify the UID of logged out users; avoid that filtering here.
		 *
		 * @see TEC-3579
		 */
		$generated_nonces[ static::PRIMARY_NONCE_KEY ] = tribe_without_filters(
			[ 'nonce_user_logged_out' ],
			function () {
				// Our current users' nonce.
				return wp_create_nonce( static::NONCE_ACTION );
			}
		);
		$generated_nonces[ static::SECONDARY_NONCE_KEY ] = tribe_without_filters(
			[ 'nonce_user_logged_out' ],
			function () {
				// In case nonce A is a logged in user and cached and served for visitors,
				// provide a valid fallback for unauthenticated visitors in B.
				$uid = get_current_user_id();
				// If not logged in, we already created this nonce in A.
				if ( ! $uid ) {
					return '';
				}

				// We are logged in, now generate an unauthenticated user nonce.
				wp_set_current_user( 0 );
				$nonce = wp_create_nonce( static::NONCE_ACTION );
				wp_set_current_user( $uid );

				return $nonce;
			}
		);

		/**
		 * Filter the list of nonces being used on REST requests for V2 views.
		 *
		 * @since 6.1.4
		 *
		 * @param array<string,string> The field => nonce array.
		 */
		return (array) apply_filters( 'tec_events_views_v2_get_rest_nonces', $generated_nonces );
	}

	/**
	 * Returns the URL View will use to fetch their content.
	 *
	 * Depending on whether the REST API is enabled or not on the site, the URL might be a REST API one or an
	 * admin AJAX one.
	 *
	 * @since   4.9.2
	 * @since   5.2.1 Add filtering to the URL.
	 *
	 * @return  string The URL of the backend endpoint Views will use to fetch their content.
	 */
	public function get_url() {
		$rest_available = $this->is_available();

		if ( ! $rest_available ) {
			$url = admin_url( 'admin-ajax.php' );
			$url = add_query_arg( [ 'action' => static::$ajax_action ], $url );
		} else {
			$url = get_rest_url( null, static::ROOT_NAMESPACE . '/html' );
		}

		/**
		 * Filters the URL Views should use to fetch their contents from the backend.
		 *
		 * @since 5.2.1
		 *
		 * @param string $url            The View endpoint URL, either a REST API URL or a admin-ajax.php fallback URL if REST API
		 *                               is not available.
		 * @param bool   $rest_available Whether the REST API endpoint URL is available on the current site or not.
		 */
		$url = apply_filters( 'tribe_events_views_v2_endpoint_url', $url, $rest_available );

		return $url;
	}

	/**
	 * Get the arguments used to setup the HTML route for Views V2 in the REST API.
	 *
	 * @link  https://developer.wordpress.org/rest-api/requests/
	 *
	 * @since  4.9.7
	 *
	 * @return array $arguments Request arguments following the WP_REST API Standards [ name => options, ... ]
	 */
	public function get_request_arguments() {
		$arguments = [
			'url' => [
				'required'          => true,
				'validate_callback' => static function ( $url ) {
					return is_string( $url );
				},
				'sanitize_callback' => static function ( $url ) {
					return filter_var( $url, FILTER_SANITIZE_URL );
				},
			],
			'view' => [
				'required'          => false,
				'validate_callback' => static function ( $view ) {
					return is_string( $view );
				},
				'sanitize_callback' => static function ( $view ) {
					return tec_sanitize_string( $view );
				},
			],
			static::PRIMARY_NONCE_KEY => [
				'required'          => false,
				'validate_callback' => static function ( $nonce ) {
					return is_string( $nonce );
				},
				'sanitize_callback' => static function ( $nonce ) {
					return tec_sanitize_string( $nonce );
				},
			],
			static::SECONDARY_NONCE_KEY => [
				'required'          => false,
				'validate_callback' => static function ( $nonce ) {
					return is_string( $nonce );
				},
				'sanitize_callback' => static function ( $nonce ) {
					return tec_sanitize_string( $nonce );
				},
			],
			'view_data' => [
				'required'          => false,
				'validate_callback' => static function ( $view_data ) {
					return is_array( $view_data );
				},
				'sanitize_callback' => static function ( $view_data ) {
					return is_array( $view_data ) ? $view_data : [];
				},
			],
		];

		// Arguments specific to AJAX requests; we add them to all requests as long as the argument is not required.
		$arguments['action'] = [
			'required'          => false,
			'validate_callback' => static function ( $action ) {
				return is_string( $action );
			},
			'sanitize_callback' => static function ( $action ) {
				return tec_sanitize_string( $action );
			},
		];

		/**
		 * Filter the arguments for the HTML REST API request.
		 * It follows the WP_REST API standards.
		 *
		 * @link  https://developer.wordpress.org/rest-api/requests/
		 *
		 * @since  4.9.7
		 *
		 * @param array $arguments Request arguments following the WP_REST API Standards [ name => options, ... ]
		 */
		return apply_filters( 'tribe_events_views_v2_request_arguments', $arguments );
	}

	/**
	 * Register the endpoint if available.
	 *
	 * @since  4.9.7
	 * @since 5.2.1 Add support for the POST method.
	 *
	 * @return boolean If we registered the endpoint.
	 */
	public function register() {
		return register_rest_route( static::ROOT_NAMESPACE, '/html', [
			// Support both GET and POST HTTP methods: we originally used GET.
			'methods'             => [ Server::READABLE, Server::CREATABLE ],
			// @todo [BTRIA-600]: Make sure we do proper handling of caches longer then 12h.
			'permission_callback' => [ $this, 'is_valid_request' ],
			'callback'            => static function ( Request $request ) {
				if ( ! headers_sent() ) {
					header( 'Content-Type: text/html; charset=' . esc_attr( get_bloginfo( 'charset' ) ) );
				}
				View::make_for_rest( $request )->send_html();
			},
			'args'                => $this->get_request_arguments(),
		] );
	}

	/**
	 * When REST is not available add AJAX fallback into the correct action.
	 *
	 * @since  4.9.7
	 * @since  4.9.12 Always enable this.
	 *
	 * @return void
	 */
	public function enable_ajax_fallback() {
		$action = static::$ajax_action;
		add_action( "wp_ajax_{$action}", [ $this, 'handle_ajax_request' ] );
		add_action( "wp_ajax_nopriv_{$action}", [ $this, 'handle_ajax_request' ] );
	}

	/**
	 * Get the mocked rest request used for the AJAX fallback used to make sure users without
	 * the REST API still have the Views V2 working.
	 *
	 * @since  4.9.7
	 * @since 5.2.1 Changed the mock request HTTP method to POST (was GET).
	 *
	 * @param  array $params Associative array with the params that will be used on this mocked request
	 *
	 * @return Request The mocked request.
	 */
	public function get_mocked_rest_request( array $params ) {
		$request   = new Request( 'POST', static::ROOT_NAMESPACE . '/html' );
		$arguments = $this->get_request_arguments();

		foreach ( $params as $key => $value ) {
			// Quick way to prevent un-wanted params.
			if ( ! isset( $arguments[ $key ] ) ) {
				continue;
			}

			$request->set_param( $key, $value );
		}

		$has_valid_params = $request->has_valid_params();
		if ( ! $has_valid_params || is_wp_error( $has_valid_params ) ) {
			return $has_valid_params;
		}

		$sanitize_params = $request->sanitize_params();
		if ( ! $sanitize_params || is_wp_error( $sanitize_params ) ) {
			return $sanitize_params;
		}

		return $request;
	}

	/**
	 * AJAX fallback for when REST endpoint is disabled. We try to mock a WP_REST_Request
	 * and use the same method behind the scenes to make sure we have consistency.
	 *
	 * @since  4.9.7
	 * @since 5.2.1 Look up the POST data before the GET one to process the request.
	 */
	public function handle_ajax_request() {
		// Use the POST method data, if set; else fallback on the GET data.
		$source  = isset( $_POST ) ? $_POST : $_GET;
		$request = $this->get_mocked_rest_request( $source );
		if ( is_wp_error( $request ) ) {
			/**
			 * @todo  Once we have a error handling on the new view we need to throw it here.
			 */
			return wp_send_json_error( $request );
		}

		View::make_for_rest( $request )->send_html();
	}

	/**
	 * Check if the REST endpoint is available.
	 *
	 * @since  4.9.7
	 *
	 * @return boolean If the REST API endpoint is available.
	 */
	public function is_available() {
		$is_available = tribe( 'tec.rest-v1.system' )->tec_rest_api_is_enabled();

		/*
		 * We should run this part of the check only after `rest_authentication_errors` filter ran.
		 * If we call `WP_REST_Server::check_authentication` before the user will be set to `0` and any following
		 * auth check will be altered.
		 */
		if ( static::$did_rest_authentication_errors ) {
			/**
			 * There is no good way to check if rest API is really disabled since `rest_enabled` is deprecated since 4.7
			 *
			 * @link https://core.trac.wordpress.org/browser/trunk/src/wp-includes/rest-api/class-wp-rest-server.php#L262
			 */
			global $wp_rest_server;
			if (
				! empty( $wp_rest_server )
				&& $wp_rest_server instanceof Server
				&& ! $wp_rest_server->check_authentication()
			) {
				$is_available = false;
			}
		}

		/**
		 * Allows third-party deactivation of the REST Endpoint for just the view V2.
		 *
		 * @since  4.9.7
		 *
		 * @param boolean $is_available If the REST API endpoint is available.
		 */
		$is_available = apply_filters( 'tribe_events_views_v2_rest_endpoint_available', $is_available );

		return $is_available;
	}

	/**
	 * Tracks if the `rest_authentication_errors` filter fired or not, using this filter as an action.
	 *
	 * This is a work-around fro the lack of the `did_filter` function.
	 *
	 * @since 4.9.12
	 *
	 * @param mixed $errors The authentication error, if any, unused by the method.
	 *
	 * @return mixed The authentication error.
	 */
	public static function did_rest_authentication_errors( $errors = null ) {
		remove_filter( 'rest_authentication_errors', [ static::class, 'did_rest_authentication_errors' ] );

		static::$did_rest_authentication_errors = true;

		return $errors;
	}

	/**
	 * Returns the filtered HTTP method Views should use to fetch their content from the backend endpoint.
	 *
	 * @since 5.2.1
	 *
	 * @return string The filtered HTTP method Views should use to fetch their content from the back-end endpoint.
	 */
	public function get_method() {
		/**
		 * Filters the HTTP method Views should use to fetch their contents calling the back-end endpoint.
		 *
		 * @since 5.2.1
		 *
		 * @param string $method The HTTP method Views will use to fetch their content. Either `POST` (default) or
		 *                       `GET`. Invalid values will be set to the default `POST`.
		 */
		$method = strtoupper( (string) apply_filters( 'tribe_events_views_v2_endpoint_method', 'POST' ) );

		$method = in_array( $method, [ 'POST', 'GET' ], true ) ? $method : 'POST';

		return $method;
	}
}
