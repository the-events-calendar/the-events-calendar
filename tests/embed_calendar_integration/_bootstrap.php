<?php
use TEC\Common\StellarWP\DB\DB;

define( 'JSON_SNAPSHOT_OPTIONS', JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

DB::query( DB::prepare( "ALTER TABLE %i AUTO_INCREMENT = 12509696", DB::prefix( 'posts' ) ) );
DB::query( DB::prepare( "ALTER TABLE %i AUTO_INCREMENT = 96961250", DB::prefix( 'terms' ) ) );

