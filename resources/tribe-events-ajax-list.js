/**
 * @file This file contains all list view specific javascript.
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

		var tribe_is_paged = tf.get_url_param( 'tribe_paged' ),
			$venue_view = $( '#tribe-events > .tribe-events-venue' );

		if ( tribe_is_paged ) {
			ts.paged = tribe_is_paged;
		}

		if ( tt.pushstate && !tt.map_view() ) {

			var params = 'action=tribe_list&tribe_paged=' + ts.paged;

			if ( td.params.length ) {
				params = params + '&' + td.params;
			}

			if ( ts.category ) {
				params = params + '&tribe_event_category=' + ts.category;
			}

			history.replaceState( {
				"tribe_params"    : params,
				"tribe_url_params": td.params
			}, document.title, location.href );

			$( window ).on( 'popstate', function( event ) {

				var state = event.originalEvent.state;

				if ( state && !$venue_view.length ) {
					ts.do_string = false;
					ts.pushstate = false;
					ts.popping = true;
					ts.params = state.tribe_params;
					ts.url_params = state.tribe_url_params;
					tf.pre_ajax( function() {
						tribe_events_list_ajax_post();
					} );

					tf.set_form( ts.params );
				}
			} );
		}

		$( '#tribe-events-content-wrapper' ).on( 'click', 'li.tribe-events-nav-next a',function( e ) {
			e.preventDefault();

			if ( ts.ajax_running ) {
				return;
			}

			if ( $( this ).parent().is( '.tribe-events-past' ) ) {
				ts.view = 'past';
			}
			else {
				ts.view = 'list';
			}

			td.cur_url = tf.url_path( $( this ).attr( 'href' ) );

			ts.paged++;

			ts.popping = false;
			tf.pre_ajax( function() {
				tribe_events_list_ajax_post();
			} );
		} ).on( 'click', 'li.tribe-events-nav-previous a', function( e ) {
			e.preventDefault();

			if ( ts.ajax_running ) {
				return;
			}

			if ( $( this ).parent().is( '.tribe-events-past' ) ) {
				ts.view = 'past';
			}
			else {
				ts.view = 'list';
			}

			td.cur_url = tf.url_path( $( this ).attr( 'href' ) );

			if ( ts.paged > 1 ) {
				ts.paged--;
			}
			ts.popping = false;
			tf.pre_ajax( function() {
				tribe_events_list_ajax_post();
			} );
		} );

		tf.snap( '#tribe-events-content-wrapper', '#tribe-events-content-wrapper', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a' );

		/**
		 * @function tribe_events_bar_listajax_actions
		 * @desc On events bar submit, this function collects the current state of the bar and sends it to the list view ajax handler.
		 * @param {event} e The event object.
		 */

		function tribe_events_bar_listajax_actions( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				if ( ts.ajax_running ) {
					return;
				}
				ts.paged = 1;
				ts.view = 'list';
				ts.popping = false;
				tf.pre_ajax( function() {
					tribe_events_list_ajax_post();
				} );
			}
		}

		if ( tt.no_bar() || tt.live_ajax() && tt.pushstate ) {
			$( '#tribe-events-bar' ).on( 'changeDate', '#tribe-bar-date', function( e ) {
				if ( !tt.reset_on() ) {
					ts.popping = false;
					tribe_events_bar_listajax_actions( e );
				}
			} );
		}

		$( 'form#tribe-bar-form' ).on( 'submit', function( e ) {
			ts.popping = false;
			tribe_events_bar_listajax_actions( e );
		} );

		$( te ).on( "tribe_ev_runAjax", function() {
			tribe_events_list_ajax_post();
		} );

		/**
		 * @function tribe_events_list_ajax_post
		 * @desc The ajax handler for list view.
		 * Fires the custom event 'tribe_ev_serializeBar' at start, then 'tribe_ev_collectParams' to gather any additional parameters before actually launching the ajax post request.
		 * As post begins 'tribe_ev_ajaxStart' and 'tribe_ev_listView_AjaxStart' are fired, and then 'tribe_ev_ajaxSuccess' and 'tribe_ev_listView_ajaxSuccess' are fired on success.
		 * Various functions in the events plugins hook into these events. They are triggered on the tribe_ev.events object.
		 */

		function tribe_events_list_ajax_post() {

			ts.ajax_running = true;

			if ( !ts.popping ) {

				if ( ts.filter_cats ) {
					td.cur_url = $( '#tribe-events-header' ).data( 'baseurl' );
				}

				var tribe_hash_string = $( '#tribe-events-list-hash' ).val();

				ts.params = {
					action             : 'tribe_list',
					tribe_paged        : ts.paged,
					tribe_event_display: ts.view
				};

				ts.url_params = {
					action     : 'tribe_list',
					tribe_paged: ts.paged,
					tribe_event_display: ts.view
				};

				if ( tribe_hash_string.length ) {
					ts.params['hash'] = tribe_hash_string;
				}

				if ( td.default_permalinks && !ts.url_params.hasOwnProperty( 'post_type' ) ) {
					ts.url_params['post_type'] = config.events_post_type;
				}

				if ( ts.category ) {
					ts.params['tribe_event_category'] = ts.category;
				}

				$( te ).trigger( 'tribe_ev_serializeBar' );

				if ( tf.invalid_date_in_params( ts.params ) ) {
					ts.ajax_running = false;
					return;
				}

				$( '#tribe-events-content .tribe-events-loop' ).tribe_spin();

				ts.params = $.param( ts.params );
				ts.url_params = $.param( ts.url_params );

				$( te ).trigger( 'tribe_ev_collectParams' );

				ts.pushstate = false;
				ts.do_string = true;

			}

			if ( tt.pushstate && !ts.filter_cats ) {

				// @ifdef DEBUG
				dbug && debug.time( 'List View Ajax Timer' );
				// @endif

				$( te ).trigger( 'tribe_ev_ajaxStart' ).trigger( 'tribe_ev_listView_AjaxStart' );

				$.post(
					TribeList.ajaxurl,
					ts.params,
					function( response ) {

						ts.initial_load = false;
						tf.enable_inputs( '#tribe_events_filters_form', 'input, select' );

						if ( response.success ) {

							ts.ajax_running = false;

							td.ajax_response = {
								'total_count': parseInt( response.total_count ),
								'view'       : response.view,
								'max_pages'  : response.max_pages,
								'tribe_paged': response.tribe_paged,
								'timestamp'  : new Date().getTime()
							};

							$( '#tribe-events-list-hash' ).val( response.hash );

							var $the_content = $.parseHTML( response.html );

							$( '#tribe-events-content' ).replaceWith( $the_content );
							if ( response.total_count === 0 ) {
								$( '#tribe-events-header .tribe-events-sub-nav' ).empty();
							}

							ts.page_title = $( '#tribe-events-header' ).data( 'title' );
							document.title = ts.page_title;

							if ( ts.do_string ) {
								history.pushState( {
									"tribe_params"    : ts.params,
									"tribe_url_params": ts.url_params
								}, ts.page_title, td.cur_url + '?' + ts.url_params );
							}

							if ( ts.pushstate ) {
								history.pushState( {
									"tribe_params"    : ts.params,
									"tribe_url_params": ts.url_params
								}, ts.page_title, td.cur_url );
							}

							$( te ).trigger( 'tribe_ev_ajaxSuccess' ).trigger( 'tribe_ev_listView_AjaxSuccess' );
							// @ifdef DEBUG
							dbug && debug.timeEnd( 'List View Ajax Timer' );
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
		dbug && debug.info( 'TEC Debug: tribe-events-ajax-list.js successfully loaded' );
		ts.view && dbug && debug.timeEnd( 'Tribe JS Init Timer' );
		// @endif
	} );

})( window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_js_config, tribe_debug );
