<?php

use Tribe\Events\Views\V2\Service_Provider;

// Disable Event Status.
define( 'TEC_EVENT_STATUS_DISABLED', true );

// Let's  make sure Views v2 are activated if not.
putenv( 'TRIBE_EVENTS_V2_VIEWS=1' );
tribe_register_provider( Service_Provider::class );

// Let's make sure to set rewrite rules.
global $wp_rewrite;
$wp_rewrite->permalink_structure = '/%postname%/';
$wp_rewrite->rewrite_rules();

update_option( 'theme', 'twentytwenty' );
update_option( 'stylesheet', 'twentytwenty' );

// Fix the fact that the subscribe links default to "today"
add_filter(
	'tec_views_v2_subscribe_links_url_args',
	function( $args ) {
		if ( empty( $args['tribe-bar-date'] ) ) {
			$args['tribe-bar-date'] = '2021-07-04';

			return $args;
		}

		// Only change if it's today.
		$passed_date = date_create( $args['tribe-bar-date'] );
		$today = date_create( 'now' );
		if ( $passed_date->format( 'Y-m-d' ) !== $today->format( 'Y-m-d' ) ) {
			return $args;
		}

		$args['tribe-bar-date'] = '2021-07-04';

		return $args;
	}
);
