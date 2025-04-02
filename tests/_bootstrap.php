<?php
use Codeception\Util\Autoload;

$common_support_dir = __DIR__ . '/../common/tests/_support';

// This is global bootstrap for autoloading
Autoload::addNamespace( 'Tribe\Tests', dirname(__DIR__) . '/common/tests/_support' );
Autoload::addNamespace( 'Tribe\Events\Test', __DIR__ . '/_support' );
Autoload::addNamespace( 'Tribe\Events\Test', __DIR__ . '/_support/classes' );
Autoload::addNamespace( 'Tribe\Events\Test\Acceptance\Steps', __DIR__ . '/acceptance/_steps' );
Autoload::addNamespace( '\\TEC\\Common\\Tests', $common_support_dir );

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

// By default, do not enable the Custom Tables v1 implementation in tests.
putenv( 'TEC_CUSTOM_TABLES_V1_DISABLED=1' );
$_ENV['TEC_CUSTOM_TABLES_V1_DISABLED'] = 1;
