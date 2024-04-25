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
 * @since
 */
class Rest_Api {

	/**
	 * Notice Slug on the user options
	 *
	 * @since
	 *
	 * @var string
	 */
	private $slug = 'events-rest-api-notice';

	/**
	 * Blocked endpoint.
	 *
	 * @since
	 *
	 * @var string
	 */
	private $blocked_endpoint = '';

	/**
	 * Constructor.
	 *
	 * @since
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
	 * @since
	 *
	 * @return boolean
	 */
	public function should_display(): bool {
		global $pagenow;

		if ( tribe( 'admin.helpers' )->is_screen() || 'index.php' === $pagenow ) {
			return $this->is_rest_api_blocked() ? true : false;
		}

		return false;
	}

	/**
	 * Checks if our endpoints are accessible.
	 *
	 * @since
	 *
	 * @return boolean
	 */
	public function is_rest_api_blocked(): bool {

		$v1_api    = new \Tribe__Events__REST__V1__Main();
		$event_api = get_rest_url( null, $v1_api->get_events_route_namespace() );
		$response  = wp_remote_get( $event_api );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->blocked_endpoint = $event_api;
			return true;
		}

		$views_api = get_rest_url( null, V2::ROOT_NAMESPACE );
		$response  = wp_remote_get( $views_api );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->blocked_endpoint = $views_api;
			return true;
		}

		return false;
	}

	/**
	 * HTML for the notice when we have blocked REST API endpoints.
	 *
	 * @since
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
