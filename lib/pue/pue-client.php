<?php
/**
 * Plugin Upgrade System
 *
 * @version 1.7
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Only load PUE if an add-on calls for it.
 */
if ( ! class_exists( 'TribePluginUpdateEngineChecker' ) ) {
	require_once( 'lib/pue_plugin_update_utility.class.php' );
	require_once( 'lib/pue_plugin_update_engine.class.php' );
	require_once( 'lib/pue_plugin_info.class.php' );
}