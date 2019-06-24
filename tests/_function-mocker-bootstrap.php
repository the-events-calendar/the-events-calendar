<?php
/**
 * Configure FunctionMocker.
 *
 * This file will be loaded by the `Tribe\Events\Test\Extensions\FunctionMocker` extension; configured in the main
 * Codeception file.
 */

$config  = \Codeception\Configuration::config();
$wp_root = $config['paths']['wp_root'] ?? false;

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

echo( "Function Mocker cache path: {$cache_path}\n" );

/*
 * Let's use exclusions and inclusions to really cover only what we need; we're really interested in catching WordPress
 * functions.
 */
$wp_content_dir = dirname( codecept_root_dir(), 2 );
$wp_php_files   = array_map( static function ( SplFileInfo $f ) {
	return $f->getPathname();
}, iterator_to_array( new CallbackFilterIterator(
	new FilesystemIterator( $wp_root, FilesystemIterator::SKIP_DOTS ),
	static function ( SplFileInfo $f ) {
		return 'php' === $f->getExtension();
	}
), false ) );
$wp_core_files  = array_merge( $wp_php_files, [ $wp_root . '/wp-admin', $wp_root . '/wp-includes' ] );

tad\FunctionMocker\FunctionMocker::init( [
	'redefinable-internals' => [ 'date', 'time' ],
	'cache-path'            => $cache_path,
	// Include WP Core files and the plugin src files.
	'include'               => array_merge( $wp_core_files, [ codecept_root_dir( 'src' ) ] ),
	// Exclude the whole wp-content dir, the vendor and tests folder.
	'exclude'               => [ $wp_content_dir, codecept_root_dir( 'vendor' ), codecept_root_dir( 'tests' ) ]
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
