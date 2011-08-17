<?php

      $root = dirname(dirname(dirname(dirname(__FILE__))));
      if (file_exists($root.'/wp-load.php')) {
          // WP 2.6
          require_once($root.'/wp-load.php');
      } else {
          // Before 2.6
          require_once($root.'/wp-config.php');
      }

$tribe_ecp = Events_Calendar_Pro::instance();
if($_POST['validate_name'] && $_POST['validation_nonce']){
	if($_REQUEST['type'] == 'venue' && wp_verify_nonce($_REQUEST['validation_nonce'], 'venue-validation-nonce')){
		echo $tribe_ecp->verify_unique_name($_REQUEST['validate_name'],'venue');
	}elseif($_REQUEST['type'] == 'organizer' && wp_verify_nonce($_REQUEST['validation_nonce'], 'organizer-validation-nonce')){
		echo $tribe_ecp->verify_unique_name($_REQUEST['validate_name'],'organizer');
	}
	exit;
}else{
	exit;
}
