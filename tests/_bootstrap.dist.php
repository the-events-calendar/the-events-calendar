<?php
/**
 * @file Global bootstrap for all codeception tests
 */


/**
 * Set up global data passed in from the config file
 *
 * @param array $config The CodeCeption config array
 */
function tribe_events_codeception_bootstrap( $config ) {
	if ( isset( $config['php']['constants'] ) && is_array( $config['php']['constants'] ) ) {
		foreach ( $config['php']['constants'] as $key => $value ) {
			if ( ! defined( $key ) ) {
				define( $key, $value );
			}
		}
	}

	if ( isset( $config['php']['globals'] ) && is_array( $config['php']['globals'] ) ) {
		foreach ( $config['php']['globals'] as $key => $value ) {
			$GLOBALS[ $key ] = $value;
		}
	}
}

/**
 * Requires the active plugins main files set in the Codeception configuration file.@deprecated
 *
 * Use in set up method of a test case to get around the globals not being backed up for testing purposes.
 *
 * @throws \Codeception\Exception\Configuration
 */
function tribe_load_active_plugins() {
	$config = \Codeception\Configuration::config();
	if ( isset( $config['php']['globals']['wp_tests_options']['active_plugins'] ) && is_array( $config['php']['globals']['wp_tests_options']['active_plugins'] ) ) {

		$wp_plugins_dir = dirname( __FILE__ );
		while ( basename( $wp_plugins_dir ) != 'plugins' ) {
			$wp_plugins_dir = dirname( $wp_plugins_dir );
		}

		foreach ( $config['php']['globals']['wp_tests_options']['active_plugins'] as $plugin ) {
			require_once $wp_plugins_dir . '/' . $plugin;
		}
	}
}

Codeception\Util\Autoload::registerSuffix( 'Tribe__Events__WP_UnitTestCase', __DIR__ . '/helpers' );

tribe_events_codeception_bootstrap( \Codeception\Configuration::config() );
