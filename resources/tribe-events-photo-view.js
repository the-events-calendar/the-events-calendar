var tribe_list_paged = 1;

jQuery( document ).ready( function ( $ ) {
	
	var container = $('#tribe-events-photo-events');
	var containerWidth = container.width();
	if ( containerWidth < 643 ) {
		container.addClass('photo-two-col');
	} else {
		container.removeClass('photo-two-col');
	}	
	$(window).load(function(){ 
		container.imagesLoaded( function(){    
			container.isotope({
				containerStyle: {
					position: 'relative', 
					overflow: 'visible'
				},
				resizable: false // disable normal resizing
			});
		});	
	}); 

	// update columnWidth on window resize
	$(window).resize(function() {
		var containerWidth = container.width();		
		if ( containerWidth < 643 ) {
			container.addClass('photo-two-col');
		} else {
			container.removeClass('photo-two-col');
		}
		container.isotope('reLayout');
	});
	
	var tribe_is_paged = tribe_get_url_param('tribe_paged');		
	
	if( tribe_is_paged ) {
		tribe_list_paged = tribe_is_paged;
	} 

	if( typeof GeoLoc === 'undefined' ) 
		var GeoLoc = {"map_view":""};	

	if( tribe_has_pushstate && !GeoLoc.map_view ) {
		
//		var initial_url = document.URL;

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
				tribe_url_params = event.state.tribe_url_params;
				tribe_pre_ajax_tests( function() {
					tribe_events_list_ajax_post( '', tribe_pushstate, tribe_do_string, tribe_popping, tribe_params, tribe_url_params );	
				});
			} else {
//				window.location = initial_url;
			}
		} );
		
	}	

		// events bar intercept submit

		$( 'body' ).on( 'click', 'a#tribe_paged_next', function ( e ) {
			e.preventDefault();
			tribe_list_paged++;	
			tribe_pre_ajax_tests( function() { 
				tribe_events_list_ajax_post( tribe_cur_url );
			});
		} ).on( 'click', 'a#tribe_paged_prev', function ( e ) {
			e.preventDefault();
			tribe_list_paged--;
			tribe_pre_ajax_tests( function() {
				tribe_events_list_ajax_post( tribe_cur_url );
			});
		} );

		// if advanced filters active intercept submit

		if ( $( '#tribe_events_filters_form' ).length ) {
			$( 'form#tribe_events_filters_form' ).bind( 'submit', function ( e ) {
				if ( tribe_events_bar_action != 'change_view' ) {
					e.preventDefault();	
					tribe_list_paged = 1;
					tribe_pre_ajax_tests( function() {
						tribe_events_list_ajax_post( tribe_cur_url );
					});
				}
			} );
		}
		
		// event bar datepicker monitoring 
		
		function tribe_events_bar_photoajax_actions(e) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				tribe_list_paged = 1;
				tribe_pre_ajax_tests( function() {
					tribe_events_list_ajax_post( tribe_cur_url );
				});
			}
		}

		$('#tribe-bar-date').bind( 'change', function (e) {		
			tribe_events_bar_photoajax_actions(e)
		} );
		
		$( '.tribe-bar-settings button[name="settingsUpdate"]' ).bind( 'click', function (e) {		
			tribe_events_bar_photoajax_actions(e);
			$( '#tribe-events-bar [class^="tribe-bar-button-"]' )
				.removeClass( 'open' )
				.next( '.tribe-bar-drop-content' )
				.hide();
		} );
		
		$( 'form#tribe-bar-form' ).bind( 'submit', function ( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				tribe_events_bar_photoajax_actions(e)
			}
		} );


		function tribe_events_list_ajax_post( tribe_href_target, tribe_pushstate, tribe_do_string, tribe_popping, tribe_params, tribe_url_params ) {

			$('#tribe-events-photo-events').prepend('<div id="tribe-photo-loading"><span></span></div>');			
			
			if( !tribe_popping ) {			
				
				tribe_hash_string = $( '#tribe-events-list-hash' ).val();

				tribe_params = {
					action     :'tribe_photo',
					tribe_paged:tribe_list_paged					
				};
				
				tribe_url_params = {
					action     :'tribe_photo',
					tribe_paged:tribe_list_paged					
				};							
				
				if( tribe_hash_string.length ) {
					tribe_params['hash'] = tribe_hash_string;
				}				
				
				// add any set values from event bar to params. want to use serialize but due to ie bug we are stuck with second

				$( 'form#tribe-bar-form :input[value!=""]' ).each( function () {
					var $this = $( this );
					if( $this.val().length && !$this.hasClass('tribe-no-param') ) {
						if( $this.is(':checkbox') ) {
							if( $this.is(':checked') ) {
								tribe_params[$this.attr('name')] = $this.val();
								tribe_url_params[$this.attr('name')] = $this.val();	
							}
						} else {
							tribe_params[$this.attr('name')] = $this.val();
							tribe_url_params[$this.attr('name')] = $this.val();	
						}					
					}								
				} );
				
				tribe_params = $.param(tribe_params);
				tribe_url_params = $.param(tribe_url_params);

				// check if advanced filters plugin is active

				if( $('#tribe_events_filters_form').length ) {

					// serialize any set values and add to params

					tribe_filter_params = $('form#tribe_events_filters_form :input[value!=""]').serialize();
					if( tribe_filter_params.length ) {
						tribe_params = tribe_params + '&' + tribe_filter_params;
						tribe_url_params = tribe_url_params + '&' + tribe_filter_params;
					}					
				} 			
				
				tribe_pushstate = false;
				tribe_do_string = true;				
							
			}
			
			if( tribe_has_pushstate ) {

				$.post(
					TribePhoto.ajaxurl,
					tribe_params,
					function ( response ) {
						$( "#ajax-loading" ).hide();

						if ( response.success ) {

							tribe_list_paged = response.tribe_paged;

							$( '#tribe-events-list-hash' ).val( response.hash );						

							$( '#tribe-events-content' ).replaceWith( response.html );
							$( '#tribe-events-content' ).prev('#tribe-events-list-hash').remove();
							
							$('#tribe-events-photo-events').isotope({
								containerStyle: {
									position: 'relative', 
									overflow: 'visible'
								}
							});													

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
								tribe_href_target = tribe_href_target + '?' + tribe_url_params;								
								history.pushState({									
									"tribe_params": tribe_params,
									"tribe_url_params": tribe_url_params
								}, '', tribe_href_target);															
							}						

							if( tribe_pushstate ) {																
								history.pushState({									
									"tribe_params": tribe_params,
									"tribe_url_params": tribe_url_params
								}, '', tribe_href_target);
							}							
						}
					}
				);
			} else {
			
				if( tribe_do_string ) {
					tribe_href_target = tribe_href_target + '?' + tribe_url_params;													
				}
				window.location = tribe_href_target;			
			}
		} 
});
