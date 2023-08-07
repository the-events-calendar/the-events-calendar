<?php
namespace Tribe\Events\Admin;

/**
 * Manages the admin settings UI in relation to events configuration.
 */
use Tribe__App_Shop;
use Tribe__Settings;
use Tribe__Settings_Tab;
use Tribe__Events__Main as Plugin;
use Tribe\Admin\Troubleshooting as Troubleshooting;

class Settings {

	/**
	 * The Events Calendar settings page slug.
	 *
	 * @var string
	 */
	public static $settings_page_id = 'tec-events-settings';

	/**
	 * Settings tabs
	 */
	public $tabs = [];

	/**
	 * Returns the main admin settings URL.
	 *
	 * @param array $args Arguments to pass to the URL.
	 *
	 * @return string The URL to the admin settings page.
	 */
	public function get_url( array $args = [] ) {
		$defaults = [
			'page' => static::$settings_page_id,
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
		 * @since 5.15.0
		 *
		 * @param string $url The admin settings URL.
		 */
		return apply_filters( 'tec_events_settings_url', $url );
	}

	/**
	 * Adds The Events Calendar settings page to the pages configuration.
	 *
	 * @since 5.15.0
	 *
	 * @param array $pages An array containing the slug of the pages with tabs.
	 *
	 * @return array $pages The modified array containing the pages with tabs.
	 */
	public function add_to_pages_with_tabs( $pages ) {
		$pages[] = static::$settings_page_id;

		return $pages;
	}

	/**
	 * Filter The Events CAlendar Settings page title
	 *
	 * @param string $title The title of the settings page.
	 *
	 * @return string The modified title of the settings page.
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
	 * @since 5.15.0
	 *
	 * @return boolean Whether the current page is The Events Calendar Settings page.
	 */
	public function is_tec_events_settings() {
		$admin_pages = tribe( 'admin.pages' );
		$admin_page  = $admin_pages->get_current_page();

		return ! empty( $admin_page ) && static::$settings_page_id === $admin_page;
	}

	/**
	 * Get the icon for The Events Calendar menu item.
	 *
	 * @since 5.15.0
	 *
	 * @return string
	 */
	public function get_menu_icon() {
		$icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" fill="#9ba2a6" viewBox="0 0 13.24 15.4"><defs><style>.cls-1{fill-rule:evenodd;}</style></defs><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path class="cls-1" d="M12.89,6.1,11.54,2.2l0-.06A1.85,1.85,0,0,0,9.14,1.06l-.73.26L8,.29v0A.45.45,0,0,0,7.47,0a.43.43,0,0,0-.25.23A.45.45,0,0,0,7.2.6l.37,1L2.75,3.39l-.36-1,0-.05a.44.44,0,0,0-.56-.22.44.44,0,0,0-.26.57l.36,1L1.17,4A1.86,1.86,0,0,0,.11,6.33L3.19,15a.66.66,0,0,0,.61.4.59.59,0,0,0,.23,0L7.4,14.13l0,0,.1,0a5,5,0,0,0,2-2.47c.11-.24.21-.49.31-.77l.27-.72.07-.19a4.3,4.3,0,0,0,2-.39,3.13,3.13,0,0,1-1.72,2.3.43.43,0,0,0-.25.23.45.45,0,0,0,0,.34.42.42,0,0,0,.23.26.45.45,0,0,0,.34,0C13.13,11.87,13.72,8.64,12.89,6.1Zm-.56,1.81a.79.79,0,0,1-.25.58A2.85,2.85,0,0,1,10,9c-.39,0-.51.22-.68.67L9,10.52c-.1.26-.19.49-.29.71a4.32,4.32,0,0,1-1.59,2L3.94,14.44,1.7,8.12l9.74-3.46.63,1.82a5.11,5.11,0,0,1,.26,1.35V7.9Z"/></g></g></svg>' );

		/**
		 * Filter the menu icon for The Events Calendar in the WordPress admin.
		 *
		 * @since 5.15.0
		 *
		 * @param string $icon The menu icon for The Events Calendar in the WordPress admin.
		 */
		return apply_filters( 'tec_events_menu_icon', $icon );
	}

	/**
	 * Get the slug for The Events Calendar page/menu item.
	 *
	 * @since 5.15.0
	 *
	 * @return string $menu_slug The slug for the The Events Calendar page/menu item.
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
	 * @since 5.15.0
	 */
	public function add_admin_pages() {
		$admin_pages = tribe( 'admin.pages' );

		$this->maybe_register_events_menu();

		$admin_pages->register_page(
			[
				'id'       => static::$settings_page_id,
				'parent'   => $this->get_tec_events_menu_slug(),
				'title'    => esc_html__( 'Settings', 'the-events-calendar' ),
				'path'     => static::$settings_page_id,
				'callback' => [
					tribe( 'settings' ),
					'generatePage',
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
	 * @since 5.15.0
	 */
	public function maybe_add_network_settings_page() {
		$admin_pages = tribe( 'admin.pages' );
		$settings    = Tribe__Settings::instance();

		if ( ! is_plugin_active_for_network( 'the-events-calendar/the-events-calendar.php' ) ) {
			return;
		}

		$admin_pages->register_page(
			[
				'id'         => static::$settings_page_id,
				'parent'     => 'settings.php',
				'title'      => esc_html__( 'Event Settings', 'the-events-calendar' ),
				'path'       => static::$settings_page_id,
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
	 * @since 5.15.0
	 */
	public function maybe_add_troubleshooting() {
		$admin_pages = tribe( 'admin.pages' );

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
	 * @since 5.15.0
	 */
	public function maybe_add_app_shop() {
		$admin_pages = tribe( 'admin.pages' );

		$app_shop = tribe( Tribe__App_Shop::class );

		$admin_pages->register_page(
			[
				'id'         => $app_shop::MENU_SLUG,
				'parent'     => $this->get_tec_events_menu_slug(),
				'title'      => esc_html__( 'Event Add-Ons', 'the-events-calendar' ),
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
	 * @since 5.15.0
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

		if ( static::$settings_page_id !== $page ) {
			return $url;
		}

		$current_page = is_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'edit.php' );
		$url          = add_query_arg(
			[
				'page'      => $page,
				'tab'       => $tab,
			],
			$current_page
		);

		if ( ! is_network_admin() ) {
			$url = add_query_arg(
				[
					'post_type' => Plugin::POSTTYPE,
				],
				$url
			);
		}

		return $url;
	}

	/**
	 * Filters the settings page URL.
	 *
	 * @since 5.15.0
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

		if ( static::$settings_page_id !== $page ) {
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
				'id'       => 'tec-events',
				'path'     => $this->get_tec_events_menu_slug(),
				'title'    => tribe_get_event_label_plural( 'tec_events_settings_menu' ),
				'icon'     => $this->get_menu_icon(),
				'position' => 6,
			]
		);
	}

	/**
	 * Do the basic main settings page.
	 *
	 * @since 5.15.0
	 *
	 * @param string $admin_page The slug of the admin page.
	 */
	public function settings_ui( $admin_page ) {
		if ( ! empty( $admin_page ) && self::$settings_page_id !== $admin_page ) {
			return;
		}

		include_once tribe( 'tec.main' )->plugin_path . 'src/admin-views/tribe-options-general.php';
		include_once tribe( 'tec.main' )->plugin_path . 'src/admin-views/tribe-options-display.php';

		$this->tabs['general'] = new Tribe__Settings_Tab( 'general', esc_html__( 'General', 'the-events-calendar' ), $general_tab );
		$this->tabs['display'] = new Tribe__Settings_Tab( 'display', esc_html__( 'Display', 'the-events-calendar' ), $tec_events_display_tab );
		add_filter( 'tribe_settings_tabs', [ $this, 'sort_tabs' ], 100, 2 );
	}

	/**
	 * Sort the tabs, forcing some to the front and some to the end.
	 *
	 * @since 6.0.5
	 *
	 * @param array $tabs        The array of tabs.
	 * @param string $admin_page The ID of the admin page we are on.
	 *
	 * @todo deprecate this when we can get the tab priority working correctly, globally.
	 *
	 * @return array             The sorted array of tabs.
	 */
	public function sort_tabs( $tabs, $admin_page ): array {
		if ( $admin_page !== 'tec-events-settings' ) {
			return $tabs;
		}

		// Ensure these are the first tabs.
		$first   = [ 'general', 'display' ];
		$tabs   = array_merge( array_flip( $first ), $tabs );

		// Ensure these are the last tabs.
		$last = [ 'licenses', 'addons', 'imports' ];


		foreach( $last as $sort ) {
			if ( ! isset( $tabs[ $sort ] ) ) {
				continue;
			}
			// TL/DR: grab each tab, unset it and append it to the end of the array in order.
			$temp = $tabs[ $sort ];
			unset( $tabs[ $sort ] );
			$tabs[ $sort ] = $temp;
		}

		return $tabs;
	}

	/**
	 * Generate network settings page for The Events Calendar.
	 *
	 * @since 5.15.0
	 *
	 * @param string $admin_page The slug of the admin page.
	 */
	public function do_network_settings_tab( $admin_page ): void {
		if ( ! empty( $admin_page ) && self::$settings_page_id !== $admin_page ) {
			return;
		}

		include_once tribe( 'tec.main' )->plugin_path . 'src/admin-views/tribe-options-network.php';

		$this->tabs['network'] = new Tribe__Settings_Tab( 'network', esc_html__( 'Network', 'the-events-calendar' ), $networkTab );
	}

	/**
	 * Add The Events Calendar admin footer text.
	 *
	 * @since 5.15.0
	 *
	 * @param string $footer_text The admin footer text.
	 * @return string $footer_text The admin footer text, maybe modified.
	 */
	public function admin_footer_text_settings( $footer_text ) {
		$admin_pages = tribe( 'admin.pages' );
		$admin_page  = $admin_pages->get_current_page();

		if ( ! empty( $admin_page ) && static::$settings_page_id !== $admin_page ) {
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
	 * @since 5.15.0
	 *
	 * @return array $tabs Array of tabs IDs for the Events settings page.
	 */
	public function get_events_settings_tabs_ids(): array {
		$tabs = [
			'general',
			'display',
			'addons',
			'imports',
		];

		/**
		 * Filters the events settings tab IDs.
		 *
		 * @since 5.15.0
		 *
		 * @param array $tabs Array of tabs IDs for the Events settings page.
		 */
		return apply_filters( 'tec_events_settings_tabs_ids', $tabs );
	}

	/**
	 * Maybe hijack the saving for the network settings page, when not in TEC network settings.
	 * The purpose is to merge the settings between plugins.
	 *
	 * @since 5.15.0
	 *
	 * @param array  $options Formatted the same as from get_options().
	 * @param string $admin_page The admin page being saved.
	 *
	 * @return array $options Formatted the same as from get_options(), maybe modified.
	 */
	public function maybe_hijack_save_network_settings( $options, $admin_page ): array {
		// If we're saving the network settings page for TEC, bail.
		if ( ! empty( $admin_page ) && static::$settings_page_id == $admin_page ) {
			return $options;
		}

		if ( ! is_plugin_active_for_network( 'the-events-calendar/the-events-calendar.php' ) ) {
			return $options;
		}

		$tec_tabs = $this->get_events_settings_tabs_ids();
		$form_options['hideSettingsTabs'] = $_POST['hideSettingsTabs'];

		// Iterate over the TEC settings tab ids and merge the network settings.
		foreach ( $tec_tabs as $tab => $key ) {
			if ( in_array( $key, $options['hideSettingsTabs'] ) ) {
				$_POST['hideSettingsTabs'][]        = $key;
				$form_options['hideSettingsTabs'][] = $key;
			}
		}

		return $form_options;
	}

	/**
	 * Filter the event type args to add the menu icon.
	 *
	 * @since 5.15.0
	 *
	 * @param array $args The default arguments for the event type.
	 */
	public function filter_event_type_args( $args ): array {
		$args['menu_icon'] = $this->get_menu_icon();

		return $args;
	}

	/**
	 * Adds disabled ECP views to the views list as a "teaser".
	 *
	 * @since 6.0.5
	 *
	 * @param string $output The HTML output for the Views checkboxes.
	 *
	 * @return string        The modified HTML output.
	 */
	public function tease_premium_views( $output ): string {
		// If ECP is installed, we don't need to tease.
		if ( defined( 'EVENTS_CALENDAR_PRO_FILE' ) ) {
			return $output;
		}

		// Honor the "hide upsells" functionality.
		if ( tec_should_hide_upsell() ) {
			return $output;
		}

		/* Translators: These View terms should match the ones in Events Calendar PRO. */
		$views = [
			'summary' => _x( 'Summary', 'Label for the Summary View checkbox.', 'the-events-calendar' ),
			'photo'   => _x( 'Photo', 'Label for the Photo View checkbox.', 'the-events-calendar' ),
			'week'    => _x( 'Week', 'Label for the Week View checkbox.', 'the-events-calendar' ),
			'map'     => _x( 'Map', 'Label for the Map View checkbox.', 'the-events-calendar' ),
		];

		$tooltip_label = _x( 'PRO', 'The label for the premium view indicator.', 'the-events-calendar' );
		$tooltip_title = _x(
			'Get Events Calendar Pro to use this View.',
			'The title (hover text) for the premium view indicator.',
			'the-events-calendar'
		);

		// Loop through the term array above and create teaser checkboxes.
		ob_start();

		foreach( $views as $name => $label ) { ?>
			<label title="Summary" class="tec-disabled">
				<input type="checkbox" name="tribeEnableViews[]" value="<?php echo esc_attr( $name ) ?>" disabled>
				<?php echo esc_attr( $label ) ?>
				<a
					href="https://evnt.is/1bb-"
					class="tec-settings-teaser-pill"
					title="<?php echo esc_attr( $tooltip_title ); ?>"
				><?php echo esc_html( $tooltip_label ); ?>
				</a>
			</label>
		<?php }

		$ecp_string = ob_get_clean();

		// Insert the teaser checkboxes.
		$pattern    = '/label><p/m';
		$subst      = 'label>' . $ecp_string . '<p';
		$output     = preg_replace($pattern, $subst, $output, 1);

		return $output;
	}

	/**
	 * Initialize the addons api settings tab.
	 *
	 * @since 5.15.0 Added check to see if we are on TEC settings page.
	 * @since 6.0.5  Moved to Settings class.
	 */
	public function do_addons_api_settings_tab( $admin_page ): void {
		// Bail if we're not on TEC settings.
		if ( ! empty( $admin_page ) && static::$settings_page_id !== $admin_page ) {
			return;
		}

		include_once tribe( 'tec.main' )->plugin_path . 'src/admin-views/tribe-options-addons-api.php';
	}

	/**
	 * should we show the upgrade nags?
	 *
	 * @since 4.9.12
	 * @since 6.0.5	 Moved to Settings class.
	 *
	 * @return boolean
	 */
	public function show_upgrade(): bool {
		// This allows sub-site admins to utilize this setting when their access to plugins is restricted.
		$can_show_tab = current_user_can( 'activate_plugins' ) || ( is_multisite() && current_user_can( 'customize' ) );

		/**
		 * Provides an opportunity to override the decision to show or hide the upgrade tab.
		 *
		 * Normally it will only show if the current user has the "activate_plugins" capability
		 * and there are some currently-activated premium plugins.
		 *
		 * @since 4.9.12
		 * @since 6.0.0 This filter now controls only the capability to show the Upgrade tab.
		 *
		 * @param bool $can_show_tab True or False for showing the Upgrade Tab.
		 */
		$can_show_tab = apply_filters( 'tribe_events_show_upgrade_tab', $can_show_tab  );

		if ( ! $can_show_tab ) {
			return false;
		}

		/**
		 * Filters whether the Upgrade Tab has actually any content to show or not.
		 *
		 * @since 6.0.0
		 *
		 * @param bool $has_content Whether the tab has any content to show or not.
		 */
		if ( ! apply_filters( 'tec_events_upgrade_tab_has_content', false ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Create the upgrade tab
	 *
	 * @since 4.9.12
	 * @since 5.15.0 Added check to see if we are on TEC settings page.
	 * @since 6.0.5  Moved to Settings class.
	 */
	public function do_upgrade_tab( $admin_page ): void {
		// Bail if we're not on TEC settings.
		if ( ! empty( $admin_page ) && static::$settings_page_id !== $admin_page ) {
			return;
		}

		if ( ! $this->show_upgrade() ) {
			return;
		}

		tribe_asset(
			Plugin::instance(),
			'tribe-admin-upgrade-page',
			'admin-upgrade-page.js',
			[ 'tribe-common' ],
			'admin_enqueue_scripts',
			[
				'localize' => [
					'name' => 'tribe_upgrade',
					'data' => [
						'v2_is_enabled' => tribe_events_views_v2_is_enabled(),
						'button_text' => __( 'Upgrade your calendar views', 'the-events-calendar' ),
					],
				],
			]
		);

		$upgrade_tab_html = '';

		$upgrade_tab = [
			'info-box-description' => [
				'type' => 'html',
				'html' => $upgrade_tab_html,
			],
		];

		/**
		 * Allows the fields displayed in the upgrade tab to be modified.
		 *
		 * @since 4.9.12
		 *
		 * @param array $upgrade_tab Array of fields used to setup the Upgrade Tab.
		 */
		$upgrade_fields = apply_filters( 'tribe_upgrade_fields', $upgrade_tab );

		new Tribe__Settings_Tab(
			'upgrade', esc_html__( 'Upgrade', 'the-events-calendar' ),
			[
				'priority'      => 100,
				'fields'        => $upgrade_fields,
				'network_admin' => is_network_admin(),
				'show_save'     => true,
			]
		);

		add_filter(
			'tec_events_settings_tabs_ids',
			function( $tabs ) {
				$tabs[] = 'upgrade';
				return $tabs;
			}
		);
	}

	/**
	 * When TEC is activated, the Events top level menu item in the dashboard needs the post_type appended to it
	 *
	 * @since 4.3.5
	 * @since 6.0.5 Moved to Settings class.
	 *
	 * @param string $url Settings URL to filter
	 *
	 * @return string
	 */
	public function filter_url( $url ): string {
		if ( is_network_admin() ) {
			return $url;
		}

		return add_query_arg( [ 'post_type' => Plugin::POSTTYPE ], $url );
	}
}
