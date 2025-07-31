<?php
use TEC\Common\StellarWP\DB\DB;

define( 'JSON_SNAPSHOT_OPTIONS', JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

add_filter( 'tec_rest_experimental_endpoint', '__return_false' );

DB::query( DB::prepare( 'ALTER TABLE %i AUTO_INCREMENT = 76945', DB::prefix( 'posts' ) ) );
