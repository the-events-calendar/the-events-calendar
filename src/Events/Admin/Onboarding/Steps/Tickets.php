<?php
/**
 * Handles the tickets step of the onboarding wizard.
 *
 * @since 6.8.4
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use TEC\Common\Admin\Onboarding\Steps\Abstract_Step;
use WP_REST_Response;
use TEC\Common\StellarWP\Installer\Installer;
use TEC\Common\StellarWP\Installer\Handler\Plugin;

/**
 * Class Tickets
 *
 * @since 6.8.4
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Tickets extends Abstract_Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 6.8.4
	 *
	 * @var int
	 */
	public const TAB_NUMBER = 5;

	/**
	 * Process the tickets data.
	 *
	 * @since 6.8.4
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function process( $response, $request ): WP_REST_Response {
		return $this->install_event_tickets_plugin( $response, $request );
	}

	/**
	 * Install and activate the Event Tickets plugin from the WordPress.org repo.
	 *
	 * @since 6.8.4
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function install_event_tickets_plugin( $response, $request ): WP_REST_Response {
		$plugin_slug = 'event-tickets';
		$params      = $request->get_params();

		if ( ! isset( $params['eventTickets'] ) || ! $params['eventTickets'] ) {
			return $this->add_message( $response, __( 'Event Tickets install not requested.', 'the-events-calendar' ) );
		}

		// Installer and Plugin classes needs these to be loaded for them to work.
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		require_once ABSPATH . '/wp-admin/includes/file.php';

		$installed = Installer::get()->is_installed( $plugin_slug );
		$activated = Installer::get()->is_active( $plugin_slug );

		// Check if the plugin is already installed and active.
		if ( $installed && $activated ) {
			return $this->add_message( $response, __( 'Event Tickets plugin already installed and activated.', 'the-events-calendar' ) );
		}

		$plugin = new Plugin( 'Event Tickets', 'event-tickets' );

		if ( ! $installed ) {
			$install = $plugin->install();

			if ( ! $install ) {
				return $this->add_fail_message( $response, __( 'Failed to install plugin.', 'the-events-calendar' ) );
			}
		}

		if ( ! $activated ) {
			$active = $plugin->activate();

			if ( ! $active ) {
				return $this->add_fail_message( $response, __( 'Failed to activate plugin.', 'the-events-calendar' ) );
			}
		}

		return $this->add_message( $response, __( 'Event Tickets plugin installed and activated.', 'the-events-calendar' ) );
	}
}
