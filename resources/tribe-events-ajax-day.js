
jQuery( document ).ready( function ( $ ) {

	function tribe_day_add_classes() {
		// Add Some Classes
		if ( $( '.tribe-events-day-time-slot' ).length ) {
			$( '.tribe-events-day-time-slot' ).find( '.vevent:last' ).addClass( 'tribe-last' );
		}
	}

	tribe_day_add_classes();
	
	function tribe_update_daypicker(tribe_date){
		$("#tribe-bar-date").datepicker("setDate",tribe_date); 		 
	}
	
	if( tribe_has_pushstate && !GeoLoc.map_view ) {	
		
		// let's fix any browser that fires popstate on first load incorrectly
		
		var popped = ('state' in window.history), initialURL = location.href;
		
		$(window).bind('popstate', function(event) {
			
			var initialPop = !popped && location.href == initialURL;
			popped = true;
			
			// if it was an inital load, let's get out of here
			
			if ( initialPop ) return;
			
			// this really is popstate, let's fire the ajax but not overwrite our history
			
			if( event.state ) {
				tribe_do_string = false;
				tribe_pushstate = false;	
				tribe_popping = true;
				tribe_params = event.state.tribe_params;				
				tribe_events_calendar_ajax_post( tribe_date, '', tribe_pushstate, tribe_do_string, tribe_popping, tribe_params );
			}
		} );
		
	}	

	$( '#tribe-events-content .tribe-events-sub-nav a' ).live( 'click', function ( e ) {
		e.preventDefault();			
		tribe_date = $( this ).attr( "data-day" );
		tribe_href_target = $( this ).attr( "href" );
		tribe_update_daypicker(tribe_date);
		tribe_pre_ajax_tests( function() { 
			tribe_events_calendar_ajax_post( tribe_date, tribe_href_target );
		});
	} );

	// event bar datepicker monitoring 

	$('#tribe-bar-date').bind( 'change', function (e) {

		// they changed the datepicker in event bar, lets trigger ajax

		tribe_date = $(this).val();			
		var base_url = $('.tribe-events-nav-next a').attr('href').slice(0, -11);			
		tribe_href_target = base_url + tribe_date + '/';
		tribe_pre_ajax_tests( function() { 
			tribe_events_calendar_ajax_post( tribe_date, tribe_href_target );		
		});

	} );

	// events bar intercept submit

	$( 'form#tribe-events-bar-form' ).bind( 'submit', function (e) {

		if(tribe_events_bar_action != 'change_view' ) {

			e.preventDefault();
			tribe_date = $('#tribe-events-header').attr('data-date');
			tribe_cur_url = tribe_get_path( $( location ).attr( 'href' ) );	
			tribe_pre_ajax_tests( function() { 
				tribe_events_calendar_ajax_post( tribe_date, tribe_cur_url );
			});

		}
	} );

	// if advanced filters active intercept submit

	if( $('#tribe_events_filters_form').length ) {
		$( 'form#tribe_events_filters_form' ).bind( 'submit', function ( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				tribe_date = $( '#tribe-events-header' ).attr( 'data-date' );	
				tribe_cur_url = tribe_get_path( $( location ).attr( 'href' ) );	
				tribe_pre_ajax_tests( function() { 
					tribe_events_calendar_ajax_post( tribe_date, tribe_cur_url );	
				});
			}
		} );
	}

	function tribe_events_calendar_ajax_post( tribe_date, tribe_href_target ) {

		tribe_push_counter = 0;

		$( '.ajax-loading' ).show();	

		if( !tribe_popping ) {

			tribe_params = {
				action:'tribe_event_day',
				eventDate:tribe_date
			};	

			// add any set values from event bar to params. want to use serialize but due to ie bug we are stuck with second	

			$( 'form#tribe-events-bar-form :input[value!=""]' ).each( function () {
				var $this = $( this );
				if( $this.val().length && $this.attr('name') != 'submit-bar' && $this.attr('name') != 'tribe-bar-date' ) {
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

			tribe_pushstate = true;
			tribe_do_string = false;

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
						$( '#tribe-events-content.tribe-events-list' ).replaceWith( response );								

						var page_title = $(response).find( "#tribe-events-header" ).attr( 'data-title' );
						var page_header = $(response).find( "#tribe-events-header" ).attr( 'data-header' );					

						$( document ).attr( 'title', page_title );
						$( "h2.tribe-events-page-title" ).text( page_header );						

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

						tribe_day_add_classes();
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