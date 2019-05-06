<?php

use Tribe\Events\Views\V2\Service_Provider;
use Tribe\Events\Views\V2\View;

require_once __DIR__ . '/Tribe/Events/Views/V2/TestCase.php';
require_once __DIR__ . '/Tribe/Events/Views/V2/ContextMocker.php';

// Let's  make sure Views v2 are activated if not.
putenv('TRIBE_EVENTS_V2_VIEWS=1');
tribe_register_provider( Service_Provider::class );