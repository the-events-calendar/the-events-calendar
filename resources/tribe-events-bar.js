// Check for width of events bar so can kick in the view filter select input when appropriate

var tribe_events_bar_action;

function eventBarWidth() {
	var tribeBar = jQuery( '#tribe-events-bar' );
	var tribeBarWidth = tribeBar.width();
	
	if ( tribeBarWidth > 643 ) {
		tribeBar.removeClass( 'tribe-show-select' ).addClass( 'tribe-hide-select' );
	} else {
		tribeBar.removeClass( 'tribe-hide-select' ).addClass( 'tribe-show-select' );
	}
}

jQuery( document ).ready( function ( $ ) {

	// Implement our datepicker
	$( '#tribe-bar-date' ).datepicker();
	
	// Implement placeholder
	$( 'input[name*="tribe-bar-"]' ).placeholder();
	
	// Implement chosen
	$( '#tribe-events-bar-views .chzn-select' ).chosen({ disable_search_threshold: 9999 });
	
	// Wrap non-date inputs with a parent container for toggle
	$('.tribe-events-bar-filter-wrap.tribe-bar-search, .tribe-events-bar-filter-wrap.tribe-bar-geoloc').wrapAll('<div class="tribe-events-toggle-wrap" />');

	// Implement our views bit
	$( 'select[name=tribe-events-bar-view]' ).change( function () {
		var el = $( this );
		tribe_events_bar_action = 'change_view';
		$( 'form#tribe-events-bar-form' ).attr( 'action', el.val() ).submit();
	} );

	$( 'a.tribe-events-bar-view' ).on( 'click', function ( e ) {
		e.preventDefault();
		tribe_events_bar_action = 'change_view';
		var el = $( this );
		$( 'form#tribe-events-bar-form' ).attr( 'action', el.attr( 'href' ) ).submit();
	} );
	
	
	// Implement our function to check on our event bar width
	eventBarWidth();
	$( window ).resize( function() {
		eventBarWidth();
	} );	
	
	// Implement simple toggle for filters at smaller size (and close if click outside of toggle area)
	var tribeBarToggle = $( '#tribe-events-bar .tribe-events-bar-toggle' );
	var tribeBarToggleEl = $( '.tribe-events-toggle-wrap' );
	tribeBarToggle.click( function () {
		$( this ).toggleClass( 'open' );
    	tribeBarToggleEl.toggle();
    } );
    
    $( document ).bind( {
    	click: function( e ) {
		if( $(tribeBarToggle).hasClass( 'open' ) ) {	
			tribeBarToggle.toggleClass( 'open' );
			tribeBarToggleEl.toggle();
		}	
     	}
	} );
	tribeBarToggle.bind( 'click', function( e ) { return false } );
    $( '.tribe-bar-search, .tribe-bar-geoloc' ).click( function( e ) {
	e.stopPropagation();
    } );	

} );