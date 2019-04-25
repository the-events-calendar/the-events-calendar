<?php

use Tribe\Events\Views\V2\Service_Provider;
use Tribe\Events\Views\V2\View;

require_once __DIR__ . '/Tribe/Events/Views/V2/TestCase.php';
require_once __DIR__ . '/Tribe/Events/Views/V2/ContextMocker.php';

// Let's  make sure Views v2 are activated if not.
tribe_update_option( View::$option_enabled, true );
tribe_register_provider( Service_Provider::class );