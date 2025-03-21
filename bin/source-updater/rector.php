<?php

declare( strict_types=1 );

use Rector\Config\RectorConfig;
use TEC\Source_Updater\Tribe_To_Tec_Assets;

return RectorConfig::configure()
                   ->withBootstrapFiles( [ __DIR__ . '/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php' ] )
                   ->withRules( [
	                   Tribe_To_Tec_Assets::class
                   ] );
