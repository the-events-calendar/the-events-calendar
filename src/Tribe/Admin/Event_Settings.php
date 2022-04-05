<?php

/**
 * Manages the admin settings UI in relation to events configuration.
 */
use Tribe__Events__Main as Plugin;
use Tribe\Admin\Troubleshooting as Troubleshooting;


class Tribe__Events__Admin__Event_Settings {

	/**
	 * The Events Calendar settings page slug.
	 *
	 * @var string
	 */
	public static $settings_page_id = 'tec-events-settings';

	/**
	 * Sets up the display of timezone-related settings and listeners to deal with timezone-update
	 * requests (which are initiated from within the settings screen).
	 */
	public function __construct() {
		add_action( 'tribe_settings_do_tabs', [ $this, 'settings_ui' ] );
		add_action( 'admin_menu', [ $this, 'add_admin_pages' ] );
		add_action( 'network_admin_menu', [ $this, 'maybe_add_network_settings_page' ] );
		add_action( 'tribe_settings_do_tabs', [ $this, 'do_network_settings_tab' ], 400 );

		add_filter( 'tribe_settings_page_title', [ $this, 'settings_page_title' ] );
		add_filter( 'tec_settings_tab_url', [ $this, 'filter_settings_tab_url' ], 50, 3 );
		add_filter( 'tec_admin_pages_with_tabs', [ $this, 'add_to_pages_with_tabs' ], 20, 1 );
		add_filter( 'tribe_settings_page_url', [ $this, 'filter_settings_page_url' ], 50, 3 );
		add_filter( 'tec_admin_footer_text', [ $this, 'admin_footer_text_settings' ] );
		add_filter( 'tribe-events-save-network-options', [ $this, 'maybe_hijack_save_network_settings' ], 10, 2 );
	}

	/**
	 * Returns the main admin settings URL.
	 *
	 * @param array $args Arguments to pass to the URL.
	 *
	 * @return string
	 */
	public function get_url( array $args = [] ) {
		$defaults = [
			'page' => self::$settings_page_id,
		];

		if ( ! is_network_admin() ) {
			$defaults['post_type'] = Plugin::POSTTYPE;
		}

		// Allow the link to be "changed" on the fly.
		$args = wp_parse_args( $args, $defaults );

		$wp_url = is_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'edit.php' );

		// Keep the resulting URL args clean.
		$url = add_query_arg( $args, $wp_url );

