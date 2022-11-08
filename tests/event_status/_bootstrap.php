<?php

use Tribe\Events\Views\V2\Service_Provider;
use Tribe\Events\Event_Status\Event_Status_Provider;

// Ensure Views v2 are activated.
putenv( 'TRIBE_EVENTS_V2_VIEWS=1' );

// Register needed providers.
tribe_register_provider( Service_Provider::class );

// Let's make sure to set rewrite rules.
global $wp_rewrite;
$wp_rewrite->permalink_structure = '/%postname%/';
$wp_rewrite->rewrite_rules();
