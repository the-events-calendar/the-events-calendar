<?php

use Tribe\Events\Admin\Settings;

/**
 * Facilitates smoother integration with the Freemius.
 *
 * @since 4.9
 */
class Tribe__Events__Integrations__Freemius {

	/**
	 * Stores the instance for the Freemius.
	 *
	 * @since  4.9
	 *
	 * @var Freemius
	 */
	private $instance;

	/**
	 * The object class used for assets.
	 *
	 * @since 5.0.2
	 *
	 * @var string
	 */
	private $object_class = 'Tribe__Events__Main';

	/**
	 * Stores the public key for Freemius.
	 *
	 * @since 5.0.2
	 *
	 * @var string
	 */
	private $public_key = 'pk_e32061abc28cfedf231f3e5c4e626';

	/**
	 * Stores the ID for the Freemius application.
	 *
	 * @since  4.9
	 *
	 * @var string
	 */
	private $freemius_id = '3069';

	/**
	 * Stores the slug for the Freemius application.
	 *
	 * @since  4.9
	 *
	 * @var string
	 */
	private $slug = 'the-events-calendar';

	/**
	 * Stores the name for the Freemius application.
	 *
	 * @since 5.0.2
	 *
	 * @var string
	 */
	private $name = 'The Events Calendar';

	/**
	 * Store the value from the 'page' in the request.
	 *
	 * @since 4.9.5
	 *
	 * @var string
	 */
	private $page = 'tec-events-settings';

	/**
	 * Tribe__Tickets__Integrations__Freemius constructor.
	 *
	 * @since  5.0.2
	 */
	public function __construct() {
		$this->setup();
	}

	/**
	 * Performs setup for the Freemius integration singleton.
	 *
	 * @since 5.0.2
	 */
	public function setup() {
		if ( ! is_admin() ) {
			return;
		}
		// Setup possible redirect.
		add_action( 'wp_loaded', [ $this, 'action_redirect_incorrect_page' ] );

		global $pagenow;

		$page = tribe_get_request_var( 'page' );

		$valid_page = [
			\Tribe\Events\Admin\Settings::$settings_page_id => true,
			Tribe__Settings::$parent_slug                   => true,
			Tribe__App_Shop::MENU_SLUG                      => true,
			Tribe__Events__Aggregator__Page::$slug          => true,
			'tec-events-help'                               => true,
			'tec-troubleshooting'                           => true,
		];

		if ( isset( $valid_page[ $page ] ) ) {
			$this->page = $page;
		} elseif ( 'plugins.php' !== $pagenow && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			return;
		}

		// If the common that loaded doesn't include Freemius, let's bail.
		if ( ! tribe()->offsetExists( 'freemius' ) ) {
			return;
		}

		$should_load = true;

		// Check if Freemius integration is disabled.
		if ( ( defined( 'TRIBE_NO_FREEMIUS' ) && true === TRIBE_NO_FREEMIUS ) || true === (bool) getenv( 'TRIBE_NO_FREEMIUS' ) ) {
			$should_load = false;
		}

		/**
		 * Allows third-party disabling of the integration.
		 *
		 * @since  4.9
		 *
		 * @param bool $should_load Whether the Freemius integration should load.
		 */
		$should_load = apply_filters( 'tribe_events_integrations_should_load_freemius', $should_load );

		if ( ! $should_load ) {
			return;
		}

		/** @var Tribe__Freemius $freemius */
		$freemius = tribe( 'freemius' );

		$this->instance = $freemius->initialize( $this->slug, $this->freemius_id, $this->public_key, [
			'menu'           => [
				'slug'       => $this->page,
				'first-path' => $this->get_settings_path(),
				'account'    => false,
				'contact'    => false,
				'support'    => false,
			],
			'is_premium'     => false,
			'has_addons'     => false,
			'has_paid_plans' => false,
		] );

		$this->instance->add_filter( 'connect_url', [ $this, 'get_connect_url' ], 10, 10 );
		$this->instance->add_filter( 'after_skip_url', [ $this, 'get_welcome_url' ] );
		$this->instance->add_filter( 'after_connect_url', [ $this, 'get_welcome_url' ] );
		$this->instance->add_filter( 'after_pending_connect_url', [ $this, 'get_welcome_url' ] );
		$this->instance->add_filter( 'plugin_icon', [ $this, 'get_plugin_icon_url' ] );

		/*
		 * Freemius typically hooks this action–which bootstraps the deactivation dialog–during plugins_loaded, but we
		 * initialize our plugins AFTER plugins_loaded, so we'll register it on admin_init instead.
		 */
		add_action( 'admin_init', [ $this->instance, '_hook_action_links_and_register_account_hooks' ] );
		add_action( 'admin_init', [ $this, 'action_skip_activation' ] );

		$this->instance->add_filter( 'connect_message', [
			$this,
			'filter_connect_message_on_update',
		], 10, 6 );
		$this->instance->add_filter( 'connect_message_on_update', [
			$this,
			'filter_connect_message_on_update',
		], 10, 6 );

		add_action( 'admin_init', [ $this, 'maybe_remove_activation_complete_notice' ] );

		tribe_asset(
			Tribe__Events__Main::instance(),
			"tribe-{$this->slug}-freemius",
			'freemius.css',
			[],
			null
		);
	}


