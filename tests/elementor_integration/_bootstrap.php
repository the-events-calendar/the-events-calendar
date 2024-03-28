<?php

use TEC\Common\StellarWP\DB\DB;

// Start the posts auto-increment from a high number to make it easier to replace the post IDs in HTML snapshots.
global $wpdb;
DB::query( "ALTER TABLE $wpdb->posts AUTO_INCREMENT = 5096" );


// @todo: load data for elementor settings - JSON?
