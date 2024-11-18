<?php
/**
 * Handles the tickets step of the onboarding wizard.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use WP_REST_Response;
use WP_REST_Request;

/**
 * Class Tickets
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Tickets implements Contracts\Step_Interface {
	/**
	 * The tab number for this step.
	 *
	 * @since 7.0.0
	 *
	 * @var int
	 */
	public const TAB_NUMBER = 5;

	/**
	 * Handles extracting and processing the pertinent data
	 * for this step from the wizard request.
	 *
	 * @since 7.0.0
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 * @param Wizard           $wizard   The wizard object.
	 *
	 * @return WP_REST_Response
	 */
	public static function handle( $response, $request, $wizard ): WP_REST_Response {
		if ( $response->is_error() ) {
			return $response;
		}

		$params = $request->get_params();

		// If the current tab is less than this tab, we don't need to do anything yet.
		if ( $params['currentTab'] < self::TAB_NUMBER ) {
			return $response;
		}

		if ( ! isset( $params['eventTickets'] ) ) {
			return $response;
		}

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
	 * @since 7.0.0
	 *
	 * @param bool $tickets The tickets data.
	 */
	public static function process( $tickets ): bool {
		return $tickets ? self::install_event_tickets_plugin() : true;
	}

	/**
	 * Install and activate the Event Tickets plugin from the WordPress.org repo.
	 *
	 * @since 7.0.0
	 */
	public static function install_event_tickets_plugin(): bool {
		// Check if the plugin is already installed.
		if ( function_exists( 'tribe_tickets' ) ) {
			return true;
		}

		// Why, WP, why?
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$plugin_slug     = 'event-tickets'; // Plugin slug for Event Tickets.
		$plugin_repo_url = 'https://api.wordpress.org/plugins/info/1.0/' . $plugin_slug . '.json';

		// Fetch plugin information from the WordPress plugin repo.
		$response = wp_remote_get( $plugin_repo_url );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$plugin_data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $plugin_data['download_link'] ) ) {
			return false;
		}

		// Required stuff for download_url().
		global $wp_filesystem;

		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();

		$download_url = $plugin_data['download_link'];
		$plugin_file  = download_url( $download_url );

		if ( is_wp_error( $plugin_file ) ) {
			return false;
		}

		if ( ! $wp_filesystem->exists( $plugin_file ) ) {
			return false;
		}

		// Unzip the plugin into the plugins folder.
		$unzip = unzip_file( $plugin_file, ABSPATH . 'wp-content/plugins' );

		// CLean up after ourselves.
		wp_delete_file( $plugin_file );

		if ( is_wp_error( $unzip ) ) {
			return false;
		}

		if ( ! function_exists( 'install_plugin_install_status' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Install the plugin.
		$install_result = install_plugin_install_status( $plugin_data );
		if ( is_wp_error( $install_result ) ) {
			return false;
		}

		// Activate the plugin.
		$check = activate_plugin( 'event-tickets/event-tickets.php' );
		if ( is_wp_error( $check ) ) {
			return false;
		}

		return true;
	}
}
