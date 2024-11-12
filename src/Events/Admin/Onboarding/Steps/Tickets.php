<?php
/**
 * Handles the tickets step of the onboarding wizard.
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

/**
 * Class Tickets
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Tickets implements Contracts\Step_Interface {
	/**
	 * Handles extracting and processing the pertinent data
	 * for this step from the wizard request.
	 *
	 * @since TBD
	 *
	 * @param \WP_REST_Response $response The response object.
	 * @param \WP_REST_Request  $request  The request object.
	 * @param Wizard            $wizard   The wizard object.
	 *
	 * @return \WP_REST_Response
	 */
	public static function handle( $response, $request, $wizard ): \WP_REST_Response {
		if ( $response->is_error() ) {
			return $response;
		}

		$params    = $request->get_params();
		$processed = self::process( $params['eventTickets'] ?? false );
		$data      = $response->get_data();

		$new_message = $processed ?
			__( 'Event Tickets installed successfully.', 'the-events-calendar' )
			: __( 'Failed to install Event Tickets.', 'the-events-calendar' );

		$response->set_data(
			[
				'success' => $processed,
				'message' => array_merge( $data['message'], [ $new_message ] ),
			]
		);

		$response->set_status( $processed ? $response->get_status() : 500 );

		return $response;
	}

	/**
	 * Process the tickets data.
	 *
	 * @since TBD
	 *
	 * @param bool $tickets The tickets data.
	 */
	public static function process( $tickets ): bool {
		return $tickets ? self::install_event_tickets_plugin( $tickets) : true;
	}

	public static function install_event_tickets_plugin( $eventTickets ) {
		// Check if the plugin is already installed.
		if ( function_exists( 'tribe_tickets' ) ) {
			return true;
		}

		$plugin_slug = 'event-tickets'; // Plugin slug for Event Tickets.
		$plugin_repo_url = 'https://api.wordpress.org/plugins/info/1.0/' . $plugin_slug . '.json';

		// Fetch plugin information from the WordPress plugin repo.
		$response = wp_remote_get( $plugin_repo_url );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$plugin_data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $plugin_data['download_link'] ) ) {
			$download_url = $plugin_data['download_link'];
			$plugin_file = download_url( $download_url );

			if ( is_wp_error( $plugin_file ) ) {
				return false;
			}

			// Install the plugin
			$install_result = install_plugin_install_status( $plugin_file );
			if ( ! is_wp_error( $install_result ) ) {
				// Activate the plugin
				activate_plugin( 'event-tickets/event-tickets.php' );
			}
		} else {
			return false;
		}

		return true;
	}
}
