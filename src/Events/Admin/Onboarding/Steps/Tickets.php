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
		// Check if the plugin is already installed and active.
		if ( function_exists( 'tribe_tickets' ) ) {
			return self::add_message( $response, __( 'Event Tickets plugin already installed and activated.', 'the-events-calendar' ) );
		}

		if ( file_exists( WP_PLUGIN_DIR . '/event-tickets/event-tickets.php' ) ) {
			$activate = activate_plugin( 'event-tickets/event-tickets.php' );

			if ( is_wp_error( $activate ) ) {
				return self::add_fail_message( $response, __( 'Failed to activate plugin.', 'the-events-calendar' ) );
			} else {
				return self::add_message( $response, __( 'Event Tickets plugin activated.', 'the-events-calendar' ) );
			}
		}

		$plugin_info = self::get_plugin_info();

		if ( is_wp_error( $plugin_info ) ) {
			return self::add_fail_message( $response, __( 'Failed to get plugin info.', 'the-events-calendar' ) );
		}

		$plugin_data = self::get_plugin_data( $plugin_info );

		if ( ! isset( $plugin_data['download_link'] ) ) {
			return self::add_fail_message( $response, __( 'Failed to extract download link.', 'the-events-calendar' ) );
		}

		global $wp_filesystem;

		$plugin_file = self::download_plugin( $response );

		if ( is_wp_error( $plugin_file ) ) {
			return self::add_fail_message( $response, __( 'Failed to download plugin zip.', 'the-events-calendar' ) );
		}

		if ( ! $wp_filesystem->exists( $plugin_file ) ) {
			return self::add_fail_message( $response, __( 'Plugin zip does not exist.', 'the-events-calendar' ) );
		}

		$unzip = self::unzip_plugin( $plugin_file );

		if ( is_wp_error( $unzip ) ) {
			return self::add_fail_message( $response, __( 'Failed to unzip plugin.', 'the-events-calendar' ) );
		}

		$install_result = self::install_plugin( $plugin_data );

		if ( is_wp_error( $install_result ) ) {
			return self::add_fail_message( $response, __( 'Failed to install plugin.', 'the-events-calendar' ) );
		}

		// Activate the plugin.
		return activate_plugin( $response );
	}

	/**
	 * Get the plugin information from the WordPress plugin repo.
	 *
	 * @since 7.0.0
	 *
	 * @return array|WP_Error The plugin information.
	 */
	public static function get_plugin_info() {
		$plugin_slug     = 'event-tickets'; // Plugin slug for Event Tickets.
		$plugin_repo_url = 'https://api.wordpress.org/plugins/info/1.0/' . $plugin_slug . '.json';

		// Fetch plugin information from the WordPress plugin repo.
		$wp_get = wp_safe_remote_get( $plugin_repo_url );
	}

	/**
	 * Get the plugin data from the plugin info.
	 *
	 * @since 7.0.0
	 *
	 * @param array|WP_Error $plugin_info The plugin info.
	 *
	 * @return array The plugin data.
	 */
	public static function get_plugin_data( $plugin_info ) {
		return json_decode( wp_remote_retrieve_body( $plugin_info ), true );
	}

	/**
	 * Download the plugin zip file.
	 *
	 * @since 7.0.0
	 *
	 * @param array|WP_Error $plugin_data The plugin data.
	 *
	 * @return string The plugin file path.
	 */
	public static function download_plugin() {
		// Required stuff for download_url().
		require_once ABSPATH . '/wp-admin/includes/file.php';

		WP_Filesystem();


		$download_url = $plugin_data['download_link'];

		return download_url( $download_url );
	}

	/**
	 * Unzip the plugin zip file.
	 *
	 * @since 7.0.0
	 *
	 * @param string $plugin_file The plugin file path.
	 *
	 * @return bool|WP_Error True if the plugin was unzipped, WP_Error if not.
	 */
	public static function unzip_plugin( $plugin_file ) {
		// Unzip the plugin into the plugins folder.
		$unzip = unzip_file( $plugin_file, ABSPATH . 'wp-content/plugins' );

		// Clean up after ourselves.
		wp_delete_file( $plugin_file );

		return $unzip;
	}

	/**
	 * Install the plugin.
	 *
	 * @since 7.0.0
	 *
	 * @param array|WP_Error $plugin_data The plugin data.
	 *
	 * @return bool|WP_Error True if the plugin was installed, WP_Error if not.
	 */
	public static function install_plugin( $plugin_data ) {
		if ( ! function_exists( 'install_plugin_install_status' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Install the plugin.
		return install_plugin_install_status( $plugin_data );
	}

	/**
	 * Activate the plugin.
	 *
	 * @since 7.0.0
	 *
	 * @param WP_REST_Response $response The response object.
	 *
	 * @return WP_REST_Response
	 */
	public static function activate_plugin( $response ) {
		// Activate the plugin.
		$check = activate_plugin( 'event-tickets/event-tickets.php' );

		if ( is_wp_error( $check ) ) {
			return self::add_fail_message( $response, __( 'Failed to activate plugin.', 'the-events-calendar' ) );
		} else {
			return self::add_message( $response, __( 'Event Tickets plugin installed and activated.', 'the-events-calendar' ) );
		}
	}
}
