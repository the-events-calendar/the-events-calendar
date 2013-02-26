// Check for width of events bar so can kick in the view filter select input when appropriate
var tribe_events_bar_action;

jQuery( document ).ready( function ( $ ) {

	// Check width of events bar
	function eventsBarWidth() {
		var tribeBar = $( '#tribe-events-bar' );
		var tribeBarWidth = tribeBar.width();
	
		if ( tribeBarWidth > 643 ) {
			tribeBar.removeClass( 'tribe-bar-mini tribe-bar-mini-parent' ).addClass( 'tribe-bar-full' );
		} else {
			tribeBar.removeClass( 'tribe-bar-full' ).addClass( 'tribe-bar-mini' );
		}
		if ( tribeBarWidth < 470 ) {
			tribeBar.addClass( 'tribe-bar-mini-parent' );
		} else {
			tribeBar.removeClass( 'tribe-bar-mini-parent' );
		}
	}
	eventsBarWidth();
	$( '#tribe-events-bar' ).resize(function() { 
		eventsBarWidth();
	});

	// Implement our datepicker
	var tribe_var_datepickerOpts = {
		dateFormat: 'yy-mm-dd',
		showAnim: 'fadeIn'		
	};
	if ( !$( '.tribe-events-week-grid' ).length ) {
		$( '#tribe-bar-date' ).datepicker( tribe_var_datepickerOpts );
	}
	
	// Add some classes
	if( $( '.tribe-bar-settings' ).length ) {
		$( '#tribe-events-bar' ).addClass( 'tribe-has-settings' );
	}
	if ( $( '#tribe-events-bar .hasDatepicker' ).length ) {
		$( '#tribe-events-bar' ).addClass( 'tribe-has-datepicker' );
	}

	// Implement placeholder
	$( 'input[name*="tribe-bar-"]' ).placeholder();

	// Implement select2
	function format( view ) {
    	return '<span class="tribe-icon-' + view.text.toLowerCase() + '">' + view.text + '</span>';
   	}
	$( '#tribe-bar-views .tribe-select2' ).select2({
		placeholder: "Views",
		dropdownCssClass: 'tribe-select2-results-views',
		minimumResultsForSearch: 9999,
		formatResult: format,
		formatSelection: format
	});

	// Wrap date inputs with a parent container
	$('label[for="tribe-bar-date"], input[name="tribe-bar-date"]').wrapAll('<div id="tribe-bar-dates" />');
	   
	// Add our date bits outside of our filter container
	$( '#tribe-bar-filters' ).before( $('#tribe-bar-dates') );
	
	// Append our month view selects to date wrapper in bar
	if ( $( '.events-gridview' ).length ) {
		$( '#tribe-bar-dates' ).append( $('.tribe-events-calendar #tribe-events-events-picker').contents() );		
		$( '#tribe-bar-date' ).hide();
		$( '#tribe-events-bar' ).removeClass( 'tribe-has-datepicker' );
	}

	// Implement our views bit
	$( 'select[name=tribe-bar-view]' ).change( function () {
		var el = $( this );
		var url = el.val()
		var name = $( 'select[name=tribe-bar-view] option[value="' + url + '"]' ).attr('data-view');		
		tribe_events_bar_action = 'change_view';		
		tribe_events_bar_change_view( url, name );
	} );

	$( 'a.tribe-bar-view' ).on( 'click', function ( e ) {
		e.preventDefault();
		var el = $( this );
		var name = el.attr('data-view');		
		tribe_events_bar_change_view( el.attr( 'href' ), name );

	} );

	function tribe_events_bar_change_view( url, name ) {
		
		tribe_events_bar_action = 'change_view';
		
		var cv_url_params = {};		
		var $set_inputs = $( '#tribe-bar-form input' );		
		
		if( $( '#tribe-bar-geoloc' ).length ) {			
			tribe_map_val = jQuery( '#tribe-bar-geoloc' ).val();		
			if( !tribe_map_val.length ) {
				$( '#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng' ).val( '' );
			} else {
				if( name === 'map' )
					cv_url_params['action'] = 'geosearch';	
			}
		}
		
		$set_inputs.each( function () {
			var $this = $( this );
			if( $this.val().length && !$this.hasClass('tribe-no-param') ) {	
				if( $this.is(':checkbox') ) {
					if( $this.is(':checked') ) {
						cv_url_params[$this.attr('name')] = $this.val();	
					}
				} else {							
					cv_url_params[$this.attr('name')] = $this.val();					
				}
			}			
		} );
		
		cv_url_params = $.param(cv_url_params);
		
		if ( $( '#tribe_events_filters_form' ).length ) {
			
			cv_filter_params = tribe_ev.fn.serialize( '#tribe_events_filters_form', 'input, select' );			
			
			if( cv_url_params.length && cv_filter_params.length ) 				
				cv_url_params = cv_url_params + '&' + cv_filter_params;	
			else if( cv_filter_params.length )
				cv_url_params = cv_filter_params;	
			
			if( cv_url_params.length ) 
				url = url + '?' + cv_url_params;	
			
			window.location.href = url;
		} else {
			if( cv_url_params.length ) 
				url = url + '?' + cv_url_params;	
			
			window.location.href = url;
		}
	}

	// Implement simple toggle for filters at smaller size (and close if click outside of toggle area)
	var $tribeDropToggle = $( '#tribe-events-bar [class^="tribe-bar-button-"]' );
	var $tribeDropToggleEl = $tribeDropToggle.next( '.tribe-bar-drop-content' );
	
	$tribeDropToggle.click( function () {
		var $this = $(this);
		$this.toggleClass( 'open' );
		$this.next( '.tribe-bar-drop-content' ).toggle();
		return false
	} );
	
	$(document).click(function(){
		if( $tribeDropToggle.hasClass('open') ) {			
			$tribeDropToggle.removeClass( 'open' );
			$tribeDropToggleEl.hide();
		}
	});	
	
	$tribeDropToggleEl.click( function ( e ) {
		e.stopPropagation();
	} );

});
