<?php

use TEC\Events\Custom_Tables\V1\Tables\Events;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use function tad\WPBrowser\addListener;

function enable_ct1_in_database(): void {
	global $wpdb;
	if ( $wpdb->query( "INSERT INTO $wpdb->options SET option_name='tec_custom_tables_v1_active', option_value=1
                                          ON DUPLICATE KEY UPDATE option_value=1" ) === false ) {
		throw new \Exception( 'Failed to set tec_custom_tables_v1_active option to 1: ' . $wpdb->last_error );
	}
}

// Before each test empty the cache of the WordPress instance loaded in the tests context and make sure CT1 is disabled.
addListener( Codeception\Events::TEST_BEFORE, static function () {
	wp_cache_flush();
	enable_ct1_in_database();
} );

// Clean up the tables we know we might have put stuff in.
addListener( Codeception\Events::TEST_AFTER, static function () {
	// WordPress will be loaded in the context of the test, so we can use it to clean up.
	global $wpdb;

	// Disable foreign key checks to avoid issues with the truncation.
	if ( $wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' ) === false ) {
		throw new RuntimeException( "There was an issue disabling foreign key checks: $wpdb->last_error" );
	}

	foreach (
		[
			$wpdb->posts,
			$wpdb->postmeta,
			$wpdb->prefix . Events::table_name( false ),
			$wpdb->prefix . Occurrences::table_name( false ),
		] as $table
	) {
		if ( $wpdb->query( 'TRUNCATE TABLE ' . $table ) === false ) {
			throw new \RuntimeException(
				'There was an issue truncating the ' . $table . ' table: ' . $wpdb->last_error
			);
		}
	}

	// Re-enable foreign key checks.
	$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );
} );
