<?php

use Tribe\Events\Views\V2\Service_Provider;
use Tribe\Events\Views\V2\Widgets\Service_Provider as Widget_Provider;

// Ensure Views v2 are activated.
putenv( 'TRIBE_EVENTS_V2_VIEWS=1' );

// v2 Widgets.
define( 'TRIBE_EVENTS_WIDGETS_V2_DISABLED', false );

// Register needed providers.
tribe_register_provider( Service_Provider::class );
tribe_register_provider( Widget_Provider::class );

// Let's make sure to set rewrite rules.
global $wp_rewrite;
$wp_rewrite->permalink_structure = '/%postname%/';
$wp_rewrite->rewrite_rules();

update_option( 'theme', 'twentytwenty' );
update_option( 'stylesheet', 'twentytwenty' );