	/**
	 * For some reason Freemius is redirecting some customers to a page that doesnt exist. So we catch that page and
	 * redirect them back to the actual page that we are using to setup the plugins integration.
	 *
	 * @since  5.0.2
	 *
	 * @link https://moderntribe.atlassian.net/browse/TEC-3218
	 *
	 * @return void  Retuning a Redirect header, so nothing gets returned otherwise.
	 */
	public function action_redirect_incorrect_page() {
		$action = tribe_get_request_var( 'fs_action', false );

		if ( 'sync_user' !== $action ) {
			return;
		}

		$page = tribe_get_request_var( 'page', false );

		if ( 'tribe-common-account' !== $page ) {
			return;
		}

		$url = admin_url( 'admin.php' );
		$url = add_query_arg( [
			'fs_action' => $action,
			'page'      => $this->page,
			'_wpnonce'  => tribe_get_request_var( '_wpnonce' ),
		], $url );

		wp_safe_redirect( $url );
		tribe_exit();
	}

	/**
	 * Get the connect page URL.
	 *
	 * @since  5.0.2
	 *
	 * @param string $connect_url Current connect page URL.
	 *
	 * @return string The connect page URL.
	 */
	public function get_connect_url( $connect_url ) {
		$settings_url = $this->get_settings_url();

		if ( false !== strpos( $connect_url, 'fs_action' ) ) {
			$action = $this->slug . '_reconnect';

			$settings_url = add_query_arg( [
				'nonce'     => wp_create_nonce( $action ),
				'fs_action' => $action,
			], $settings_url );
		}

		return $settings_url;
	}

	/**
	 * Get the Settings page URL.
	 *
	 * @since  5.0.2
	 *
	 * @return string The Settings page URL.
	 */
	public function get_settings_url() {
		return admin_url( $this->get_settings_path() );
	}

	/**
	 * Get the plugin icon URL.
	 *
	 * @since  5.0.2
	 *
	 * @return string The plugin icon URL.
	 */
	public function get_plugin_icon_url() {
		$class = $this->object_class;

		return $class::instance()->plugin_url . '/src/resources/images/' . $this->slug . '.svg';
	}

	/**
	 * Get the Settings page path.
	 *
	 * @since  5.0.2
	 *
	 * @return string The Settings page path.
	 */
	public function get_settings_path() {
		return str_replace( get_admin_url(), '', tribe( Settings::class )->get_url() );
	}

