<?php
// Here you can initialize variables that will be available to your tests
use Codeception\Configuration;
use Codeception\Util\Autoload;

include_once codecept_data_dir( 'classes/WP_Screen.php' );

Autoload::addNamespace( 'Tribe\Events\Tests', Configuration::supportDir() );