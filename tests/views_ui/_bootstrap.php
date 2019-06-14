<?php
use Tribe\Events\Views\V2\Service_Provider;
use Tribe\Events\Views\V2\View;

// Let's  make sure Views v2 are activated if not.
$options                          = get_option( \Tribe__Main::OPTIONNAME, [] );
$options[ View::$option_enabled ] = true;

update_option( \Tribe__Main::OPTIONNAME, $options );

tribe_register_provider( Service_Provider::class );
