<?php
/**
 * Handles Telemetry setup and actions.
 *
 * @since   TBD
 *
 * @package TEC\Events\Telemetry
 */
namespace TEC\Events\Telemetry;

use TEC\Events\StellarWP\Telemetry\Core;
use TEC\Events\StellarWP\Telemetry\Config;
use TEC\Events\StellarWP\Telemetry\Opt_In\Status;
use TEC\Events\Container;
use TEC\Events\Site_Health\Site_Health;

/**
 * Class Telemetry
 *
 * @since   TBD

 * @package TEC\Events\Telemetry
 */
class Telemetry {
	/**
	 * The plugin slug used for identification
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $plugin_slug  = 'the-events-calendar';

	/**
	 * The custom hook prefix.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public $hook_prefix = 'tec-events';

	/**
	 * Array to hold the optin args.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	private $optin_args = [];

	function init() {
		static::$plugin_slug = Site_Health::$slug;
		/**
		 * Configure the container.
		 *
		 * The container must be compatible with stellarwp/container-contract.
		 * See here: https://github.com/stellarwp/container-contract#usage.
		 *
		 * If you do not have a container, we recommend https://github.com/lucatume/di52
		 * and the corresponding wrapper:
		 * https://github.com/stellarwp/container-contract/blob/main/examples/di52/Container.php
		 */
		$container = new Container();
		Config::set_container( $container );

		// Set the full URL for the Telemetry Server API.
		Config::set_server_url( 'https://telemetry-api.moderntribe.qa/api/v1' );

		// Set a unique prefix for actions & filters.
		Config::set_hook_prefix( $this->hook_prefix );

		// Set a unique plugin slug.
		Config::set_stellar_slug( static::$plugin_slug );

		// Initialize the library.
		Core::instance()->init( __FILE__ );
	}

	/**
	 * Triggers Telemetry's opt-in modal with our parameters.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function do_optin_modal() {
		$user_name   = esc_html( wp_get_current_user()->display_name );
		$plugin_slug = static::$plugin_slug;

		$this->optin_args = [
			'plugin_logo'           => tribe_resource_url( 'images/logos/tec-brand.svg', false, null, \Tribe__Events__Main::instance() ),
			'plugin_logo_width'     => 151,
			'plugin_logo_height'    => 32,
			'plugin_logo_alt'       => 'The Events Calendar Logo',
			'plugin_name'           => 'The Events Calendar',
			'plugin_slug'           => $plugin_slug,
			'user_name'             => $user_name,
			'permissions_url'       => '#',
			'tos_url'               => '#',
			'privacy_url'           => '#',
			'opted_in_plugins_text' => __( 'See which plugins you have opted in to tracking for', 'the-events-calendar' ),
			'heading'               => __( 'We hope you love The Events Calendar.', 'the-events-calendar' ),
			'intro'                 => __( "Hi, {$user_name}! This is an invitation to help our StellarWP community. If you opt-in, some data about your usage of The Events Calendar and future StellarWP Products will be shared with our teams (so they can work their butts off to improve). We will also share some helpful info on WordPress, and our products from time to time. And if you skip this, that’s okay! Our products still work just fine.", 'the-events-calendar' ),
		];

		add_filter(
			"stellarwp/telemetry/{$plugin_slug}/optin_args",
			function( $args ) {
				return array_merge( $args, $this->optin_args );
			}
		);

		do_action( "stellarwp/telemetry/{$plugin_slug}/optin" );
	}

	/**
	 * Saves the "Opt In Status" setting.
	 *
	 * @return void
	 */
	public function save_opt_in_setting_field() {
		// Return early if not saving the Opt In Status field.
		if ( ! isset( $_POST[ 'opt-in-status' ] ) ) {
			return;
		}

		// Get an instance of the Status class.
		$Status = Config::get_container()->get( Status::class );

		// Get the value submitted on the settings page as a boolean.
		$value = filter_input( INPUT_POST, 'opt-in-status', FILTER_VALIDATE_BOOL );

		$Status->set_status( $value );
	}
}
