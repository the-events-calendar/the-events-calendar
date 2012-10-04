// Check for width of events bar so can kick in the view filter select input when appropriate
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

	// Implement our views bit
	$( 'select[name=tribe-events-bar-view]' ).change( function () {
		var el = $( this );
		$( 'form#tribe-events-bar-form' ).attr( 'action', el.val() ).submit();
	} );

	$( 'a.tribe-events-bar-view' ).on( 'click', function ( e ) {
		e.preventDefault();
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
	var tribeBarToggleEl = $( '.tribe-events-bar-filter-wrap.tribe-bar-search, .tribe-events-bar-filter-wrap.tribe-bar-geoloc' );
	tribeBarToggle.click( function () {
		$( this ).toggleClass( 'open' );
    	tribeBarToggleEl.toggle();
    } );
    
    $( document ).bind( {
    	click: function( e ) {
    		tribeBarToggle.toggleClass( 'open' );
        	tribeBarToggleEl.toggle();
     	}
	} );
	tribeBarToggle.bind( 'click', function( e ) { return false } );

} );