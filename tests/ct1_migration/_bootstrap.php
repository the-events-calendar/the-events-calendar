<?php

// Include the base CT1 migration test case.
require_once __DIR__ . '/CT1_Migration_Test_Case.php';

use TEC\Events\Custom_Tables\V1\Activation;
use TEC\Events\Custom_Tables\V1\Tables\Events;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use function tad\WPBrowser\addListener;
use function tad\WPBrowser\importDumpWithMysqlBin;

// If the `uopz` extension is installed, let's make sure to `exit` and `die` will work properly.
if ( function_exists( 'uopz_allow_exit' ) ) {
	uopz_allow_exit( true );
}

if ( ! function_exists( 'tec_ct1_migration_as_truncate_tables' ) ) {
	function tec_ct1_migration_as_truncate_tables(): void {
		global $wpdb;
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );
		foreach ( $wpdb->get_col( 'SHOW TABLES' ) as $table ) {
			if ( 0 === strpos( $table, $wpdb->prefix . 'actionscheduler_' ) ) {
				$truncated = $wpdb->query( 'TRUNCATE TABLE ' . $table );
				if ( $truncated === false ) {
					throw new RuntimeException( 'Could not truncate table ' . $table );
				}
			}
		}
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );
	}
}

// Since we do not drop and import the DB dump after each test, let's do a lighter cleanup here.
$clean_custom_tables = static function () {
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
	tec_ct1_migration_as_truncate_tables();
};
addListener( Codeception\Events::TEST_AFTER, $clean_custom_tables );
addListener( Codeception\Events::SUITE_BEFORE, $clean_custom_tables );

addListener( Codeception\Events::SUITE_BEFORE, static function () {
	// Ensure the CT1 code branch is enabled.
	putenv( 'TEC_CUSTOM_TABLES_V1_DISABLED=0' );
	$_ENV['TEC_CUSTOM_TABLES_V1_DISABLED'] = 0;
	add_filter( 'tec_events_custom_tables_v1_enabled', '__return_true' );
	tribe()->register( TEC\Events\Custom_Tables\V1\Provider::class );
	// Run the activation routine to ensure the tables will be set up independently of the previous state.
	Activation::activate();
	do_action( 'tec_events_custom_tables_v1_load_action_scheduler' );
} );
