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
	if( $( '.tribe-bar-settings' ).length ) {
		$( '#tribe-events-bar' ).addClass( 'tribe-has-settings' );
	}
	if ( $( '#tribe-events-bar .hasDatepicker' ).length ) {
		$( '#tribe-events-bar' ).addClass( 'tribe-has-datepicker' );
	}
	if ( $( '.tribe-events-calendar' ).length ) {
		$( '.tribe-events-calendar' ).addClass( 'tribe-nav-alt' );
	}

	// Implement placeholder
	$( 'input[name*="tribe-bar-"]' ).placeholder();

	// Implement select2
	function format( view ) {
    	return '<span class="tribe-icon-' + view.text.toLowerCase() + '">' + view.text + '</span>';
   	}
	$( '#tribe-bar-views .tribe-select2' ).select2({
    	placeholder: "Views",
    	dropdownCssClass: 'tribe-select2-results',
    	minimumResultsForSearch: 9999,
    	formatResult: format,
        formatSelection: format
    });

	// Wrap date inputs with a parent container
	$('label[for="tribe-bar-date"], input[name="tribe-bar-date"]').wrapAll('<div id="tribe-bar-dates" />');
	   
	// Add our date bits outside of our filter container
	$( '#tribe-bar-filters' ).after( $('#tribe-bar-dates') );
	
	// Append our month view selects to date wrapper in bar
	if ( $( '.tribe-events-calendar' ).length ) {
		$( '#tribe-bar-dates' ).append( $('.tribe-events-calendar #tribe-events-events-picker') );
		$( '.tribe-events-events-dropdown' ).unwrap();
		$( '#tribe-bar-date' ).remove();
		$( '#tribe-events-bar' ).removeClass( 'tribe-has-datepicker' );
	}

	// Implement our views bit
	$( 'select[name=tribe-bar-view]' ).change( function () {
		var el = $( this );
		tribe_events_bar_action = 'change_view';
		tribe_events_bar_change_view( el.val() );
	} );

	$( 'a.tribe-bar-view' ).on( 'click', function ( e ) {
		e.preventDefault();

		var el = $( this );
		tribe_events_bar_change_view( el.attr( 'href' ) );

	} );

	function tribe_events_bar_change_view( url ) {
		
		tribe_events_bar_action = 'change_view';
		
		var cv_url_params = {};		
		var $set_inputs = $( 'form#tribe-bar-form :input[value!=""]' );		
		
		if( $( '#tribe-bar-geoloc' ).length ) {			
			tribe_map_val = jQuery( '#tribe-bar-geoloc' ).val();		
			if( !tribe_map_val.length ) {
				$( '#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng' ).val( '' );
			}
		}
		
		$set_inputs.each( function () {
			var $this = $( this );
			if( $this.val().length && $this.attr('name') != 'submit-bar' && $this.attr('name') != 'tribe-bar-view' ) {				
				cv_url_params[$this.attr('name')] = $this.val();						
			}			
		} );
		
		cv_url_params = $.param(cv_url_params);
		
		if ( $( '#tribe_events_filters_form' ).length ) {
			
			cv_filter_params = $('form#tribe_events_filters_form :input[value!=""]').serialize();	
			
			if( cv_filter_params.length ) {				
				cv_url_params = cv_url_params + '&' + cv_filter_params;
			}	
			if( cv_url_params.length ) {
				url = url + '?' + cv_url_params;
			}			
			window.location.href = url;
		} else {
			if( cv_url_params.length ) {
				url = url + '?' + cv_url_params;
			}			
			window.location.href = url;
		}
	}

	// Implement our function to check on our event bar width
	eventBarWidth();
	$( window ).resize( function () {
		eventBarWidth();
	} );

	// Implement simple toggle for filters at smaller size (and close if click outside of toggle area)
	var tribeDropToggle = $( '#tribe-events-bar [class^="tribe-bar-button-"]' );
	var tribeDropToggleEl = tribeDropToggle.next( '.tribe-bar-drop-content' );
	
	tribeDropToggle.click( function () {
		$( this ).toggleClass( 'open' );
		$( this ).next( '.tribe-bar-drop-content' ).toggle();
	} );

	
	tribeDropToggle.bind( 'click', function ( e ) {
		return false
	} );
	tribeDropToggleEl.click( function ( e ) {
		e.stopPropagation();
	} );

} );