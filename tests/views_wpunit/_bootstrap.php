<?php

// Let's  make sure Views v2 are activated if not.
putenv( 'TRIBE_EVENTS_V2_VIEWS=1' );
tribe_register_provider( Tribe\Events\Views\V2\Service_Provider::class );
tribe_register_provider( Tribe\Events\Views\V2\Widgets\Service_Provider::class );

// Let's make sure to set rewrite rules.
global $wp_rewrite;
$wp_rewrite->permalink_structure = '/%postname%/';
$wp_rewrite->rewrite_rules();
