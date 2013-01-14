jQuery( document ).ready( function ( $ ) {
	
	var base_url = $('#tribe-events-header .tribe-nav-next a').attr('href').slice(0, -8);
	
	if($('.tribe-events-calendar').length) {
		$( '.tribe-events-events-dropdown' ).select2({
			minimumResultsForSearch: 9999
		});
	}

	if( tribe_ev.tests.pushstate && !tribe_ev.tests.map_view() ) {		
					
		var params = 'action=tribe_calendar&eventDate=' + $('#tribe-events-header').attr('data-date');

		if( tribe_ev.data.params.length ) 
			params = params + '&' + tribe_ev.data.params;	
		
		history.replaceState({									
			"tribe_params": params
		}, '', location.href);	
		
		$(window).on('popstate', function(event) {		
		
			var state = event.originalEvent.state;				

			if( state ) {			
				tribe_ev.state.do_string = false;
				tribe_ev.state.pushstate = false;	
				tribe_ev.state.popping = true;
				tribe_ev.state.params = state.tribe_params;
				tribe_ev.fn.pre_ajax( function() {
					tribe_events_calendar_ajax_post();	
				});
				
				tribe_ev.fn.set_form( tribe_ev.state.params );	
			} 
		} );
	}

	$( '#tribe-events-content' ).on( 'click', '.tribe-events-sub-nav a', function ( e ) {
		e.preventDefault();
		var $this = $(this);
		tribe_ev.state.date = $this.attr( "data-month" );
		$( '#tribe-bar-date' ).val(tribe_ev.state.date + tribe_ev.fn.get_day());			
		tribe_ev.data.cur_url = $this.attr( "href" );
		tribe_ev.state.popping = false;
		tribe_ev.fn.pre_ajax( function() { 		
			tribe_events_calendar_ajax_post();	
		});
	} );	

	$( '#tribe-events-bar' ).on( 'change', '#tribe-bar-dates select', function ( e ) {
		e.preventDefault();				
		tribe_ev.state.date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();	
		$( '#tribe-bar-date' ).val(tribe_ev.state.date + tribe_ev.fn.get_day());		
		tribe_ev.data.cur_url = base_url + tribe_ev.state.date + '/';		
		tribe_ev.state.popping = false;
		tribe_ev.fn.pre_ajax( function() { 
			tribe_events_calendar_ajax_post();	
		});
	} );	

	tribe_ev.fn.snap( '#tribe-events-content', '#tribe-events-content', '#tribe-events-footer .tribe-nav-previous a, #tribe-events-footer .tribe-nav-next a' );
	
	// events bar intercept submit
	
	function tribe_events_bar_calajax_actions(e) {
		if( tribe_events_bar_action != 'change_view' ) {
			e.preventDefault();	
			tribe_ev.state.date = $('#tribe-events-header').attr('data-date');
			tribe_ev.data.cur_url = tribe_ev.data.initial_url;
			tribe_ev.state.popping = false;
			tribe_ev.fn.pre_ajax( function() { 
				tribe_events_calendar_ajax_post();
			});		
		}
	}

	$( 'form#tribe-bar-form' ).on( 'submit', function (e) {
		tribe_events_bar_calajax_actions(e);
	} );
	
	$( '.tribe-bar-settings button[name="settingsUpdate"]' ).on( 'click', function (e) {		
		tribe_events_bar_calajax_actions(e);
		tribe_ev.fn.hide_settings();
	} );

	// if advanced filters active intercept submit

	if( $('#tribe_events_filters_form').length ) {
		
		var $form = $('#tribe_events_filters_form');
		
		if( tribe_ev.tests.live_ajax() && tribe_ev.tests.pushstate ) {
			
			$form.find('input[type="submit"]').remove();
			
			function run_filtered_month_ajax() {				
				tribe_ev.state.date = $( '#tribe-events-header' ).attr( 'data-date' );	
				tribe_ev.data.cur_url = tribe_ev.data.initial_url;
				tribe_ev.state.popping = false;
				tribe_ev.fn.pre_ajax( function() { 
					tribe_events_calendar_ajax_post();
				});				
			}
			
			$( "#tribe_events_filters_form" ).on( "slidechange", ".ui-slider", function() {
				if( !tribe_ev.tests.reset_on() ){
					run_filtered_month_ajax();
				}
			} );
			$("#tribe_events_filters_form").on("change", "input, select", function(){
				if( !tribe_ev.tests.reset_on() ){
					run_filtered_month_ajax();
				}
			});			
		}		
		
		$form.on( 'submit', function ( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				tribe_ev.state.date = $( '#tribe-events-header' ).attr( 'data-date' );	
				tribe_ev.data.cur_url = tribe_ev.data.initial_url;
				tribe_ev.state.popping = false;
				tribe_ev.fn.pre_ajax( function() { 
					tribe_events_calendar_ajax_post();
				});	
			}
		} );
	}	


	function tribe_events_calendar_ajax_post() {

		tribe_ev.fn.spin_show();
		tribe_ev.state.pushcount = 0;
		
		if( !tribe_ev.state.popping ) {		

			tribe_ev.state.params = {
				action:'tribe_calendar',
				eventDate:tribe_ev.state.date
			};
			
			tribe_ev.state.url_params = {};

			$( 'form#tribe-bar-form :input[value!=""]' ).each( function () {
				var $this = $( this );
				if( $this.val().length && !$this.hasClass('tribe-no-param') ) {
					if( $this.is(':checkbox') ) {
						if( $this.is(':checked') ) {
							tribe_ev.state.params[$this.attr('name')] = $this.val();
							tribe_ev.state.url_params[$this.attr('name')] = $this.val();
							tribe_ev.state.pushcount++;
						}
					} else {
						tribe_ev.state.params[$this.attr('name')] = $this.val();
						tribe_ev.state.url_params[$this.attr('name')] = $this.val();
						tribe_ev.state.pushcount++;
					}					
				}			
			} );

			tribe_ev.state.params = $.param(tribe_ev.state.params);
			tribe_ev.state.url_params = $.param(tribe_ev.state.url_params);

			if( $('#tribe_events_filters_form').length ) {

				var tribe_filter_params = $('form#tribe_events_filters_form :input[value!=""]').serialize();				
				if( tribe_filter_params.length ) {
					tribe_ev.state.params = tribe_ev.state.params + '&' + tribe_filter_params;
					tribe_ev.state.url_params = tribe_ev.state.url_params + '&' + tribe_filter_params;
				}
			}			
			
			if ( tribe_ev.state.pushcount > 0 || tribe_filter_params != '' ) {
				tribe_ev.state.do_string = true;
				tribe_ev.state.pushstate = false;			
			} else {
				tribe_ev.state.do_string = false;
				tribe_ev.state.pushstate = true;
			}
			
			
		} 

		if( tribe_ev.tests.pushstate ) {

			$.post(
				TribeCalendar.ajaxurl,
				tribe_ev.state.params,
				function ( response ) {
					
					tribe_ev.fn.spin_hide();
					tribe_ev.state.initial_load = false;	
					
					if ( response !== '' ) {
						
						tribe_ev.data.ajax_response = {
							'type':'tribe_events_ajax',
							'view':'month',
							'timestamp':new Date().getTime()
						};
						
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
						
						if( tribe_ev.state.do_string ) {							
							tribe_ev.data.cur_url = tribe_ev.data.cur_url + '?' + tribe_ev.state.url_params;								
							history.pushState({
								"tribe_date": tribe_ev.state.date,
								"tribe_params": tribe_ev.state.params
							}, page_title, tribe_ev.data.cur_url);															
						}						

						if( tribe_ev.state.pushstate ) {								
							history.pushState({
								"tribe_date": tribe_ev.state.date,
								"tribe_params": tribe_ev.state.params
							}, page_title, tribe_ev.data.cur_url);
						}
					}
				}
			);
				
		} else {			
			if( tribe_ev.state.do_string ) 
				window.location = tribe_ev.data.cur_url + '?' + tribe_ev.state.url_params;													
			else			
				window.location = tribe_ev.data.cur_url;			
		}
	}	
} );