<?php
namespace Tribe\Events\Admin;

class Provider extends \tad_DI52_ServiceProvider {
	/**
	 * Register implementations.
	 *
	 * @since 5.15.0
	 */
	public function register() {
		$this->container->singleton( Settings::class, Settings::class );
		$this->container->singleton( 'tec.admin.notice.legacy-views', Notice\Legacy_Views_Deprecation::class );
		$this->container->singleton( Notice\Legacy_Views_Updated::class, Notice\Legacy_Views_Updated::class );
		$this->container->singleton( 'tec.admin.notice.fse', Notice\Full_Site_Editor::class );
		$this->container->singleton( 'tec.admin.notice.timezones', Notice\Timezones::class );
		$this->container->singleton( 'tec.admin.notice.marketing', Notice\Marketing::class );
		$this->container->singleton( 'tec.admin.notice.update', Notice\Update::class );
		$this->container->singleton( Notice\Install_Event_Tickets::class, Notice\Install_Event_Tickets::class );

		$this->add_hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 5.15.0
	 */
	public function add_hooks() {
		add_action( 'tribe_settings_do_tabs', $this->container->callback( Settings::class, 'settings_ui' ) );
		add_action( 'admin_menu', $this->container->callback( Settings::class, 'add_admin_pages' ), 11 );
		add_action( 'tribe_settings_do_tabs', $this->container->callback(  Settings::class, 'do_addons_api_settings_tab' ) );
		add_action( 'tribe_settings_do_tabs', $this->container->callback(  Settings::class, 'do_upgrade_tab' ) );
		add_filter( 'tribe_settings_url', $this->container->callback(  Settings::class, 'filter_url' ) );
		add_action( 'network_admin_menu', $this->container->callback( Settings::class, 'maybe_add_network_settings_page' ) );
		add_action( 'tribe_settings_do_tabs', $this->container->callback( Settings::class, 'do_network_settings_tab' ), 400 );
		add_filter( 'tribe_settings_page_title', $this->container->callback( Settings::class, 'settings_page_title' ) );
		add_filter( 'tec_settings_tab_url', $this->container->callback( Settings::class, 'filter_settings_tab_url' ), 50, 3 );
		add_filter( 'tec_admin_pages_with_tabs', $this->container->callback( Settings::class, 'add_to_pages_with_tabs' ), 20, 1 );
		add_filter( 'tribe_settings_page_url', $this->container->callback( Settings::class, 'filter_settings_page_url' ), 50, 3 );
		add_filter( 'tec_admin_footer_text', $this->container->callback( Settings::class, 'admin_footer_text_settings' ) );
		add_filter( 'tribe-events-save-network-options', $this->container->callback( Settings::class, 'maybe_hijack_save_network_settings' ), 10, 2 );
		add_filter( 'tribe_events_register_event_type_args', $this->container->callback( Settings::class, 'filter_event_type_args' ) );
		add_filter( 'tribe_field_output_checkbox_list_tribeEnableViews', $this->container->callback( Settings::class, 'tease_premium_views' ) );
		add_action( 'admin_init', $this->container->callback( 'tec.admin.notice.update', 'register' ) );
		add_action( 'admin_init', $this->container->callback( 'tec.admin.notice.timezones', 'hook' ) );
		add_action( 'admin_init', $this->container->callback( 'tec.admin.notice.marketing', 'hook' ) );
		add_action( 'admin_init', $this->container->callback( 'tec.admin.notice.legacy-views', 'hook' ) );
		add_action( 'admin_init', $this->container->callback( 'tec.admin.notice.fse', 'hook' ) );
		add_action( 'admin_init', $this->container->callback( Notice\Legacy_Views_Updated::class, 'hook' ) );
		add_action( 'admin_init', $this->container->callback( Notice\Install_Event_Tickets::class, 'hook' ) );
	}
}
