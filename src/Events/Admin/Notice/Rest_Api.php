<?php
/**
 * Shows an admin notice when our REST API endpoints are not available.
 *
 * @since
 *
 * @package TEC\Events\Admin\Notice
 */

namespace TEC\Events\Admin\Notice;

use Tribe\Events\Views\V2\Rest_Endpoint as V2;

/**
 * Class Rest_Api
 *
 * Shows an admin notice when our REST API endpoints are not available.
 *
 * @since 6.5.0
 */
class Rest_Api {

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
		$slug = $this->slug;

		tribe_notice(
			$slug,
			[ $this, 'notice' ],
			[
				'type'               => 'error',
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
	 * @return boolean
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
	 * @return boolean
	 */
	public function is_rest_api_blocked( bool $force = false ): bool {
		$cache_key     = 'events_is_rest_api_blocked';
		$cache_timeout = 48 * HOUR_IN_SECONDS;
		if ( ! $force && tec_timed_option()->exists( $cache_key, $force ) ) {
			$this->blocked_endpoint = tec_timed_option()->get( $cache_key, null, $force );

			return ! empty( $this->blocked_endpoint );
		}

		$v1_api    = tribe( 'tec.rest-v1.main' );
		$event_api = get_rest_url( null, $v1_api->get_events_route_namespace() );
		$response  = wp_remote_get( $event_api );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->blocked_endpoint = $event_api;
			tec_timed_option()->set( $cache_key, $event_api, $cache_timeout );

			return true;
		}

		$views_api = get_rest_url( null, V2::ROOT_NAMESPACE );
		$response  = wp_remote_get( $views_api );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->blocked_endpoint = $views_api;
			tec_timed_option()->set( $cache_key, $views_api, $cache_timeout );

			return true;
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
		/* Translators: %1$s and %2$s - opening and closing strong tags, respectively. */
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
}
