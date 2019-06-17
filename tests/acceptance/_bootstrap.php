<?php

use Codeception\Util\Autoload;

Autoload::addNamespace( '\\', __DIR__ );

update_option( 'theme', 'twentynineteen' );
update_option( 'stylesheet', 'twentynineteen' );
