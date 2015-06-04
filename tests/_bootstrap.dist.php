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

Codeception\Util\Autoload::registerSuffix( 'Tribe__Events__WP_UnitTestCase', __DIR__ . '/helpers' );

tribe_events_codeception_bootstrap( \Codeception\Configuration::config() );
