<?php

// Include the base CT1 migration test case.
require_once __DIR__ . '/CT1_Migration_Test_Case.php';

use TEC\Events\Custom_Tables\V1\Tables\Events;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use function tad\WPBrowser\addListener;
use function tad\WPBrowser\importDumpWithMysqlBin;

// If the migration feature flag is not defined, then define it now.
// @todo remove this when no more required.
if ( ! defined( 'TEC_EVENTS_CUSTOM_TABLES_V1_MIGRATION_ENABLED' ) ) {
	define( 'TEC_EVENTS_CUSTOM_TABLES_V1_MIGRATION_ENABLED', true );
}

// If the `uopz` extension is installed, let's make sure to `exit` and `die` will work properly.
if ( function_exists( 'uopz_allow_exit' ) ) {
	uopz_allow_exit( true );
}

// Since we do not drop and import the DB dump after each test, let's do a lighter cleanup here.
$clean_after_test = static function () {
	global $wpdb;
	$last_error        = $wpdb->last_error;
	$occurrences_table = Occurrences::table_name( true );
	$wpdb->query( "TRUNCATE TABLE {$occurrences_table}" );
	if ( ! empty( $wpdb->last_error ) && $wpdb->last_error !== $last_error ) {
		throw new RuntimeException( "There was an issue cleaning the Occurrences table: $wpdb->last_error" );
	}
	$events_table = Events::table_name( true );
	$wpdb->query( "DELETE FROM {$events_table}" ); // To skip FOREIGN KEY constraints.
	if ( ! empty( $wpdb->last_error ) && $wpdb->last_error !== $last_error ) {
		throw new RuntimeException( "There was an issue cleaning the Events table: $wpdb->last_error" );
	}
	$wpdb->query( "TRUNCATE TABLE $wpdb->postmeta" );
	if ( ! empty( $wpdb->last_error ) && $wpdb->last_error !== $last_error ) {
		throw new RuntimeException( "There was an issue cleaning the postmeta table: $wpdb->last_error" );
	}

	$wpdb->query( "TRUNCATE TABLE $wpdb->posts" );
	if ( ! empty( $wpdb->last_error ) && $wpdb->last_error !== $last_error ) {
		throw new RuntimeException( "There was an issue cleaning the posts table: $wpdb->last_error" );
	}

	// Drop and re-import the options table.
	$wpdb->query( "TRUNCATE TABLE $wpdb->options" );
	if ( ! empty( $wpdb->last_error ) && $wpdb->last_error !== $last_error ) {
		throw new RuntimeException( "There was an issue cleaning the options table: $wpdb->last_error" );
	}

	// Leverage the `options` only dump.
	importDumpWithMysqlBin( __DIR__ . '/../_data/ct1_migration/options_dump.sql', DB_NAME, DB_USER, DB_PASSWORD, DB_HOST );

	// Empty all Action Scheduler tables.
	$all_tables = (array) $wpdb->get_col( 'SHOW TABLES' );
	foreach ( $all_tables as $table ) {
		if ( 0 === strpos( $table, $wpdb->prefix . 'actionscheduler_' ) ) {
			$wpdb->query( 'TRUNCATE TABLE ' . $table );
		}
	}
};
addListener( Codeception\Events::TEST_AFTER, $clean_after_test );
