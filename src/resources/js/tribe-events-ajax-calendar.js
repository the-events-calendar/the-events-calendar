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

	$( function() {

		var $body        = $( 'body' );
		var $navLink     = $( '[class^="tribe-events-nav-"] a' );
		var $wrapper     = $( document.getElementById( 'tribe-events' ) );
		var $tribedate   = $( document.getElementById( 'tribe-bar-date' ) );
		var dateMod      = false;
		var maskKey      = 'm' + ts.datepicker_format.toString();
		var dateFormat   = tribeDateFormat.masks[maskKey] || 'yyyy-mm';

		let initialDateInfo = tribeUtils.getInitialDateInfo( maskKey, dateFormat, true );

		if ( 1 > $wrapper.length ) {
			return;
		}

		// Bail if we're on single event page
		if ( $body.hasClass( 'single-tribe_events' ) ) {
			return;
		}

		var baseUrl = '/';

		if ( 'undefined' !== typeof config.events_base ) {
			baseUrl =  $( document.getElementById( 'tribe-events-header' ) ).data( 'baseurl' );
		} else if ( $navLink.length ) {
			baseUrl = $navLink.first().attr( 'href' ).slice( 0, -8 );
		}

		if ( td.default_permalinks ) {
			baseUrl = baseUrl.split("?")[0];
		}

		if (
			$( '.tribe-events-calendar' ).length &&
			$( document.getElementById( 'tribe-events-bar' ) ).length
		) {
			$( document.getElementById( 'tribe-bar-date-day' ) )
				.val( initialDateInfo.defaultFormattedDate );
			$tribedate.val( initialDateInfo.formattedDate );
		}

		td.datepicker_opts = {
			format      : dateFormat,
			minViewMode : 'months',
			autoclose   : true
		};

		// Set up some specific strings for datepicker i18n.
		tribe_ev.fn.ensure_datepicker_i18n();

		$tribedate
			.bootstrapDatepicker( td.datepicker_opts )
			.on( 'changeDate', function( e ) {

				ts.mdate = e.date;

				var year  = e.date.getFullYear();
				var month = ( '0' + ( e.date.getMonth() + 1 ) ).slice( -2 );
				var day   = ( '0' + ( e.date.getDate() ) ).slice( -2 );

				dateMod = true;
				ts.date = maybeAlterDayOfMonth( year + '-' + month + '-' + day );

				if ( tt.no_bar() || tt.live_ajax() && tt.pushstate ) {
					if ( ts.ajax_running || ts.updating_picker ) {
						return;
					}
					if ( ts.filter_cats ) {
						td.cur_url = $( document.getElementById( 'tribe-events-header' ) )
							.data( 'baseurl' ) + ts.date + '/';
					}
					else {
						if ( td.default_permalinks ) {
							td.cur_url = baseUrl;
						} else {
							td.cur_url = baseUrl + ts.date + '/';
						}
					}

					ts.popping = false;

					tf.pre_ajax( function() {
						tribe_events_calendar_ajax_post();
					} );
				}

			} );

		function maybeAlterDayOfMonth( date ) {
			if ( ! date ) {
				return date;
			}

			var now = new Date();
			var initialDateMonth = date.substr( 5, 2 );
			var currentMonth     = ( '0' + ( now.getMonth() + 1 ) ).substr( -2 );
			var currentDay       = ( '0' + now.getDate() ).substr( -2 );

			if ( initialDateMonth === currentMonth ) {
				if ( date.length <= 7 ) {
					date = date + '-' + currentDay;
				} else {
					date = date.substr( 0, 8 ) + currentDay;
				}
			} else {
				if ( date.length <= 7 ) {
					date = date + '-01';
				} else {
					date = date.substr( 0, 8 ) + '01';
				}
			}

			return date;
		}

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
							if ( 'string' === typeof data ) {
								try {
									data = JSON.parse( data );
								} catch ( e ) {
									data = {};
								}
							}

							if ( data && 'eventId' in data ) {
								$target.append( tribe_tmpl( 'tribe_tmpl_month_mobile', data ) )
							}
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

			if ( 'undefined' === typeof $date.attr( 'data-day' ) ) {
				return;
			}

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

			var $activeDay = $wrapper.find( '.mobile-active' );
			var $mobileTrigger = $wrapper.find( '.mobile-trigger' );
			var $tribeGrid = $wrapper
				.find( document.getElementById( 'tribe-events-content' ) )
				.find( '.tribe-events-calendar' );

			// If for some reason we don't have a "$activeDay" selected, default to today.
			if ( ! $activeDay.length ) {
				$activeDay = $wrapper.find( '.tribe-events-present' );
			}

			if ( ! $( document.getElementById( 'tribe-mobile-container' ) ).length ) {
				$( '<div id="tribe-mobile-container" />' ).insertAfter( $tribeGrid );
			}

			if ( $activeDay.length && $activeDay.is( '.tribe-events-thismonth' ) ) {
				tribe_mobile_setup_day( $activeDay );
			}
			else {
				var $first_current_day = $mobileTrigger.filter( '.tribe-events-thismonth' ).first();
				tribe_mobile_setup_day( $first_current_day );
			}

		}

		function tribe_mobile_day_abbr() {

			$wrapper.find( '.tribe-events-calendar th' ).each( function() {
				var $this    = $( this );
				var dayAbbr = $this.attr( 'data-day-abbr' );
				var dayFull = $this.attr( 'title' );

				if ( $body.is( '.tribe-mobile' ) ) {
					$this.text( dayAbbr );
				}
				else {
					$this.text( dayFull );
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

			var isShortcode = $( document.getElementById( 'tribe-events' ) )
				.is( '.tribe-events-shortcode' );

			if( ! isShortcode || false !== config.update_urls.shortcode.month ){
				history.replaceState( {
					"tribe_params": params
				}, ts.page_title, location.href );
			}

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
				if ( '0' !== ts.datepicker_format ) {
					tf.update_picker( ts.mdate );
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

				// if using the shortcode
				if ( $wrapper.is( '.tribe-events-shortcode' ) ) {
					// and plain permalinks
					if ( td.default_permalinks ) {
						// we get the base URL
						url = tf.get_base_url();
					}
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

		tf.snap( '#tribe-bar-form', 'body', '#tribe-events-footer .tribe-events-nav-previous, #tribe-events-footer .tribe-events-nav-next' ); // eslint-disable-line max-len

		/* eslint-disable max-len */
		/**
		 * @function tribe_events_bar_calendar_ajax_actions
		 * @desc On events bar submit, this function collects the current state of the bar and sends it to the month view ajax handler.
		 * @param {event} e The event object.
		 */
		/* eslint-enable max-len */

		function tribe_events_bar_calendar_ajax_actions( e ) {
			if ( tribe_events_bar_action != 'change_view' ) { // eslint-disable-line eqeqeq
				e.preventDefault();
				if ( ts.ajax_running ) {
					return;
				}

				if (
					typeof $tribedate.val() !== 'undefined'
					&& $tribedate.val().length
				) {
					if ( '0' !== ts.datepicker_format ) {
						let maskKey = ts.datepicker_format.toString();
						ts.date = tribeUtils.formatDateWithMoment(
							$tribedate.bootstrapDatepicker( 'getDate' ),
							"tribeMonthQuery",
							maskKey
						);
					}
					else {
						ts.date = $tribedate.val();
					}
				}

				else {
					if ( !dateMod ) {
						ts.date = td.cur_date.slice( 0, -3 );
					}
				}

				if ( ts.filter_cats ) {
					td.cur_url = $( '#tribe-events-header' ).data( 'baseurl' ) + ts.date + '/';
				}
				else {
					if ( td.default_permalinks ) {
						td.cur_url = baseUrl;
					} else {
						td.cur_url = baseUrl + ts.date + '/';
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
					td.cur_url = baseUrl;
				} else {
					td.cur_url = baseUrl + ts.date + '/';
				}
			}
			ts.popping = false;
		} );

		/* eslint-disable max-len */
		/**
		 * @function tribe_events_calendar_ajax_post
		 * @desc The ajax handler for month view.
		 * Fires the custom event 'tribe_ev_serializeBar' at start, then 'tribe_ev_collectParams' to gather any additional paramters before actually launching the ajax post request.
		 * As post begins 'tribe_ev_ajaxStart' and 'tribe_ev_monthView_AjaxStart' are fired, and then 'tribe_ev_ajaxSuccess' and 'tribe_ev_monthView_ajaxSuccess' are fired on success.
		 * Various functions in the events plugins hook into these events. They are triggered on the tribe_ev.events object.
		 */
		/* eslint-enable max-len */

		function tribe_events_calendar_ajax_post() {

			if ( tf.invalid_date( ts.date ) ) {
				return;
			}

			$( '.tribe-events-calendar' ).tribe_spin();
			ts.pushcount = 0;
			ts.ajax_running = true;

			if ( ! ts.popping ) {

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

				// when having plain permalinks
				if ( td.default_permalinks ) {
					// when not using the shorcode
					if ( ! $wrapper.is( '.tribe-events-shortcode' ) ) {
						if ( ! ts.url_params.hasOwnProperty( 'post_type' ) ) { // eslint-disable-line no-prototype-builtins,max-len
							ts.url_params['post_type'] = config.events_post_type;
						}
						if ( ! ts.url_params.hasOwnProperty( 'eventDisplay' ) ) { // eslint-disable-line no-prototype-builtins,max-len
							ts.url_params['eventDisplay'] = ts.view;
						}
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
				dbug && tec_debug.time( 'Month View Ajax Timer' );
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
							tec_debug.warn( 'Month view ajax had an error in the query and returned 0.' );
						}
						// @endif

						var $theContent = '';
						if ( 'function' === typeof $.fn.parseHTML ) {
							$theContent = $.parseHTML( response.html );
						} else {
							$theContent = response.html;
						}

						// @TODO: We need to D.R.Y. this assignment and the following if statement about shortcodes/do_string
						// Ensure that the base URL is, in fact, the URL we want
						td.cur_url = tf.get_base_url();

						$( '#tribe-events-content' ).replaceWith( $theContent );

						tribe_month_view_init( true );

						ts.page_title = $( '#tribe-events-header' ).data( 'title' );
						ts.view_title = $( '#tribe-events-header' ).data( 'viewtitle' );
						document.title = ts.page_title;
						$( '.tribe-events-page-title' ).html(ts.view_title);

						// we only want to add query args for Shortcodes and ugly URL sites
						if (
								$( '#tribe-events.tribe-events-shortcode' ).length
								|| ts.do_string
						) {
							if ( td.default_permalinks ) {
								td.cur_url = td.cur_url + '&' + ts.url_params;
							} else {
								if ( -1 !== td.cur_url.indexOf( '?' ) ) {
									td.cur_url = td.cur_url.split( '?' )[0];
								}
								td.cur_url = td.cur_url + '?' + ts.url_params;
							}
						}

						var isShortcode = $( document.getElementById( 'tribe-events' ) )
							.is( '.tribe-events-shortcode' );
						var shouldUpdateHistory = ! isShortcode ||
							false !== config.update_urls.shortcode.month;


						if ( ts.do_string && shouldUpdateHistory ) {
							history.pushState( {
								"tribe_date"  : ts.date,
								"tribe_params": ts.params
							}, ts.page_title, td.cur_url );
						}

						if ( ts.pushstate && shouldUpdateHistory ) {
							history.pushState( {
								"tribe_date"  : ts.date,
								"tribe_params": ts.params
							}, ts.page_title, td.cur_url );
						}

						$( te ).trigger( 'tribe_ev_ajaxSuccess' ).trigger( 'tribe_ev_monthView_ajaxSuccess' );
						$( te ).trigger( 'ajax-success.tribe' ).trigger( 'tribe_ev_monthView_ajaxSuccess' );

						// @ifdef DEBUG
						dbug && tec_debug.timeEnd( 'Month View Ajax Timer' );
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
		dbug && tec_debug.info( 'TEC Debug: tribe-events-ajax-calendar.js successfully loaded, Tribe Events Init finished' ); // eslint-disable-line max-len
		dbug && tec_debug.timeEnd( 'Tribe JS Init Timer' );
		// @endif
	} );

})( window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_js_config, tribe_debug ); // eslint-disable-line max-len
