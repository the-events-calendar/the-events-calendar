jQuery( document ).ready( function ( $ ) {

	// Add select2 to our month/year selects
	/*
		@Samuel
		This needs to get hooked up for ajax changes in the view :) 
	*/
	if($('.tribe-events-calendar').length) {
		$( '.tribe-events-events-dropdown' ).select2({
    		minimumResultsForSearch: 9999
    	});
    }

	// our vars
	
	var tribe_base_url = $('#tribe-events-events-picker').attr('action');	
	
	if( typeof GeoLoc === 'undefined' ) 
		var GeoLoc = {"map_view":""};

	if( tribe_has_pushstate && !GeoLoc.map_view ) {

		// fix any browser that fires popstate on first load incorrectly

		var popped = ('state' in window.history), initialURL = location.href;

		$(window).bind('popstate', function(event) {

			var initialPop = !popped && location.href == initialURL;
			popped = true;

			// if it was an inital load, get out of here

			if ( initialPop ) return;

			// this really is popstate: fire the ajax, send the stored params from the browser, don't overwrite the history

			if( event.state ) {			
				tribe_do_string = false;
				tribe_pushstate = false;	
				tribe_popping = true;
				tribe_params = event.state.tribe_params;
				tribe_pre_ajax_tests( function() {
					tribe_events_calendar_ajax_post( '', '', tribe_pushstate, tribe_do_string, tribe_popping, tribe_params );
				});
			}
		} );
	}

	$( '.tribe-events-calendar .tribe-events-sub-nav a' ).live( 'click', function ( e ) {
		e.preventDefault();		
		tribe_date = $( this ).attr( "data-month" );
		tribe_href_target = $( this ).attr( "href" );
		tribe_pushstate = true;
		tribe_do_string = false;
		tribe_pre_ajax_tests( function() { 		
			tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );	
		});
	} );

	$( '#tribe-bar-dates select' ).live( 'change', function ( e ) {
		e.preventDefault();			
		tribe_date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();
		tribe_href_target = tribe_base_url + tribe_date + '/';		
		tribe_pushstate = true;
		tribe_do_string = false;
		tribe_pre_ajax_tests( function() { 
			tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );	
		});
	} );	

	// events bar intercept submit

	$( 'form#tribe-bar-form' ).bind( 'submit', function (e) {

		if( tribe_events_bar_action != 'change_view' ) {

			e.preventDefault();			

			tribe_date = $('#tribe-events-header').attr('data-date');
			tribe_href_target = tribe_get_path( jQuery( location ).attr( 'href' ) );		

			tribe_pushstate = false;
			tribe_do_string = true;			
			
			tribe_pre_ajax_tests( function() { 
				tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );
			});		
		}
	} );

	// if advanced filters active intercept submit

	if( $('#tribe_events_filters_form').length ) {
		
		var $form = $('#tribe_events_filters_form');
		
		if( $form.hasClass('tribe-filter-live') ) {
			$( "#tribe_events_filters_form .ui-slider" ).on( "slidechange", function() {
				if( !$form.hasClass('tribe-reset-on') ){
					tribe_date = $( '#tribe-events-header' ).attr( 'data-date' );					
					tribe_href_target = tribe_get_path( jQuery( location ).attr( 'href' ) );	
					tribe_pushstate = false;
					tribe_do_string = true;
					tribe_pre_ajax_tests( function() { 
						tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );
					});
				}			
			} );
			$("#tribe_events_filters_form").on("change", "input, select", function(){
				if( !$form.hasClass('tribe-reset-on') ){
					tribe_date = $( '#tribe-events-header' ).attr( 'data-date' );					
					tribe_href_target = tribe_get_path( jQuery( location ).attr( 'href' ) );	
					tribe_pushstate = false;
					tribe_do_string = true;
					tribe_pre_ajax_tests( function() { 
						tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );
					});
				}
			});			
		}		
		
		$form.bind( 'submit', function ( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				tribe_date = $( '#tribe-events-header' ).attr( 'data-date' );					
				tribe_href_target = tribe_get_path( jQuery( location ).attr( 'href' ) );	
				tribe_pushstate = false;
				tribe_do_string = true;
				tribe_pre_ajax_tests( function() { 
					tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );
				});
			}
		} );
	}	


	function tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string, tribe_popping, tribe_params ) {

		$( '.ajax-loading' ).show();
		
		if( !tribe_popping ) {
			
			

			tribe_params = {
				action:'tribe_calendar',
				eventDate:tribe_date
			};	

			// add any set values from event bar to params. want to use serialize but due to ie bug we are stuck with second	

			$( 'form#tribe-bar-form :input[value!=""]' ).each( function () {
				var $this = $( this );
				if( $this.val().length  && $this.attr('name') != 'submit-bar' && $this.attr('name') != 'tribe-bar-view' && $this.attr('name') != 'EventJumpToMonth' && $this.attr('name') != 'EventJumpToYear' ) {
					tribe_params[$this.attr('name')] = $this.val();
					tribe_push_counter++;
				}			
			} );

			tribe_params = $.param(tribe_params);

			// check if advanced filters plugin is active

			if( $('#tribe_events_filters_form').length ) {

				// serialize any set values and add to params

				tribe_filter_params = $('form#tribe_events_filters_form :input[value!=""]').serialize();				
				if( tribe_filter_params.length ) {
					tribe_params = tribe_params + '&' + tribe_filter_params;
				}
			}			
			
			if ( tribe_push_counter > 0 || tribe_filter_params != '' ) {
				tribe_pushstate = false;
				tribe_do_string = true;				
			}
			
			
		} 

		if( tribe_has_pushstate ) {

			$.post(
				TribeCalendar.ajaxurl,
				tribe_params,
				function ( response ) {
					$( "#ajax-loading" ).hide();
					if ( response !== '' ) {
						var $the_content = $( response ).contents();
						$( '#tribe-events-content.tribe-events-calendar' ).html( $the_content );

						var page_title = $the_content.filter("#tribe-events-header").attr('data-title');
						var $date_picker = $the_content.find("#tribe-events-events-picker").contents();
						
						$( '#tribe-bar-dates' ).contents().remove();
						$( '#tribe-bar-dates' ).append( $date_picker );
						$( '.tribe-events-events-dropdown' ).select2({
							minimumResultsForSearch: 9999
						});				

						$(document).attr('title', page_title);
						
						if( tribe_do_string ) {							
							tribe_href_target = tribe_href_target + '?' + tribe_params;								
							history.pushState({
								"tribe_date": tribe_date,
								"tribe_params": tribe_params
							}, page_title, tribe_href_target);															
						}						

						if( tribe_pushstate ) {								
							history.pushState({
								"tribe_date": tribe_date,
								"tribe_params": tribe_params
							}, page_title, tribe_href_target);
						}
					}
				}
			);
				
		} else {
			
			if( tribe_do_string ) {
				tribe_href_target = tribe_href_target + '?' + tribe_params;													
			}			
			
			window.location = tribe_href_target;			
		}
	}
	
} );