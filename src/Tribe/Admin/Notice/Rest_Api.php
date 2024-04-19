<?php
/**
 * Shows an admin notice when our REST API endpoints are not available.
 *
 * @since
 *
 * @package Tribe\Events\Admin\Notice
 */

namespace Tribe\Events\Admin\Notice;

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
	public function hook() : void {
		$slug = $this->slug;

		tribe_notice(
			$slug,
			[ $this, 'notice' ],
			[
				'type'    => 'error',
				'dismiss' => 1,
				'wrap'    => 'p',
			],
			[ $this, 'should_display' ]
		);
	}

	/**
	 * Checks if we are in an TEC page or in main admin Dashboard.
	 *
	 * @since
	 *
	 * @return boolean
	 */
	public function should_display() : bool {
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
	public function is_rest_api_blocked() : bool {

		$event_api = get_rest_url( null, '/tribe/events/v1/' );
		$response  = wp_remote_get( $event_api );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->blocked_endpoint = $event_api;
			return true;
		}

		$views_api = get_rest_url( null, 'tribe/views/v2/' );
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

		$text = [];
		// Translators: %s is the "Warning" word in bold.
		$text[] = sprintf( __( '%s : The Events Calendar REST API endpoints are not accessible! This may be due to a server configuration or another plugin blocking access to the REST API.', 'the-events-calendar' ), '<strong>' . __( 'Warning', 'the-events-calendar' ) . '</strong>' );
		$text[] = __( 'Please check with your hosting provider or system administrator to ensure that the below is accessible:', 'the-events-calendar' );
		$text[] = '<p><a href="' . $this->blocked_endpoint . '" target="_blank">' . $this->blocked_endpoint . '</a></p>';

		return implode( '<br />', $text );
	}
}
