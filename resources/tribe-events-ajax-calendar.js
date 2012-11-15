jQuery( document ).ready( function ( $ ) {

	// our vars
	
	var tribe_base_url = $('#tribe-events-events-picker').attr('action');	

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
				tribe_events_calendar_ajax_post( '', '', tribe_pushstate, tribe_do_string, tribe_popping, tribe_params );
			}
		} );
	}

	$( '.tribe-events-calendar .tribe-events-nav a' ).live( 'click', function ( e ) {
		e.preventDefault();		
		tribe_date = $( this ).attr( "data-month" );
		tribe_href_target = $( this ).attr( "href" );
		tribe_pushstate = true;
		tribe_do_string = false;	
		tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );			
	} );

	$( '.tribe-events-calendar select.tribe-events-events-dropdown' ).live( 'change', function ( e ) {
		e.preventDefault();			
		tribe_date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();
		tribe_href_target = tribe_base_url + tribe_date + '/';		
		tribe_pushstate = true;
		tribe_do_string = false;
		tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );			
	} );

	// event bar datepicker monitoring 

	$('#tribe-bar-date').bind( 'change', function (e) {		

		// they changed the datepicker in event bar, trigger ajax

		tribe_daypicker_date = $(this).val();
		tribe_year_month = tribe_daypicker_date.slice(0, -3);
		tribe_date = $('#tribe-events-header').attr('data-date');
		tribe_href_target = tribe_cur_url;			

		if ( tribe_year_month !=  tribe_date) {

			// it's a different month, overwrite the vars and initiate pushstate

			tribe_date = tribe_year_month;				
			tribe_href_target = tribe_base_url + tribe_date + '/';				
		}

		tribe_pushstate = false;
		tribe_do_string = true;

		tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );

	} );

	// events bar intercept submit

	$( 'form#tribe-events-bar-form' ).bind( 'submit', function (e) {

		if(tribe_events_bar_action != 'change_view' ) {

			e.preventDefault();				

			// in calendar view we have to test if they are switching month and extract month for call for eventDate param plus create url for pushstate

			tribe_date = $('#tribe-events-header').attr('data-date');
			tribe_href_target = tribe_cur_url;


			if($('#tribe-bar-date').val().length) {

				// they picked a date in event bar daypicker, let's process and test

				tribe_daypicker_date = $('#tribe-bar-date').val().slice(0, -3);

				if ( tribe_daypicker_date !=  tribe_date) {

					// it's a different month, let's overwrite the vars and initiate pushstate

					tribe_date = tribe_daypicker_date;
					tribe_href_target = tribe_base_url + tribe_date + '/';						
				}

			}

			tribe_events_calendar_ajax_post( tribe_date, tribe_href_target );

		}
	} );

	// if advanced filters active intercept submit

	if( $('#tribe_events_filters_form').length ) {
		$( 'form#tribe_events_filters_form' ).bind( 'submit', function ( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				tribe_date = $( '#tribe-events-header' ).attr( 'data-date' );					
				tribe_href_target = tribe_cur_url;				
				tribe_events_calendar_ajax_post( tribe_date, tribe_href_target );
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

			$( 'form#tribe-events-bar-form :input[value!=""]' ).each( function () {
				var $this = $( this );
				if( $this.val().length && $this.attr('name') != 'submit-bar' ) {
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
			
			if ( tribe_push_counter > 0 || tribe_filter_params.length ) {
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