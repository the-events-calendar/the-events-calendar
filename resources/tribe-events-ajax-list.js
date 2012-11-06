var tribe_list_paged = 1;

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
				tribe_events_list_ajax_post( null, tribe_nopop );
			}
		} );


		// events bar intercept submit

		$( '#tribe-events-list-view' ).on( 'click', 'a#tribe_paged_next', function ( e ) {
			e.preventDefault();
			tribe_list_paged++;
			var same_page = $( location ).attr( 'href' );
			var tribe_nopop = false;
			tribe_events_list_ajax_post( same_page, tribe_nopop );
		} );

		$( '#tribe-events-list-view' ).on( 'click', 'a#tribe_paged_prev', function ( e ) {
			e.preventDefault();
			tribe_list_paged--;
			var same_page = $( location ).attr( 'href' );
			var tribe_nopop = false;
			tribe_events_list_ajax_post( same_page, tribe_nopop );
		} );

		// if advanced filters active intercept submit

		if ( $( '#tribe_events_filters_form' ).length ) {
			$( 'form#tribe_events_filters_form' ).bind( 'submit', function ( e ) {
				if ( tribe_events_bar_action != 'change_view' ) {
					e.preventDefault();
					var same_page = $( location ).attr( 'href' );
					var tribe_nopop = false;
					tribe_events_list_ajax_post( same_page, tribe_nopop );
				}
			} );
		}

		$( 'form#tribe-events-bar-form' ).bind( 'submit', function ( e ) {

			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				var href_target = $( location ).attr( 'href' );
				var tribe_nopop = false;
				tribe_events_list_ajax_post( href_target, tribe_nopop );

			}
		} );


		function tribe_events_list_ajax_post( href_target, tribe_nopop ) {

			$( '.ajax-loading' ).show();

			var params = {
				action:'tribe_list',
				paged:tribe_list_paged
			};

			// add any set values from event bar to params

			$( 'form#tribe-events-bar-form :input' ).each( function () {
				var $this = $( this );
				if( $this.val().length ) {
					params[$this.attr('name')] = $this.val();
				}
			} );

			// check if advanced filters plugin is active

			if ( $( '#tribe_events_filters_form' ).length ) {

				// get selected form fields and create array

				var filter_array = $( 'form#tribe_events_filters_form' ).serializeArray();

				var fixed_array = [];
				var counts = {};
				var multiple_filters = {};

				// test for multiples of same name

				$.each( filter_array, function ( index, value ) {
					if ( counts[value.name] ) {
						counts[value.name] += 1;
					} else {
						counts[value.name] = 1;
					}
				} );

				// modify array

				$.each( filter_array, function ( index, value ) {
					if ( multiple_filters[value.name] || counts[value.name] > 1 ) {
						if ( !multiple_filters[value.name] ) {
							multiple_filters[value.name] = 0;
						}
						multiple_filters[value.name] += 1;
						fixed_array.push( {
							name :value.name + "_" + multiple_filters[value.name],
							value:value.value
						} );
					} else {
						fixed_array.push( {
							name :value.name,
							value:value.value
						} );
					}
				} );

				// merge filter params with existing params

				params = $.param( fixed_array ) + '&' + $.param( params );

			}

			$.post(
				TribeList.ajaxurl,
				params,
				function ( response ) {
					$( "#ajax-loading" ).hide();

					console.log(response);

					if ( response.success ) {

						$( '#tribe-events-list-view' ).html( response.html );

						if ( response.max_pages > tribe_list_paged ) {
							$( 'a#tribe_paged_next' ).show();
						} else {
							$( 'a#tribe_paged_next' ).hide();
						}
						if ( tribe_list_paged > 1 ) {
							$( 'a#tribe_paged_prev' ).show();
						} else {
							$( 'a#tribe_paged_prev' ).hide();
						}

						var page_title = $( 'tribe-events-list-title' ).val();
						$(document).attr('title', page_title);
						$( "h2.tribe-events-page-title" ).text( page_title );

						if ( tribe_nopop ) {
							history.pushState( {
								"page":tribe_list_paged
							}, '', href_target );
						}
					}
				}
			);
		}
	} else {
		// here we can write all our code for non pushstate browsers

		$( '.tribe-events-calendar select.tribe-events-events-dropdown' ).live( 'change', function ( e ) {

			var baseUrl = $( this ).parent().attr( 'action' );
			var date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();
			var href_target = baseUrl + date + '/';
			window.location = href_target;
		} );
	}

} );