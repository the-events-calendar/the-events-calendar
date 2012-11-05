
jQuery( document ).ready( function ( $ ) {

	function tribe_day_add_classes() {
		// Add Some Classes
		if ( $( '.tribe-events-day-time-slot' ).length ) {
			$( '.tribe-events-day-time-slot' ).find( '.vevent:last' ).addClass( 'tribe-last' );
		}
	}

	tribe_day_add_classes();
	
	function tribe_update_daypicker(date){
		$("#tribe-bar-date").datepicker("setDate",date); 		 
	}


	// we'll determine if the browser supports pushstate and drop those that say they do but do it badly ;)
	
	var hasPushstate = window.history && window.history.pushState && !navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]|WebApps\/.+CFNetwork)/);
	
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
				var tribe_nopop = false;
				var pop_date = event.state.date;				
				tribe_events_calendar_ajax_post( pop_date, null, tribe_nopop );
			}
		} );
		
	}	

		$( '#tribe-events-content .tribe-events-sub-nav a' ).live( 'click', function ( e ) {

			e.preventDefault();
			var tribe_nopop = true;
			var day_target = $( this ).attr( "data-day" );
			var href_target = $( this ).attr( "href" );
			tribe_update_daypicker(day_target);
			tribe_events_calendar_ajax_post( day_target, href_target, tribe_nopop );
		} );
		
		// event bar datepicker monitoring 

		$('#tribe-bar-date').bind( 'change', function (e) {

			// they changed the datepicker in event bar, lets trigger ajax

			var date = $(this).val();			
			var base_url = $('.tribe-events-nav-next a').attr('href').slice(0, -11);			
			href_target = base_url + date + '/';
			tribe_nopop = true;			

			tribe_events_calendar_ajax_post( date, href_target, tribe_nopop );

		} );

		// events bar intercept submit

		$( 'form#tribe-events-bar-form' ).bind( 'submit', function (e) {

			if(tribe_events_bar_action != 'change_view' ) {

				e.preventDefault();				

				var date = $('#tribe-events-header').attr('data-date');
				var href_target = $(location).attr('href');
				var tribe_nopop = false;
				
				if($('#tribe-bar-date').val().length) {

					// they picked a date in event bar daypicker, let's process and test

					var daypicker_date = $('#tribe-bar-date').val();

					if ( daypicker_date !=  date) {

						// it's a different day, let's overwrite the vars and initiate pushstate

						var base_url = $('.tribe-events-nav-next a').attr('href').slice(0, -11);
						date = daypicker_date;
						href_target = base_url + date + '/';
						tribe_nopop = true;
						tribe_update_daypicker(date);
					}

				}

				tribe_events_calendar_ajax_post( date, href_target, tribe_nopop );

			}
		} );
		
		// if advanced filters active intercept submit

		if( $('#tribe_events_filters_form').length ) {
			$( 'form#tribe_events_filters_form' ).bind( 'submit', function ( e ) {
				if ( tribe_events_bar_action != 'change_view' ) {
					e.preventDefault();
					var same_date = $( '#tribe-events-header' ).attr( 'data-date' );
					var same_page = $( location ).attr( 'href' );
					var tribe_nopop = false;
					tribe_events_calendar_ajax_post( same_date, same_page, tribe_nopop );
				}
			} );
		}

		function tribe_events_calendar_ajax_post( date, href_target, tribe_nopop ) {

			$( '.ajax-loading' ).show();	
			
			var params = {
				action:'tribe_event_day',
				eventDate:date
			};
			
			// add any set values from event bar to params

			$( 'form#tribe-events-bar-form :input' ).each( function () {
				var $this = $( this );
				if( $this.val().length ) {
					params[$this.attr('name')] = $this.val();
				}
			} );

			// check if advanced filters plugin is active

			if( $('#tribe_events_filters_form').length ) {

				// get selected form fields and create array

				var filter_array = $('form#tribe_events_filters_form').serializeArray();

				var fixed_array = [];
				var counts = {};
				var multiple_filters = {};

				// test for multiples of same name

				$.each(filter_array, function(index, value) {
					if (counts[value.name]){
						counts[value.name] += 1;
					} else {
						counts[value.name] = 1;
					}
				});

				// modify array

				$.each(filter_array, function(index, value) {
					if (multiple_filters[value.name] || counts[value.name] > 1){
						if (!multiple_filters[value.name]) {
							multiple_filters[value.name] = 0;
						}
						multiple_filters[value.name] += 1;
						fixed_array.push({
							name: value.name + "_" + multiple_filters[value.name],
							value: value.value
							});
					} else {
						fixed_array.push({
							name: value.name,
							value: value.value
							});
					}
				});

				// merge filter params with existing params

				params = $.param(fixed_array) + '&' + $.param(params);

			}

			$.post(
				TribeCalendar.ajaxurl,
				params,
				function ( response ) {
					$( "#ajax-loading" ).hide();
					if ( response !== '' ) {
						$( '#tribe-events-content.tribe-events-list' ).replaceWith( response );								
							
						var page_title = $(response).find( "#tribe-events-header" ).attr( 'data-title' );
						var page_header = $(response).find( "#tribe-events-header" ).attr( 'data-header' );					
						
						$( document ).attr( 'title', page_title );
						$( "h2.tribe-events-page-title" ).text( page_header );
						
						// let's write our history for this ajax request and save the date for popstate requests to use only if not a popstate request itself
						
						if( hasPushstate ) {	
						
							if( tribe_nopop ) {
								history.pushState({
									"date": date
								}, page_title, href_target);
							}
						
						}
						
						tribe_day_add_classes();
					}
				}
			);
		}	 
} );