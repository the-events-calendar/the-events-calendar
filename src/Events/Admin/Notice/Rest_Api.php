<?php
/**
 * Shows an admin notice when our REST API endpoints are not available.
 *
 * @since
 *
 * @package TEC\Events\Admin\Notice
 */

namespace TEC\Events\Admin\Notice;

use TEC\Events\Traits\Development_Mode;
use Tribe\Events\Views\V2\Rest_Endpoint as V2;
use Tribe__Events__REST__V1__Main as V1;
use WP_Error;

/**
 * Class Rest_Api
 *
 * Shows an admin notice when our REST API endpoints are not available.
 *
 * @since 6.5.0
 */
class Rest_Api {

	use Development_Mode;

	/**
	 * Notice Slug on the user options
	 *
	 * @since 6.5.0
	 *
	 * @var string
	 */
	private $slug = 'events-rest-api-notice';

	/**
	 * Blocked endpoint.
	 *
	 * @since 6.5.0
	 *
	 * @var string
	 */
	private $blocked_endpoint = '';

	/**
	 * Constructor.
	 *
	 * @since 6.5.0
	 *
	 * @return void
	 */
	public function hook(): void {
		tribe_notice(
			$this->slug,
			[ $this, 'notice' ],
			[
				'type'               => 'warning',
				'dismiss'            => 1,
				'wrap'               => 'p',
				'recurring'          => true,
				'recurring_interval' => 'P7D',
			],
			[ $this, 'should_display' ]
		);
	}

	/**
	 * Checks if we are in a TEC page or in the main Dashboard.
	 *
	 * @since 6.5.0
	 *
	 * @return bool
	 */
	public function should_display(): bool {
		if ( ! tribe( 'admin.helpers' )->is_screen() ) {
			return false;
		}

		return $this->is_rest_api_blocked();
	}

	/**
	 * Checks if our endpoints are accessible.
	 *
	 * @since 6.5.0
	 * @sicne 6.5.0.1 Introduce a force param.
	 *
	 * @param bool $force Force the check, skipping timed option cache.
	 *
	 * @return bool
	 */
	public function is_rest_api_blocked( bool $force = false ): bool {
		$cache_key     = 'events_is_rest_api_blocked';
		$cache_timeout = 48 * HOUR_IN_SECONDS;
		if ( ! $force && tec_timed_option()->exists( $cache_key, $force ) ) {
			$this->blocked_endpoint = tec_timed_option()->get( $cache_key, null, $force );

			return ! empty( $this->blocked_endpoint );
		}

		// Check multiple endpoints to determine if the REST API is blocked.
		$endpoints = $this->get_routes_to_check();
		foreach ( $endpoints as $endpoint ) {
			$response = wp_safe_remote_get( $endpoint, [ 'timeout' => 3 ] );
			if ( $this->is_response_blocked( $response ) ) {
				$this->blocked_endpoint = $endpoint;
				tec_timed_option()->set( $cache_key, $endpoint, $cache_timeout );

				return true;
			}
		}

		tec_timed_option()->set( $cache_key, false, $cache_timeout );

		return false;
	}

	/**
	 * HTML for the notice when we have blocked REST API endpoints.
	 *
	 * @since 6.5.0
	 *
	 * @return false|string
	 */
	public function notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return false;
		}

		$output = sprintf(
			/* translators: %1$s and %2$s - opening and closing strong tags, respectively. */
			__( '%1$sWarning%2$s: The Events Calendar REST API endpoints are not accessible! This may be due to a server configuration or another plugin blocking access to the REST API.', 'the-events-calendar' ),
			'<strong>',
			'</strong>'
		);
		$output .= '<br />';
		$output .= __( 'Please check with your hosting provider or system administrator to ensure that the below is accessible:', 'the-events-calendar' );
		$output .= '<br />';
		$output .= '<a href="' . $this->blocked_endpoint . '" target="_blank">' . $this->blocked_endpoint . '</a>';

		return $output;
	}

	/**
	 * Checks if the response is blocked.
	 *
	 * @since 6.6.3
	 *
	 * @param array|WP_Error $response The response from the REST API.
	 *
	 * @return bool
	 */
	private function is_response_blocked( $response ): bool {
		// First handle a WP_Error object.
		if ( is_wp_error( $response ) ) {
			$blocked = $this->is_wp_error_response_blocking( $response );
		} else {
			$response_code = wp_remote_retrieve_response_code( $response );
			$blocked       = ( 200 !== $response_code );
		}

		/**
		 * Filters whether the REST API response is considered to be blocked.
		 *
		 * @since 6.6.3
		 *
		 * @param bool           $blocked  Whether the REST API response is blocked.
		 * @param array|WP_Error $response The response from the REST API.
		 */
		return apply_filters( 'tec_events_rest_api_response_blocked', $blocked, $response );
	}

	/**
	 * Checks if the WP_Error response is blocking.
	 *
	 * @since 6.6.3
	 *
	 * @param WP_Error $response The response from the REST API.
	 *
	 * @return bool
	 */
	private function is_wp_error_response_blocking( WP_Error $response ): bool {
		switch ( $response->get_error_code() ) {
			case 'http_request_failed':
				$message = $response->get_error_message();

				// If the site is in development mode, we allow cURL error 60.
				if ( str_starts_with( $message, 'cURL error 60' ) && $this->is_site_development_mode() ) {
					$blocked = false;
				} elseif ( str_starts_with( $message, 'cURL error 28: Operation timed out' ) ) {
					/**
					 * Filters whether the REST API response is considered to be blocked due to a timeout.
					 *
					 * @since 6.6.3
					 *
					 * @param bool     $blocked  Whether the REST API response is blocked.
					 * @param WP_Error $response The response from the REST API.
					 */
					$blocked = (bool) apply_filters( 'tec_events_rest_api_response_blocked_due_to_timeout', false, $response );
				} else {
					$blocked = true;
				}
				break;

			default:
				$blocked = true;
				break;
		}

		return $blocked;
	}

	/**
	 * Get the routes to check for possible REST API blocking.
	 *
	 * @since 6.6.3
	 *
	 * @return array
	 */
	private function get_routes_to_check(): array {
		$routes  = [];
		$v1_main = tribe( 'tec.rest-v1.main' );

		// Ensure that what we got from tribe() is the instance we expect.
		if ( $v1_main instanceof V1 ) {
			$routes[] = rest_url( $v1_main->get_events_route_namespace() );
		}

		$routes[] = rest_url( V2::ROOT_NAMESPACE );

		return $routes;
	}
}
