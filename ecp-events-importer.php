<?php
/*
Plugin Name: The Events Calendar - CSV Importer
Version: 1.0
Description: The Events Calendar PRO Events Importer is a premium add-on to the Events Calendar PRO plugin. This add-on enables import of organizers, venues and events via CSV file. Column mapping is chosen after the file is uploaded.
Author: Modern Tribe, Inc.
Author URI: http://tri.be?ref=importer-addon
Text Domain: tribe-events-importer
License: GPLv2 or later
*/

/*
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


if ( !function_exists( 'Tribe_Events_Importer_Load' ) ) {

	/** Load and dependecy checks. **/

	function Tribe_Events_Importer_Load() {
		if ( class_exists( 'TribeEvents' ) ) {
			TribeEventsImporter_Plugin::set_plugin_basename(plugin_basename(__FILE__));
			add_action( 'plugins_loaded', array( 'TribeEventsImporter_Plugin', 'init_textdomain' ), 15, 0 );
			add_action( 'plugin_row_meta', array( 'TribeEventsImporter_Plugin', 'add_plugin_row_meta_links' ), 10, 2 );

			if ( is_admin() ) {
				add_action( 'init', array('TribeEventsImporter_Plugin', 'initialize_admin'), 10, 0 );
			}

		} else {
			add_action( 'admin_notices', 'show_importer_fail_message' );
		}
	}

	add_action( 'plugins_loaded', 'Tribe_Events_Importer_Load' );

	function TribeEventsImporter_autoload( $classname ) {
		if ( strpos( $classname, 'TribeEventsImporter' ) === 0 ) {
			$path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $classname . '.php';
			if ( file_exists( $path ) ) {
				include( $path );
			}
		}
	}

	spl_autoload_register( 'TribeEventsImporter_autoload' );

	function show_importer_fail_message() {
		$activate_plugins = current_user_can( 'activate_plugins' ) && ( substr( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), -11 ) == 'plugins.php' );
		if ( !class_exists( 'TribeEvents' ) && $activate_plugins ) {
			$url = get_admin_url() . 'plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true';
			echo '<div class="error"><p>' . sprintf( __( 'To begin using The Events Calendar: CSV Importer, please install the latest version of <a href="%s" class="thickbox" title="The Events Calendar">The Events Calendar</a>.', 'tribe-events-importer' ), $url ) . '</p></div>';
		}
	}
}