jQuery( document ).ready( function ( $ ) {
	
	var tribe_is_paged = tribe_ev.fn.get_url_param('tribe_paged');		
	
	if( tribe_is_paged ) {
		tribe_ev.state.paged = tribe_is_paged;
	} 

	if( typeof GeoLoc === 'undefined' ) 
		var GeoLoc = {"map_view":""};	

	if( tribe_ev.tests.pushstate && !GeoLoc.map_view ) {	

		if( !tribe_ev.data.params.length ) {
			tribe_ev.data.params = { 
				action     :'tribe_list',
				tribe_paged:tribe_ev.state.paged					
			}
		}
		
		history.replaceState({									
			"tribe_params": tribe_ev.data.params,
			"tribe_url_params": tribe_ev.data.params
		}, '', location.href);		

		$(window).on('popstate', function(event) {			

			var state = event.originalEvent.state;

			if( state ) {				
				tribe_ev.state.do_string = false;
				tribe_ev.state.pushstate = false;	
				tribe_ev.state.popping = true;
				tribe_ev.state.params = state.tribe_params;
				tribe_ev.state.url_params = state.tribe_url_params;
				tribe_ev.fn.pre_ajax( function() {
					tribe_events_list_ajax_post(  );	
				});
				
				tribe_ev.fn.set_form( tribe_ev.state.params );
				
			} else if( !tribe_ev.state.initial_load ) {
				window.location = tribe_ev.data.cur_url;
			}
		} );
		
	}

	// events bar intercept submit

	$( '#tribe-events-list-view' ).on( 'click', 'li.tribe-nav-next a', function ( e ) {
		e.preventDefault();
		tribe_ev.state.paged++;				
		tribe_ev.state.popping = false;
		tribe_ev.fn.pre_ajax( function() { 
			tribe_events_list_ajax_post( tribe_ev.data.cur_url );
		});
	} ).on( 'click', 'li.tribe-nav-previous a', function ( e ) {
		e.preventDefault();
		tribe_ev.state.paged--;
		tribe_ev.state.popping = false;
		tribe_ev.fn.pre_ajax( function() {
			tribe_events_list_ajax_post( tribe_ev.data.cur_url );
		});
	} );

	tribe_ev.fn.snap( '#tribe-events-list-view', '#tribe-events-list-view', '#tribe-events-footer .tribe-nav-previous a, #tribe-events-footer .tribe-nav-next a' );

	// if advanced filters active intercept submit

	if ( $( '#tribe_events_filters_form' ).length ) {

		var $form = $('#tribe_events_filters_form');

		$form.on( 'submit', function ( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				tribe_ev.state.popping = false;
				tribe_ev.state.paged = 1;
				tribe_ev.fn.pre_ajax( function() {
					tribe_events_list_ajax_post( tribe_ev.data.cur_url );
				});
			}
		} );

		if( tribe_ev.tests.live_ajax() && tribe_ev.tests.pushstate ) {

			$form.find('input[type="submit"]').remove();

			$( "#tribe_events_filters_form" ).on( "slidechange", ".ui-slider", function() {
				if( !$('body').hasClass('tribe-reset-on') ){
					tribe_ev.state.paged = 1;
					tribe_ev.state.popping = false;
					tribe_ev.fn.pre_ajax( function() {
						tribe_events_list_ajax_post( tribe_ev.data.cur_url );
					});
				}			
			} );
			$("#tribe_events_filters_form").on("change", "input, select", function(){
				if( !$('body').hasClass('tribe-reset-on') ){
					tribe_ev.state.paged = 1;
					tribe_ev.state.popping = false;
					tribe_ev.fn.pre_ajax( function() {
						tribe_events_list_ajax_post( tribe_ev.data.cur_url );
					});
				}
			});			
		}	
	}

	// event bar monitoring 

	function tribe_events_bar_listajax_actions(e) {
		if ( tribe_events_bar_action != 'change_view' ) {
			e.preventDefault();
			tribe_ev.state.paged = 1;
			tribe_ev.state.popping = false;
			tribe_ev.fn.pre_ajax( function() {
				tribe_events_list_ajax_post( tribe_ev.data.cur_url );
			});
		}
	}

	if( tribe_ev.tests.live_ajax() && tribe_ev.tests.pushstate ) {
		$('#tribe-bar-date').on( 'change', function (e) {	
			if( !$('body').hasClass('tribe-reset-on') ) {
				tribe_ev.state.popping = false;
				tribe_events_bar_listajax_actions(e);
			}
		} );
	}

	$( 'form#tribe-bar-form' ).on( 'submit', function (e) {
		tribe_ev.state.popping = false;
		tribe_events_bar_listajax_actions(e);
	} );

	$( '.tribe-bar-settings button[name="settingsUpdate"]' ).on( 'click', function (e) {	
		tribe_ev.state.popping = false;
		tribe_events_bar_listajax_actions(e);			
		$( '#tribe-events-bar [class^="tribe-bar-button-"]' )
			.removeClass( 'open' )
			.next( '.tribe-bar-drop-content' )
			.hide();
	} );

	function tribe_events_list_ajax_post( tribe_href_target, tribe_pushstate, tribe_do_string, tribe_popping, tribe_params, tribe_url_params ) {			

		$( '#tribe-events-footer, #tribe-events-header' ).find('.tribe-ajax-loading').show();

		if( !tribe_ev.state.popping ) {

			var tribe_hash_string = $( '#tribe-events-list-hash' ).val();

			tribe_ev.state.params = {
				action     :'tribe_list',
				tribe_paged:tribe_ev.state.paged					
			};

			tribe_ev.state.url_params = {
				action     :'tribe_list',
				tribe_paged:tribe_ev.state.paged					
			};							

			if( tribe_hash_string.length ) {
				tribe_ev.state.params['hash'] = tribe_hash_string;
			}				

			// add any set values from event bar to params. want to use serialize but due to ie bug we are stuck with second

			$( 'form#tribe-bar-form :input[value!=""]' ).each( function () {					
				var $this = $( this );
				if( $this.val().length && !$this.hasClass('tribe-no-param') ) {
					if( $this.is(':checkbox') ) {
						if( $this.is(':checked') ) {
							tribe_ev.state.params[$this.attr('name')] = $this.val();
							tribe_ev.state.url_params[$this.attr('name')] = $this.val();
						}
					} else {
						tribe_ev.state.params[$this.attr('name')] = $this.val();
						tribe_ev.state.url_params[$this.attr('name')] = $this.val();
					}					
				}								
			} );

			tribe_ev.state.params = $.param(tribe_ev.state.params);
			tribe_ev.state.url_params = $.param(tribe_ev.state.url_params);

			// check if advanced filters plugin is active

			if( $('#tribe_events_filters_form').length ) {

				// serialize any set values and add to params

				var tribe_filter_params = $('form#tribe_events_filters_form :input[value!=""]').serialize();
				if( tribe_filter_params.length ) {
					tribe_ev.state.params = tribe_ev.state.params + '&' + tribe_filter_params;
					tribe_ev.state.url_params = tribe_ev.state.url_params + '&' + tribe_filter_params;
				}					
			} 			

			tribe_ev.state.pushstate = false;
			tribe_ev.state.do_string = true;				

		}

		if( tribe_ev.tests.pushstate ) {

			$.post(
				TribeList.ajaxurl,
				tribe_ev.state.params,
				function ( response ) {
					$( '#tribe-events-footer, #tribe-events-header' ).find('.tribe-ajax-loading').hide();

					tribe_ev.state.initial_load = false;											

					if ( response.success ) {

						tribe_ev.state.paged = response.tribe_paged;							

						tribe_ev.data.ajax_response = {
							'type':'tribe_events_ajax',
							'post_count':parseInt(response.total_count),
							'view':'list',
							'max_pages':response.max_pages,
							'page':response.tribe_paged,
							'timestamp':new Date().getTime()
						};							

						$( '#tribe-events-list-hash' ).val( response.hash );
						$( '#tribe-events-list-view' ).html( response.html );

						if ( response.max_pages > tribe_ev.state.paged ) {
							$( 'li.tribe-nav-next a' ).show();
						} else {
							$( 'li.tribe-nav-next a' ).hide();
						}
						if ( tribe_ev.state.paged > 1 ) {
							$( 'li.tribe-nav-previous a' ).show();
						} else {
							$( 'li.tribe-nav-previous a' ).hide();
						}

						if( tribe_ev.state.do_string ) {
							tribe_href_target = tribe_href_target + '?' + tribe_ev.state.url_params;								
							history.pushState({									
								"tribe_params": tribe_ev.state.params,
								"tribe_url_params": tribe_ev.state.url_params
							}, '', tribe_href_target);															
						}						

						if( tribe_ev.state.pushstate ) {																
							history.pushState({									
								"tribe_params": tribe_ev.state.params,
								"tribe_url_params": tribe_ev.state.url_params
							}, '', tribe_href_target);
						}							
					}
				}
			);
		} else {

			if( tribe_ev.state.do_string ) {
				tribe_href_target = tribe_href_target + '?' + tribe_ev.state.url_params;													
			}
			window.location = tribe_href_target;			
		}
	} 
		
} );