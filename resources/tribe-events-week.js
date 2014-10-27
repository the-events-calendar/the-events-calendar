/**
 * @file This file contains all week view specific javascript.
 * This file should load after all vendors and core events javascript.
 * @version 3.0
 */

(function( window, document, $, td, te, tf, ts, tt, config, dbug ) {

	/*
	 * $    = jQuery
	 * td   = tribe_ev.data
	 * te   = tribe_ev.events
	 * tf   = tribe_ev.fn
	 * ts   = tribe_ev.state
	 * tt   = tribe_ev.tests
	 * dbug = tribe_debug
	 */

	$( document ).ready( function() {

		var $body = $( 'body' ),
			$tribedate = $( '#tribe-bar-date' ),
			$tribe_container = $( '#tribe-events' ),
			$tribe_bar = $( '#tribe-events-bar' ),
			$tribe_header = $( '#tribe-events-header' ),
			start_day = 0,
			date_mod = false,
			$first_event = $( '.column.tribe-week-grid-hours div:first-child' );

		if ( !Array.prototype.indexOf ) {

			Array.prototype.indexOf = function( elt /*, from*/ ) {
				var len = this.length >>> 0;

				var from = Number( arguments[1] ) || 0;
				from = (from < 0)
					? Math.ceil( from )
					: Math.floor( from );
				if ( from < 0 ) {
					from += len;
				}

				for ( ; from < len; from++ ) {
					if ( from in this &&
						this[from] === elt ) {
						return from;
					}
				}
				return -1;
			};
		}

		if ( $tribe_header.length ) {
			start_day = $tribe_header.data( 'startofweek' );
		}

		$tribe_bar.addClass( 'tribe-has-datepicker' );

		var initial_date = $tribe_header.data( 'date' );

		if ( ts.datepicker_format !== '0' ) {
			initial_date = tribeDateFormat( initial_date, "tribeQuery" );
		}

		ts.date = initial_date;

		var days_to_disable = [0, 1, 2, 3, 4, 5, 6],
			index = days_to_disable.indexOf( start_day );

		if ( index > -1 ) {
			days_to_disable.splice( index, 1 );
		}

		// begin display date formatting

		var date_format = 'yyyy-mm-dd';

		if ( ts.datepicker_format !== '0' ) {

			// we are not using the default query date format, lets grab it from the data array

			date_format = td.datepicker_formats.main[ts.datepicker_format];

			var url_date = tf.get_url_param( 'tribe-bar-date' );

			// if url date is set and datepicker format is different from query format
			// we need to fix the input value to emulate that before kicking in the datepicker

			if ( url_date ) {
				$tribedate.val( tribeDateFormat( url_date, ts.datepicker_format ) );
			}
		}

		td.datepicker_opts = {
			format            : date_format,
			weekStart         : start_day,
			daysOfWeekDisabled: days_to_disable,
			autoclose         : true
		};

		$tribedate
			.bootstrapDatepicker( td.datepicker_opts )
			.on( 'changeDate', function( e ) {
				if ( ts.updating_picker ) {
					return;
				}
				var date = tribeDateFormat( e.date, "tribeQuery" );
				ts.date = date;
				date_mod = true;
				if ( tt.no_bar() || tt.live_ajax() && tt.pushstate ) {
					if ( !tt.reset_on() ) {
						tribe_events_bar_weekajax_actions( e, date );
					}
				}

			} );

		function tribe_go_to_earliest_event() {

			$( '.tribe-week-grid-wrapper' ).slimScroll( {
				height       : '500px',
				railVisible  : true,
				alwaysVisible: true,
				start        : $first_event
			} );

		}

		function tribe_add_right_class() {
			$( '.tribe-grid-body .column:eq(5), .tribe-grid-body .column:eq(6), .tribe-grid-body .column:eq(7)' ).addClass( 'tribe-events-right' );
		}

		function tribe_set_allday_placeholder_height() {
			$( '.tribe-event-placeholder' ).each( function() {
				var pid = $( this ).attr( "data-event-id" );
				var hght = parseInt( $( '#tribe-events-event-' + pid ).outerHeight() );
				$( this ).height( hght );
			} );
		}

		function tribe_set_allday_spanning_events_width() {

			var $ad = $( '.tribe-grid-allday' );
			var $ad_e = $ad.find( '.vevent' );
			var ad_c_w = parseInt( $( '.tribe-grid-content-wrap .column' ).width() ) - 8;

			for ( var i = 1; i < 8; i++ ) {
				if ( $ad_e.hasClass( 'tribe-dayspan' + i ) ) {
					$ad.find( '.tribe-dayspan' + i ).children( 'div' ).css( 'width', ad_c_w * i + ((i * 2 - 2) * 4 + (i - 1)) + 'px' );
				}
			}

		}

		function tribe_find_overlapped_events( $week_events ) {

			$week_events.each( function() {

				var $this = $( this );
				var $target = $this.next();

				var css_left = {"left": "0", "width": "65%"};
				var css_right = {"right": "0", "width": "65%"};

				if ( $target.length ) {

					var tAxis = $target.offset();
					var t_x = [tAxis.left, tAxis.left + $target.outerWidth()];
					var t_y = [tAxis.top, tAxis.top + $target.outerHeight()];
					var thisPos = $this.offset();
					var i_x = [thisPos.left, thisPos.left + $this.outerWidth()];
					var i_y = [thisPos.top, thisPos.top + $this.outerHeight()];

					if ( t_x[0] < i_x[1] && t_x[1] > i_x[0] && t_y[0] < i_y[1] && t_y[1] > i_y[0] ) {

						if ( $this.is( '.overlap-right' ) ) {
							$target.css( css_left ).addClass( 'overlap-left' );
						}
						else if ( $this.is( '.overlap-left' ) ) {
							$target.css( css_right ).addClass( 'overlap-right' );
						}
						else {
							$this.css( css_left );
							$target.css( css_right ).addClass( 'overlap-right' );
						}
					}
				}
			} );
		}

		function tribe_display_week_view() {

			var $week_events = $( ".tribe-grid-body .tribe-grid-content-wrap .column > div[id*='tribe-events-event-']" ),
				grid_height = $( ".tribe-week-grid-inner-wrap" ).height(),
				offset_top = 5000;

			$week_events.each( function() {

				// iterate through each event in the main grid and set their length plus position in time.

				var $this = $( this ),
					event_hour = $this.attr( "data-hour" ),
					event_length = $this.attr( "data-duration" ),
					event_min = $this.attr( "data-min" );

				// $event_target is our grid block with the same data-hour value as our event.

				var $event_target = $( '.tribe-week-grid-block[data-hour="' + event_hour + '"]' );

				// find it's offset from top of main grid container

				var event_position_top =
					$event_target.offset().top -
						$event_target.parent().offset().top -
						$event_target.parent().scrollTop();

				// add the events minutes to the offset (relies on grid block being 60px, 1px per minute, nice)

				event_position_top = parseInt( Math.round( event_position_top ) ) + parseInt( event_min );

				// test if we've exceeded space because this event runs into next day

				var free_space = parseInt( grid_height ) - parseInt( event_length ) - parseInt( event_position_top );

				if ( free_space < 0 ) {
					event_length = event_length + free_space - 14;
				}

				// set length and position from top for our event and show it. Also set length for the event anchor so the entire event is clickable.

				var link_setup = {"height": event_length - 16 + "px"};

				if ( event_position_top < offset_top ) {
					offset_top = event_position_top;
					$first_event = $this;
				}

				$this
					.css( {
						"height": event_length + "px",
						"top"   : event_position_top + "px"
					} )
					.find( 'a' )
					.css( link_setup )
					.parent()
					.css( link_setup );
			} );

			if ( !$week_events.length ) {
				$first_event = $( '.column.tribe-week-grid-hours div:first-child' );
			}

			tribe_go_to_earliest_event();

			// Fade our events in upon js load

			$( "div[id^='tribe-events-event-']" ).css( {'visibility': 'visible', 'opacity': '0'} ).delay( 500 ).animate( {"opacity": "1"}, {duration: 250} );

			// deal with our overlaps

			tribe_find_overlapped_events( $week_events );

			// set the height of the header columns to the height of the tallest

			tribe_ev.fn.equal_height( $( ".tribe-grid-header .tribe-grid-content-wrap .column" ) );

			// set the height of the allday columns to the height of the tallest

			tribe_ev.fn.equal_height( $( ".tribe-grid-allday .column" ) );

			// set the height of the other columns for week days to be as tall as the main container

			setTimeout( function() {

				var week_day_height = $( ".tribe-grid-body" ).height();

				$( ".tribe-grid-body .tribe-grid-content-wrap .column" ).height( week_day_height );

			}, 250 );

		}

		function tribe_mobile_load_events( date ) {

			var $target = $( '.tribe-mobile-day[data-day="' + date + '"]' ),
				$events = $( '.column[title="' + date + '"] .tribe-week-event' );

			if ( $events.length ) {
				$events
					.each( function() {

						var $this = $( this );

						if ( $this.tribe_has_attr( 'data-tribejson' ) ) {

							var data = $this.data( 'tribejson' );

							$target
								.append( tribe_tmpl( 'tribe_tmpl_week_mobile', data ) );
						}

					} );
			}

		}

		function tribe_mobile_setup_day( date, day_attr ) {

			var $container = $( '#tribe-mobile-container' ),
				$target_day = $( '.tribe-mobile-day[data-day="' + date + '"]' );

			if ( $target_day.length ) {
				$target_day.show();
			}
			else {
				$container
					.append( '<div class="tribe-mobile-day" data-day="' + date + '"></div>' );

				tribe_mobile_load_events( date );
			}

			if ( !$target_day.length ) {
				$target_day = $( '.tribe-mobile-day[data-day="' + date + '"]' );
			}

			if ( !$target_day.find( 'h5' ).length && $target_day.find( '.tribe-events-mobile' ).length ) {
				$target_day.prepend( '<h5 class="tribe-mobile-day-date">' + day_attr + '</h5>' );
			}


		}

		function tribe_mobile_week_setup() {

			var $mobile_days = $( '.tribe-events-mobile-day' ),
				$tribe_grid = $( '#tribe-events-content > .tribe-events-grid' );

			if ( !$( '#tribe-mobile-container' ).length ) {
				$( '<div id="tribe-mobile-container" />' ).insertAfter( $tribe_grid );
			}

			$mobile_days.each( function() {
				var $this = $( this ),
					day_date = $this.attr( 'title' ),
					$grid_day_col = $( '.tribe-grid-content-wrap .column[title="' + day_date + '"]' ),
					day_attr = $grid_day_col.find( 'span' ).attr( 'data-full-date' );

				tribe_mobile_setup_day( day_date, day_attr );
			} );

		}

		function tribe_week_view_init() {
			if ( $body.is( '.tribe-mobile' ) ) {
				tribe_mobile_week_setup();
			}
			else {
				tribe_set_allday_placeholder_height();
				tribe_set_allday_spanning_events_width();
				tribe_add_right_class();
				tribe_display_week_view();
			}
		}

		tribe_week_view_init();

		$( te ).on( 'tribe_ev_resizeComplete', function() {
			tribe_week_view_init();
		} );

		if ( tt.pushstate && !tt.map_view() ) {

			var params = 'action=tribe_week&eventDate=' + ts.date;

			if ( td.params.length ) {
				params = params + '&' + td.params;
			}

			if ( ts.category ) {
				params = params + '&tribe_event_category=' + ts.category;
			}

			history.replaceState( {
				"tribe_params"    : params,
				"tribe_url_params": td.params
			}, '', location.href );

			$( window ).on( 'popstate', function( event ) {

				var state = event.originalEvent.state;

				if ( state ) {
					ts.do_string = false;
					ts.pushstate = false;
					ts.popping = true;
					ts.params = state.tribe_params;
					ts.url_params = state.tribe_url_params;
					tf.pre_ajax( function() {
						tribe_events_week_ajax_post();
					} );

					tf.set_form( ts.params );
				}
			} );
		}

		$tribe_container
			.on( 'click', '.tribe-events-nav-previous, .tribe-events-nav-next', function( e ) {
				e.preventDefault();
				if ( ts.ajax_running ) {
					return;
				}
				var $this = $( this ).find( 'a' );
				ts.popping = false;
				ts.date = $this.attr( "data-week" );
				td.cur_url = $this.attr( "href" );
				if ( ts.datepicker_format !== '0' ) {
					tf.update_picker( tribeDateFormat( ts.date, td.datepicker_formats.main[ts.datepicker_format] ) );
				}
				else {
					tf.update_picker( ts.date );
				}
				tf.pre_ajax( function() {
					tribe_events_week_ajax_post();
				} );
			} );

		/**
		 * @function tribe_events_bar_weekajax_actions
		 * @desc On events bar submit, this function collects the current state of the bar and sends it to the week view ajax handler.
		 * @param {event} e The event object.
		 * @param {string} date Date passed by datepicker.
		 */

		function tribe_events_bar_weekajax_actions( e, date ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				if ( ts.ajax_running ) {
					return;
				}

				var $tdate = $( '#tribe-bar-date' );

				ts.popping = false;

				if ( date ) {

					ts.date = date;
					td.cur_url = td.base_url + ts.date + '/';

				}
				else if ( $tdate.length && $tdate.val() !== '' ) {

					if ( ts.datepicker_format !== '0' ) {
						ts.date = tribeDateFormat( $tdate.bootstrapDatepicker( 'getDate' ), "tribeQuery" );
					}
					else {
						ts.date = $tdate.val();
					}

					td.cur_url = td.base_url + ts.date + '/';

				}
				else if ( date_mod ) {

					td.cur_url = td.base_url + ts.date + '/';

				}
				else {

					ts.date = td.cur_date;
					td.cur_url = td.base_url + td.cur_date + '/';

				}

				tf.pre_ajax( function() {
					tribe_events_week_ajax_post();
				} );
			}
		}

		$( 'form#tribe-bar-form' ).on( 'submit', function( e ) {
			tribe_events_bar_weekajax_actions( e, null );
		} );

		tf.snap( '#tribe-events-content', 'body', '#tribe-events-footer .tribe-events-nav-previous, #tribe-events-footer .tribe-events-nav-next' );

		$( te ).on( "tribe_ev_runAjax", function() {
			tribe_events_week_ajax_post();
		} );

		/**
		 * @function tribe_events_week_ajax_post
		 * @desc The ajax handler for week view.
		 * Fires the custom event 'tribe_ev_serializeBar' at start, then 'tribe_ev_collectParams' to gather any additional parameters before actually launching the ajax post request.
		 * As post begins 'tribe_ev_ajaxStart' and 'tribe_ev_weekView_AjaxStart' are fired, and then 'tribe_ev_ajaxSuccess' and 'tribe_ev_weekView_ajaxSuccess' are fired on success.
		 * Various functions in the events plugins hook into these events. They are triggered on the tribe_ev.events object.
		 */

		function tribe_events_week_ajax_post() {

			if ( tf.invalid_date( ts.date ) ) {
				return;
			}

			var $tribe_header = $( '#tribe-events-header' );

			$( '.tribe-events-grid' ).tribe_spin();
			ts.pushcount = 0;
			ts.ajax_running = true;

			if ( !ts.popping ) {

				if ( ts.filter_cats ) {
					td.cur_url = td.base_url;
				}

				ts.params = {
					action   : 'tribe_week',
					eventDate: ts.date
				};

				ts.url_params = {};

				if ( td.default_permalinks ) {
					if( !ts.url_params.hasOwnProperty( 'eventDate' ) ){
						ts.url_params['eventDate'] = ts.date;
					}
					if( !ts.url_params.hasOwnProperty( 'post_type' ) ){
						ts.url_params['post_type'] = config.events_post_type;
					}
					if( !ts.url_params.hasOwnProperty( 'eventDisplay' ) ){
						ts.url_params['eventDisplay'] = ts.view;
					}
				}

				if ( ts.category ) {
					ts.params['tribe_event_category'] = ts.category;
				}

				$( te ).trigger( 'tribe_ev_serializeBar' );

				ts.params = $.param( ts.params );
				ts.url_params = $.param( ts.url_params );

				$( te ).trigger( 'tribe_ev_collectParams' );

				ts.pushstate = true;
				ts.do_string = false;

				if ( ts.pushcount > 0 || ts.filters || td.default_permalinks ) {
					ts.pushstate = false;
					ts.do_string = true;
				}

			}

			if ( tt.pushstate ) {

				// @ifdef DEBUG
				dbug && debug.time( 'Week View Ajax Timer' );
				// @endif
				$( te ).trigger( 'tribe_ev_ajaxStart' ).trigger( 'tribe_ev_weekView_AjaxStart' );

				$.post(
					TribeWeek.ajaxurl,
					ts.params,
					function( response ) {

						ts.initial_load = false;
						tf.enable_inputs( '#tribe_events_filters_form', 'input, select' );

						if ( response.success ) {

							ts.ajax_running = false;

							td.ajax_response = {
								'total_count': '',
								'view'       : response.view,
								'max_pages'  : '',
								'tribe_paged': '',
								'timestamp'  : new Date().getTime()
							};

							var $the_content = $.parseHTML( response.html );

							$( '#tribe-events-content.tribe-events-week-grid' ).replaceWith( $the_content );

							tribe_week_view_init();

							$( "div[id*='tribe-events-event-']" ).hide().fadeIn( 'fast' );

							ts.page_title = $( '#tribe-events-header' ).data( 'title' );
							document.title = ts.page_title;

							if ( ts.do_string ) {
								if(td.cur_url.indexOf('?') !== -1){
									td.cur_url = td.cur_url.split("?")[0];
								}
								history.pushState( {
									"tribe_url_params": ts.url_params,
									"tribe_params"    : ts.params
								}, ts.page_title, td.cur_url + '?' + ts.url_params );
							}

							if ( ts.pushstate ) {
								history.pushState( {
									"tribe_url_params": ts.url_params,
									"tribe_params"    : ts.params
								}, ts.page_title, td.cur_url );
							}

							$( te )
								.trigger( 'tribe_ev_ajaxSuccess' )
								.trigger( 'tribe_ev_weekView_AjaxSuccess' );

							// @ifdef DEBUG
							dbug && debug.timeEnd( 'Week View Ajax Timer' );
							// @endif

						}
					}
				);

			}
			else {
				if ( ts.url_params.length ) {
					window.location = td.cur_url + '?' + ts.url_params;
				}
				else {
					window.location = td.cur_url;
				}
			}
		}

		// @ifdef DEBUG
		dbug && debug.info( 'TEC Debug: tribe-events-week.js successfully loaded' );
		ts.view && dbug && debug.timeEnd( 'Tribe JS Init Timer' );
		// @endif
	} );

})( window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_js_config, tribe_debug );
