jQuery(document).ready(function($){	
	
	$( '.tribe-event-placeholder' ).each(function(){
		id = $(this).attr("data-event-id");
		height = $('#tribe-events-event-' + id ).height();
		$(this).height( height );
	});

	$( '#tribe-events-bar' ).addClass( 'tribe-has-datepicker' );
	tribe_ev.state.date = $( '#tribe-events-header' ).attr( 'data-date' );		
	var base_url = $('#tribe-events-header .tribe-nav-next a').attr('href').slice(0, -11);	
	
	var tribe_var_datepickerOpts = {
		dateFormat: 'yy-mm-dd',
		showAnim: 'fadeIn',
		beforeShowDay: disableSpecificWeekDays		
	};
	
	$( '#tribe-bar-date' ).datepicker( tribe_var_datepickerOpts );
	
	var daysToDisable = [0, 2, 3, 4, 5, 6];

	function disableSpecificWeekDays(date) {
		var day = date.getDay();
		for (i = 0; i < daysToDisable.length; i++) {
			if ($.inArray(day, daysToDisable) != -1) {
				return [false];
			}
		}
		return [true];
	}
	
	function tribe_go_to_8() {		
		$('.tribe-week-grid-wrapper').scrollTop(480);
	}
	
	tribe_go_to_8();
	
	
	function tribe_set_allday_spanning_events_width() {	
	
		// Set vars
		var $ad = $('.tribe-grid-allday');
		var $ad_e = $ad.find('.vevent');
		var ad_c_w = parseInt($('.tribe-grid-content-wrap .column').width()) - 8;
		
		// Loop through the span #'s and set width
		for (var i=1; i<8; i++) {
			if( $ad_e.hasClass('tribe-dayspan' + i) ) {
				$ad.find('.tribe-dayspan' + i).children('div').css('width', ad_c_w*i+((i*2-2)*4+(i-1))+'px');	
			}
   		}
 					
	}
	
	tribe_set_allday_spanning_events_width();	
				
	function tribe_find_overlapped_events($week_events) {			    

		$week_events.each(function() {
				
			var $this = $(this);
			var $target = $this.next();
				
			if($target.length){
					
				var tAxis = $target.offset();
				var t_x = [tAxis.left, tAxis.left + $target.outerWidth()];
				var t_y = [tAxis.top, tAxis.top + $target.outerHeight()];			    
				var thisPos = $this.offset();
				var i_x = [thisPos.left, thisPos.left + $this.outerWidth()]
				var i_y = [thisPos.top, thisPos.top + $this.outerHeight()];

				if ( t_x[0] < i_x[1] && t_x[1] > i_x[0] && t_y[0] < i_y[1] && t_y[1] > i_y[0]) {
						
					// we've got an overlap
						
					$this.css({
						"left":"0",
						"width":"60%"
					});
					$target.css({
						"right":"0",
						"width":"60%"
					});
				}
			}
		});			
	}
	
	function tribe_display_week_view() {	
					
		var $week_events = $(".tribe-grid-body .tribe-grid-content-wrap .column > div[id*='tribe-events-event-']");
		var grid_height = $(".tribe-week-grid-inner-wrap").height();

		$week_events.hide();

		$week_events.each(function() {

			// iterate through each event in the main grid and set their length plus position in time.

			var $this = $(this);			
			var event_hour = $this.attr("data-hour");			
			var event_length = $this.attr("duration");	
			var event_min = $this.attr("data-min");

			// $event_target is our grid block with the same data-hour value as our event.

			var $event_target = $('.tribe-week-grid-block[data-hour="' + event_hour + '"]');

			// find it's offset from top of main grid container

			var event_position_top = 
			$event_target.offset().top -
			$event_target.parent().offset().top - 
			$event_target.parent().scrollTop();

			// add the events minutes to the offset (relies on grid block being 60px, 1px per minute, nice)

			event_position_top = parseInt(Math.round(event_position_top)) + parseInt(event_min);

			// test if we've exceeded space because this event runs into next day

			var free_space = grid_height - event_length - event_position_top;

			if(free_space < 0) {
				event_length = event_length + free_space - 14;
			}

			// set length and position from top for our event and show it. Also set length for the event anchor so the entire event is clickable.

			$this.css({
				"height":event_length + "px",
				"top":event_position_top + "px"
				}).show().find('a').css({
					"height":event_length - 16 + "px"
				});			
		});

		// deal with our overlaps

		tribe_find_overlapped_events($week_events);
		
		// set the height of the header columns to the height of the tallest

		var header_column_height = $(".tribe-grid-header .tribe-grid-content-wrap").height();

		$(".tribe-grid-header .column").height(header_column_height);

		// set the height of the allday columns to the height of the tallest

		var all_day_height = $(".tribe-grid-allday .tribe-grid-content-wrap").height();

		$(".tribe-grid-allday .column").height(all_day_height);

		// set the height of the other columns for week days to be as tall as the main container

		var week_day_height = $(".tribe-grid-body").height();

		$(".tribe-grid-body .tribe-grid-content-wrap .column").height(week_day_height);
	
	}
	tribe_display_week_view();
	
	$('.tribe-events-grid').resize(function() {
		tribe_set_allday_spanning_events_width();
		$('.tribe-grid-content-wrap .column').css('height','auto');
		tribe_display_week_view();
	});
	
	// Little splash of style
	
	$("div[id*='tribe-events-event-']").hide().fadeIn('slow');	

	if( tribe_ev.tests.pushstate && !tribe_ev.tests.map_view() ) {	

		var params = 'action=tribe_week&eventDate=' + tribe_ev.state.date;

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
				tribe_ev.state.url_params = state.tribe_url_params;
				tribe_ev.fn.pre_ajax( function() {
					tribe_events_week_ajax_post();	
				});
				
				tribe_ev.fn.set_form( tribe_ev.state.params );				
			} 
		} );
	}

	$( 'body' ).on( 'click', '.tribe-events-sub-nav a', function ( e ) {
		e.preventDefault();
		var $this = $(this);
		tribe_ev.state.popping = false;
		tribe_ev.state.date = $this.attr( "data-week" );
		tribe_ev.data.cur_url = $this.attr( "href" );
		tribe_ev.fn.update_picker( tribe_ev.state.date );
		tribe_ev.fn.pre_ajax( function() { 		
			tribe_events_week_ajax_post();	
		});
	} );
	
	function tribe_events_bar_weekajax_actions(e) {
		if( tribe_events_bar_action != 'change_view' ) {

			e.preventDefault();	
			var picker = $('#tribe-bar-date').val();
			tribe_ev.state.popping = false;
			if ( picker.length ) {
				tribe_ev.state.date = $('#tribe-bar-date').val();
				tribe_ev.data.cur_url = base_url + tribe_ev.state.date + '/';
			} else {
				tribe_ev.state.date = $('#tribe-events-header').attr('data-date');	
			}
			
			tribe_ev.fn.pre_ajax( function() { 
				tribe_events_week_ajax_post();
			});		
		}
	}	

	$( 'form#tribe-bar-form' ).on( 'submit', function (e) {		
		tribe_events_bar_weekajax_actions(e);
	} );
	
	$( '.tribe-bar-settings button[name="settingsUpdate"]' ).on( 'click', function (e) {	
		tribe_events_bar_weekajax_actions(e);	
		tribe_ev.fn.hide_settings();
	} );

	if( tribe_ev.tests.live_ajax() && tribe_ev.tests.pushstate ) {
		$('#tribe-bar-date').on( 'change', function (e) {
			if( !tribe_ev.tests.reset_on() ) {				
				tribe_events_bar_weekajax_actions(e);
			}			
		} );
	}	
	
	tribe_ev.fn.snap( '#tribe-events-content', 'body', '#tribe-events-footer .tribe-nav-previous a, #tribe-events-footer .tribe-nav-next a' );

	if( $('#tribe_events_filters_form').length ) {
		
		var $form = $('#tribe_events_filters_form');
		
		if( tribe_ev.tests.live_ajax() && tribe_ev.tests.pushstate ) {
			
			$form.find('input[type="submit"]').remove();
			
			function tribe_week_filter_submit() {
				tribe_ev.fn.disable_inputs( '#tribe_events_filters_form', 'input, select' );
				tribe_ev.state.popping = false;
				tribe_ev.state.date = $( '#tribe-events-header' ).attr( 'data-date' );					
				tribe_ev.fn.pre_ajax( function() { 
					tribe_events_week_ajax_post();	
				});
			}
			
			$form.on( "slidechange", ".ui-slider", function() {
				tribe_ev.fn.setup_ajax_timer( function() {
					tribe_week_filter_submit();	
				} );						
			} );
			$form.on("change", "input, select", function(){
				tribe_ev.fn.setup_ajax_timer( function() {
					tribe_week_filter_submit();	
				} );			
			});			
		}		
		
		$form.on( 'submit', function ( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				tribe_week_filter_submit();
			}
		} );
	}	


	function tribe_events_week_ajax_post() {

		tribe_ev.fn.spin_show();
		tribe_ev.state.pushcount = 0;
		
		if( !tribe_ev.state.popping ) {

			tribe_ev.state.params = {
				action:'tribe_week',
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

				tribe_ev.fn.enable_inputs( '#tribe_events_filters_form', 'input, select' );
				var tribe_filter_params = $('form#tribe_events_filters_form :input[value!=""]').serialize();
				tribe_ev.fn.disable_inputs( '#tribe_events_filters_form', 'input, select' );				
				if( tribe_filter_params.length ) {
					tribe_ev.state.params = tribe_ev.state.params + '&' + tribe_filter_params;
					tribe_ev.state.url_params = tribe_ev.state.url_params + '&' + tribe_filter_params;
				}
			}

			tribe_ev.state.pushstate = true;
			tribe_ev.state.do_string = false;
			
			if ( tribe_ev.state.pushcount > 0 || tribe_filter_params != '' ) {
				tribe_ev.state.pushstate = false;
				tribe_ev.state.do_string = true;				
			}
			
			
		} 

		if( tribe_ev.tests.pushstate ) {

			$.post(
				TribeWeek.ajaxurl,
				tribe_ev.state.params,
				function ( response ) {
					
					tribe_ev.fn.spin_hide();
					tribe_ev.state.initial_load = false;
					tribe_ev.fn.enable_inputs( '#tribe_events_filters_form', 'input, select' );
					
					if ( response !== '' ) {						
						
						$( '#tribe-events-content.tribe-events-week-grid' ).replaceWith( response );
						
						tribe_set_allday_spanning_events_width();
						tribe_display_week_view();
						
						$('.tribe-events-grid').resize(function() {
							tribe_set_allday_spanning_events_width();
							$('.tribe-grid-content-wrap .column').css('height','auto');
							tribe_display_week_view();
						});
						
						tribe_go_to_8();
						
						$("div[id*='tribe-events-event-']").hide().fadeIn('slow');
						
						if( tribe_ev.state.do_string ) {													
							history.pushState({
								"tribe_url_params": tribe_ev.state.url_params,
								"tribe_params": tribe_ev.state.params
							}, '', tribe_ev.data.cur_url + '?' + tribe_ev.state.url_params);															
						}						

						if( tribe_ev.state.pushstate ) {								
							history.pushState({
								"tribe_url_params": tribe_ev.state.url_params,
								"tribe_params": tribe_ev.state.params
							}, '', tribe_ev.data.cur_url);
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
});