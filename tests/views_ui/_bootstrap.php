<?php

use Tribe\Events\Views\V2\Service_Provider;

// Let's  make sure Views v2 are activated if not.
define( 'TRIBE_EVENTS_V2_VIEWS', true );
tribe_register_provider( Service_Provider::class );
