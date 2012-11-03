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

		$( '.tribe-events-calendar .tribe-events-nav a' ).live( 'click', function ( e ) {

			e.preventDefault();
			var tribe_nopop = true;
			var month_target = $( this ).attr( "data-month" );
			var href_target = $( this ).attr( "href" );
			tribe_events_calendar_ajax_post( month_target, href_target, tribe_nopop );
		} );

		$( '.tribe-events-calendar select.tribe-events-events-dropdown' ).live( 'change', function ( e ) {

			var tribe_nopop = true;
			var baseUrl = $(this).parent().attr('action');
			var date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();		
			var href_target = baseUrl + date + '/';		
			tribe_events_calendar_ajax_post( date, href_target, tribe_nopop );
		} );

		function tribe_events_calendar_ajax_post( date, href_target, tribe_nopop ) {

			$( '.ajax-loading' ).show();	

			var params = {
				action:'tribe_calendar',
				eventDate:date
			};
			
			if( $('#tribe_events_filters_form').length ) {
			
//				var $test = ["2","4","6"]
//				params['tribe_events_filter_dayofweek[]'] = $test;			

				var $checked = [];		
				var $counter = 0;

				$( 'form#tribe_events_filters_form :input:checked' ).each( function () {
					var $this = $( this );
					var $the_type = $this.attr('name');
					var $the_type_checked = $('input[name="' + $the_type + '"]:checked');
					if( $the_type_checked.length > 1 ) {
						$counter++;
						$checked.push($this.val());
						if( $counter === $the_type_checked.length ) {
							params[this.name.slice(0,-2)] = JSON.stringify($checked);
							console.log(JSON.stringify($checked));
							$counter = 0;
							$checked.length = 0;
						}
					}			
					else if( $the_type_checked.length == 1 ) {
						params[this.name] = $this.val();
					}				
				} );			



//				var $checked_filters = $( 'form#tribe_events_filters_form :input:checked' ).serializeArray();
//				params[this.name] = $checked_filters;
//				console.log($checked_filters);
//				$.each($checked_filters, function () {
//					console.log($(this));
//
//					params[this.name] = [this.value];
//
//				} );
			
			}



			$( 'form#tribe-events-bar-form :input' ).each( function () {
				if( $( this ).val() ) {
					params[this.name] = $( this ).val();
				}
			} );

			$.post(
				TribeCalendar.ajaxurl,
				params,
				function ( response ) {
					$( "#ajax-loading" ).hide();
					if ( response !== '' ) {
						var $the_content = $( response ).contents();
						$( '#tribe-events-content.tribe-events-calendar' ).html( $the_content );
						
						var page_title = $the_content.find("#tribe-events-header").attr('data-title');	
						$(document).attr('title', page_title);
						
						// let's write our history for this ajax request and save the date for popstate requests to use only if not a popstate request itself
						
						if( tribe_nopop ) {
							history.pushState({
								"date": date
							}, page_title, href_target);
						}
					}
				}
			);
		}
	} else {
		// here we can write all our code for non pushstate browsers
		
		$( '.tribe-events-calendar select.tribe-events-events-dropdown' ).live( 'change', function ( e ) {			
			
			var baseUrl = $(this).parent().attr('action');
			var date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();		
			var href_target = baseUrl + date + '/';		
			window.location = href_target;			
		} );
	}

} );