<?php
// This is global bootstrap for autoloading
Codeception\Util\Autoload::addNamespace( 'Tribe\Tests', dirname(__DIR__) . '/common/tests/_support' );
Codeception\Util\Autoload::addNamespace( 'Tribe\Events\Test', __DIR__ . '/_support' );
Codeception\Util\Autoload::addNamespace( 'Tribe\Events\Test', __DIR__ . '/_support/classes' );
Codeception\Util\Autoload::addNamespace( 'Tribe\Events\Test\Acceptance\Steps', __DIR__ . '/acceptance/_steps' );

// Setup Function Mocker before any test runs or loads WordPress.
$cache_path = realpath( sys_get_temp_dir() ) . DIRECTORY_SEPARATOR . 'function-mocker';
if ( ! is_dir( $cache_path ) ) {
	if ( ! mkdir( $cache_path ) && ! is_dir( $cache_path ) ) {
		throw new \RuntimeException( sprintf( 'Directory "%s" could not be created.', $cache_path ) );
	}
}

tad\FunctionMocker\FunctionMocker::init( [
	'redefinable-internals' => [ 'date' ],
	'cache-path'            => $cache_path,
] );
