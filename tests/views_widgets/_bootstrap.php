<?php

use Tribe\Events\Views\V2\Service_Provider;

// Ensure Views v2 are activated.
putenv( 'TRIBE_EVENTS_V2_VIEWS=1' );
define( 'TRIBE_EVENTS_WIDGETS_V2_DISABLED', false );
tribe_register_provider( Service_Provider::class );

// Let's make sure to set rewrite rules.
global $wp_rewrite;
$wp_rewrite->permalink_structure = '/%postname%/';
$wp_rewrite->rewrite_rules();
