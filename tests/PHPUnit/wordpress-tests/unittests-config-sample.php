<?php
/**
 * Tribe Unit Tests
 * How to get Started: 
 * First, copy this file to unittests-config.php, and customize with your settings (see below).
 * After you're done customizing your config file (see instructions below), you are ready
 * to run your tests! Navigate your terminal to the tests/PHPUnit directory and run the command:
 * $ phpunit
 * That's all there is to it. Happy testing!
 */

/**
 * Path to the WordPress codebase you'd like to test. Add a backslash in the end.
 */
define( 'ABSPATH', 'path-to-WP/' );

/**
 * Now setup a new database for test purposes of a new wordpress instance. 
 * USE A NEW DATABASE, BECAUSE ALL THE DATA INSIDE WILL BE DELETED.
 */
define( 'DB_NAME', 'trunk_test' );
define( 'DB_USER', 'user' );
define( 'DB_PASSWORD', 'password' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'WPLANG', '' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );

/**
 * Cron tries to make an HTTP request to the blog, which always fails, because tests are run in CLI mode only.
 */
define( 'DISABLE_WP_CRON', true );

/** 
 * Set this constant to a serialized array of plugins 
 * (without php suffix, but can be with directory) that you want to activate.
 * e.g. define( 'LOCAL_PLUGINS', serialize( array( 'the-events-calendar/the-events-calendar', 'events-calendar-pro/events-calendar-pro' ) ) );
 */
define( 'LOCAL_PLUGINS', serialize( array(
	'the-events-calendar/the-events-calendar'
) ) );

$table_prefix  = 'wp_';
