// Check for width of events bar so can kick in the view filter select input when appropriate

var tribe_events_bar_action;

function eventBarWidth() {
	var tribeBar = jQuery( '#tribe-events-bar' );
	var tribeBarWidth = tribeBar.width();

	if ( tribeBarWidth > 643 ) {
		tribeBar.removeClass( 'tribe-bar-mini' ).addClass( 'tribe-bar-full' );
	} else {
		tribeBar.removeClass( 'tribe-bar-full' ).addClass( 'tribe-bar-mini' );
	}
}

jQuery( document ).ready( function ( $ ) {

	var tribe_var_datepickerOpts = {
		dateFormat: 'yy-mm-dd',
		showAnim: 'fadeIn'
	};

	// Implement our datepicker
	$( '#tribe-bar-date' ).datepicker( tribe_var_datepickerOpts );
	
	// Add some classes
	if( $( '.tribe-events-bar-drop-settings' ).length ) {
		$( '#tribe-events-bar' ).addClass( 'tribe-has-settings' );
	}

	// Implement placeholder
	$( 'input[name*="tribe-bar-"]' ).placeholder();

	// Implement select2
	function format( view ) {
    	return '<span class="tribe-icon-' + view.text.toLowerCase() + '">' + view.text + '</span>';
   	}
	$( '#tribe-events-bar-views .tribe-select2' ).select2({
    	placeholder: "Views",
    	dropdownCssClass: 'tribe-select2-results',
    	minimumResultsForSearch: 9999,
    	formatResult: format,
        formatSelection: format
    });

	// Wrap non-date inputs with a parent container for toggle
	$('.tribe-events-bar-filter-wrap.tribe-bar-search, .tribe-events-bar-filter-wrap.tribe-bar-geoloc, .tribe-events-bar-filter-wrap.tribe-bar-submit').wrapAll('<div class="tribe-events-toggle-wrap" />');

	// Implement our views bit
	$( 'select[name=tribe-events-bar-view]' ).change( function () {
		var el = $( this );
		tribe_events_bar_action = 'change_view';
		tribe_events_bar_change_view( el.val() );
	} );

	$( 'a.tribe-events-bar-view' ).on( 'click', function ( e ) {
		e.preventDefault();

		var el = $( this );
		tribe_events_bar_change_view( el.attr( 'href' ) );

	} );

	function tribe_events_bar_change_view( url ) {
		tribe_events_bar_action = 'change_view';
		if( $( '#tribe-bar-geoloc' ).length ) {			
			tribe_map_val = jQuery( '#tribe-bar-geoloc' ).val();		
			if( !tribe_map_val.length ) {
				$( '#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng' ).val( '' );
			}
		}
		if ( $( '#tribe_events_filters_form' ).length ) {
			$( 'form#tribe-events-bar-form :input' ).each( function () {
				var $this = $( this );
				$( '#tribe_events_filters_form' ).append( $this );
			} );
			$( '#tribe_events_filters_form' ).attr( 'action', url ).submit();
		} else {
			$( 'form#tribe-events-bar-form' ).attr( 'action', url ).submit();
		}
	}


	// Implement our function to check on our event bar width
	eventBarWidth();
	$( window ).resize( function () {
		eventBarWidth();
	} );

	// Implement simple toggle for filters at smaller size (and close if click outside of toggle area)
	
	//var tribeBarToggle = $( '#tribe-events-bar .tribe-events-bar-toggle' );
	//var tribeBarToggleEl = $( '.tribe-events-toggle-wrap' );
	
	var tribeDropToggle = $( '#tribe-events-bar .tribe-bar-settings' );
	var tribeDropToggleEl = $( '.tribe-bar-settings ~ div' );
	
	tribeDropToggle.click( function () {
		$( '.tribe-events-bar-drop-settings' ).toggleClass( 'open' );
		tribeDropToggleEl.toggle();
	} );

	$( document ).bind( {
		click:function ( e ) {
			if ( $( '.tribe-events-bar-drop-settings' ).hasClass( 'open' ) ) {
				$( '.tribe-events-bar-drop-settings' ).toggleClass( 'open' );
				tribeDropToggleEl.toggle();
			}
		}
	} );
	tribeDropToggle.bind( 'click', function ( e ) {
		return false
	} );
	$( '.tribe-bar-settings ~ div' ).click( function ( e ) {
		e.stopPropagation();
	} );
	/* Old
	$( '.tribe-bar-search, .tribe-bar-geoloc, .tribe-bar-settings ~ div' ).click( function ( e ) {
		e.stopPropagation();
	} );
	*/
	
	
	var tribeBarToggle = $( '#tribe-events-bar .tribe-events-bar-toggle' );
	var tribeBarToggleEl = $( '.tribe-events-toggle-wrap' );
	
	tribeBarToggle.mouseenter(function(){
    	tribeBarToggleEl.show();
    }).mouseleave(function(){
    	tribeBarToggleEl.hide();
    });

} );