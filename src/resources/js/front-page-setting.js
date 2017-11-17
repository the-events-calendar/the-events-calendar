var tribe_events_front_page_setting = tribe_events_front_page_setting || {};

jQuery( function( $ ) {
	'use strict';

	var obj                 = tribe_events_front_page_setting;
	var enabled             = obj.enabled || false;
	var events_page_label   = obj.events_page_label || 'Main Events Page';
	var check_value         = obj.check || '';
	var $show_on_front      = $( 'input[name="show_on_front"]' );
	var $page_on_front      = $( document.getElementById( 'page_on_front' ) );
	var $events_page_option = $( '<option value="main_events_page">' + events_page_label + '</option>' );
	var $nonce_field        = $( '<input type="hidden" name="set_main_events_page" value="' + check_value + '">' );

	// Insert our new option *after* the first item (which is placeholder text)
	$page_on_front.find( 'option:first' ).after( $events_page_option );
	$page_on_front.after( $nonce_field );

	// Initial setup if a front page events archive is enabled
	if ( enabled ) {
		// Select our injected 'main_events_page' option
		$page_on_front.find( ':selected' ).prop( 'selected', false );
		$events_page_option.prop( 'selected', true );

		// Ensure the front page options reflect this setting
		$show_on_front.filter( '[value="post"]' ).prop( 'checked', false );
		$show_on_front.filter( '[value="page"]' ).prop( 'checked', true ).trigger( 'change' );
	}
} );
