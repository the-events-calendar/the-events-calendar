<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

require __DIR__ . '/common/vendor/freemius/start.php';
$tec_freemius = fs_dynamic_init( [
	'id' => '3069',
	'slug' => 'the-events-calendar',
	'public_key' => 'pk_e32061abc28cfedf231f3e5c4e626',
	'is_premium'     => false,
	'has_addons'     => false,
	'has_paid_plans' => false,
] );

define( 'WP_FS__UNINSTALL_MODE', true );
$tec_freemius->_uninstall_plugin_event();
$tec_freemius->do_action( 'after_uninstall' );
