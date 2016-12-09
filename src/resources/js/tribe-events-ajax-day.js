/**
 * @file This file contains all day view specific javascript.
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

		var $nav_link = $( '[class^="tribe-events-nav-"] a' );
		var base_url = '/';

		if ( 'undefined' !== typeof config.events_base ) {
			base_url = config.events_base;
		} else if ( $nav_link.length ) {
			base_url = $nav_link.first().attr( 'href' ).slice( 0, -11 );
		}

		if ( ts.filter_cats ) {
			base_url = $( '#tribe-events-header' ).data( 'baseurl' ).slice( 0, -11 );
		}

		if ( td.default_permalinks ) {
			base_url = base_url.split("?")[0];
		}

		ts.date = $( '#tribe-events-header' ).data( 'date' );

		/**
		 * @function tribe_day_add_classes
		 * @desc Add css classes needed for correct styling of the day list.
		 */

		function tribe_day_add_classes() {
			if ( $( '.tribe-events-day-time-slot' ).length ) {
				$( '.tribe-events-day-time-slot' ).find( '.vevent:last' ).addClass( 'tribe-events-last' );
				$( '.tribe-events-day-time-slot:first' ).find( '.vevent:first' ).removeClass( 'tribe-events-first' );
			}
		}

		tribe_day_add_classes();

		if ( tt.pushstate && !tt.map_view() ) {

			var params = 'action=tribe_event_day&eventDate=' + ts.date;

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
					tf.pre_ajax( function() {
						tribe_events_day_ajax_post();
					} );

					tf.set_form( ts.params );
				}
			} );
		}

		$( '#tribe-events' ).on( 'click', '.tribe-events-nav-previous a, .tribe-events-nav-next a', function( e ) {
			e.preventDefault();
			if ( ts.ajax_running || ts.updating_picker ) {
				return;
			}
			var $this = $( this );
			ts.popping = false;
			ts.date = $this.attr( "data-day" );
			if ( ts.filter_cats ) {
				td.cur_url = base_url + ts.date + '/';
			}
			else {
				td.cur_url = $this.attr( "href" );
			}
			if ( ts.datepicker_format !== '0' ) {
				tf.update_picker( tribeDateFormat( ts.date, td.datepicker_formats.main[ts.datepicker_format] ) );
			}
			else {
				tf.update_picker( ts.date );
			}
			tf.pre_ajax( function() {
				tribe_events_day_ajax_post();
			} );
		} );

		tf.snap( '#tribe-events-bar', '#tribe-events', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a' );

		/**
		 * @function tribe_events_bar_dayajax_actions
		 * @desc On events bar submit, this function collects the current state of the bar and sends it to the day view ajax handler.
		 * @param {event} e The event object.
		 */

		function tribe_events_bar_dayajax_actions( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				if ( ts.ajax_running ) {
					return;
				}
				var picker = $( '#tribe-bar-date' ).val();
				ts.popping = false;
				if ( picker.length ) {
					ts.date = $( '#tribe-bar-date' ).val();
					td.cur_url = ( td.default_permalinks ) ? base_url + '=' + td.cur_date : base_url + td.cur_date + '/';
				}
				else {
					ts.date = td.cur_date;
					td.cur_url = ( td.default_permalinks ) ? base_url + '=' + td.cur_date : base_url + td.cur_date + '/';
				}
				tf.pre_ajax( function() {
					tribe_events_day_ajax_post();
				} );

			}
		}

		$( 'form#tribe-bar-form' ).on( 'submit', function( e ) {
			tribe_events_bar_dayajax_actions( e );
		} );

		if ( tt.no_bar() || tt.live_ajax() && tt.pushstate ) {

			$( '#tribe-bar-date' ).on( 'changeDate', function( e ) {
				if( ts.updating_picker ){
					return;
				}

				if ( !tt.reset_on() ) {
					ts.popping = false;
					if ( ts.datepicker_format !== '0' ) {
						ts.date = tribeDateFormat( $( this ).bootstrapDatepicker( 'getDate' ), "tribeQuery" );
					}
					else {
						ts.date = $( this ).val();
					}
					td.cur_url = ( td.default_permalinks ) ? base_url : base_url + ts.date + '/';

					tf.pre_ajax( function() {
						tribe_events_day_ajax_post();
					} );
				}
			} );

		}

		$( te ).on( 'tribe_ev_runAjax', function() {
			tribe_events_day_ajax_post();
		} );

		$( te ).on( 'tribe_ev_updatingRecurrence', function() {
			if ( ts.filter_cats ) {
				td.cur_url = ( td.default_permalinks ) ? base_url + '=' + td.cur_date : base_url + td.cur_date + '/';
			}
			else {
				td.cur_url = $( '#tribe-events-header' ).attr( "data-baseurl" );
			}
			ts.popping = false;
		} );

		/**
		 * @function tribe_events_day_ajax_post
		 * @desc The ajax handler for day view.
		 * Fires the custom event 'tribe_ev_serializeBar' at start, then 'tribe_ev_collectParams' to gather any additional parameters before actually launching the ajax post request.
		 * As post begins 'tribe_ev_ajaxStart' and 'tribe_ev_dayView_AjaxStart' are fired, and then 'tribe_ev_ajaxSuccess' and 'tribe_ev_dayView_ajaxSuccess' are fired on success.
		 * Various functions in the events plugins hook into these events. They are triggered on the tribe_ev.events object.
		 */

		function tribe_events_day_ajax_post() {

			if ( tf.invalid_date( ts.date ) ) {
				return;
			}

			ts.pushcount = 0;
			ts.ajax_running = true;

			if ( !ts.popping ) {

				ts.url_params = {};

				ts.params = {
					action   : 'tribe_event_day',
					eventDate: ts.date,
					featured : tf.is_featured()
				};

				ts.url_params = {
					action: 'tribe_event_day'
				};

				if ( ts.category ) {
					ts.params['tribe_event_category'] = ts.category;
				}

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

			if ( tt.pushstate && !ts.filter_cats ) {

				// @ifdef DEBUG
				dbug && debug.time( 'Day View Ajax Timer' );
				// @endif

				$( te ).trigger( 'tribe_ev_ajaxStart' ).trigger( 'tribe_ev_dayView_AjaxStart' );

				$( '#tribe-events-content .tribe-events-loop' ).tribe_spin();

				$.post(
					TribeCalendar.ajaxurl,
					ts.params,
					function( response ) {

						ts.initial_load = false;
						tf.enable_inputs( '#tribe_events_filters_form', 'input, select' );

						if ( response.success ) {

							ts.ajax_running = false;

							td.ajax_response = {
								'total_count': parseInt( response.total_count ),
								'view'       : response.view,
								'max_pages'  : '',
								'tribe_paged': '',
								'timestamp'  : new Date().getTime()
							};

							var $the_content = $.parseHTML( response.html );

							$( '#tribe-events-content' ).replaceWith( $the_content );

							if ( response.total_count === 0 ) {
								$( '#tribe-events-header .tribe-events-sub-nav' ).empty();
							}
							$( '.tribe-events-promo' ).next( '.tribe-events-promo' ).remove();

							ts.page_title = $( '#tribe-events-header' ).data( 'title' );
							document.title = ts.page_title;

							// @TODO: We need to D.R.Y. this assignment and the following if statement about shortcodes/do_string
							// Ensure that the base URL is, in fact, the URL we want
							td.cur_url = tf.get_base_url();

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

							tribe_day_add_classes();

							$( te ).trigger( 'tribe_ev_ajaxSuccess' ).trigger( 'tribe_ev_dayView_AjaxSuccess' );
							$( te ).trigger( 'ajax-success.tribe' ).trigger( 'tribe_ev_dayView_AjaxSuccess' );

							// @ifdef DEBUG
							dbug && debug.timeEnd( 'Day View Ajax Timer' );
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
		dbug && debug.info( 'TEC Debug: tribe-events-ajax-day.js successfully loaded' );
		ts.view && dbug && debug.timeEnd( 'Tribe JS Init Timer' );
		// @endif

	} );

})( window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_js_config, tribe_debug );
