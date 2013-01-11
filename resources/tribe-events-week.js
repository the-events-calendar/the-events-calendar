jQuery(document).ready(function($){	
	
	$( '#tribe-events-bar' ).addClass( 'tribe-has-datepicker' );
	
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
	
	
	function tribe_set_allday_spanning_events_width() {	
	
		// Set vars
		var $ad = $('.tribe-grid-allday');
		var $ad_e = $('.tribe-grid-allday .vevent');
		var ad_c_w = $('.tribe-grid-allday .vevent').not('[class^="tribe-dayspan"]').width();
		
		// Set width
		//X paddings * 4 + X borders
		if( $ad_e.hasClass('tribe-dayspan2') ) {
			$ad.find('.tribe-dayspan2').children('div').css('width', ad_c_w * 2 + (2 * 4 + 1) + 'px');
		} 
		if( $ad_e.hasClass('tribe-dayspan3') ) {
			$ad.find('.tribe-dayspan3').children('div').css('width', ad_c_w * 3 + (4 * 4 + 2) + 'px');
		}
		if( $ad_e.hasClass('tribe-dayspan4') ) {
			$ad.find('.tribe-dayspan4').children('div').css('width', ad_c_w * 4 + (6 * 4 + 3) + 'px');
		}
		if( $ad_e.hasClass('tribe-dayspan5') ) {
			$ad.find('.tribe-dayspan5').children('div').css('width', ad_c_w * 5 + (8 * 4 + 4) + 'px');
		}
		if( $ad_e.hasClass('tribe-dayspan6') ) {
			$ad.find('.tribe-dayspan6').children('div').css('width', ad_c_w * 6 + (10 * 4 + 5) + 'px');
		} 
		if( $ad_e.hasClass('tribe-dayspan7') ) {
			$ad.find('.tribe-dayspan7').children('div').css('width', ad_c_w * 7 + (12 * 4 + 6) + 'px');
		}
					
	}
	tribe_set_allday_spanning_events_width();
	$('.tribe-grid-allday').resize(function() { 
		tribe_set_allday_spanning_events_width();
	});
	
				
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

			// let's iterate through each event in the main grid and set their length plus position in time.

			var $this = $(this);			
			var event_hour = $this.attr("data-hour");			
			var event_length = $this.attr("duration");	
			var event_min = $this.attr("data-min");

			// $event_target is our grid block with the same data-hour value as our event.

			var $event_target = $('.tribe-week-grid-block[data-hour="' + event_hour + '"]');

			// let's find it's offset from top of main grid container

			var event_position_top = 
			$event_target.offset().top -
			$event_target.parent().offset().top - 
			$event_target.parent().scrollTop();

			// now let's add the events minutes to the offset (relies on grid block being 60px, 1px per minute, nice)

			event_position_top = parseInt(Math.round(event_position_top)) + parseInt(event_min);

			// now let's see if we've exceeding space because this event runs into next day

			var free_space = grid_height - event_length - event_position_top;

			if(free_space < 0) {
				event_length = event_length + free_space - 14;
			}

			// ok we have all our values, let's set length and position from top for our event and show it. And let's also set length for the event anchor so the entire event length is clickable.

			$this.css({
				"height":event_length + "px",
				"top":event_position_top + "px"
				}).show().find('a').css({
					"height":event_length - 16 + "px"
				});			
		});

		// now that we have set our events up correctly let's deal with our overlaps

		tribe_find_overlapped_events($week_events);

		// let's set the height of the allday columns to the height of the tallest

		var all_day_height = $(".tribe-grid-allday .tribe-grid-content-wrap").height();

		$(".tribe-grid-allday .column").height(all_day_height);

		// let's set the height of the other columns for week days to be as tall as the main container

		var week_day_height = $(".tribe-grid-body").height();

		$(".tribe-grid-body .tribe-grid-content-wrap .column").height(week_day_height);
	
	}
	
	tribe_display_week_view();
	
	if( typeof GeoLoc === 'undefined' ) 
		var GeoLoc = {"map_view":""};

	if( tribe_ev.tests.pushstate && !GeoLoc.map_view ) {		

		var initial_url = location.href;
		
		if( tribe_storage )
			tribe_storage.setItem( 'tribe_initial_load', 'true' );	

		$(window).on('popstate', function(event) {

			var initial_load = '';
			
			if( tribe_storage )
				initial_load = tribe_storage.getItem( 'tribe_initial_load' );	
			
			var state = event.originalEvent.state;

			if( state ) {			
				tribe_do_string = false;
				tribe_pushstate = false;	
				tribe_popping = true;
				tribe_params = state.tribe_params;
				tribe_ev.fn.pre_ajax( function() {
					tribe_events_week_ajax_post( '', '', tribe_pushstate, tribe_do_string, tribe_popping, tribe_params );
				});
			} else if( tribe_storage && initial_load !== 'true' ){				
				window.location = initial_url;
			}
		} );
	}

	$( 'body' ).on( 'click', '.tribe-events-sub-nav a', function ( e ) {
		e.preventDefault();		
		tribe_date = $( this ).attr( "data-week" );
		tribe_href_target = $( this ).attr( "href" );
		tribe_pushstate = true;
		tribe_do_string = false;
		$('#tribe-bar-date').val(tribe_date);
		tribe_ev.fn.pre_ajax( function() { 		
			tribe_events_week_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );	
		});
	} );

	// events bar intercept submit
	
	var tribe_picker = false;
	
	function tribe_events_bar_weekajax_actions(e) {
		if( tribe_events_bar_action != 'change_view' ) {

			e.preventDefault();			
			if ( tribe_picker )
				tribe_date = $('#tribe-bar-date').val();
			else
				tribe_date = $('#tribe-events-header').attr('data-date');
			
			tribe_href_target = tribe_ev.data.cur_url;	

			tribe_pushstate = false;
			tribe_do_string = true;			
			
			tribe_ev.fn.pre_ajax( function() { 
				tribe_events_week_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );
			});		
		}
	}	

	$( 'form#tribe-bar-form' ).on( 'submit', function (e) {
		tribe_picker = false;
		tribe_events_bar_weekajax_actions(e, tribe_picker);
	} );
	
	$( '.tribe-bar-settings button[name="settingsUpdate"]' ).on( 'click', function (e) {	
		tribe_picker = false;
		tribe_events_bar_weekajax_actions(e, tribe_picker);	
		$( '#tribe-events-bar [class^="tribe-bar-button-"]' )
			.removeClass( 'open' )
			.next( '.tribe-bar-drop-content' )
			.hide();
	} );

	if( tribe_ev.tests.live_ajax() ) {
		$('#tribe-bar-date').on( 'change', function (e) {
			if( !$('body').hasClass('tribe-reset-on') ) {
				tribe_picker = true;
				tribe_events_bar_weekajax_actions(e, tribe_picker);
			}			
		} );
	}	
	
	tribe_ev.fn.snap( '#tribe-events-content', '#tribe-events-content', '#tribe-events-footer .tribe-nav-previous a, #tribe-events-footer .tribe-nav-next a' );

	// if advanced filters active intercept submit

	if( $('#tribe_events_filters_form').length ) {
		
		var $form = $('#tribe_events_filters_form');
		
		if( tribe_ev.tests.live_ajax() ) {
			$( "#tribe_events_filters_form .ui-slider" ).on( "slidechange", function() {
				if( !$('body').hasClass('tribe-reset-on') ){
					tribe_date = $( '#tribe-events-header' ).attr( 'data-date' );					
					tribe_href_target = tribe_ev.data.cur_url;
					tribe_pushstate = false;
					tribe_do_string = true;
					tribe_ev.fn.pre_ajax( function() { 
						tribe_events_week_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );
					});
				}			
			} );
			$("#tribe_events_filters_form").on("change", "input, select", function(){
				if( !$('body').hasClass('tribe-reset-on') ){
					tribe_date = $( '#tribe-events-header' ).attr( 'data-date' );					
					tribe_href_target = tribe_ev.data.cur_url;
					tribe_pushstate = false;
					tribe_do_string = true;
					tribe_ev.fn.pre_ajax( function() { 
						tribe_events_week_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );
					});
				}
			});			
		}		
		
		$form.on( 'submit', function ( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				tribe_date = $( '#tribe-events-header' ).attr( 'data-date' );					
				tribe_href_target = tribe_ev.data.cur_url;
				tribe_pushstate = false;
				tribe_do_string = true;
				tribe_ev.fn.pre_ajax( function() { 
					tribe_events_week_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string );
				});
			}
		} );
	}	


	function tribe_events_week_ajax_post( tribe_date, tribe_href_target, tribe_pushstate, tribe_do_string, tribe_popping, tribe_params ) {

		$( '#tribe-events-footer, #tribe-events-header' ).find('.tribe-ajax-loading').show();
		
		if( !tribe_popping ) {

			tribe_params = {
				action:'tribe_week',
				eventDate:tribe_date
			};	

			// add any set values from event bar to params. want to use serialize but due to ie bug we are stuck with second	

			$( 'form#tribe-bar-form :input[value!=""]' ).each( function () {
				var $this = $( this );
				if( $this.val().length && !$this.hasClass('tribe-no-param') ) {
					if( $this.attr('name') !== 'tribe-bar-date' ) {
						if( $this.is(':checkbox') ) {
							if( $this.is(':checked') ) {
								tribe_params[$this.attr('name')] = $this.val();
								tribe_push_counter++;
							}
						} else {
							tribe_params[$this.attr('name')] = $this.val();
							tribe_push_counter++;
						}	
					}
				}			
			} );

			tribe_params = $.param(tribe_params);

			// check if advanced filters plugin is active

			if( $('#tribe_events_filters_form').length ) {

				// serialize any set values and add to params

				tribe_filter_params = $('form#tribe_events_filters_form :input[value!=""]').serialize();				
				if( tribe_filter_params.length ) {
					tribe_params = tribe_params + '&' + tribe_filter_params;
				}
			}			
			
			if ( tribe_push_counter > 0 || tribe_filter_params != '' ) {
				tribe_pushstate = false;
				tribe_do_string = true;				
			}
			
			
		} 

		if( tribe_ev.tests.pushstate ) {

			$.post(
				TribeWeek.ajaxurl,
				tribe_params,
				function ( response ) {
					$( '#tribe-events-footer, #tribe-events-header' ).find('.tribe-ajax-loading').hide();
					if( tribe_storage )
							tribe_storage.setItem( 'tribe_initial_load', 'false' );
					if ( response !== '' ) {						
						
						$( '#tribe-events-content.tribe-events-week-grid' ).replaceWith( response );

						tribe_display_week_view();
						tribe_set_allday_spanning_events_width();
						$('.tribe-grid-allday').resize(function() { 
							tribe_set_allday_spanning_events_width();
						});
						
						if( tribe_do_string ) {							
							tribe_href_target = tribe_href_target + '?' + tribe_params;								
							history.pushState({
								"tribe_date": tribe_date,
								"tribe_params": tribe_params
							}, '', tribe_href_target);															
						}						

						if( tribe_pushstate ) {								
							history.pushState({
								"tribe_date": tribe_date,
								"tribe_params": tribe_params
							}, '', tribe_href_target);
						}
					}
				}
			);
				
		} else {
			
			if( tribe_do_string ) {
				tribe_href_target = tribe_href_target + '?' + tribe_params;													
			}			
			
			window.location = tribe_href_target;			
		}
	}
		
});