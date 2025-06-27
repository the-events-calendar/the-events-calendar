<?php

// Disable Event Status.
define( 'TEC_EVENT_STATUS_DISABLED', true );

// Let's make sure Views v2 are activated if not.
putenv( 'TRIBE_EVENTS_V2_VIEWS=1' );

// Let's make sure to set rewrite rules.
global $wp_rewrite;
$wp_rewrite->set_permalink_structure( '/%postname%/' );
$wp_rewrite->rewrite_rules();

update_option( 'theme', 'twentytwentyfour' );
update_option( 'stylesheet', 'twentytwentyfour' );

// Fix the fact that the subscribe links default to "today"
add_filter(
	'tec_views_v2_subscribe_links_url_args',
	function( $args ) {
		if ( empty( $args['tribe-bar-date'] ) ) {
			$args['tribe-bar-date'] = '2021-07-04';

			return $args;
		}

		// Only change if it's today.
		$passed_date = date_create( $args['tribe-bar-date'] );
		$today = date_create( 'now' );
		if ( $passed_date->format( 'Y-m-d' ) !== $today->format( 'Y-m-d' ) ) {
			return $args;
		}

		$args['tribe-bar-date'] = '2021-07-04';

		return $args;
	}
);

/**
 * Base class for views test suites
 */
abstract class ViewsTestSuite {

	/**
	 * Reset global WordPress state
	 */
	protected function resetGlobalState() {
		// Reset WordPress globals
		global $wp_query, $wp_rewrite, $wp;
		$wp_query = null;
		$wp_rewrite = null;
		$wp = null;

		// Clear all filters and actions
		remove_all_filters();
		remove_all_actions();

		// Reset Tribe-specific globals
		if ( function_exists( 'tribe_context' ) ) {
			tribe_context()->reset();
		}
		if ( function_exists( 'tribe' ) && tribe( 'cache' ) ) {
			tribe( 'cache' )->reset();
		}

		// Clear options cache
		wp_cache_flush();
	}

	/**
	 * Setup database with suite-specific prefix
	 */
	protected function setupDatabase( $suite_prefix ) {
		global $wpdb;

		// Set table prefix for this suite
		$wpdb->prefix = 'test_' . $suite_prefix . '_';

		// Ensure tables exist
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta();
	}

	/**
	 * Clean up database after tests
	 */
	protected function cleanupDatabase() {
		global $wpdb;

		// Reset to default prefix
		$wpdb->prefix = 'test_';

		// Clear any cached data
		wp_cache_flush();
	}
}
