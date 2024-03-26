<?php

use TEC\Common\StellarWP\DB\DB;

// Start the posts auto-increment from a high number to make it easier to replace the post IDs in HTML snapshots.
global $wpdb;
DB::query( "ALTER TABLE $wpdb->posts AUTO_INCREMENT = 5096" );


// @todo: load data for elementor settings - JSON?


Codeception\Util\Autoload::addNamespace( '\\Tribe\\Tests', dirname(__DIR__ ) . '/common/tests/_support' );
Codeception\Util\Autoload::addNamespace( '\\Tribe\\Events\\Test', dirname(__DIR__ ) . '/_support' );
Codeception\Util\Autoload::addNamespace( '\\Tribe\\Events\\Test', dirname(__DIR__ ) . '/_support/classes' );
Codeception\Util\Autoload::addNamespace( '\\Tribe\\Events\\Test\\Acceptance\\Steps', dirname(__DIR__ ) . '/acceptance/_steps' );

codecept_debug( dirname(__DIR__ ) );