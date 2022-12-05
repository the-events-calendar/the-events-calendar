<?php

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use function tad\WPBrowser\addListener;

/*
 * Ensure the tec-canonical/url plugin is placed.
 * We're not using the `WPFilesystem::haveMuPlugin` method here as the plugin should be in place
 * for the whole suite execution.
 */
addListener( Codeception\Events::TEST_BEFORE, static function ( TestEvent $e ) {
	static $placed;

	if ( $placed ) {
		return;
	}

	$placed = true;

	$plugin_source_pathname = codecept_data_dir( 'plugins/tec-canonical-url-service.php' );
	$plugin_dest_pathname   = dirname( __DIR__, 3 ) . '/tec-canonical-url-service.php';

	if ( file_exists( $plugin_dest_pathname ) && ! unlink( $plugin_dest_pathname ) ) {
		throw new RuntimeException( "Could not remove file $plugin_dest_pathname." );
	}

	if ( ! copy( $plugin_source_pathname, $plugin_dest_pathname ) ) {
		throw new \RuntimeException( "Could not copy $plugin_source_pathname to $plugin_dest_pathname" );
	}

	// Place the translations files before the tests.
	$fs_iterator_flags = FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS | FilesystemIterator::CURRENT_AS_PATHNAME;
	$translation_files = new RegexIterator(
		new FilesystemIterator( codecept_data_dir( 'translations' ), $fs_iterator_flags ),
		'/\.mo$/'
	);
	foreach ( $translation_files as $translation_file ) {
		$destination = dirname( __DIR__, 2 ) . '/lang/' . basename( $translation_file );

		if ( is_file( $destination ) && ! unlink( $destination ) ) {
			throw new \RuntimeException( "Could not delete {$destination}" );
		}

		if ( ! copy( $translation_file, $destination ) ) {
			throw new \RuntimeException( "Could not copy {$translation_file} to {$destination}" );
		}
	}
} );

// When the suite is done, remove the plugin file.
addListener( Codeception\Events::SUITE_AFTER, static function ( SuiteEvent $e ) {
	$plugin_dest_pathname = dirname( __DIR__, 3 ) . '/tec-canonical-url-service.php';
	if ( file_exists( $plugin_dest_pathname ) && ! unlink( $plugin_dest_pathname ) ) {
		throw new RuntimeException( "Could not remove file $plugin_dest_pathname." );
	}
} );