jQuery( document ).ready( function ( $ ) {
	
	// functions
	
	function tribe_get_path( url ) {
		return url.split("?")[0];
	}
	
	function tribe_string_setup() {
		has_string = window.location.search;			
		if( has_string.length ) {
			do_string = true;
			tribe_nopop = false;
		} else {
			do_string = false;
			tribe_nopop = true;
		}
	}

	// we'll determine if the browser supports pushstate and drop those that say they do but do it badly ;)

	var hasPushstate = window.history && window.history.pushState && !navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]|WebApps\/.+CFNetwork)/);
	
	// we'll set up our other vars
	
	var base_url = $('#tribe-events-events-picker').attr('action');
	var cur_url = tribe_get_path( $( location ).attr( 'href' ) );
	var do_string = false;
	var tribe_nopop = true;	
	var tribe_popping = false;
	var has_string = '';	
	var href_target = '';
	var date = '';
	var daypicker_date = '';
	var year_month = '';
	var counter = 0;
	var params = '';
	var event_bar_params = '';	
	var filter_params = '';

	if( hasPushstate ) {

		// let's fix any browser that fires popstate on first load incorrectly

		var popped = ('state' in window.history), initialURL = location.href;

		$(window).bind('popstate', function(event) {

			var initialPop = !popped && location.href == initialURL;
			popped = true;

			// if it was an inital load, let's get out of here

			if ( initialPop ) return;

			// this really is popstate, let's fire the ajax but not overwrite our history

			if( event.state ) {					
				date = event.state.date;
				do_string = false;
				tribe_nopop = false;	
				tribe_popping = true;
				params = event.state.params;
				tribe_events_calendar_ajax_post( date, '', tribe_nopop, do_string, tribe_popping, params );
			}
		} );
	}

	$( '.tribe-events-calendar .tribe-events-nav a' ).live( 'click', function ( e ) {
		e.preventDefault();
		tribe_string_setup();
		date = $( this ).attr( "data-month" );
		href_target = $( this ).attr( "href" );
		tribe_events_calendar_ajax_post( date, href_target, tribe_nopop, do_string );			
	} );

	$( '.tribe-events-calendar select.tribe-events-events-dropdown' ).live( 'change', function ( e ) {
		e.preventDefault();
		tribe_string_setup();		
		date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();
		href_target = base_url + date + '/';			
		tribe_events_calendar_ajax_post( date, href_target, tribe_nopop, do_string );			
	} );

	// event bar datepicker monitoring 

	$('#tribe-bar-date').bind( 'change', function (e) {		

		// they changed the datepicker in event bar, lets trigger ajax

		daypicker_date = $(this).val();
		year_month = daypicker_date.slice(0, -3);
		date = $('#tribe-events-header').attr('data-date');
		href_target = cur_url;			

		if ( year_month !=  date) {

			// it's a different month, let's overwrite the vars and initiate pushstate

			date = year_month;				
			href_target = base_url + date + '/';				
		}

		tribe_nopop = false;
		do_string = true;

		tribe_events_calendar_ajax_post( date, href_target, tribe_nopop, do_string );

	} );

	// events bar intercept submit

	$( 'form#tribe-events-bar-form' ).bind( 'submit', function (e) {

		if(tribe_events_bar_action != 'change_view' ) {

			e.preventDefault();				

			// in calendar view we have to test if they are switching month and extract month for call for eventDate param plus create url for pushstate

			date = $('#tribe-events-header').attr('data-date');
			href_target = cur_url;


			if($('#tribe-bar-date').val().length) {

				// they picked a date in event bar daypicker, let's process and test

				daypicker_date = $('#tribe-bar-date').val().slice(0, -3);

				if ( daypicker_date !=  date) {

					// it's a different month, let's overwrite the vars and initiate pushstate

					date = daypicker_date;
					href_target = base_url + date + '/';						
				}

			}

			tribe_nopop = false;
			do_string = true;

			tribe_events_calendar_ajax_post( date, href_target, tribe_nopop, do_string );

		}
	} );

	// if advanced filters active intercept submit

	if( $('#tribe_events_filters_form').length ) {
		$( 'form#tribe_events_filters_form' ).bind( 'submit', function ( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				date = $( '#tribe-events-header' ).attr( 'data-date' );					
				href_target = cur_url;
				tribe_nopop = false;
				do_string = true;
				tribe_events_calendar_ajax_post( date, href_target, tribe_nopop, do_string );
			}
		} );
	}


	function tribe_events_calendar_ajax_post( date, href_target, tribe_nopop, do_string, tribe_popping, params ) {

		$( '.ajax-loading' ).show();
		
		if( !tribe_popping ) {

			params = {
				action:'tribe_calendar',
				eventDate:date
			};	

			// add any set values from event bar to params. i want to use first method but due to ie bug we are stuck with second	

			$( 'form#tribe-events-bar-form :input[value!=""]' ).each( function () {
				var $this = $( this );
				if( $this.val().length && $this.attr('name') != 'submit-bar' ) {
					params[$this.attr('name')] = $this.val();
					counter++;
				}			
			} );

			params = $.param(params);

			// check if advanced filters plugin is active

			if( $('#tribe_events_filters_form').length ) {

				// serialize any set values and add to params

				filter_params = $('form#tribe_events_filters_form :input[value!=""]').serialize();				
				params = params + '&' + filter_params;
			} 


			if ( counter > 0 || filter_params.length ) {
				tribe_nopop = false;
				do_string = true;				
			}
		} 

		if( hasPushstate ) {

			$.post(
				TribeCalendar.ajaxurl,
				params,
				function ( response ) {
					$( "#ajax-loading" ).hide();
					if ( response !== '' ) {
						var $the_content = $( response ).contents();
						$( '#tribe-events-content.tribe-events-calendar' ).html( $the_content );

						var page_title = $the_content.filter("#tribe-events-header").attr('data-title');

						$(document).attr('title', page_title);

						if( do_string ) {
							href_target = href_target + '?' + params;								
							history.pushState({
								"date": date,
								"params": params
							}, page_title, href_target);															
						}						

						if( tribe_nopop ) {																
							history.pushState({
								"date": date,
								"params": params
							}, page_title, href_target);
						}
					}
				}
			);
				
		} else {
			
			if( do_string ) {
				href_target = href_target + '?' + params;													
			}
			
			window.location = href_target;			
		}
	}
	
} );