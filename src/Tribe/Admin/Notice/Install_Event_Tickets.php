<?php
/**
 * Install_Event_Tickets notice.
 * Install and/or activate Event Tickets when it is not active.
 */

namespace Tribe\Events\Admin\Notice;

use WP_Upgrader;
use WP_Ajax_Upgrader_Skin;
use Plugin_Upgrader;
use Tribe__Main;
use Tribe__Admin__Notices;
use Tribe__Template;

/**

 */
class Install_Event_Tickets {

	/**
	 * Stores the plugin slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static $plugin_slug = 'event-tickets';

	/**
	 * Stores the assets group ID for the notice.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static $assets_group = 'tribe-events-admin-notice-install-event-tickets';

	/**
	 * Stores the instance of the notice template.
	 *
	 * @since TBD
	 *
	 * @var Tribe__Template
	 */
	protected $template;

	/**
	 * Register update notices.
	 *
	 * @since TBD
	 */
	public function hook() {
		if ( ! is_admin() || ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		add_action( 'wp_ajax_nopriv_notice_install_event_ticketsr', [ $this, 'ajax_handle_notice_install_event_tickets' ] );
		add_action( 'wp_ajax_notice_install_event_tickets', [ $this, 'ajax_handle_notice_install_event_tickets' ] );

		$this->assets();
		$this->notice_install();
		$this->notice_activate();
	}


	/**
	 * Register `Install` notice assets.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function assets() {
		$plugin = tribe( 'tec.main' );

		tribe_asset(
			$plugin,
			'tribe-events-admin-notice-install-event-tickets-js',
			'admin/notice-install-event-tickets.js',
			[
				'jquery',
				'tribe-common',
			],
			null,
			[
				'localize' => [
					[
						'name' => 'TribeEventsAdminNoticeInstall',
						'data' => [ 'ajaxurl' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ), ]
					],
				],
				'groups'   => [
					self::$assets_group,
				],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-admin-notice-install-event-tickets-css',
			'admin/notice-install-event-tickets.css',
			[ 'tec-variables-full' ],
			[
				'admin_enqueue_scripts',
				'wp_enqueue_scripts',
			],
			[
				'groups' => self::$assets_group,
			]
		);
	}

	/**
	 * Get the plugin path for `Event Tickets`, by default.
	 *
	 * @since TBD
	 *
	 * @param string $slug The plugin slug.
	 *
	 * @return string $path The plugin path.
	 */
	protected function get_plugin_path( $slug = '' ): string {
		if ( empty( $slug ) ) {
			$slug = self::$plugin_slug;
		}

		return $slug . '/' . $slug . '.php';
	}

	/**
	 * Checks if `Event Tickets` is installed.
	 *
	 * @since TBD
	 *
	 * @param string $slug The plugin slug.
	 *
	 * @return boolean True if active
	 */
	public function is_installed( $slug = '' ): bool {
		$installed_plugins = get_plugins();

		return array_key_exists( $this->get_plugin_path( $slug ), $installed_plugins ) || in_array( $this->get_plugin_path( $slug ), $installed_plugins, true );
	}

	/**
	 * Checks if `Event Tickets` is active.
	 *
	 * @since TBD
	 *
	 * @param string $slug The plugin slug.
	 *
	 * @return boolean True if active.
	 */
	public function is_active( $slug = '' ): bool {
		return is_plugin_active( $this->get_plugin_path( $slug ) );
	}

	/**
	 * Check if we're on the classic "Install Plugin" page.
	 *
	 * @since TBD
	 *
	 * @return boolean
	 */
	public function is_install_plugin_page(): bool {
		return 'install-plugin' === tribe_get_request_var( 'action' );
	}

	/**
	 * Should the `Install` notice be displayed?
	 *
	 * @since TBD
	 *
	 * @return bool True if the install notice should be displayed.
	 */
	public function should_display_notice_install() {
		return ! $this->is_installed()
			&& empty( tribe_get_request_var( 'welcome-message-the-events-calendar' ) )
			&& ! $this->is_install_plugin_page();
	}

	/**
	 * Should the `Activate` notice be displayed?
	 *
	 * @since TBD
	 *
	 * @return bool True if the activate notice should be displayed.
	 */
	public function should_display_notice_activate() {
		return $this->is_installed() && ! $this->is_active() && ! $this->is_install_plugin_page();
	}

	/**
	 * Install notice for `Event Tickets`.
	 *
	 * @since TBD
	 */
	public function notice_install() {
		if ( ! $this->should_display_notice_install() ) {
			return '';
		}

		$this->enqueue_assets();

		$html = $this->get_template()->template(
			'notices/install-event-tickets',
			$this->get_template_data(),
			false
		);

		tribe_notice(
			'event-tickets-install',
			$html,
			[
				'dismiss' => true,
				'type'    => 'warning',
			]
		);
	}

	/**
	 * Enqueue assets required for the notice.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function enqueue_assets() {
		wp_enqueue_style( 'wp-components' );
		tribe_asset_enqueue_group( self::$assets_group );
	}

	/**
	 * Activate notice for `Event Tickets`.
	 *
	 * @since TBD
	 */
	public function notice_activate() {
		if ( ! $this->should_display_notice_activate() ) {
			return '';
		}

		$this->enqueue_assets();

		$args = [
			'description'  => __( 'You\'re almost there! Activate Event Tickets to manage attendee registration and ticket sales to your events, for free.', 'the-events-calendar' ),
			'button_label' => __( 'Activate Event Tickets', 'the-events-calendar' ),
			'action'       => 'activate',
		];

		$html = $this->get_template()->template(
			'notices/install-event-tickets',
			$this->get_template_data( $args ),
			false
		);

		tribe_notice(
			'event-tickets-activate',
			$html,
			[
				'dismiss' => true,
				'type'    => 'warning',
			]
		);
	}

	/**
	 * Data for the notice template.
	 *
	 * @since TBD
	 *
	 * @param array $args Array with arguments to override the defaults.
	 *
	 * @return array The template args.
	 */
	private function get_template_data( $args = [] ): array {
		$admin_url    = is_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'admin.php' );
		$redirect_url = add_query_arg( [ 'page' => 'tec-tickets-settings' ], $admin_url );

		$defaults = [
			'plugin_slug'      => self::$plugin_slug,
			'action'           => 'install',
			'title'            => __( 'Start selling tickets to your Events', 'the-events-calendar' ),
			'description'      => __( 'Get Event Tickets to manage attendee registration and ticket sales to your events, for free.', 'the-events-calendar' ),
			'button_label'     => __( 'Install Event Tickets', 'the-events-calendar' ),
			'tickets_logo'     => Tribe__Main::instance()->plugin_url . '/src/resources/images/tec-tickets-logo.svg',
			'ajax_nonce'       => wp_create_nonce( 'tribe_events_admin_notice_install' ),
			'redirect_url'     => $redirect_url,
			'installing_label' => __( 'Installing...', 'the-events-calendar' ),
			'installed_label'  => __( 'Installed!', 'the-events-calendar' ),
			'activating_label' => __( 'Activating...', 'the-events-calendar' ),
			'activated_label'  => __( 'Activated!', 'the-events-calendar' ),

		];

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Handle AJAX response for the notice actions.
	 *
	 * @return void
	 */
	public function ajax_handle_notice_install_event_tickets() {
		if ( ! check_ajax_referer( 'tribe_events_admin_notice_install', 'nonce', false ) ) {
			$response['message'] = wpautop( __( 'Insecure request.', 'the-events-calendar' ) );

			wp_send_json_error( $response );
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( [ 'message' => wpautop( __( 'Security Error, Need higher Permissions to install plugin.' ), 'the-events-calendar' ) ] );
		}

		$vars    = $_REQUEST;
		$success = false;

		if ( 'install' === $vars['request'] ) {
			$success = $this->action_install( $vars );
		} elseif ( 'activate' === $vars['request'] ) {
			$success = $this->action_activate( $vars );
		}

		if ( false === $success ) {
			$install_url = wp_nonce_url(
				self_admin_url( 'update.php?action=install-plugin&plugin=' . $vars['slug'] ),
				'install-plugin_' . $vars['slug']
			);
			$message     = sprintf(
				/* Translators: %1$s - opening link tag, %2$s - closing link tag. */
				__( 'There was an error and plugin could not be installed, %1$splease install manually%2$s.', 'the-events-calendar' ),
				'<a href="' . esc_url( $install_url ) . '">',
				'</a>',
			);

			wp_send_json_error( [ 'message' => wpautop( $message ) ] );
		} else {
			wp_send_json_success( [ 'message' => __( 'Success.', 'the-events-calendar' ) ] );
		}
	}

	/**
	 * Action to install & activate plugin.
	 *
	 * @since TBD
	 *
	 * @param array $vars The AJAX vars.
	 *
	 * @return bool $success True if the plugin was successfully installed and activated.
	 */
	public function action_install( $vars ): bool {
		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}
		if ( ! class_exists( 'WP_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		$api = plugins_api(
			'plugin_information',
			[
				'slug'   => $vars['slug'],
				'fields' => [
					'short_description' => false,
					'sections'          => false,
					'requires'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'compatibility'     => false,
					'homepage'          => false,
					'donate_link'       => false,
				],
			]
		);
		if ( ! is_wp_error( $api ) ) {
			$upgrader  = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );
			$installed = $upgrader->install( $api->download_link );

			if ( $installed ) {
				$activate = activate_plugin( $this->get_plugin_path( $vars['slug'] ), '', false, true );
				$success  = ! is_wp_error( $activate );
			} else {
				$success = false;
			}
		} else {
			$success = false;
		}

		return $success;
	}

	/**
	 * Action to activate the plugin.
	 *
	 * @since TBD
	 *
	 * @param array $vars The AJAX vars.
	 *
	 * @return bool $success True if the plugin was successfully activated.
	 */
	public function action_activate( $vars ) {
		if ( ! $this->is_installed( $vars['slug'] ) ) {
			return $this->action_install( $vars );
		}

		if ( $this->is_active( $vars['slug'] ) ) {
			return true;
		}

		$activate = activate_plugin( $this->get_plugin_path( $vars['slug'] ), '', false, true );

		return ! is_wp_error( $activate );
	}

	/**
	 * Get template object.
	 *
	 * @since TBD
	 *
	 * @return \Tribe__Template
	 */
	public function get_template() {
		if ( empty( $this->template ) ) {
			$this->template = new Tribe__Template();
			$this->template->set_template_origin( tribe( 'tec.main' ) );
			$this->template->set_template_folder( 'src/admin-views' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
	}
}
