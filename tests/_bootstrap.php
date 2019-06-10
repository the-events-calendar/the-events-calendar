<?php
// This is global bootstrap for autoloading
Codeception\Util\Autoload::addNamespace( 'Tribe\Tests', dirname(__DIR__) . '/common/tests/_support' );
Codeception\Util\Autoload::addNamespace( 'Tribe\Events\Test', __DIR__ . '/_support' );
Codeception\Util\Autoload::addNamespace( 'Tribe\Events\Test', __DIR__ . '/_support/classes' );
Codeception\Util\Autoload::addNamespace( 'Tribe\Events\Test\Acceptance\Steps', __DIR__ . '/acceptance/_steps' );

/*
 * Configure FunctionMocker.
 * It has to be done here as it needs to load before WordPress loads.
 */
$config = \Codeception\Configuration::config();
$wp_root = $config['paths']['wp_root']  ?? false;

if ( false === $wp_root ) {
	throw new RuntimeException( 'Please set the `paths.wp_root` parameter in the Codeception configuration file.' );
}

if ( ! is_dir( $wp_root ) ) {
	throw new RuntimeException( "The `paths.wp_root` parameter (`{$wp_root}`) is not a directory." );
}

/*
 * Set the cache path depending on the environment.
 */
if ( getenv( 'CI' ) ) {
	$cache_path = __DIR__ . '/.function-mocker-cache';
} else if ( getenv( 'FUNCTION_MOCKER_CACHE_PATH' ) ) {
	// Allow local override of the value.
	$cache_path = getenv( 'FUNCTION_MOCKER_CACHE_PATH' );
} else {
	// Let's set the cache path somewhere it should not be part of the IDE inspections.
	$cache_path = realpath( sys_get_temp_dir() ) . DIRECTORY_SEPARATOR . 'function-mocker';
}

if ( ! is_dir( $cache_path ) ) {
	if ( ! mkdir( $cache_path ) && ! is_dir( $cache_path ) ) {
		throw new \RuntimeException( sprintf( 'Directory "%s" could not be created.', $cache_path ) );
	}
}

codecept_debug( 'Function Mocker cache path: ' . $cache_path );

tad\FunctionMocker\FunctionMocker::init( [
	'redefinable-internals' => [ 'date' ],
	'cache-path'            => $cache_path,
	'include'               => [ $wp_root ],
	'exclude'               => [ codecept_root_dir( 'vendor' ), codecept_root_dir( 'tests' ) ]
] );

/**
 * Due to some mix of modern and legacy PHP approaches the wrapping done by Function Mocker might cause warnings.
 * Let's mute those, and only those, we expect.
 */
set_error_handler( static function ( $errno, $errstr ) {
	$pattern  = '/Parameter.*to wp_default_(scripts|packages|styles)/';
	$expected = $errno === 2 && preg_match( $pattern, $errstr );
	if ( ! $expected ) {
		// This is not the error we're looking for; let PHP handle it.
		return false;
	}

	// We expected this error, move on.
	return null;
}, E_WARNING );
