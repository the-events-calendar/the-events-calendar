/**
 * @file This file contains all month view specific javascript.
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

		var $body        = $( 'body' );
		var $nav_link    = $( '[class^="tribe-events-nav-"] a' );
		var initial_date = tf.get_url_param( 'tribe-bar-date' );
		var $wrapper     = $( document.getElementById( 'tribe-events' ) );
		var $tribedate   = $( document.getElementById( 'tribe-bar-date' ) );
		var date_mod     = false;

		var base_url = '/';

		if ( 'undefined' !== typeof config.events_base ) {
			base_url =  $( document.getElementById( 'tribe-events-header' ) ).data( 'baseurl' );
		} else if ( $nav_link.length ) {
			base_url = $nav_link.first().attr( 'href' ).slice( 0, -8 );
		}

		if ( td.default_permalinks ) {
			base_url = base_url.split("?")[0];
		}

		if ( $( '.tribe-events-calendar' ).length && $( document.getElementById( 'tribe-events-bar' ) ).length ) {
			if ( initial_date && initial_date.length > 7 ) {
				$( document.getElementById( 'tribe-bar-date-day' ) ).val( initial_date.slice( -3 ) );
				$tribedate.val( initial_date.substring( 0, 7 ) );
			}
		}

		// begin display date formatting

		var date_format = 'yyyy-mm';

		if ( ts.datepicker_format !== '0' ) {

			// we are not using the default query date format, lets grab it from the data array

			var arr_key  = parseInt( ts.datepicker_format );
			var mask_key = 'm' + ts.datepicker_format.toString();

			date_format = td.datepicker_formats.month[arr_key];

			// if url date is set and datepicker format is different from query format
			// we need to fix the input value to emulate that before kicking in the datepicker

			if ( initial_date ) {
				if ( initial_date.length <= 7 ) {
					initial_date = initial_date + '-01';
				}

				$tribedate.val( tribeDateFormat( initial_date, mask_key ) );
			}
		}

		td.datepicker_opts = {
			format      : date_format,
			minViewMode : 'months',
			autoclose   : true
		};

		$tribedate
			.bootstrapDatepicker( td.datepicker_opts )
			.on( 'changeDate', function( e ) {

				ts.mdate = e.date;

				var year  = e.date.getFullYear();
				var month = ( '0' + ( e.date.getMonth() + 1 ) ).slice( -2 );

				date_mod = true;
				ts.date  = year + '-' + month;

				if ( tt.no_bar() || tt.live_ajax() && tt.pushstate ) {
					if ( ts.ajax_running || ts.updating_picker ) {
						return;
					}
					if ( ts.filter_cats ) {
						td.cur_url = $( document.getElementById( 'tribe-events-header' ) ).data( 'baseurl' ) + ts.date + '/';
					}
					else {
						if ( td.default_permalinks ) {
							td.cur_url = base_url;
						} else {
							td.cur_url = base_url + ts.date + '/';
						}
					}

					ts.popping = false;

					tf.pre_ajax( function() {
						tribe_events_calendar_ajax_post();
					} );
				}

			} );

		function tribe_mobile_load_events( date ) {
			var $target = $( '.tribe-mobile-day[data-day="' + date + '"]' );
			var $cell   = $( '.tribe-events-calendar td[data-day="' + date + '"]' );
			var $more   = $cell.find( '.tribe-events-viewmore' );
			var $events = $cell.find( '.type-tribe_events' );

			if ( $events.length ) {
				$events
					.each( function() {

						var $this = $( this );

						if ( $this.tribe_has_attr( 'data-tribejson' ) ) {

							var data = $this.data( 'tribejson' );

							$target.append( tribe_tmpl( 'tribe_tmpl_month_mobile', data ) );
						}

					} );

				if ( $more.length ) {
					$target
						.append( $more.clone() );
				}
			}

		}

		function tribe_mobile_setup_day( $date ) {
			
			var data  = $date.data( 'tribejson' );
			data.date = $date.attr( 'data-day' );

			var $calendar  = $date.parents( '.tribe-events-calendar' );
			var $container = $calendar.next( document.getElementById( 'tribe-mobile-container' ) );
			var $days      = $container.find( '.tribe-mobile-day' );
			var $triggers  = $calendar.find( '.mobile-trigger' );
			var _active    = '[data-day="' + data.date + '"]';
			var $day       = $days.filter( _active );

			data.has_events = $date.hasClass( 'tribe-events-has-events' );

			$triggers.removeClass( 'mobile-active' )
				// If full_date_name is empty then default to highlighting the first day of the current month
				.filter( _active ).addClass( 'mobile-active' );

			$days.hide();

			if ( $day.length ) {
				$day.show();
			} else {
				$container.append( tribe_tmpl( 'tribe_tmpl_month_mobile_day_header', data ) );

				tribe_mobile_load_events( data.date );
			}
		}

		function tribe_mobile_month_setup() {

			var $today          = $wrapper.find( '.tribe-events-present' );
			var $mobile_trigger = $wrapper.find( '.mobile-trigger' );
			var $tribe_grid     = $wrapper.find( document.getElementById( 'tribe-events-content' ) ).find( '.tribe-events-calendar'  );

			if ( ! $( document.getElementById( 'tribe-mobile-container' ) ).length ) {
				$( '<div id="tribe-mobile-container" />' ).insertAfter( $tribe_grid );
			}

			if ( $today.length && $today.is( '.tribe-events-thismonth' ) ) {
				tribe_mobile_setup_day( $today );
			}
			else {
				var $first_current_day = $mobile_trigger.filter( '.tribe-events-thismonth' ).first();
				tribe_mobile_setup_day( $first_current_day );
			}

		}

		function tribe_mobile_day_abbr() {

			$wrapper.find( '.tribe-events-calendar th' ).each( function() {
				var $this    = $( this );
				var day_abbr = $this.attr( 'data-day-abbr' );
				var day_full = $this.attr( 'title' );

				if ( $body.is( '.tribe-mobile' ) ) {
					$this.text( day_abbr );
				}
				else {
					$this.text( day_full );
				}
			} );

		}

		function tribe_month_view_init( resize ) {
			if ( $body.is( '.tribe-mobile' ) ) {
				tribe_mobile_day_abbr();
				tribe_mobile_month_setup();
			}
			else {
				if ( resize ) {
					tribe_mobile_day_abbr();
				}
			}
		}

		tribe_month_view_init( true );

		$( te ).on( 'tribe_ev_resizeComplete', function() {
			tribe_month_view_init( true );
		} );

		if ( tt.pushstate && !tt.map_view() ) {

			var params = 'action=tribe_calendar&eventDate=' + $( '#tribe-events-header' ).data( 'date' );

			if ( td.params.length ) {
				params = params + '&' + td.params;
			}

			if ( ts.category ) {
				params = params + '&tribe_event_category=' + ts.category;
			}

			if ( tf.is_featured() ) {
				params = params + '&featured=1';
			}

			history.replaceState( {
				"tribe_params": params
			}, ts.page_title, location.href );

			$( window ).on( 'popstate', function( event ) {

				var state = event.originalEvent.state;

				if ( state ) {
					ts.do_string = false;
					ts.pushstate = false;
					ts.popping = true;
					ts.params = state.tribe_params;
					tf.pre_ajax( function() {
						tribe_events_calendar_ajax_post();
					} );

					tf.set_form( ts.params );
				}
			} );
		}

		$( document.getElementById( 'tribe-events' ) )
			.on( 'click', '.tribe-events-nav-previous, .tribe-events-nav-next', function( e ) {
				e.preventDefault();
				if ( ts.ajax_running ) {
					return;
				}

				var $this = $( this ).find( 'a' ),
					url;

				ts.date = $this.data( "month" );
				ts.mdate = ts.date + '-01';
				if ( ts.datepicker_format !== '0' ) {
					tf.update_picker( tribeDateFormat( ts.mdate, mask_key ) );
				}
				else {
					tf.update_picker( ts.date );
				}

				if ( ts.filter_cats ) {
					url = $( '#tribe-events-header' ).data( 'baseurl' );
				} else {
					url = $this.attr( "href" );
				}

				// If we don't have Permalink
				if ( td.default_permalinks ) {
					url = td.cur_url.split("?")[0];
				}

				// Update the baseurl
				tf.update_base_url( url );

				ts.popping = false;
				tf.pre_ajax( function() {
					tribe_events_calendar_ajax_post();
				} );
			} )
			.on( 'click', 'td.tribe-events-thismonth a', function( e ) {
				e.stopPropagation();
			} )
			.on( 'click', '[id*="tribe-events-daynum-"] a', function( e ) {
				if ( $body.is( '.tribe-mobile' ) ) {
					e.preventDefault();

					var $trigger = $( this ).closest( '.mobile-trigger' );
					tribe_mobile_setup_day( $trigger );

				}
			} )
			.on( 'click', '.mobile-trigger', function( e ) {
				if ( $body.is( '.tribe-mobile' ) ) {
					e.preventDefault();
					e.stopPropagation();
					tribe_mobile_setup_day( $( this ) );
				}
			} );

		tf.snap( '#tribe-bar-form', 'body', '#tribe-events-footer .tribe-events-nav-previous, #tribe-events-footer .tribe-events-nav-next' );

		/**
		 * @function tribe_events_bar_calendar_ajax_actions
		 * @desc On events bar submit, this function collects the current state of the bar and sends it to the month view ajax handler.
		 * @param {event} e The event object.
		 */

		function tribe_events_bar_calendar_ajax_actions( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				if ( ts.ajax_running ) {
					return;
				}
				if ( $tribedate.val().length ) {
					if ( ts.datepicker_format !== '0' ) {
						ts.date = tribeDateFormat( $tribedate.bootstrapDatepicker( 'getDate' ), 'tribeMonthQuery' );
					}
					else {
						ts.date = $tribedate.val();
					}
				}
				else {
					if ( !date_mod ) {
						ts.date = td.cur_date.slice( 0, -3 );
					}
				}

				if ( ts.filter_cats ) {
					td.cur_url = $( '#tribe-events-header' ).data( 'baseurl' ) + ts.date + '/';
				}
				else {
					if ( td.default_permalinks ) {
						td.cur_url = base_url;
					} else {
						td.cur_url = base_url + ts.date + '/';
					}
				}
				ts.popping = false;
				tf.pre_ajax( function() {
					tribe_events_calendar_ajax_post();
				} );
			}
		}

		$( 'form#tribe-bar-form' ).on( 'submit', function( e ) {
			tribe_events_bar_calendar_ajax_actions( e );
		} );

		$( te ).on( 'tribe_ev_runAjax', function() {
			tribe_events_calendar_ajax_post();
		} );

		$( te ).on( 'tribe_ev_updatingRecurrence', function() {
			ts.date = $( '#tribe-events-header' ).data( "date" );
			if ( ts.filter_cats ) {
				td.cur_url = $( '#tribe-events-header' ).data( 'baseurl' ) + ts.date + '/';
			}
			else {
				if ( td.default_permalinks ) {
					td.cur_url = base_url;
				} else {
					td.cur_url = base_url + ts.date + '/';
				}
			}
			ts.popping = false;
		} );
		/**
		 * @function tribe_events_calendar_ajax_post
		 * @desc The ajax handler for month view.
		 * Fires the custom event 'tribe_ev_serializeBar' at start, then 'tribe_ev_collectParams' to gather any additional paramters before actually launching the ajax post request.
		 * As post begins 'tribe_ev_ajaxStart' and 'tribe_ev_monthView_AjaxStart' are fired, and then 'tribe_ev_ajaxSuccess' and 'tribe_ev_monthView_ajaxSuccess' are fired on success.
		 * Various functions in the events plugins hook into these events. They are triggered on the tribe_ev.events object.
		 */

		function tribe_events_calendar_ajax_post() {

			if ( tf.invalid_date( ts.date ) ) {
				return;
			}

			$( '.tribe-events-calendar' ).tribe_spin();
			ts.pushcount = 0;
			ts.ajax_running = true;

			if ( !ts.popping ) {

				ts.params = {
					action   : 'tribe_calendar',
					eventDate: ts.date,
					featured:  tf.is_featured()
				};

				ts.url_params = {};

				if ( ts.category ) {
					ts.params.tribe_event_category = ts.category;
					ts.url_params.tribe_events_cat = ts.category;
				}

				if ( td.default_permalinks ) {
					if( !ts.url_params.hasOwnProperty( 'post_type' ) ){
						ts.url_params['post_type'] = config.events_post_type;
					}
					if( !ts.url_params.hasOwnProperty( 'eventDisplay' ) ){
						ts.url_params['eventDisplay'] = ts.view;
					}
				}

				$( te ).trigger( 'tribe_ev_serializeBar' );

				ts.params = $.param( ts.params );
				ts.url_params = $.param( ts.url_params );

				$( te ).trigger( 'tribe_ev_collectParams' );

				if ( ts.pushcount > 0 || ts.filters || td.default_permalinks || ts.category ) {
					ts.do_string = true;
					ts.pushstate = false;
				}
				else {
					ts.do_string = false;
					ts.pushstate = true;
				}
			}

			if ( tt.pushstate && !ts.filter_cats ) {

				// @ifdef DEBUG
				dbug && debug.time( 'Month View Ajax Timer' );
				// @endif

				$( te ).trigger( 'tribe_ev_ajaxStart' ).trigger( 'tribe_ev_monthView_AjaxStart' );

				$.post(
					TribeCalendar.ajaxurl,
					ts.params,
					function( response ) {

						ts.initial_load = false;
						tf.enable_inputs( '#tribe_events_filters_form', 'input, select' );

						// If it's not a succesful request we bail here
						if ( ! response.success ) {
							return
						}

						// Flag the end of the AJAX request
						ts.ajax_running = false;

						td.ajax_response = {
							'total_count': '',
							'view'       : response.view,
							'max_pages'  : '',
							'tribe_paged': '',
							'timestamp'  : new Date().getTime()
						};

						// @ifdef DEBUG
						if ( dbug && response.html === 0 ) {
							debug.warn( 'Month view ajax had an error in the query and returned 0.' );
						}
						// @endif

						// @TODO: We need to D.R.Y. this assignment and the following if statement about shortcodes/do_string
						// Ensure that the base URL is, in fact, the URL we want
						td.cur_url = tf.get_base_url();

						var $the_content = '';
						if ( $.isFunction( $.fn.parseHTML ) ) {
							$the_content = $.parseHTML( response.html );
						} else {
							$the_content = response.html;
						}

						$( '#tribe-events-content' ).replaceWith( $the_content );

						tribe_month_view_init( true );

						ts.page_title = $( '#tribe-events-header' ).data( 'title' );
						document.title = ts.page_title;

						// we only want to add query args for Shortcodes and ugly URL sites
						if (
								$( '#tribe-events.tribe-events-shortcode' ).length
								|| ts.do_string
						) {
							if ( -1 !== td.cur_url.indexOf( '?' ) ) {
								td.cur_url = td.cur_url.split( '?' )[0];
							}

							td.cur_url = td.cur_url + '?' + ts.url_params;
						}

						if ( ts.do_string ) {
							history.pushState( {
								"tribe_date"  : ts.date,
								"tribe_params": ts.params
							}, ts.page_title, td.cur_url );
						}

						if ( ts.pushstate ) {
							history.pushState( {
								"tribe_date"  : ts.date,
								"tribe_params": ts.params
							}, ts.page_title, td.cur_url );
						}

						$( te ).trigger( 'tribe_ev_ajaxSuccess' ).trigger( 'tribe_ev_monthView_ajaxSuccess' );
						$( te ).trigger( 'ajax-success.tribe' ).trigger( 'tribe_ev_monthView_ajaxSuccess' );

						// @ifdef DEBUG
						dbug && debug.timeEnd( 'Month View Ajax Timer' );
						// @endif
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
		dbug && debug.info( 'TEC Debug: tribe-events-ajax-calendar.js successfully loaded, Tribe Events Init finished' );
		dbug && debug.timeEnd( 'Tribe JS Init Timer' );
		// @endif
	} );

})( window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_js_config, tribe_debug );
