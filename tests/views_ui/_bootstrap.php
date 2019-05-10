<?php
use Tribe\Events\Views\V2\Service_Provider;
use Tribe\Events\Views\V2\View;

// Let's  make sure Views v2 are activated if not.
tribe_update_option( View::$option_enabled, true );
tribe_register_provider( Service_Provider::class );
