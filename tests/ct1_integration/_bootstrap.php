<?php

// Ensure the CT1 code branch is enabled.
use TEC\Events\Custom_Tables\V1\Activation;

putenv( 'TEC_CUSTOM_TABLES_V1_DISABLED=0' );
$_ENV['TEC_CUSTOM_TABLES_V1_DISABLED'] = 0;
add_filter( 'tec_events_custom_tables_v1_enabled', '__return_true' );
tribe()->register( TEC\Events\Custom_Tables\V1\Provider::class );
// Run the activation routine to ensure the tables will be set up independently of the previous state.
Activation::activate();
