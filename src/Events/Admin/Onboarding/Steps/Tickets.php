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

/**
 * Class Tickets
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Tickets extends Abstract_Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 7.0.0
	 *
	 * @var int
	 */
	public const TAB_NUMBER = 5;

	/**
	 * Process the tickets data.
	 *
	 * @since 7.0.0
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 *
	 * @return WP_REST_Response
	 */
	public static function process( $response, $request ): WP_REST_Response {
		return self::install_event_tickets_plugin( $response, $request );
	}

	/**
	 * Install and activate the Event Tickets plugin from the WordPress.org repo.
	 *
	 * @since 7.0.0
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 *
	 * @return WP_REST_Response
	 */
	public static function install_event_tickets_plugin( $response, $request ): WP_REST_Response {
		// Check if the plugin is already installed.
		if ( function_exists( 'tribe_tickets' ) ) {
			return $response;
		}

		// Why, WP, why?
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$plugin_slug     = 'event-tickets'; // Plugin slug for Event Tickets.
		$plugin_repo_url = 'https://api.wordpress.org/plugins/info/1.0/' . $plugin_slug . '.json';

		// Fetch plugin information from the WordPress plugin repo.
		$response = wp_safe_remote_get( $plugin_repo_url );
		if ( is_wp_error( $response ) ) {
			return self::add_fail_message( $response, __( 'Failed to get plugin info.', 'the-events-calendar' ) );
		}

		$plugin_data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $plugin_data['download_link'] ) ) {
			return self::add_fail_message( $response, __( 'Failed to extract diwnload link.', 'the-events-calendar' ) );
		}

		// Required stuff for download_url().
		global $wp_filesystem;

		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();

		$download_url = $plugin_data['download_link'];
		$plugin_file  = download_url( $download_url );

		if ( is_wp_error( $plugin_file ) ) {
			return self::add_fail_message( $response, __( 'Failed to download plugin zip.', 'the-events-calendar' ) );
		}

		if ( ! $wp_filesystem->exists( $plugin_file ) ) {
			return self::add_fail_message( $response, __( 'Plugin zip does not exist.', 'the-events-calendar' ) );
		}

		// Unzip the plugin into the plugins folder.
		$unzip = unzip_file( $plugin_file, ABSPATH . 'wp-content/plugins' );

		// CLean up after ourselves.
		wp_delete_file( $plugin_file );

		if ( is_wp_error( $unzip ) ) {
			return self::add_fail_message( $response, __( 'Failed to unzip plugin.', 'the-events-calendar' ) );
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
			return self::add_fail_message( $response, __( 'Failed to install plugin.', 'the-events-calendar' ) );
		}

		// Activate the plugin.
		$check = activate_plugin( 'event-tickets/event-tickets.php' );
		if ( is_wp_error( $check ) ) {
			return self::add_fail_message( $response, __( 'Failed to activate plugin.', 'the-events-calendar' ) );
		}

		return $response;
	}
}
