jQuery( document ).ready( function ( $ ) {
	
	var base_url;
	if( tribe_ev.state.filter_cats )
		base_url = $('#tribe-events-header').attr( 'data-baseurl' ).slice(0, -11);	
	else 
		base_url = $('#tribe-events-header .tribe-nav-next a').attr('href').slice(0, -11);	
	
	
	
	tribe_ev.state.date = $( '#tribe-events-header' ).attr( 'data-date' );
	
	tribe_ev.state.view = 'day';

	function tribe_day_add_classes() {		
		if ( $( '.tribe-events-day-time-slot' ).length ) {
			$( '.tribe-events-day-time-slot' ).find( '.vevent:last' ).addClass( 'tribe-last' );
			$( '.tribe-events-day-time-slot:first' ).find( '.vevent:first' ).removeClass( 'tribe-first' );
		}		
	}

	tribe_day_add_classes();
	
	if( tribe_ev.tests.pushstate && !tribe_ev.tests.map_view() ) {	
		
		var params = 'action=tribe_event_day&eventDate=' + tribe_ev.state.date;

		if( tribe_ev.data.params.length ) 
			params = params + '&' + tribe_ev.data.params;		
		
		history.replaceState({									
			"tribe_params": params,
			"tribe_url_params": tribe_ev.data.params
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

	$( 'body' ).on( 'click', '.tribe-nav-previous a, .tribe-nav-next a', function ( e ) {
		e.preventDefault();
		$this = $(this);
		tribe_ev.state.popping = false;
		tribe_ev.state.date = $this.attr( "data-day" );
		if( tribe_ev.state.filter_cats )
			tribe_ev.data.cur_url = base_url + tribe_ev.state.date + '/';
		else
			tribe_ev.data.cur_url = $this.attr( "href" );
		tribe_ev.fn.update_picker( tribe_ev.state.date );
		tribe_ev.fn.pre_ajax( function() { 
			tribe_events_calendar_ajax_post();
		});
	} );
	
	tribe_ev.fn.snap( '#tribe-events-content', '#tribe-events-content', '#tribe-events-footer .tribe-nav-previous a, #tribe-events-footer .tribe-nav-next a' );
	
	function tribe_events_bar_dayajax_actions(e) {
		if(tribe_events_bar_action != 'change_view' ) {
			e.preventDefault();
			tribe_ev.state.popping = false;
			tribe_ev.state.date = $('#tribe-events-header').attr('data-date');
			if( tribe_ev.state.filter_cats )
				tribe_ev.data.cur_url = base_url + tribe_ev.state.date + '/';
			tribe_ev.fn.pre_ajax( function() { 
				tribe_events_calendar_ajax_post();
			});

		}
	}

	$( 'form#tribe-bar-form' ).on( 'submit', function ( e ) {		
		tribe_events_bar_dayajax_actions(e);
	} );

	$( '.tribe-bar-settings button[name="settingsUpdate"]' ).on( 'click', function (e) {		
		tribe_events_bar_dayajax_actions(e);	
		tribe_ev.fn.hide_settings();
	} );

	if( tribe_ev.tests.live_ajax() && tribe_ev.tests.pushstate ) {

		$('#tribe-bar-date').on( 'change', function (e) {
			if( !tribe_ev.tests.reset_on() ) {
				tribe_ev.state.popping = false;
				tribe_ev.state.date = $(this).val();							
				tribe_ev.data.cur_url = base_url + tribe_ev.state.date + '/';
				tribe_ev.fn.pre_ajax( function() { 
					tribe_events_calendar_ajax_post();		
				});
			}
		} );

	}
	
	$(tribe_ev.events).on("tribe_ev_runAjax", function() {
		tribe_events_calendar_ajax_post();		
	});

	function tribe_events_calendar_ajax_post() {

		tribe_ev.fn.spin_show();
		tribe_ev.state.pushcount = 0;		

		if( !tribe_ev.state.popping ) {
			
			tribe_ev.state.url_params = '';

			tribe_ev.state.params = {
				action:'tribe_event_day',
				eventDate:tribe_ev.state.date
			};	
			
			tribe_ev.state.url_params = {
				action     :'tribe_event_day'					
			};

			$(tribe_ev.events).trigger('tribe_ev_scrapeBar');

			tribe_ev.state.params = $.param(tribe_ev.state.params);
			tribe_ev.state.url_params = $.param(tribe_ev.state.url_params);

			$(tribe_ev.events).trigger('tribe_ev_collectParams');		

			tribe_ev.state.pushstate = true;
			tribe_ev.state.do_string = false;

			if ( tribe_ev.state.pushcount > 0 || tribe_ev.state.filters ) {
				tribe_ev.state.pushstate = false;
				tribe_ev.state.do_string = true;				
			}
		} 	

		if( tribe_ev.tests.pushstate && !tribe_ev.state.filter_cats ) {
			
			$(tribe_ev.events).triggerAll('tribe_ev_ajaxStart tribe_ev_dayView_AjaxStart');					

			$.post(
				TribeCalendar.ajaxurl,
				tribe_ev.state.params,
				function ( response ) {
					
					tribe_ev.fn.spin_hide();
					tribe_ev.state.initial_load = false;	
					tribe_ev.fn.enable_inputs( '#tribe_events_filters_form', 'input, select' );
					
					if ( response !== '' ) {						
						
						$(tribe_ev.events).triggerAll('tribe_ev_ajaxSuccess tribe_ev_dayView_AjaxSuccess');
						
						tribe_ev.data.ajax_response = {
							'type':'tribe_events_ajax',
							'view':'day',
							'timestamp':new Date().getTime()
						};
						
						var $the_content;
						
						if ($.isFunction(jQuery.parseHTML))
							$the_content = $( $.parseHTML(response) ).contents();										
						else
							$the_content = $( response ).contents();						
						
						$( '#tribe-events-content.tribe-events-list' ).html( $the_content );								

						var page_title = $( "#tribe-events-header" ).attr( 'data-title' );
						var page_header = $( "#tribe-events-header" ).attr( 'data-header' );
						
						console.log(page_title);

						$( document ).attr( 'title', page_title );
						$( "h2.tribe-events-page-title" ).text( page_header );						

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

						tribe_day_add_classes();
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