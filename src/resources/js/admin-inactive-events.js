var tribe_inactive_events = tribe_inactive_events || {};

( function ( $, obj ) {
	"use strict";

	obj.selector = {
		container  : '.tribe-datetime-block',
	};

	obj.$ = {};

	obj.post_type = window.tribe_events_inactive_event_post_type;

	obj.init = function() {
		obj.setup_menu_visibility();
	};

	obj.setup_menu_visibility = function() {
		if ( ! $( 'body' ).hasClass( 'post-type-' + obj.post_type ) ) {
			return;
		}

		$( '#menu-posts-tribe_events, #menu-posts-tribe_events a.wp-has-submenu' )
			.addClass( 'wp-menu-open wp-has-current-submenu wp-has-submenu' ).removeClass( 'wp-not-current-submenu' )
			.find( '.wp-submenu' ).eq( 0 ).find( '.wp-first-item' ).addClass( 'current' );
	};

	$( document ).ready( obj.init );
} ( jQuery, tribe_inactive_events ) );