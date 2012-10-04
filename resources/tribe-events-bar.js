jQuery( document ).ready( function ( $ ) {

	// Implement our datepicker
	$( "#tribe-bar-date" ).datepicker();
	
	// Implement placeholder
	// $( 'input[name*="tribe-bar-"]' ).placeholder();
	
	// Implement chosen
	// $( '#tribe-events-bar-views .chzn-select' ).chosen({ disable_search_threshold: 9999 });

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
	
	// Implement simple toggle for filters at smaller size
	$( '#tribe-events-bar .tribe-events-bar-toggle' ).click(function () {
		$( this ).toggleClass( 'open' );
    	$( '.tribe-events-bar-filter-wrap.tribe-bar-search, .tribe-events-bar-filter-wrap.tribe-bar-geoloc' ).toggle();
    });

} );