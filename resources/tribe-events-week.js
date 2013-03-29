jQuery(document).ready(function($){

//	$( '.tribe-event-overlap').each(function(){
//		$(this).css({
//			'margin-left' : '15px',
//			'right' : ''
//		});
//	});

	tribe_ev.state.view = 'week';

	$( '#tribe-events-bar' ).addClass( 'tribe-has-datepicker' );
	tribe_ev.state.date = $( '#tribe-events-header' ).attr( 'data-date' );
	var base_url = $('#tribe-events-header .tribe-nav-next a').attr('href').slice(0, -11);
	
		// setup list view datepicker
		var tribe_var_datepickerOpts = {
			format: 'yyyy-mm-dd',
			showAnim: 'fadeIn',
			onRender: disableSpecificWeekDays
		};

		var tribeBarDate = $('#tribe-bar-date').bootstrapDatepicker( tribe_var_datepickerOpts ).on('changeDate', function() {
		  tribeBarDate.hide();
		}).data('datepicker');

	function disableSpecificWeekDays(date) {
        var start_day = $('#tribe-events-header').attr('data-startofweek');
		var daysToDisable = [0, 1, 2, 3, 4, 5, 6];
        delete daysToDisable[start_day];
		var day = date.getDay();
		for (i = 0; i < daysToDisable.length; i++) {
			if ($.inArray(day, daysToDisable) != -1) {
				return 'disabled';
			}
		}
		return '';
	}
	
	function tribe_go_to_8() {
		var $start = $('.time-row-8AM');
		$('.tribe-week-grid-wrapper').slimScroll({
			height: '500px',
			railVisible: true,
			alwaysVisible: true,
			start: $start
		});
	}
	
	tribe_go_to_8();
	
	function tribe_set_allday_placeholder_height() {		
		// Loop through placeholders and make sure height matches corresponding real event
		$('.tribe-event-placeholder').each(function(){
			var pid = $(this).attr("data-event-id");
			var hght = parseInt($('#tribe-events-event-' + pid ).outerHeight());
			$(this).height( hght );
		});
	}
	
	tribe_set_allday_placeholder_height();
	
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

            var css_left = {"left":"0","width":"65%"};
            var css_right = {"right":"0","width":"65%"};
				
			if($target.length){
					
				var tAxis = $target.offset();
				var t_x = [tAxis.left, tAxis.left + $target.outerWidth()];
				var t_y = [tAxis.top, tAxis.top + $target.outerHeight()];			    
				var thisPos = $this.offset();
				var i_x = [thisPos.left, thisPos.left + $this.outerWidth()];
				var i_y = [thisPos.top, thisPos.top + $this.outerHeight()];
				
				// Need to deal with case where overlapping event is sandwiched between
				// 2 overlapping events and is being pushed left
				
				// For each overlapping event that starts at same time evenly space left
				// For overlapping event that starts later on, start flush left
				
				//var o_w = 100 / # overlapping events starting at same time + '%';
				//var c_w = parseInt($('.tribe-grid-content-wrap .column').width());
				//var o_p = c_w / # overlapping events starting at same time + '%';

				if ( t_x[0] < i_x[1] && t_x[1] > i_x[0] && t_y[0] < i_y[1] && t_y[1] > i_y[0] ) {
						
					// we've got an overlap

                    if($this.is('.overlap-right')){
                        $target.css(css_left).addClass('overlap-left');
                    } else if($this.is('.overlap-left')){
                        $target.css(css_right).addClass('overlap-right');
                    } else {
                        $this.css(css_left);
                        $target.css(css_right).addClass('overlap-right');
                    }
				}
			}
		});			
	}
	
	function tribe_display_week_view() {	
					
		var $week_events = $(".tribe-grid-body .tribe-grid-content-wrap .column > div[id*='tribe-events-event-']");
		var grid_height = $(".tribe-week-grid-inner-wrap").height();

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
			}).find('a').css({
				"height":event_length - 16 + "px"
			});			
		});
		
		// Fade our events in upon js load
	
		$("div[id^='tribe-events-event-']").css({'visibility':'visible','opacity':'0'}).delay(500).animate({"opacity":"1"}, {duration: 250});		

		// deal with our overlaps

		tribe_find_overlapped_events($week_events);
		
		// set the height of the header columns to the height of the tallest

		var header_column_height = $(".tribe-grid-header .tribe-grid-content-wrap .column").height();

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
		tribe_set_allday_placeholder_height();
		tribe_set_allday_spanning_events_width();
		$('.tribe-grid-content-wrap .column').css('height','auto');
		tribe_display_week_view();
	});

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
		$('#tribe-bar-date').on( 'changeDate', function (e) {
			if( !tribe_ev.tests.reset_on() ) {				
				tribe_events_bar_weekajax_actions(e);
			}			
		} );
	}	
	
	tribe_ev.fn.snap( '#tribe-events-content', 'body', '#tribe-events-footer .tribe-nav-previous a, #tribe-events-footer .tribe-nav-next a' );

	$(tribe_ev.events).on("tribe_ev_runAjax", function() {
		tribe_events_week_ajax_post();		
	});


	function tribe_events_week_ajax_post() {

		tribe_ev.fn.spin_show();
		tribe_ev.state.pushcount = 0;
		
		if( !tribe_ev.state.popping ) {

            if (tribe_ev.state.filter_cats)
                tribe_ev.data.cur_url = $('#tribe-events-header').attr('data-baseurl');

			tribe_ev.state.params = {
				action:'tribe_week',
				eventDate:tribe_ev.state.date
			};
			
			tribe_ev.state.url_params = {};

            if( tribe_ev.state.category ) {
                tribe_ev.state.params['tribe_event_category'] = tribe_ev.state.category;
            }

			$(tribe_ev.events).trigger('tribe_ev_serializeBar');

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

		if( tribe_ev.tests.pushstate ) {

            $(tribe_ev.events).trigger('tribe_ev_ajaxStart').trigger('tribe_ev_weekView_AjaxStart');

			$.post(
				TribeWeek.ajaxurl,
				tribe_ev.state.params,
				function ( response ) {
					
					tribe_ev.fn.spin_hide();
					tribe_ev.state.initial_load = false;
					tribe_ev.fn.enable_inputs( '#tribe_events_filters_form', 'input, select' );
					
					if ( response.success ) {

                        tribe_ev.data.ajax_response = {
                            'total_count':'',
                            'view':response.view,
                            'max_pages':'',
                            'tribe_paged':'',
                            'timestamp':new Date().getTime()
                        };
						
						$( '#tribe-events-content.tribe-events-week-grid' ).replaceWith( response.html );
                        $( '.tribe-events-promo').next('.tribe-events-promo').remove();

                        var page_title = $( "#tribe-events-header" ).attr( 'data-title' );

                        $( document ).attr( 'title', page_title );
						
						tribe_set_allday_placeholder_height();
						tribe_set_allday_spanning_events_width();
						tribe_display_week_view();
						
						$('.tribe-events-grid').resize(function() {
							tribe_set_allday_placeholder_height();
							tribe_set_allday_spanning_events_width();
							$('.tribe-grid-content-wrap .column').css('height','auto');
							tribe_display_week_view();
						});
						
						tribe_go_to_8();
						
						$("div[id*='tribe-events-event-']").hide().fadeIn('fast');
						
						if( tribe_ev.state.do_string ) {													
							history.pushState({
								"tribe_url_params": tribe_ev.state.url_params,
								"tribe_params": tribe_ev.state.params
							}, page_title, tribe_ev.data.cur_url + '?' + tribe_ev.state.url_params);
						}						

						if( tribe_ev.state.pushstate ) {								
							history.pushState({
								"tribe_url_params": tribe_ev.state.url_params,
								"tribe_params": tribe_ev.state.params
							}, page_title, tribe_ev.data.cur_url);
						}

                        $(tribe_ev.events).trigger('tribe_ev_ajaxSuccess').trigger('tribe_ev_weekView_AjaxSuccess');

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
