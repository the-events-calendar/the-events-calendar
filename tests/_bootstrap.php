<?php

use Codeception\Util\Autoload;
use TEC\Common\Tests\Extensions\Suite_Env;

// This is global bootstrap for autoloading
$common_support_dir = dirname( __DIR__ ) . '/common/tests/_support';
Autoload::addNamespace( 'Tribe\Tests', $common_support_dir );
Autoload::addNamespace( 'Tribe\Events\Test', __DIR__ . '/_support' );
Autoload::addNamespace( 'Tribe\Events\Test', __DIR__ . '/_support/classes' );
Autoload::addNamespace( 'Tribe\Events\Test\Acceptance\Steps', __DIR__ . '/acceptance/_steps' );
Autoload::addNamespace( 'TEC\Common\Tests', $common_support_dir );
Autoload::addNamespace( 'TEC\Common\Tests\Extensions', $common_support_dir . '/Extensions' );

/**
 * Codeception will regenerate snapshots on `--debug`, while the `spatie/snapshot-assertions`
 * library will do the same on `--update-snapshots`.
 * Since Codeception has strict check on the CLI arguments appending `--update-snapshots` to the
 * `vendor/bin/codecept run` command will throw an error.
 * We handle that intention here.
 */
if ( in_array( '--debug', $_SERVER['argv'], true ) ) {
	$_SERVER['argv'][] = '--update-snapshots';
}

/*
 * Feature activation/deactivation per-suite.
 * Use hard-coded environment variables as the feature controller will not be loaded yet.
 */
Suite_Env::toggle_features( [
	'Custom Tables v1' => [
		'disable_env_var'    => 'TEC_CUSTOM_TABLES_V1_DISABLED',
		'enabled_by_default' => false,
		'active_for_suites'  => [
			'ct1_integration',
			'ct1_migration',
			'ct1_multisite_migration',
			'ct1_wp_json_api',
			'classy_integration',
		],
	],
	'Classy Editor'    => [
		'disable_env_var'    => 'TEC_CLASSY_EDITOR_DISABLED',
		'enabled_by_default' => false,
		'active_for_suites'  => [
			'classy_integration'
		],
	],
] );
