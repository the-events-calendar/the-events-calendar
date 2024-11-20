<?php
/**
 * Abstract step-handler class for the onboarding wizard.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use TEC\Events\Admin\Onboarding\Steps\Factory;
use TEC\Events\Admin\Onboarding\Wizard;

/**
 * Class Optin
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
abstract class Abstract_Step implements Contracts\Step_Interface {
	/**
	 * The tab number for this step.
	 *
	 * @since 7.0.0
	 *
	 * @var int
	 */
	public static $step_number = 0;

	/**
	 * Which arguments are valid for the step,
	 *
	 * @since 7.0.0
	 *
	 * @var bool $has_options
	 */
	public $has_options;

	/**
	 * If the step has settings.
	 *
	 * @since 7.0.0
	 *
	 * @var bool $has_settings
	 */
	public $has_settings;

	/**
	 * If the step installs a plugin(s).
	 *
	 * @since 7.0.0
	 *
	 * @var bool $is_install
	 */
	public $is_install;

	/**
	 * Holds the options for the step.
	 * In the format:
	 * [
	 *    'key'    => '',
	 *    'value'  => '',
	 * ]
	 *
	 * @since 7.0.0
	 *
	 * @var array<string,array<string,mixed>> $options
	 */
	public $options = [];

	/**
	 * Holds the settings for the step.
	 * In the format:
	 * [
	 *    'plugin' => '',
	 *    'key'    => '',
	 *    'value'  => '',
	 * ]
	 *
	 * @since 7.0.0
	 *
	 * @var array<string,array<string,mixed>> $settings
	 */
	public $settings = [];

	/**
	 * Holds the plugins for the step.
	 * In the format:
	 * [
	 *    'plugin'   => '',
	 *    'class'    => '',
	 *    'required' => false,
	 *    'function' => '',
	 * ]
	 *
	 * @since 7.0.0
	 *
	 * @var array<string,array<string,mixed>> $plugins
	 */
	public $plugins = [];

	/**
	 * Store all errors that happen during the last creation.
	 *
	 * @since 7.0.0
	 *
	 * @var ?WP_Error
	 */
	protected ?WP_Error $error;

	/**
	 * The response object.
	 *
	 * @since 7.0.0
	 *
	 * @var WP_REST_Response
	 */
	protected WP_REST_Response $response;

	/**
	 * The request object.
	 *
	 * @since 7.0.0
	 *
	 * @var WP_REST_Request
	 */
	protected WP_REST_Request $request;

	/**
	 * The step object.
	 *
	 * @since TBD
	 *
	 * @var [type]
	 */
	protected $step;

	/**
	 * The wizard object.
	 *
	 * @since 7.0.0
	 *
	 * @var Wizard
	 */
	protected Wizard $wizard;

	/**
	 * Create the step object.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function create() {
		$this->step = Factory::from_array( $this->get_data() );
	}

	/**
	 * Get the data for the step.
	 *
	 * In the format:
	 * [
	 *    'step_number' => int, required
	 *    'options' => [],
	 *    'settings' => [],
	 *    'plugins' => [],
	 * ]
	 *
	 * @since 7.0.0
	 *
	 * @return array
	 */
	protected function get_data() {
		return [];
	}

	/**
	 * Check if the step has options to save.
	 *
	 * @since TBD
	 *
	 * @return boolean
	 */
	protected function has_options() {
		if ( ! is_null( $this->has_options ) ) {
			return $this->has_options;
		}

		if ( ! empty( $this->options ) ) {
			$this->has_options = true;
		} else {
			$this->has_options = false;
		}

		return $this->has_options;
	}

	/**
	 * Check if the step has settings to save.
	 *
	 * @since TBD
	 *
	 * @return boolean
	 */
	protected function has_settings() {
		if ( ! is_null( $this->has_settings ) ) {
			return $this->has_settings;
		}

		if ( ! empty( $this->settings ) ) {
			$this->has_settings = true;
		} else {
			$this->has_settings = false;
		}

		return $this->has_settings;
	}

	/**
	 * Check if the step is installing a plugin(s).
	 *
	 * @since TBD
	 *
	 * @return boolean
	 */
	protected function is_install() {
		if ( ! is_null( $this->is_install ) ) {
			return $this->is_install;
		}

		if ( ! empty( $this->plugins ) ) {
			$this->is_install = true;
		} else {
			$this->is_install = false;
		}

		return $this->is_install;
	}

	/**
	 * Passes the request and data to the handler.
	 *
	 * @since 7.0.0
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 * @param Wizard            $wizard   The wizard object.
	 *
	 * @return WP_REST_Response
	 */
	public function handle( $response, $request, $wizard ): WP_REST_Response {
		// If it's already an error, bail.
		if ( $response->is_error() ) {
			return $response;
		}

		// Ensure we should be processing this step.
		if ( ! static::tab_check( $request ) ) {
			return $response;
		}

		$this->response = $response;
		$this->request  = $request;
		$this->wizard   = $wizard;

		$this->process();

		return $response;
	}

	/**
	 * Check if the current tab is the one we should be processing.
	 *
	 * @since 7.0.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return bool
	 */
	public function tab_check( $request ) {
		$params = $request->get_params();
		// If the current tab is less than this tab, we don't need to do anything yet.
		return isset( $params['currentTab'] ) && $params['currentTab'] <= static::$step_number;
	}

	/**
	 * Add a success message to the response.
	 *
	 * @since 7.0.0
	 *
	 * @param string $message  The message to add.
	 */
	public function add_success( $message ): void {
		$data            = $this->response->get_data();
		$data['message'] = array_merge( (array) $data['message'], [ $message ] );

		$this->response->set_data( $data );
	}

	/**
	 * Add a failure message to the response.
	 *
	 * @since 7.0.0
	 *
	 * @param string $message  The message to add.
	 */
	public function add_fail( $message ): void {
		$data            = $this->response->get_data();
		$data['message'] = array_merge( (array) $data['message'], [ $message ] );

		$this->response->set_data( $data );

		$this->response->set_status( 500 );
	}

	/**
	 * Process the step.
	 *
	 * @since 7.0.0
	 */
	public function process(): self {
		if ( $this->has_options() ) {
			foreach ( $this->options as $option ) {
				$this->save_option( $option );
			}
		}

		if ( $this->has_settings() ) {
			foreach ( $this->settings as $setting ) {
				$this->save_setting( $setting );
			}
		}

		if ( $this->is_install() ) {
			foreach ( $this->plugins as $plugin ) {
				$this->install_plugin( $plugin );
			}
		}

		return $this;
	}

	/**
	 * Save an option.
	 *
	 * @since 7.0.0
	 *
	 * @param array $option The option data.
	 *
	 * @return self
	 */
	protected function save_option( $option ): self {
		$current = get_option( $option['key'], false );
		if ( $option['value'] === $current ) {
			return $this;
		}

		$updated = update_option( $option['key'], $option['value'] );

		if ( ! $updated ) {
			$this->add_fail(
				sprintf(
					__( 'Failed to update option %1$s', 'the-events-calendar' ),
					$option['key']
				)
			);
		}

		return $this;
	}

	/**
	 * Save a setting.
	 *
	 * @since 7.0.0
	 *
	 * @param array $setting The setting data.
	 *
	 * @return self
	 */
	protected function save_setting( $setting ): self {
		$current = tribe_get_option( $setting['key'], false );
		if ( $setting['value'] === $current ) {
			return $this;
		}

		$updated = tribe_update_option( $setting['key'], $setting['value'] );

		if ( ! $updated ) {
			$this->add_fail(
				sprintf(
					__( 'Failed to updateTEC setting %1$s', 'the-events-calendar' ),
					$setting['key']
				)
			);
		}

		return $this;
	}

	/**
	 * Install and activate the passed plugin from the WordPress.org repo.
	 *
	 * @since 7.0.0
	 *
	 * @param object $plugin The plugin data.
	 *
	 * @return self
	 */
	protected function install_plugin( $plugin ): self {
		// Check if the plugin is already installed.
		if (
			( $plugin->function && function_exists( (string) $plugin->function ) )
			|| ( $plugin->class && class_exists( (string) $plugin->class, false ) )
		) {
			$this->add_fail(
				sprintf(
					__( '%1$s already installed.', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);
		}

		// Why, WP, why?
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$plugin_repo_url = 'https://api.wordpress.org/plugins/info/1.0/' . $plugin->plugin . '.json';

		// Fetch plugin information from the WordPress plugin repo.
		$response = wp_safe_remote_get( $plugin_repo_url );
		if ( is_wp_error( $response ) ) {
			$this->add_fail(
				sprintf(
					__( 'Could not fetch plugin info for %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);
		}

		$plugin_data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $plugin_data['download_link'] ) ) {
			$this->add_fail(
				sprintf(
					__( 'Could not extract plugin download link for %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);
		}

		// Required stuff for download_url().
		global $wp_filesystem;

		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();

		$download_url = $plugin_data['download_link'];
		$plugin_file  = download_url( $download_url );

		if ( is_wp_error( $plugin_file ) ) {
			$this->add_fail(
				sprintf(
					__( 'Failed to download plugin: %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);
		}

		if ( ! $wp_filesystem->exists( $plugin_file ) ) {
			$this->add_fail(
				sprintf(
					__( 'Downloaded plugin file does not exist for %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);
		}

		// Unzip the plugin into the plugins folder.
		$unzip = unzip_file( $plugin_file, ABSPATH . 'wp-content/plugins' );

		if ( is_wp_error( $unzip ) ) {
			$this->add_fail(
				sprintf(
					__( 'Failed to unzip plugin: %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);
		}

		// Clean up after ourselves.
		$deleted = wp_delete_file( $plugin_file );

		if ( ! $deleted ) {
			$this->add_fail(
				sprintf(
					__( 'Failed to delete plugin zip file for %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);
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
			$this->add_fail(
				sprintf(
					__( 'Failed to install %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);
		}

		// Activate the plugin.
		$check = activate_plugin( 'event-tickets/event-tickets.php' );

		if ( is_wp_error( $check ) ) {
			$this->add_fail(
				sprintf(
					__( 'Failed to activate %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);
		}

		return $this;
	}
}