	/**
	 * Get the Welcome page URL.
	 *
	 * @since 5.1.6
	 *
	 * @return string The welcome page URL.
	 */
	public function get_welcome_url() {
		return tribe( Settings::class )->get_url( [ Tribe__Events__Main::instance()->activation_page->welcome_slug => 1 ] );
	}

	/**
	 * Action to skip activation since Freemius code does not skip correctly here.
	 *
	 * @since  4.9
	 *
	 * @return bool Whether activation was skipped.
	 */
	public function action_skip_activation() {
		$fs_action = tribe_get_request_var( 'fs_action' );

		// Prevent fatal errors.
		if ( ! function_exists( 'fs_redirect' ) || ! function_exists( 'fs_is_network_admin' ) ) {
			return false;
		}

		// Actually do the skipping of connection, since Freemius code does not do this.
		if ( $this->slug . '_skip_activation' !== $fs_action ) {
			return false;
		}

		check_admin_referer( $this->slug . '_skip_activation' );

		$this->instance->skip_connection( null, fs_is_network_admin() );

		fs_redirect( $this->instance->get_after_activation_url( 'after_skip_url' ) );

		return true;
	}

	/**
	 * Filter the content for the Freemius Popup.
	 *
	 * @since  4.9
	 *
	 * @param string $message         The message content.
	 * @param string $user_first_name The first name of user.
	 * @param string $product_title   The product title.
	 * @param string $user_login      The user_login of user.
	 * @param string $site_link       The site URL.
	 * @param string $freemius_link   The Freemius URL.
	 *
	 * @return string
	 */
	public function filter_connect_message_on_update(
		$message, $user_first_name, $product_title, $user_login, $site_link, $freemius_link
	) {
		$class = $this->object_class;

		tribe_asset_enqueue( "tribe-{$this->slug}-freemius" );

		// Add the heading HTML.
		$plugin_name = $this->name;
		$title       = '<h3>' . sprintf( esc_html__( 'We hope you love %1$s', 'the-events-calendar' ), $plugin_name ) . '</h3>';
		$html        = '';

		// Add the introduction HTML.
		$html .= '<p>';
		$html .= sprintf( esc_html__( 'Hi, %1$s! This is an invitation to help our %2$s community. If you opt-in, some data about your usage of %2$s will be shared with our teams (so they can work their butts off to improve). We will also share some helpful info on events management, WordPress, and our products from time to time.', 'the-events-calendar' ), $user_first_name, $plugin_name );
		$html .= '</p>';

		$html .= '<p>';
		$html .= sprintf( esc_html__( 'And if you skip this, that\'s okay! %1$s will still work just fine.', 'the-events-calendar' ), $plugin_name );
		$html .= '</p>';

		// Add the "Powered by" HTML.
		$html .= '<div class="tribe-powered-by-freemius">' . esc_html__( 'Powered by', 'the-events-calendar' ) . '</div>';

		return $title . $html;
	}

	/**
	 * Returns the instance of Freemius plugin.
	 *
	 * @since  4.9
	 *
	 * @return Freemius
	 */
	public function get() {
		return $this->instance;
	}

	/**
	 * Method to remove the sticky message when the plugin is active for Freemius.
	 *
	 * @since  4.9
	 */
	public function maybe_remove_activation_complete_notice() {
		// Bail if the is_pending_activation() method doesn't exist.
		if ( ! method_exists( $this->instance, 'is_pending_activation' ) ) {
			return;
		}

		// Bail if it's still pending activation.
		if ( $this->instance->is_pending_activation() ) {
			return;
		}

		$admin_notices = FS_Admin_Notices::instance( $this->slug, $this->name, $this->instance->get_unique_affix() );

		// Bail if it doesn't have the activation complete notice.
		if ( ! $admin_notices->has_sticky( 'activation_complete' ) ) {
			return;
		}

		// Remove the sticky notice for activation complete.
		$admin_notices->remove_sticky( 'activation_complete' );
	}
}
