//alert( TribeCalendar.ajaxurl );


jQuery( document ).ready( function ( $ ) {

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

		$( '.tribe-events-calendar .tribe-events-nav a' ).live( 'click', function ( e ) {
			if( hasPushstate ) {
				e.preventDefault();
				var tribe_nopop = true;
				var month_target = $( this ).attr( "data-month" );
				var href_target = $( this ).attr( "href" );
				tribe_events_calendar_ajax_post( month_target, href_target, tribe_nopop );
			}
		} );

		$( '.tribe-events-calendar select.tribe-events-events-dropdown' ).live( 'change', function ( e ) {

			var tribe_nopop = true;
			var baseUrl = $('#tribe-events-events-picker').attr('action');
			var date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();
			var href_target = baseUrl + date + '/';
			if( hasPushstate ) {
				tribe_events_calendar_ajax_post( date, href_target, tribe_nopop );
			} else {
				window.location = href_target;
			}
		} );

		// event bar datepicker monitoring 

		$('#tribe-bar-date').bind( 'change', function (e) {

			// they changed the datepicker in event bar, lets trigger ajax

			var daypicker_date = $(this).val();
			var year_month = daypicker_date.slice(0, -3);
			var date = $('#tribe-events-header').attr('data-date');
			var href_target = $(location).attr('href');
			var tribe_nopop = false;

			if ( year_month !=  date) {

				// it's a different month, let's overwrite the vars and initiate pushstate

				date = year_month;
				var base_url = $('#tribe-events-events-picker').attr('action');
				href_target = base_url + date + '/';
				tribe_nopop = true;
			}

			tribe_events_calendar_ajax_post( date, href_target, tribe_nopop );

		} );

		// events bar intercept submit

		$( 'form#tribe-events-bar-form' ).bind( 'submit', function (e) {

			if(tribe_events_bar_action != 'change_view' ) {

				e.preventDefault();

				// in calendar view we have to test if they are switching month and extract month for call for eventDate param plus create url for pushstate

				var date = $('#tribe-events-header').attr('data-date');
				var href_target = $(location).attr('href');
				var tribe_nopop = false;

				if($('#tribe-bar-date').val().length) {

					// they picked a date in event bar daypicker, let's process and test

					var daypicker_date = $('#tribe-bar-date').val().slice(0, -3);

					if ( daypicker_date !=  date) {

						// it's a different month, let's overwrite the vars and initiate pushstate

						var base_url = $('#tribe-events-events-picker').attr('action');
						date = daypicker_date;
						href_target = base_url + date + '/';
						tribe_nopop = true;
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
				action:'tribe_calendar',
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
						var $the_content = $( response ).contents();
						$( '#tribe-events-content.tribe-events-calendar' ).html( $the_content );

						var page_title = $the_content.filter("#tribe-events-header").attr('data-title');

						$(document).attr('title', page_title);

						// let's write our history for this ajax request and save the date for popstate requests to use only if not a popstate request itself

						if( tribe_nopop && hasPushstate ) {
							history.pushState({
								"date": date
							}, page_title, href_target);
						}
					}
				}
			);
		}
//	} else {
//		// here we can write all our code for non pushstate browsers
//
//		$( '.tribe-events-calendar select.tribe-events-events-dropdown' ).live( 'change', function ( e ) {
//
//			var baseUrl = $(this).parent().attr('action');
//			var date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();
//			var href_target = baseUrl + date + '/';
//			window.location = href_target;
//		} );
//	}

} );