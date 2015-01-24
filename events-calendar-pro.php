<?php
	/*
	Plugin Name: The Events Calendar PRO
	Description: The Events Calendar PRO, a premium add-on to the open source The Events Calendar plugin (required), enables recurring events, custom attributes, venue pages, new widgets and a host of other premium features.
	Version: 3.9
	Author: Modern Tribe, Inc.
	Author URI: http://m.tri.be/20
	Text Domain: tribe-events-calendar-pro
	License: GPLv2 or later
	*/

	/*
	Copyright 2010-2012 by Modern Tribe Inc and the contributors

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
	*/

	// Instantiate class and set up WordPress actions.
	function Tribe_ECP_Load() {
		add_filter( 'tribe_tec_addons', 'tribe_init_ecp_addon' );
		$to_run_or_not_to_run = ( class_exists( 'TribeEvents' ) && defined( 'TribeEvents::VERSION' ) && version_compare( TribeEvents::VERSION, TribeEventsPro::REQUIRED_TEC_VERSION, '>=' ) );
		if ( apply_filters( 'tribe_ecp_to_run_or_not_to_run', $to_run_or_not_to_run ) ) {
			TribeEventsPro::instance();
		} else {
			/**
			 * Dummy function to avoid fatal error in edge upgrade case
			 *
			 * @todo remove in 3.1
			 * @return bool
			 **/
			function tribe_is_recurring_event() {
				return false;
			}
		}
		if ( ! class_exists( 'TribeEvents' ) ) {
			add_action( 'admin_notices', 'tribe_show_fail_message' );
		}
	}

	add_action( 'plugins_loaded', 'Tribe_ECP_Load', 1 ); // high priority so that it's not too late for tribe_register-helpers class

	/**
	 * Shows message if the plugin can't load due to TEC not being installed.
	 */
	function tribe_show_fail_message() {
		if ( current_user_can( 'activate_plugins' ) ) {
			$langpath = trailingslashit( basename( dirname( __FILE__ ) ) ) . 'lang/';
			load_plugin_textdomain( 'tribe-events-calendar-pro', false, $langpath );
			$url = 'plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true';
			$title = __( 'The Events Calendar', 'tribe-events-calendar-pro' );
			echo '<div class="error"><p>' . sprintf( __( 'To begin using Events Calendar PRO, please install the latest version of <a href="%s" class="thickbox" title="%s">The Events Calendar</a>.', 'tribe-events-calendar-pro' ), $url, $title ) . '</p></div>';
		}
	}

	/**
	 * Add Events PRO to the list of add-ons to check required version.
	 *
	 * @return array $plugins the required info
	 */
	function tribe_init_ecp_addon( $plugins ) {
		$plugins['TribeEventsPro'] = array(
			'plugin_name' => 'Events Calendar PRO',
			'required_version' => TribeEventsPro::REQUIRED_TEC_VERSION,
			'current_version' => TribeEventsPro::VERSION,
			'plugin_dir_file' => basename( dirname( __FILE__ ) ) . '/events-calendar-pro.php'
		);

		return $plugins;
	}

	register_deactivation_hook( __FILE__, array( 'TribeEventsPro', 'deactivate' ) );

	/**
	 * The uninstall hook is no longer registered, but leaving the function
	 * here to prevent a fatal error if uninstalled on a site that had
	 * it registered previously.
	 */
	function tribe_ecp_uninstall() {
	}

	/**
	 * Requires the autoloader class from the main plugin class and sets up
	 * autoloading.
	 */
	function tribe_init_events_pro_autoloading() {
		require_once dirname( dirname( __FILE__ ) ) . '/the-events-calendar/lib/utils/Autoloader.php';
		$autoloader = Tribe__Events__Autoloader::instance();

		$autoloader->register_prefix( 'Tribe__Events__Pro__', dirname( __FILE__ ) . '/lib' );
		$autoloader->register_prefix( 'Tribe__Events__Pro__Templates__', dirname( __FILE__ ) . '/lib/template-classes' );

		// deprecated classes are registered in a class to path fashion
		foreach ( glob( dirname( __FILE__ ) . '/lib/deprecated-classes/*.php' ) as $file ) {
			$class_name = str_replace( '.php', '', basename( $file ) );
			$autoloader->register_class( $class_name, $file );
		}
		$autoloader->register_autoloader();
	}

	tribe_init_events_pro_autoloading();

	new Tribe__Events__Pro__PUE( __FILE__ );
