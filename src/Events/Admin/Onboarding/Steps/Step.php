<?php
/**
 * Step-handler class for the onboarding wizard.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use WP_REST_Request;
use WP_REST_Response;
use TEC\Events\Admin\Onboarding\Steps\Factory;
use TEC\Events\Admin\Onboarding\Wizard;
use Tribe__Events__API;

/**
 * Class Optin
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Step implements Contracts\Step_Interface {
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
	 * If the step has venue.
	 *
	 * @since 7.0.0
	 *
	 * @var bool $has_venue
	 */
	public $has_venue;

	/**
	 * If the step has organizer.
	 *
	 * @since 7.0.0
	 *
	 * @var bool $has_organizer
	 */
	public $has_organizer;

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
	 *    'required' => false,
	 * ]
	 *
	 * @since 7.0.0
	 *
	 * @var array<string,array<string,mixed>> $plugins
	 */
	public $plugins = [];

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
	 * @since 7.0.0
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


	public static function create( $key ) {
		$classname = 'TEC\Events\Admin\Onboarding\Steps\\' . Factory::$steps[$key];

		$step = Factory::from_array( $classname::get_data() );

		static::register();

		return $step;
	}

	/**
	 * Sets up needed bindings.
	 *
	 * @since 7.0.0
	 */
	public function register() {
		$this->add_filters();
	}

	public function add_filters() {
		add_filter( 'tribe_events_onboarding_wizard_initial_data', [ $this, 'add_data' ] );
		add_filter( 'tec_events_onboarding_wizard_handle', [ $this, 'handle' ], 14, 3 );
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
	public static function get_data(): array {
		return [];
	}

	/**
	 * Add data to the wizard for the step.
	 *
	 * @since 7.0.0
	 *
	 * @param array $data The data for the step.
	 *
	 * @return array
	 */
	public function add_data( array $data ): array {
		return $data;
	}

	/**
	 * Get the step number.
	 *
	 * @since 7.0.0
	 *
	 * @return int
	 */
	public function get_step_number(): int {
		return static::$step_number;
	}

	/**
	 * Check if the step has options to save.
	 *
	 * @since 7.0.0
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
	 * @since 7.0.0
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
	 * @since 7.0.0
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
	 * Check if the step has an organizer.
	 *
	 * @since 7.0.0
	 *
	 * @return boolean
	 */
	protected function has_organizer() {
		if ( ! is_null( $this->has_organizer ) ) {
			return $this->has_organizer;
		}

		if ( ! empty( $this->organizer ) ) {
			$this->has_organizer = true;
		} else {
			$this->has_organizer = false;
		}

		return $this->has_organizer;
	}

	/**
	 * Check if the step has a venue.
	 *
	 * @since 7.0.0
	 *
	 * @return boolean
	 */
	protected function has_venue() {
		if ( ! is_null( $this->has_venue ) ) {
			return $this->has_venue;
		}

		if ( ! empty( $this->venue ) ) {
			$this->has_venue = true;
		} else {
			$this->has_venue = false;
		}

		return $this->has_venue;
	}

	/**
	 * Get the options for the step in a key->value format.
	 *
	 * @since 7.0.0
	 *
	 * @return array
	 */
	public function get_options() {
		$return = [];
		foreach ( $this->options as $option ) {
			$return[ $option['key'] ] = $option['value'];
		}

		return $return;
	}

	/**
	 * Get the settings for the step in a key->value format.
	 *
	 * @since 7.0.0
	 *
	 * @return array
	 */
	public function get_settings() {
		$return = [];
		foreach ( $this->settings as $setting ) {
			$return[ $setting['key'] ] = $setting['value'];
		}

		return $return;
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
		$params = $this->request->get_params();

		if ( ! isset( $params[ $option['key'] ] ) ) {
			return $this;
		}

		$option['value'] = $params[ $option['key'] ];

		if ( $option['value'] === get_option( $option['key'], null ) ) {
			return $this;
		}

		$updated = update_option( $option['key'], $option['value'] );

		if ( ! $updated ) {
			$this->add_fail(
				sprintf(
					/* Translators: %1$s: option key */
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
		$params = $this->request->get_params();

		if ( ! isset( $params[ $setting['key'] ] ) ) {
			return $this;
		}

		$option['value'] = $params[ $setting['key'] ];

		if ( $option['value'] === tribe_get_option( $setting['key'], null ) ) {
			return $this;
		}

		$updated = tribe_update_option( $setting['key'], $setting['value'] );

		if ( ! $updated ) {
			$this->add_fail(
				sprintf(
					/* Translators: %1$s: setting key */
					__( 'Failed to update setting %1$s', 'the-events-calendar' ),
					$setting['key']
				)
			);
		}

		return $this;
	}

	/**
	 * Check if a plugin is installed.
	 *
	 * @since 7.0.0
	 *
	 * @param array $plugin The plugin data.
	 *
	 * @return array
	 */
	protected function is_installed( $plugin ) {
		// Check if get_plugins() function exists.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();

		$plugins = array_filter(
			$plugins,
			function ( $key ) use ( $plugin ) {
				return false !== strpos( $key, $plugin['plugin'] );
			},
			ARRAY_FILTER_USE_KEY
		);

		return array_keys( $plugins );
	}

	/**
	 * Install and activate the passed plugin from the WordPress.org repo.
	 *
	 * @since 7.0.0
	 *
	 * @param array $plugin The plugin data.
	 *
	 * @return self
	 */
	protected function install_plugin( $plugin ): self {
		$params = $this->request->get_params();

		if ( ! isset( $params[ $plugin['plugin'] ] ) ) {
			return $this;
		}
		// Check if the plugin is already installed.
		if ( ! empty( $this->is_installed( $plugin ) ) ) {
			$this->add_success(
				sprintf(
					/* Translators: %1$s: plugin name */
					__( '%1$s already installed.', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);

			return $this;
		}

		// Why, WP, why?
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$plugin_repo_url = 'https://api.wordpress.org/plugins/info/1.0/' . $plugin['plugin'] . '.json';

		// Fetch plugin information from the WordPress plugin repo.
		$response = wp_safe_remote_get( $plugin_repo_url );
		if ( is_wp_error( $response ) ) {
			$this->add_fail(
				sprintf(
					/* Translators: %1$s: plugin name */
					__( 'Could not fetch plugin info for %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);

			return $this;
		}

		$plugin_data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $plugin_data['download_link'] ) ) {
			$this->add_fail(
				sprintf(
					/* Translators: %1$s: plugin name */
					__( 'Could not extract plugin download link for %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);

			return $this;
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
					/* Translators: %1$s: plugin name */
					__( 'Failed to download plugin: %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);

			return $this;
		}

		if ( ! $wp_filesystem->exists( $plugin_file ) ) {
			$this->add_fail(
				sprintf(
					/* Translators: %1$s: plugin name */
					__( 'Downloaded plugin file does not exist for %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);

			return $this;
		}

		// Unzip the plugin into the plugins folder.
		$unzip = unzip_file( $plugin_file, ABSPATH . 'wp-content/plugins' );

		if ( is_wp_error( $unzip ) ) {
			$this->add_fail(
				sprintf(
					/* Translators: %1$s: plugin name */
					__( 'Failed to unzip plugin: %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);

			return $this;
		}

		// Clean up after ourselves.
		$deleted = wp_delete_file( $plugin_file );

		if ( ! $deleted ) {
			$this->add_fail(
				sprintf(
					/* Translators: %1$s: plugin name */
					__( 'Failed to delete plugin zip file for %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);

			return $this;
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
					/* Translators: %1$s: plugin name */
					__( 'Failed to install %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);

			return $this;
		}

		// Activate the plugin.
		$check = activate_plugin( 'event-tickets/event-tickets.php' );

		if ( is_wp_error( $check ) ) {
			$this->add_fail(
				sprintf(
					/* Translators: %1$s: plugin name */
					__( 'Failed to activate %1$s', 'the-events-calendar' ),
					$plugin['plugin']
				)
			);

			return $this;
		}

		return $this;
	}

	protected function create_organizer( $organizer ): self {
		// No data to process, bail out.
		if ( ! $organizer ) {
			return $this;
		}

		// If we already have an organizer, we're not editing it here.
		if ( ! empty( $organizer['id'] ) ) {
			return $this;
		}

		$organizer['Organizer'] = $organizer['name'];
		unset( $organizer['name'] );

		$post_id = Tribe__Events__API::createOrganizer( $organizer );

		if ( ! $post_id ) {
			$this->add_fail(
				__( 'Failed to create organizer', 'the-events-calendar' )
			);
		}

		return $this;
	}

	protected function create_venue( $venue ) {
		// No data to process, bail out.
		if ( ! $venue ) {
			return true;
		}

		// If we already have a venue, we're not editing it here.
		if ( ! empty( $venue['id'] ) ) {
			return true;
		}

		// Massage the data a bit.
		$new_venue['Venue'] = $venue['name'];
		unset( $venue['name'] );

		$post_id = Tribe__Events__API::createVenue( $new_venue );

		if ( ! $post_id ) {
			$this->add_fail(
				__( 'Failed to create venue', 'the-events-calendar' )
			);
		}

		return $this;
	}
}
