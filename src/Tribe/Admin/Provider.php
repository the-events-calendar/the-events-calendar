<?php
namespace Tribe\Events\Admin;

class Provider extends \tad_DI52_ServiceProvider {
	/**
	 * Register implementations.
	 */
	public function register() {
		tribe_singleton( Events\Settings::class, Events\Settings::class );

		$this->add_hooks();
	}

	/**
	 * Add hooks.
	 */
	public function add_hooks() {
		add_action( 'tribe_settings_do_tabs', tribe_callback( Events\Settings::class, 'settings_ui' ) );
		add_action( 'admin_menu', tribe_callback( Events\Settings::class, 'add_admin_pages' ), 11 );
		add_action( 'network_admin_menu', tribe_callback( Events\Settings::class, 'maybe_add_network_settings_page' ) );
		add_action( 'tribe_settings_do_tabs', tribe_callback( Events\Settings::class, 'do_network_settings_tab' ), 400 );

		add_filter( 'tribe_settings_page_title', tribe_callback( Events\Settings::class, 'settings_page_title' ) );
		add_filter( 'tec_settings_tab_url', tribe_callback( Events\Settings::class, 'filter_settings_tab_url' ), 50, 3 );
		add_filter( 'tec_admin_pages_with_tabs', tribe_callback( Events\Settings::class, 'add_to_pages_with_tabs' ), 20, 1 );
		add_filter( 'tribe_settings_page_url', tribe_callback( Events\Settings::class, 'filter_settings_page_url' ), 50, 3 );
		add_filter( 'tec_admin_footer_text', tribe_callback( Events\Settings::class, 'admin_footer_text_settings' ) );
		add_filter( 'tribe-events-save-network-options', tribe_callback( Events\Settings::class, 'maybe_hijack_save_network_settings' ), 10, 2 );
	}
}