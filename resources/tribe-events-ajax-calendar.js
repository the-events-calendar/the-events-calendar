jQuery( document ).ready( function ( $ ) {

	// Add select2 to our month/year selects
	
	if($('.tribe-events-calendar').length) {
		$( '.tribe-events-events-dropdown' ).select2({
			minimumResultsForSearch: 9999
		});
	}
	
	function tribe_get_if_day_is_set(){
		var dp_day = '';
		if( $('#tribe-bar-date').length ) {
			var dp_date = $('#tribe-bar-date').val();
			if( dp_date.length )
				dp_day = dp_date.slice(-3);
		}
		return dp_day;
	}
	
	var tribe_base_url = $('#tribe-events-events-picker').attr('action');	
	
	if( typeof GeoLoc === 'undefined' ) 
		var GeoLoc = {"map_view":""};

	if( tribe_has_pushstate && !GeoLoc.map_view ) {	
		
		var initial_url = location.href;
		
		if( tribe_storage )
			tribe_storage.setItem( 'tribe_initial_load', 'true' );	
		
		$(window).bind('popstate', function(event) {
		
		var initial_load = '';
		if( tribe_storage )
			initial_load = tribe_storage.getItem( 'tribe_initial_load' );	
		
			var state = event.originalEvent.state;

			if( state ) {			
				tribe_do_string = false;
				tribe_pushstate = false;	
				tribe_popping = true;
				tribe_params = state.tribe_params;
				tribe_pre_ajax_tests( function() {
					tribe_events_calendar_ajax_post( '', '', tribe_pushstate, tribe_do_string, tribe_popping, tribe_params );
				});
			} else if( tribe_storage && initial_load !== 'true' ){				
				window.location = initial_url;
			}
		} );
	}

	$( '#tribe-events-content' ).on( 'click', '.tribe-events-sub-nav a', function ( e ) {
		e.preventDefault();		
		tribe_date = $( this ).attr( "data-month" );
		$( '#tribe-bar-date' ).val(tribe_date + tribe_get_if_day_is_set());
		tribe_href_target = $( this ).attr( "href" );
		tribe_pushstate = true;
		tribe_do_string = false;
		tribe_pre_ajax_tests( function() { 		
			tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );	
		});
	} );

	$( '#tribe-events-bar' ).on( 'change', '#tribe-bar-dates select', function ( e ) {
		e.preventDefault();			
		tribe_date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();		
		$( '#tribe-bar-date' ).val(tribe_date + tribe_get_if_day_is_set());
		tribe_href_target = tribe_base_url + tribe_date + '/';		
		tribe_pushstate = true;
		tribe_do_string = false;
		tribe_pre_ajax_tests( function() { 
			tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );	
		});
	} );	

	// events bar intercept submit
	
	function tribe_events_bar_calajax_actions(e) {
		if( tribe_events_bar_action != 'change_view' ) {

			e.preventDefault();			

			tribe_date = $('#tribe-events-header').attr('data-date');
			tribe_href_target = tribe_get_path( jQuery( location ).attr( 'href' ) );		

			tribe_pushstate = false;
			tribe_do_string = true;			
			
			tribe_pre_ajax_tests( function() { 
				tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );
			});		
		}
	}

	$( 'form#tribe-bar-form' ).on( 'submit', function (e) {
		tribe_events_bar_calajax_actions(e);
	} );
	
	$( '.tribe-bar-settings button[name="settingsUpdate"]' ).on( 'click', function (e) {		
		tribe_events_bar_calajax_actions(e);
		$( '#tribe-events-bar [class^="tribe-bar-button-"]' )
			.removeClass( 'open' )
			.next( '.tribe-bar-drop-content' )
			.hide();
	} );

	// if advanced filters active intercept submit

	if( $('#tribe_events_filters_form').length ) {
		
		var $form = $('#tribe_events_filters_form');
		
		if( $('body').hasClass('tribe-filter-live') ) {
			$( "#tribe_events_filters_form .ui-slider" ).on( "slidechange", function() {
				if( !$form.hasClass('tribe-reset-on') ){
					tribe_date = $( '#tribe-events-header' ).attr( 'data-date' );					
					tribe_href_target = tribe_get_path( jQuery( location ).attr( 'href' ) );	
					tribe_pushstate = false;
					tribe_do_string = true;
					tribe_pre_ajax_tests( function() { 
						tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );
					});
				}			
			} );
			$("#tribe_events_filters_form").on("change", "input, select", function(){
				if( !$form.hasClass('tribe-reset-on') ){
					tribe_date = $( '#tribe-events-header' ).attr( 'data-date' );					
					tribe_href_target = tribe_get_path( jQuery( location ).attr( 'href' ) );	
					tribe_pushstate = false;
					tribe_do_string = true;
					tribe_pre_ajax_tests( function() { 
						tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );
					});
				}
			});			
		}		
		
		$form.on( 'submit', function ( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				tribe_date = $( '#tribe-events-header' ).attr( 'data-date' );					
				tribe_href_target = tribe_get_path( jQuery( location ).attr( 'href' ) );	
				tribe_pushstate = false;
				tribe_do_string = true;
				tribe_pre_ajax_tests( function() { 
					tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );
				});
			}
		} );
	}	


	function tribe_events_calendar_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string, tribe_popping, tribe_params ) {

		$( '#ajax-loading' ).show();
		
		if( !tribe_popping ) {		

			tribe_params = {
				action:'tribe_calendar',
				eventDate:tribe_date
			};
			
			tribe_url_params = {};

			// add any set values from event bar to params. want to use serialize but due to ie bug we are stuck with second	

			$( 'form#tribe-bar-form :input[value!=""]' ).each( function () {
				var $this = $( this );
				if( $this.val().length && !$this.hasClass('tribe-no-param') ) {
					if( $this.is(':checkbox') ) {
						if( $this.is(':checked') ) {
							tribe_params[$this.attr('name')] = $this.val();
							tribe_url_params[$this.attr('name')] = $this.val();
							tribe_push_counter++;
						}
					} else {
						tribe_params[$this.attr('name')] = $this.val();
						tribe_url_params[$this.attr('name')] = $this.val();
						tribe_push_counter++;
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
			
			if ( tribe_push_counter > 0 || tribe_filter_params != '' ) {
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
					if( tribe_storage )
						tribe_storage.setItem( 'tribe_initial_load', 'false' );
					if ( response !== '' ) {
						var $the_content = $( response ).contents();
						$( '#tribe-events-content.tribe-events-calendar' ).html( $the_content );

						var page_title = $the_content.filter("#tribe-events-header").attr('data-title');
						var $date_picker = $the_content.find("#tribe-events-events-picker").contents();
						
						$( '#tribe-bar-dates' ).contents().not('#tribe-bar-date, #tribe-date-storage').remove();
						$( '#tribe-bar-dates' ).append( $date_picker );
						$( '.tribe-events-events-dropdown' ).select2({
							minimumResultsForSearch: 9999
						});	
						
						$(document).attr('title', page_title);
						
						if( tribe_do_string ) {							
							tribe_href_target = tribe_href_target + '?' + tribe_url_params;								
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
				tribe_href_target = tribe_href_target + '?' + tribe_url_params;													
			}			
			
			window.location = tribe_href_target;			
		}
	}
	
} );