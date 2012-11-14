var tribe_list_paged = 1;

jQuery( document ).ready( function ( $ ) {
	
	function tribe_get_url_param(name) {
		return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null;
	}
	
	function tribe_get_path( url ) {
		return url.split("?")[0];
	}

	// we'll determine if the browser supports pushstate and drop those that say they do but do it badly ;)

	var hasPushstate = window.history && window.history.pushState && !navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]|WebApps\/.+CFNetwork)/);	
	
	var cur_url = tribe_get_path( $( location ).attr( 'href' ) );
	var is_paged = tribe_get_url_param('tribe_paged');
	var tribe_do_string = false;
	var tribe_pushstate = true;	
	var tribe_popping = false;	
	var href_target = '';	
	var daypicker_date = '';	
	var counter = 0;
	var params = '';
	var url_params = '';
	var event_bar_params = '';	
	var filter_params = '';
	var hash_string = '';	
	
	if( is_paged ) {
		tribe_list_paged = is_paged;
	}

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
				tribe_do_string = false;
				tribe_pushstate = false;	
				tribe_popping = true;
				params = event.state.params;
				url_params = event.state.url_params;
				tribe_events_list_ajax_post( '', tribe_pushstate, tribe_do_string, tribe_popping, params, url_params );				
			}
		} );
		
	}


		// events bar intercept submit

		$( '#tribe-events-list-view' ).on( 'click', 'a#tribe_paged_next', function ( e ) {
			e.preventDefault();
			tribe_list_paged++;						
			tribe_events_list_ajax_post( cur_url );
		} );

		$( '#tribe-events-list-view' ).on( 'click', 'a#tribe_paged_prev', function ( e ) {
			e.preventDefault();
			tribe_list_paged--;
			tribe_events_list_ajax_post( cur_url );
		} );

		// if advanced filters active intercept submit

		if ( $( '#tribe_events_filters_form' ).length ) {
			$( 'form#tribe_events_filters_form' ).bind( 'submit', function ( e ) {
				if ( tribe_events_bar_action != 'change_view' ) {
					e.preventDefault();					
					tribe_events_list_ajax_post( cur_url );
				}
			} );
		}
		
		// event bar datepicker monitoring 

		$('#tribe-bar-date').bind( 'change', function (e) {		

			e.preventDefault();					
			tribe_events_list_ajax_post( cur_url );

		} );

		$( 'form#tribe-events-bar-form' ).bind( 'submit', function ( e ) {

			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				tribe_events_list_ajax_post( cur_url );

			}
		} );


		function tribe_events_list_ajax_post( href_target, tribe_pushstate, tribe_do_string, tribe_popping, params, url_params ) {

			$( '.ajax-loading' ).show();
			
			
			
			if( !tribe_popping ) {
				
				hash_string = $( '#tribe-events-list-hash' ).val();

				params = {
					action     :'tribe_list',
					tribe_paged:tribe_list_paged					
				};
				
				url_params = {
					action     :'tribe_list',
					tribe_paged:tribe_list_paged					
				};							
				
				if( hash_string.length ) {
					params['hash'] = hash_string;
				}				
				
				// add any set values from event bar to params. want to use serialize but due to ie bug we are stuck with second

				$( 'form#tribe-events-bar-form :input[value!=""]' ).each( function () {
					var $this = $( this );
					if( $this.val().length && $this.attr('name') != 'submit-bar' ) {
						params[$this.attr('name')] = $this.val();
						url_params[$this.attr('name')] = $this.val();						
					}			
				} );
				
				params = $.param(params);
				url_params = $.param(url_params);

				// check if advanced filters plugin is active

				if( $('#tribe_events_filters_form').length ) {

					// serialize any set values and add to params

					filter_params = $('form#tribe_events_filters_form :input[value!=""]').serialize();
					if( filter_params.length ) {
						params = params + '&' + filter_params;
						url_params = url_params + '&' + filter_params;
					}					
				} 			
				
				tribe_pushstate = false;
				tribe_do_string = true;				
							
			}
			
			if( hasPushstate ) {

				$.post(
					TribeList.ajaxurl,
					params,
					function ( response ) {
						$( "#ajax-loading" ).hide();

						if ( response.success ) {

							tribe_list_paged = response.tribe_paged;

							$( '#tribe-events-list-hash' ).val( response.hash );

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
							
							if( tribe_do_string ) {
								href_target = href_target + '?' + url_params;								
								history.pushState({									
									"params": params,
									"url_params": url_params
								}, '', href_target);															
							}						

							if( tribe_pushstate ) {																
								history.pushState({									
									"params": params,
									"url_params": url_params
								}, '', href_target);
							}							
						}
					}
				);
			} else {
			
				if( tribe_do_string ) {
					href_target = href_target + '?' + url_params;													
				}
				window.location = href_target;			
			}
		} 
		
} );