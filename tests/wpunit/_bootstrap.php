<?php
// Here you can initialize variables that will be available to your tests
use Codeception\Configuration;

$support = Configuration::supportDir();

include_once codecept_data_dir( 'classes/WP_Screen.php' );
include_once  $support . 'utils.php';

tribe( 'log' )->disable();