		/**
		 * Filters the admin settings URL.
		 *
		 * @since TBD
		 *
		 * @param string $url The admin settings URL.
		 */
		return apply_filters( 'tec_events_settings_url', $url );
	}

	/**
	 * Adds The Events Calendar settings page to the pages configuration.
	 *
	 * @since TBD
	 *
	 * @param array $pages An array containing the slug of the pages with tabs.
	 *
	 * @return array $pages The modified array containing the pages with tabs.
	 */
	public function add_to_pages_with_tabs( $pages ) {
		$pages[] = self::$settings_page_id;

		return $pages;
	}

	/**
	 * Filter The Events CAlendar Settings page title
	 *
	 * @param string $title The title of the settings page.
	 *
	 * @return string The modified title of the settings page..
	 */
	public function settings_page_title( $title ) {
		if ( ! $this->is_tec_events_settings() ) {
			return $title;
		}

		return sprintf(
			// Translators: %s is the `Events` in plural.
			__( '%s Settings', 'the-events-calendar' ),
			tribe_get_event_label_plural( 'tec_events_settings_title' )
		);
	}

	/**
	 * Defines wether the current page is The Events Calendar Settings page.
	 *
	 * @since TBD
	 *
	 * @return boolean
	 */
	public function is_tec_events_settings() {
		$admin_pages = tribe( 'admin.pages' );
		$admin_page  = $admin_pages->get_current_page();

		return ! empty( $admin_page ) && self::$settings_page_id === $admin_page;
	}

	/**
	 * Get the slug for The Events Calendar page/menu item.
	 *
	 * @return void
	 */
	public function get_tec_events_menu_slug() {
		// If the `tribe_events` post type is not registered, return generic slug.
		if ( ! post_type_exists( Plugin::POSTTYPE ) ) {
			return 'tec-events';
		}

		$menu_slug = add_query_arg(
			[
				'post_type' => Plugin::POSTTYPE,
			],
			'edit.php'
		);

		return $menu_slug;
	}

	/**
	 * Adds the menu and pages for The Events Calendar.
	 *
	 * @since TBD
	 */
	public function add_admin_pages() {
		$admin_pages = tribe( 'admin.pages' );

		$this->maybe_register_events_menu();

		$admin_pages->register_page(
			[
				'id'       => self::$settings_page_id,
				'parent'   => $this->get_tec_events_menu_slug(),
				'title'    => esc_html__( 'Settings', 'tribe-common' ),
				'path'     => self::$settings_page_id,
				'callback' => [
					tribe( 'settings' ),
					'generatePage'
				],
			]
		);

		$admin_pages->register_page(
			[
				'id'       => 'tec-events-help',
				'parent'   => $this->get_tec_events_menu_slug(),
				'title'    => esc_html__( 'Help', 'the-events-calendar' ),
				'path'     => 'tec-events-help',
				'callback' => [
					tribe( 'settings.manager' ),
					'do_help_tab',
				],
			]
		);

		$this->maybe_add_troubleshooting();
		$this->maybe_add_app_shop();
	}

	/**
	 * Maybe add network settings page for The Events Calendar.
	 *
	 * @since TBD
	 */
	public function maybe_add_network_settings_page() {
		$admin_pages = tribe( 'admin.pages' );
		$settings    = Tribe__Settings::instance();

		if ( ! is_plugin_active_for_network( 'the-events-calendar/the-events-calendar.php' ) ) {
			return;
		}

		$admin_pages->register_page(
			[
				'id'         => self::$settings_page_id,
				'parent'     => 'settings.php',
				'title'      => esc_html__( 'Event Settings', 'the-events-calendar' ),
				'path'       => self::$settings_page_id,
				'capability' => $admin_pages->get_capability( 'manage_network_options' ),
				'callback'   => [
					$settings,
					'generatePage',
				],
			]
		);
	}

	/**
	 * Maybe add troubleshooting page for The Events Calendar
	 *
	 * @since TBD
	 */
	public function maybe_add_troubleshooting() {
		$admin_pages = tribe( 'admin.pages' );

		if ( ! Tribe__Settings::instance()->should_setup_pages() ) {
			return;
		}

		$troubleshooting = tribe( Troubleshooting::class );

		$admin_pages->register_page(
			[
				'id'         => $troubleshooting::MENU_SLUG,
				'parent'     => $this->get_tec_events_menu_slug(),
				'title'      => esc_html__( 'Troubleshooting', 'the-events-calendar' ),
				'path'       => $troubleshooting::MENU_SLUG,
				'capability' => $troubleshooting->get_required_capability(),
				'callback'   => [
					$troubleshooting,
					'do_menu_page',
				],
			]
		);
	}

	/**
	 * Maybe add troubleshooting page for The Events Calendar
	 *
	 * @since TBD
	 */
	public function maybe_add_app_shop() {
		$admin_pages = tribe( 'admin.pages' );

		if ( ! Tribe__Settings::instance()->should_setup_pages() ) {
			return;
		}

		$app_shop = tribe( Tribe__App_Shop::class );

		$admin_pages->register_page(
			[
				'id'         => $app_shop::MENU_SLUG,
				'parent'     => $this->get_tec_events_menu_slug(),
				'title'      => esc_html__( 'Event Add-Ons', 'event-tickets' ),
				'path'       => $app_shop::MENU_SLUG,
				'capability' => 'install_plugins',
				'callback'   => [
					$app_shop,
					'do_menu_page',
				],
			]
		);
	}

	/**
	 * Filters the settings tab URL.
	 *
	 * @since TBD
	 *
	 * @param string $url The URL of the settings tab.
	 * @param string $page The slug of the page.
	 * @param string $tab The slug of the settings tab.
	 *
	 * @return string $url The modified URL of the tab.
	 */
	public function filter_settings_tab_url( $url, $page, $tab ) {
		// Bail if `tribe_events` doesn't exist.
		if ( ! post_type_exists( Plugin::POSTTYPE ) ) {
			return $url;
		}

		if ( self::$settings_page_id !== $page ) {
			return $url;
		}

		$current_page = is_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'edit.php' );
		$url          = add_query_arg(
			[
				'post_type' => Plugin::POSTTYPE,
				'page'      => $page,
				'tab'       => $tab,
			],
			$current_page
		);

		return $url;
	}

	/**
	 * Filters the settings page URL.
	 *
	 * @since TBD
	 *
	 * @param string $url The URL of the settings page.
	 * @param string $page The slug of the page.
	 * @param string $tab The slug of the settings tab.
	 *
	 * @return string $url The modified URL of the settings.
	 */
	public function filter_settings_page_url( $url, $page, $tab ) {
		// Bail if `tribe_events` doesn't exist or if we're on the network settings page.
		if ( ! post_type_exists( Plugin::POSTTYPE ) || is_network_admin() ) {
			return $url;
		}

		if ( self::$settings_page_id !== $page ) {
			return $url;
		}

		$current_page = admin_url( 'edit.php' );
		$url          = add_query_arg(
			[
				'post_type' => Plugin::POSTTYPE,
				'page'      => $page,
				'tab'       => $tab,
			],
			$current_page
		);

		return $url;
	}

	/**
	 * Maybe register the events menu.
	 *
	 * @return void
	 */
	public function maybe_register_events_menu() {
		// Bail if the `tribe_events` post type exists.
		if ( post_type_exists( Plugin::POSTTYPE ) ) {
			return;
		}

		$admin_pages = tribe( 'admin.pages' );

		$admin_pages->register_page(
			[
				'id'       => 'tec-events', // @todo: this to constant (?)
				'path'     => $this->get_tec_events_menu_slug(), //'tec-events',
				'title'    => tribe_get_event_label_plural( 'tec_events_settings_menu' ),
				'icon'     => 'dashicons-calendar',
				'position' => 6,
			]
		);
	}

	/**
	 * TBD
	 *
	 * @since TBD
	 */
	public function settings_ui( $admin_page ) {
		if ( ! empty( $admin_page ) && self::$settings_page_id !== $admin_page ) {
			return;
		}

		include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/tribe-options-general.php';
		include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/tribe-options-display.php';

		new Tribe__Settings_Tab( 'general', esc_html__( 'General', 'tribe-common' ), $generalTab );
		new Tribe__Settings_Tab( 'display', esc_html__( 'Display', 'tribe-common' ), $displayTab );
	}

	/**
	 * TBD
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function get_settings_array() {
		$plugin_path = Tribe__Tickets__Main::instance()->plugin_path;
		include $plugin_path . 'src/admin-views/tribe-options-tickets.php';

		/** @var array $tickets_tab Set in the file included above*/
		return $tickets_tab;
	}

	/**
	 * Generate network settings page for The Events Calendar.
	 *
	 * @since TBD
	 */
	public function do_network_settings_tab( $admin_page ) {
		if ( ! empty( $admin_page ) && self::$settings_page_id !== $admin_page ) {
			return;
		}

		include_once Tribe__Events__Main::instance()->plugin_path . 'src/admin-views/tribe-options-network.php';

		new Tribe__Settings_Tab( 'network', esc_html__( 'Network', 'the-events-calendar' ), $networkTab );
	}

	/**
	 * Add The Events Calendar admin footer text.
	 *
	 * @since TBD
	 *
	 * @param string $footer_text The admin footer text.
	 * @param string $footer_text The admin footer text, maybe modified.
	 */
	public function admin_footer_text_settings( $footer_text ) {
		$admin_pages = tribe( 'admin.pages' );
		$admin_page  = $admin_pages->get_current_page();

		if ( ! empty( $admin_page ) && self::$settings_page_id !== $admin_page ) {
			return $footer_text;
		}

		// Translators: %1$s: Opening `<a>` to The Events Calendar rating page. %2$s: Closing `</a>` tag. %3$s: Five stars.
		$review_text = esc_html__( 'Rate %1$sThe Events Calendar%2$s %3$s', 'the-events-calendar' );
		$review_url  = 'https://wordpress.org/support/plugin/the-events-calendar/reviews/?filter=5';

		$footer_text = sprintf(
			$review_text,
			'<strong>',
			'</strong>',
			'<a href="' . $review_url . '" target="_blank" rel="noopener noreferrer" class="tribe-rating">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
		);

		return $footer_text;
	}

	/**
	 * Get Events settings tab IDs.
	 *
	 * @since TBD
	 *
	 * @return array $tabs Array of tabs IDs for the Events settings page.
	 */
	public function get_events_settings_tabs_ids() {
		$tabs = [
			'general',
			'display',
		];

		/**
		 * Filters the events settings tab IDs.
		 *
		 * @since TBD
		 *
		 * @param array $tabs Array of tabs IDs for the Events settings page.
		 */
		return apply_filters( 'tec_events_settings_tabs_ids', $tabs );
	}

	/**
	 * Maybe hijack the saving for the network settings page, when not in TEC network settings.
	 * The purpose is to merge the settings between plugins.
	 *
	 * @since TBD
	 *
	 * @param array  $options Formatted the same as from get_options().
	 * @param string $admin_page The admin page being saved.
	 *
	 * @return array $options Formatted the same as from get_options(), maybe modified.
	 */
	public function maybe_hijack_save_network_settings( $options, $admin_page ) {
		// If we're saving the network settings page for TEC, bail.
		if ( ! empty( $admin_page ) && self::$settings_page_id == $admin_page ) {
			return $options;
		}

		$tec_tabs = $this->get_events_settings_tabs_ids();

		// Iterate over the TEC settings tab ids and merge the network settings.
		foreach ( $tec_tabs as $tab => $key ) {
			if ( in_array( $key, $options['hideSettingsTabs'] ) ) {
				$_POST['hideSettingsTabs'][] = $key;
				$options['hideSettingsTabs'] = $key;
			}
		}

		return $options;
	}
}
