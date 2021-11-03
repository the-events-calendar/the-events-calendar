<?php
/**
 * Plugin Name: The Events Calendar
 * Description: The Events Calendar is a carefully crafted, extensible plugin that lets you easily share your events. Beautiful. Solid. Awesome.
 * Version: 5.10.0
 * Author: The Events Calendar
 * Author URI: https://evnt.is/1x
 * Text Domain: the-events-calendar
 * License: GPLv2 or later
 *
 * @package TEC
 */

/**
 * Copyright 2009-2021 by The Events Calendar and the contributors
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
define( 'TRIBE_EVENTS_FILE', __FILE__ );

// Load the required php min version functions.
require_once dirname( TRIBE_EVENTS_FILE ) . '/src/functions/php-min-version.php';

// Load the Composer autoload file.
require_once dirname( TRIBE_EVENTS_FILE ) . '/vendor/autoload.php';

/**
 * Verifies if we need to warn the user about min PHP version and bail to avoid fatals
 */
if ( tribe_is_not_min_php_version() ) {
	tribe_not_php_version_textdomain( 'the-events-calendar', TRIBE_EVENTS_FILE );

	/**
	 * Include the plugin name into the correct place
	 *
	 * @since  4.8
	 *
	 * @param  array $names current list of names.
	 *
	 * @return array
	 */
	function tribe_events_not_php_version_plugin_name( $names ) {
		$names['the-events-calendar'] = esc_html__( 'The Events Calendar', 'the-events-calendar' );
		return $names;
	}

	add_filter( 'tribe_not_php_version_names', 'tribe_events_not_php_version_plugin_name' );

	if ( ! has_filter( 'admin_notices', 'tribe_not_php_version_notice' ) ) {
		add_action( 'admin_notices', 'tribe_not_php_version_notice' );
	}
	return false;
}

/**
 * Loads the action plugin
 */
require_once dirname( TRIBE_EVENTS_FILE ) . '/src/Tribe/Main.php';

Tribe__Events__Main::instance();

register_activation_hook( TRIBE_EVENTS_FILE, array( 'Tribe__Events__Main', 'activate' ) );
register_deactivation_hook( TRIBE_EVENTS_FILE, array( 'Tribe__Events__Main', 'deactivate' ) );

/**
 * TODO: Move this method to an appropriare directory. This is just a temporary solution.
 *
 * Enable the legacy widget blocks on post types.
 *
 * @since TBD
 */
function enable_wp_legacy_widget_blocks_on_post_types() {

	/**
	 * Ensure that any styles and scripts required by the legacy widgets are
	 * loaded onto the page.
	 */
	add_action( 'admin_print_styles', function() {
		if ( get_current_screen()->is_block_editor() ) {
			do_action( 'admin_print_styles-widgets.php' );
		}
	} );

	add_action( 'admin_print_scripts', function() {
		if ( get_current_screen()->is_block_editor() ) {
			do_action( 'load-widgets.php' );
			do_action( 'widgets.php' );
			do_action( 'sidebar_admin_setup' );
			do_action( 'admin_print_scripts-widgets.php' );
		}
	} );

	add_action( 'admin_print_footer_scripts', function() {
		if ( get_current_screen()->is_block_editor() ) {
			do_action( 'admin_print_footer_scripts-widgets.php' );
		}
	} );

	add_action( 'admin_footer', function() {
		if ( get_current_screen()->is_block_editor() ) {
			do_action( 'admin_footer-widgets.php' );
		}
	} );
}

add_action( 'admin_init', 'enable_wp_legacy_widget_blocks_on_post_types' );

/**
 * TODO: Move this method to an appropriare directory. This is just a temporary solution.
 *
 * Enqueue some missing assets that are required by the legacy widget block.
 *
 * @since TBD
 */
function enqueue_missing_assets() {
	wp_enqueue_style(
		'wp-widgets'
	);

	wp_enqueue_style(
		'wp-edit-widgets'
	);

	wp_enqueue_script(
		'wp-widgets'
	);

	wp_enqueue_script(
		'wp-edit-widgets'
	);

	wp_enqueue_script(
		'admin-widgets'
	);

	wp_enqueue_script(
		'legacy-widget',
		'legacy-widget.js',
		'',
		Tribe__Events__Main::VERSION,
		true
	);

	wp_add_inline_script( 
		'legacy-widget', 
		'wp.widgets.registerLegacyWidgetBlock()' 
	);
}

add_action( 'enqueue_block_editor_assets', 'enqueue_missing_assets' );