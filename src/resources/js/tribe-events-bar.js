/**
 * @file Sets up the event bar javascript.
 * This file should load after tribe events core and pro js and before any events view javascript.
 * @version 3.0
 */

/**
 * @global
 * @desc This global is used in various functions throughout the plugin to determine if the view is being changed. Its value will be set to 'change_view' when true.
 */

var tribe_events_bar_action;

(function( window, document, $, td, te, tf, ts, tt, dbug ) {

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

		// @ifdef DEBUG
		if ( dbug ) {
			if ( !$().bootstrapDatepicker ) {
				debug.warn( 'TEC Debug: vendor bootstrapDatepicker was not loaded before its dependant file tribe-events-bar.js' );
			}
			if ( !$().placeholder ) {
				debug.warn( 'TEC Debug: vendor placeholder was not loaded before its dependant file tribe-events-bar.js' );
			}
		}
		// @endif

		var $tribebar       = $( document.getElementById( 'tribe-bar-form' ) );
		var $tribedate      = $( document.getElementById( 'tribe-bar-date' ) );
		var $tribe_events   = $( document.getElementById( 'tribe-events' ) );
		var $tribe_header   = $( document.getElementById( 'tribe-events-header' ) );
		var start_day       = 0;
		var $tribebarselect = $( 'select[name=tribe-bar-view]' );

		if ( $tribe_header.length ) {
			start_day = $tribe_header.data( 'startofweek' );
		}

		/**
		 * @function eventsBarWidth
		 * @desc eventsBarWidth applies responsive css classes to the bar to adjust its layout for smaller screens.
		 * @param {jQuery} $tribebar The event bar jquery object.
		 */
		function eventsBarWidth( $tribebar ) {
			if ( $tribebar.parents( '.tribe-bar-disabled' ).length ) {
				return;
			}

			var tribeBarWidth = $tribebar.width();

			if ( tribeBarWidth > 800 ) {
				$tribebar.removeClass( 'tribe-bar-mini tribe-bar-collapse' ).addClass( 'tribe-bar-full' );
			}
			else {
				$tribebar.removeClass( 'tribe-bar-full' ).addClass( 'tribe-bar-mini' );
			}
			if ( tribeBarWidth < 728 ) {
				$tribebar.removeClass( 'tribe-bar-mini' ).addClass( 'tribe-bar-collapse' );
			}
			else {
				$tribebar.removeClass( 'tribe-bar-collapse' );
			}
		}

		eventsBarWidth( $tribebar );

		$tribebar.resize( function() {
			eventsBarWidth( $tribebar );
		} );

		if ( ! $( '.tribe-events-week-grid' ).length ) {

			if ( ts.view !== 'month' ) {

				// begin display date formatting

				var date_format = 'yyyy-mm-dd';

				if ( ts.datepicker_format !== '0' ) {

					// we are not using the default query date format, lets grab it from the data array

					date_format = td.datepicker_formats.main[ ts.datepicker_format ];

					var url_date = tf.get_url_param( 'tribe-bar-date' );

					// if url date is set and datepicker format is different from query format
					// we need to fix the input value to emulate that before kicking in the datepicker

					if ( url_date ) {
						$tribedate.val( tribeDateFormat( url_date, ts.datepicker_format ) );
					}
					else if ( ts.view === 'day' && $tribedate.val().length !== 0 ) {
						$tribedate.val( tribeDateFormat( $tribedate.val(), ts.datepicker_format ) );
					}
				}

				// @ifdef DEBUG
				dbug && debug.info( 'TEC Debug: bootstrapDatepicker was just initialized in "tribe-events-bar.js" on:', $tribedate );
				// @endif

				td.datepicker_opts = {
					weekStart : start_day,
					format    : date_format,
					autoclose : true
				};

				$tribedate.bootstrapDatepicker( td.datepicker_opts );
			}
		}

		$tribedate.blur( function() {
			if ( $tribedate.val() === '' && $( '.datepicker.dropdown-menu' ).is( ':hidden' ) && tt.live_ajax() && tt.pushstate ) {
				ts.date    = td.cur_date;
				td.cur_url = td.base_url;
				/**
				 * DEPRECATED: tribe_ev_runAjax has been deprecated in 4.0. Use run-ajax.tribe instead
				 */
				$( te ).trigger( 'tribe_ev_runAjax' );
				$( te ).trigger( 'run-ajax.tribe' );
			}
		} );

		// Add some classes
		if ( $( '.tribe-bar-settings' ).length ) {
			$( document.getElementById( 'tribe-events-bar' ) ).addClass( 'tribe-has-settings' );
		}
		if ( $( '#tribe-events-bar .hasDatepicker' ).length ) {
			$( document.getElementById( 'tribe-events-bar' ) ).addClass( 'tribe-has-datepicker' );
		}

		// Implement views links
		function format( view ) {
			return '<span class="tribe-icon-' + $.trim( view.text.toLowerCase() ) + '">' + view.text + '</span>';
		}

		// Implement placeholder
		$( 'input[name*="tribe-bar-"]' ).placeholder();

		// Create list
		$( '<ul class="tribe-bar-views-list" />' ).insertAfter( $tribebarselect );

		var $tribebarviews = $( '.tribe-bar-views-list' );

		// Create list from select options
		$tribebarselect.find( 'option' ).each( function( i ) {
			var $view = $( this );
			displaying = $view.data( 'view' );
			// build list items and append them
			var unique_c = 'tribe-bar-views-option-' + $view.data( 'view' );
			$( '<li></li>', {
				'class'               : 'tribe-bar-views-option ' + unique_c,
				'data-tribe-bar-order': i,
				'data-view'           : displaying
			} ).html( [
				'   <a href="#">',
				'   <span class="tribe-icon-' + displaying + '">' + $view.text() + '</span>',
				'</a>'].join( "" )
			).appendTo( '.tribe-bar-views-list' );

		} );

		//find the current view and select it in the bar
		var currentview = $tribebarselect.find( ':selected' ).data( 'view' );
		var $currentli  = $tribebarviews.find( 'li[data-view=' + currentview + ']' );

		$currentli.prependTo( $tribebarviews ).addClass( 'tribe-bar-active' );

		// Disable the select
		$tribebarselect.hide();

		// toggle the views dropdown
		$tribebar.on( 'click', '#tribe-bar-views', function( e ) {
			e.stopPropagation();

			$( this ).toggleClass( 'tribe-bar-views-open' );
		} );

		// change views
		$tribebar.on( 'click', '.tribe-bar-views-option', function( e ) {
			e.preventDefault();
			
			var $this = $( this );

			if ( ! $this.is( '.tribe-bar-active' ) ) {

				var target = $this.data( 'view' );

				ts.cur_url              = $( 'option[data-view=' + target + ']' ).val();
				ts.view_target          = $( 'select[name=tribe-bar-view] option[value="' + ts.cur_url + '"]' ).data( 'view' );
				tribe_events_bar_action = 'change_view';

				tribe_events_bar_change_view();
			}
		} );

		// Trigger Mobile Change
		tf.maybe_default_view_change();

		// change views with select (for skeleton styles)
		$tribebar.on( 'change', '.tribe-bar-views-select', function( e ) {
			e.preventDefault();
			
			var $this  = $( 'option:selected', this );
			var target = $this.data( 'view' );

			ts.cur_url              = $( 'option[data-view=' + target + ']' ).val();
			ts.view_target          = $( 'select[name=tribe-bar-view] option[value="' + ts.cur_url + '"]' ).data( 'view' );
			tribe_events_bar_action = 'change_view';

			tribe_events_bar_change_view();
		} );

		$tribebar.on( 'click', '#tribe-bar-collapse-toggle', function() {
			$( this ).toggleClass( 'tribe-bar-filters-open' );
			$( '.tribe-bar-filters' ).slideToggle( 'fast' );
		} );

		// Wrap date inputs with a parent container
		$( 'label[for="tribe-bar-date"], input[name="tribe-bar-date"]' ).wrapAll( '<div id="tribe-bar-dates" />' );

		// Add our date bits outside of our filter container
		$( document.getElementById( 'tribe-bar-filters' ) ).before( $( document.getElementById( 'tribe-bar-dates' ) ) );

		$( te ).on( 'tribe_ev_serializeBar', function() {
			$( 'form#tribe-bar-form input, form#tribe-bar-form select, #tribeHideRecurrence' ).each( function() {
				var $this = $( this );
				if ( $this.is( '#tribe-bar-date' ) ) {
					var this_val = $this.val();

					if ( this_val.length ) {
						if ( ts.view === 'month' ) {
							ts.params[$this.attr( 'name' )] = tribeDateFormat( ts.mdate, "tribeMonthQuery" );
							ts.url_params[$this.attr( 'name' )] = tribeDateFormat( ts.mdate, "tribeMonthQuery" );
						}
						// If this is not month view, but we came from there, the value of #tribe-bar-date will
						// describe a year and a month: preserve this if so to ensure accuracy of pagination
						else if ( this_val.match( /[0-9]{4}-[0-9]{2}/ ) ) {
							ts.params[ $this.attr( 'name') ] = ts.url_params[ $this.attr( 'name' ) ] = this_val;
						}
						// In all other cases, pull the date from the datepicker
						else {
							ts.params[ $this.attr( 'name' ) ]     = tribeDateFormat( $this.bootstrapDatepicker( 'getDate' ), 'tribeQuery' );
							ts.url_params[ $this.attr( 'name' ) ] = tribeDateFormat( $this.bootstrapDatepicker( 'getDate' ), 'tribeQuery' );
						}
					}
					else if ( $this.is( '.placeholder' ) && $this.is( '.bd-updated' ) ) {
						ts.url_params[$this.attr( 'name' )] = $this.attr( 'data-oldDate' );
					}
					else {
						ts.date = td.cur_date;
					}
				}

				if ( $this.val().length && !$this.hasClass( 'tribe-no-param' ) && !$this.is( '#tribe-bar-date' ) ) {
					if ( $this.is( ':checkbox' ) ) {
						if ( $this.is( ':checked' ) ) {
							ts.params[$this.attr( 'name' )] = $this.val();
							if ( ts.view !== 'map' ) {
								ts.url_params[$this.attr( 'name' )] = $this.val();
							}
							if ( ts.view === 'month' || ts.view === 'day' || ts.view === 'week' || ts.recurrence ) {
								ts.pushcount++;
							}
						}
					}
					else {
						ts.params[$this.attr( 'name' )] = $this.val();
						if ( ts.view !== 'map' ) {
							ts.url_params[$this.attr( 'name' )] = $this.val();
						}
						if ( ts.view === 'month' || ts.view === 'day' || ts.view === 'week' ) {
							ts.pushcount++;
						}
					}
				}
			} );
		} );

		/**
		 * @function tribe_events_bar_change_view
		 * @desc tribe_events_bar_change_view handles switching views and collecting any params from the events bar. It also fires 2 custom actions that can be hooked into: 'tribe_ev_preCollectBarParams' and 'tribe_ev_postCollectBarParams'.
		 */

		function tribe_events_bar_change_view() {

			tribe_events_bar_action = 'change_view';

			if ( 'month' === ts.view && $tribedate.length ) {
				var dp_date = $tribedate.val();
				var day     = tf.get_day();

				if ( '0' != ts.datepicker_format ) {
					dp_date = tribeDateFormat( $tribedate.bootstrapDatepicker( 'getDate' ), 'tribeMonthQuery' );
					$tribedate.val( dp_date + day );
				}
				else {
					if ( 7 === dp_date.length ) {
						$tribedate.val( dp_date + day );
					}
				}

			}

			ts.url_params = {};

			/**
			 * DEPRECATED: tribe_ev_preCollectBarParams has been deprecated in 4.0. Use pre-collect-bar-params.tribe instead
			 */
			$( te ).trigger( 'tribe_ev_preCollectBarParams' );
			$( te ).trigger( 'pre-collect-bar-params.tribe' );

			// Select all the required fields
			// Normal Form + Filter Bar
			var $forms  = $( document.getElementById( 'tribe-bar-form' ) ).add( document.getElementById( 'tribe_events_filters_wrapper' ) );
			var $inputs = $forms.find( 'input, select' );

			$inputs.each( function() {
				var $this = $( this );
				if ( $this.val() && $this.val().length && ! $this.hasClass( 'tribe-no-param' ) ) {
					if ( 'month' !== ts.view  && '0' !== ts.datepicker_format && $this.is( $tribedate ) ) {

						ts.url_params[ $this.attr( 'name' ) ] = tribeDateFormat( $this.bootstrapDatepicker( 'getDate' ), 'tribeQuery' );

					}
					else {
						if ( $this.is( ':checkbox' ) ) {
							if ( $this.is( ':checked' ) ) {

								// if checkbox and not defined setup as an array
								if ( 'undefined' === typeof ts.url_params[ $this.attr( 'name' ) ] ) {
									ts.url_params[$this.attr( 'name' )] = [];
								}

								// add value to array
								ts.url_params[ $this.attr( 'name' ) ].push( $this.val() );
							}
						}
						else if ( 'radio' === $this.attr( 'type' ) ) {
							if ( $this.is( ':checked' ) ) {
								ts.url_params[$this.attr( 'name' )] = $this.val();
							}
						}
						else if ( 'undefined' !== typeof $this.attr( 'name' ) ) {
							ts.url_params[ $this.attr( 'name' ) ] = $this.val();
						}
					}
				}
			} );

			// setup redirected param to prevent after initial redirect
			var redirected = $( '.tribe-bar-views-option-' + td.default_mobile_view ).data( 'redirected' );
			if ( td.redirected_view || redirected ) {
				ts.url_params['tribe_redirected'] = true;
			}

			ts.url_params = $.param( ts.url_params );

			/**
			 * DEPRECATED: tribe_ev_postCollectBarParams has been deprecated in 4.0. Use post-collect-bar-params.tribe instead
			 */
			$( te ).trigger( 'tribe_ev_postCollectBarParams' );
			$( te ).trigger( 'post-collect-bar-params.tribe' );

			if ( ts.url_params.length ) {
				ts.cur_url += tt.starting_delim() + ts.url_params;
			}

			window.location.href = ts.cur_url;
		}

		// Implement simple toggle for filters at smaller size (and close if click outside of toggle area)
		var $tribeDropToggle = $( '#tribe-events-bar [class^="tribe-bar-button-"]' );
		var $tribeDropToggleEl = $tribeDropToggle.next( '.tribe-bar-drop-content' );

		$tribeDropToggle.click( function() {
			var $this = $( this );
			$this.toggleClass( 'open' );
			$this.next( '.tribe-bar-drop-content' ).toggle();
			return false
		} );

		$( document ).click( function() {
			$( document.getElementById( 'tribe-bar-views' ) ).removeClass( 'tribe-bar-views-open' );
			if ( $tribeDropToggle.hasClass( 'open' ) ) {
				$tribeDropToggle.removeClass( 'open' );
				$tribeDropToggleEl.hide();
			}
		} );

		$tribeDropToggleEl.click( function( e ) {
			e.stopPropagation();
		} );

		// @ifdef DEBUG
		dbug && debug.info( 'TEC Debug: tribe-events-bar.js successfully loaded' );
		// @endif
	} );

})( window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_debug );

