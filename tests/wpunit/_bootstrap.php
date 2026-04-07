<?php
// Here you can initialize variables that will be available to your tests
use Codeception\Configuration;
use TEC\Common\StellarWP\DB\DB;

$support = Configuration::supportDir();

DB::query( DB::prepare( 'ALTER TABLE %i AUTO_INCREMENT = 37694575', DB::prefix( 'posts' ) ) );

include_once codecept_data_dir( 'classes/WP_Screen.php' );
include_once  $support . 'utils.php';

tribe( 'log' )->disable();
