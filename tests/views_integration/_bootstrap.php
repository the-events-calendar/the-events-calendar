<?php

use tad\FunctionMocker\FunctionMocker;
use Tribe\Events\Views\V2\Service_Provider;

require_once __DIR__ . '/Tribe/Events/Views/V2/TestCase.php';
require_once __DIR__ . '/Tribe/Events/Views/V2/ContextMocker.php';

// Let's  make sure Views v2 are activated if not.
putenv( 'TRIBE_EVENTS_V2_VIEWS=1' );
tribe_register_provider( Service_Provider::class );

$cache_path = realpath( sys_get_temp_dir() ) . DIRECTORY_SEPARATOR . 'function-mocker';
if ( ! is_dir( $cache_path ) ) {
	if ( ! mkdir( $cache_path ) && ! is_dir( $cache_path ) ) {
		throw new \RuntimeException( sprintf( 'Directory "%s" could not be created.', $cache_path ) );
	}
}

FunctionMocker::init( [
	'redefinable-internals' => [ 'date' ],
	'cache-path'            => $cache_path,
] );
