<?php

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Module\WPFilesystem;
use function tad\WPBrowser\addListener;

// This function will get around re-configuration or late configuration settings.
function tec_canonical_url_mu_plugin_path( WPFilesystem $fs ): string {
	static $pathname;

	if ( $pathname ) {
		return $pathname;
	}

	$pathname = $fs->_getConfig( 'mu-plugins' ) . 'tec-canonical-url-service.php';

	return $pathname;
}

/*
 * Ensure the tec-canonical/url plugin is placed.
 * We're not using the `WPFilesystem::haveMuPlugin` method here as the plugin should be in place
 * for the whole suite execution.
 */
addListener( Codeception\Events::TEST_BEFORE, static function ( TestEvent $e ) {
	$container = $e->getTest()->getMetadata()->getService( 'di' );
	/** @var WPFilesystem $fs */
	$fs = $container->get( WPFilesystem::class );

	$code     = file_get_contents( codecept_data_dir( 'plugins/tec-canonical-url-service.php' ) );
	$pathname = tec_canonical_url_mu_plugin_path( $fs );

	if ( file_exists( $pathname ) ) {
		$fs->deleteFile( $pathname );
	}

	$fs->writeToFile( $pathname, $code );
} );

// When the suite is done, remove the mu-plugin file.
addListener( Codeception\Events::SUITE_AFTER, static function ( SuiteEvent $e ) {
	$fs = $e->getSuite()->getModules()['WPFilesystem'];
	$fs->deleteFile( tec_canonical_url_mu_plugin_path( $fs ) );
} );