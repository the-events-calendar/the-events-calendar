<?php
/**
 * Class Tribe__Events__Importer__Plugin
 */
class Tribe__Events__Importer__Plugin {
	private static $plugin_basename = '';

	/** @var Tribe__Events__Importer__Admin_Page */
	public static $admin = null;

	public static function path( $path ) {
		$base = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );
		$path = $base . DIRECTORY_SEPARATOR . $path;

		return untrailingslashit( $path );
	}

	public static function set_plugin_basename( $basename ) {
		self::$plugin_basename = $basename;
	}

	public static function initialize_admin() {
		self::$admin = new Tribe__Events__Importer__Admin_Page();
		add_action( 'admin_menu', array( self::$admin, 'register_admin_page' ) );
		add_action( 'load-tribe_events_page_events-importer', array( self::$admin, 'handle_submission' ) );
		add_filter( 'tribe_import_general_settings', array( self::$admin, 'add_settings_fields' ) );
	}

	public static function get_admin_object() {
		return self::$admin;
	}
}
