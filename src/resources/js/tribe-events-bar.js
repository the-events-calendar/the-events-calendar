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
				tec_debug.warn( 'TEC Debug: vendor bootstrapDatepicker was not loaded before its dependant file tribe-events-bar.js' );
			}
			if ( !$().placeholder ) {
				tec_debug.warn( 'TEC Debug: vendor placeholder was not loaded before its dependant file tribe-events-bar.js' );
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
				closeFiltersToggle( $( '#tribe-bar-collapse-toggle' ) );
			}
			else {
				$tribebar.removeClass( 'tribe-bar-collapse' );
				openFiltersToggle( $( '#tribe-bar-collapse-toggle' ) );
			}
		}

		eventsBarWidth( $tribebar );

		$tribebar.resize( function() {
			eventsBarWidth( $tribebar );
		} );

		if ( ! $( '.tribe-events-week-grid' ).length ) {

			if ( ts.view !== 'month' ) {

				// begin display date formatting

				let maskKey         = ts.datepicker_format.toString();
				let dateFormat      = tribeDateFormat.masks[maskKey] || 'yyyy-mm-dd';
				let initialDateInfo = tribeUtils.getInitialDateInfo( maskKey, dateFormat );

				$( document.getElementById( 'tribe-bar-date-day' ) ).val( tribeUtils.formatMoment( initialDateInfo.dateMoment, 'tribeQuery' ) );
				$tribedate.val( initialDateInfo.formattedDate );

				// @ifdef DEBUG
				dbug && tec_debug.info( 'TEC Debug: bootstrapDatepicker was just initialized in "tribe-events-bar.js" on:', $tribedate );
				// @endif

				td.datepicker_opts = {
					weekStart : start_day,
					format    : dateFormat,
					autoclose : true
				};

				// Set up some specific strings for datepicker i18n.
				tribe_ev.fn.ensure_datepicker_i18n();

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

		// Implement placeholder
		$( 'input[name*="tribe-bar-"]' ).placeholder();

		// Create list
		$( '<ul class="tribe-bar-views-list" tabindex="-1" role="listbox" aria-labelledby="tribe-bar-views-label" />' ).insertAfter( $tribebarselect );

		var $tribebarviews = $( '.tribe-bar-views-list' );

		function getCurrentViewItem() {
			return $tribebarviews.find( 'li[data-view=' + $tribebarselect.find( ':selected' ).data( 'view' ) + ']' );
		}

		// Create list from select options
		$tribebarselect.find( 'option' ).each( function( i ) {
			var $view = $( this );
			displaying = $view.data( 'view' );
			// build list items and append them
			var unique_c = 'tribe-bar-views-option-' + $view.data( 'view' );
			$( '<li></li>', {
				'id'                  : unique_c,
				'class'               : 'tribe-bar-views-option',
				'role'                : 'option',
				'tabindex'            : '-1',
				'data-tribe-bar-order': i,
				'data-view'           : displaying,
				'aria-labelledby'     : 'tribe-bar-views-label ' + unique_c,
			} ).html( '<span class="tribe-icon-' + displaying + '" aria-hidden="true" role="none"></span>' + $view.text() ).appendTo( '.tribe-bar-views-list' );

		} );

		//find the current view and select it in the bar
		var $currentli = getCurrentViewItem();

		// Se a class on the current view element
		$currentli.addClass( 'tribe-bar-active' );

		// Create the listbox toggle button
		var $tribebarviewstoggle = $( '<button>', {
			'id'              : 'tribe-bar-views-toggle',
			'class'           : 'tribe-bar-views-toggle',
			'data-view'       : $currentli.data( 'view' ),
			'aria-haspopup'   : 'listbox',
			'aria-labelledby' : 'tribe-bar-views-label tribe-bar-views-toggle',
		} );

		$tribebarviewstoggle.html( $currentli.html() ).insertBefore( $tribebarviews );

		// Disable the select
		$tribebarselect.hide();

		function openViewsToggle() {
			var $currentli = getCurrentViewItem();
			$tribebarviews
				.slideDown( 'fast' )
				.attr( 'aria-activedescendant', $currentli.attr( 'id' ) )
				.focus();
			$tribebar.addClass( 'tribe-bar-views-open' );
			$tribebarviewstoggle.attr( 'aria-expanded', 'true' );
		}

		function closeViewsToggle() {
			var $currentli = getCurrentViewItem();
			$tribebarviewstoggle.removeAttr( 'aria-expanded' );
			$tribebar.removeClass('tribe-bar-views-open');
			$tribebarviews
				.slideUp( 'fast' )
				.removeAttr( 'aria-activedescendant' )
				.find( '.tribe-bar-views-option' ).removeClass( 'tribe-bar-active' );
			$currentli.addClass( 'tribe-bar-active' );
		}

		function triggerViewsChange( e, el ) {
			e.preventDefault();

			var $this = $( el );

			// If the new selection is the same as the current view, just close the drop-down and bail.
			if ( $this.data( 'view' ) === $tribebarviewstoggle.data( 'view' ) ) {
				$tribebarviewstoggle.focus();
				closeViewsToggle();
				return;
			}

			// Otherwise, update the page with the selected view and trigger the change
			$tribebarviewstoggle.html( $this.html() ).focus();
			closeViewsToggle();

			ts.cur_url              = $( 'option[data-view=' + $this.data( 'view' ) + ']' ).val();
			ts.view_target          = $( 'select[name=tribe-bar-view] option[value="' + ts.cur_url + '"]' ).data( 'view' );
			tribe_events_bar_action = 'change_view';

			tribe_events_bar_change_view();
		}

		// toggle the views dropdown
		$tribebar.on( 'click', '#tribe-bar-views-toggle', function( e ) {
			e.preventDefault();

			if ( $tribebar.hasClass( 'tribe-bar-views-open' ) ) {
				closeViewsToggle();
			} else {
				openViewsToggle();
			}
		} );

		// change views via click
		$tribebar.on( 'click', '.tribe-bar-views-option', function( e ) {
			triggerViewsChange( e, this );
		} );

		// Arrow Keys
		$( document ).on( 'keydown', function ( e ) {
			if ( 38 !== e.which && 40 !== e.which ) {
				return;
			}

			if ( ! $tribebar.hasClass( 'tribe-bar-views-open' ) ) {
				return;
			}

			e.preventDefault();

			var key      = e.which;
			var $newView = null;
			var $oldView = $tribebarviews.find( 'li.tribe-bar-active' );

			// Up Arrow
			if ( 38 === key && $oldView.prev( '.tribe-bar-views-option' ) ) {
				$newView = $oldView.prev( '.tribe-bar-views-option' );
			}

			// Down arrow
			if ( 40 === key && $oldView.next( '.tribe-bar-views-option' ) ) {
				$newView = $oldView.next( '.tribe-bar-views-option' );
			}

			if ( $newView.length ) {
				$tribebarviews.attr( 'aria-activedescendant', $newView.attr( 'id' ) );
				$oldView.removeClass( 'tribe-bar-active' );
				$newView.addClass( 'tribe-bar-active' ).focus();
			}
		} );

		// Enter Key
		$tribebar.on( 'keyup', '.tribe-bar-views-option', function( e ) {
			if ( 13 !== e.which ) {
				return;
			}

			if ( ! $tribebar.hasClass( 'tribe-bar-views-open' ) ) {
				return;
			}

			triggerViewsChange( e, this );
		} );

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

		// Trigger Mobile Change
		tf.maybe_default_view_change();

		function openFiltersToggle( $toggle ) {
			var label_shown = $toggle.attr( 'data-label-shown' );
			$toggle.attr( 'aria-expanded', 'true' );
			$toggle.find( '.tribe-bar-toggle-text' ).html( label_shown );
			$toggle.addClass( 'tribe-bar-filters-open' );
			$( '.tribe-bar-filters' ).slideDown( 'fast' ).attr( 'aria-hidden', 'false' );
		}

		function closeFiltersToggle( $toggle ) {
			var label_hidden = $toggle.attr( 'data-label-hidden' );
			$( '.tribe-bar-filters' ).slideUp( 'fast' ).attr( 'aria-hidden', 'true' );
			$toggle.removeClass( 'tribe-bar-filters-open' );
			$toggle.find( '.tribe-bar-toggle-text' ).html( label_hidden );
			$toggle.attr( 'aria-expanded', 'false' );
		}

		$tribebar.on( 'click', '#tribe-bar-collapse-toggle', function( e ) {
			e.preventDefault();
			var $this = $( this );
			if ( $this.hasClass( 'tribe-bar-filters-open' ) ) {
				closeFiltersToggle( $this );
			} else {
				openFiltersToggle( $this );
			}
		} );

		// Tab Key
		$( document ).on( 'keyup', function( e ) {
			if ( 9 !== e.which ) {
				return;
			}

			// Close Event Filters if open and tabbed outside
			var $filters_toggle = $( '#tribe-bar-collapse-toggle' );
			if ( $tribebar.hasClass( 'tribe-bar-collapse' ) && $filters_toggle.hasClass( 'tribe-bar-filters-open' ) && ! $.contains( document.getElementById( 'tribe-bar-filters-wrap' ), e.target ) ) {
				closeFiltersToggle( $filters_toggle );
			}

			// Close Event Views if open and tabbed past
			var $views_toggle = $( '#tribe-bar-views-toggle' );
			if ( $tribebar.hasClass( 'tribe-bar-views-open' ) && $views_toggle.not( ':focus' ) ) {
				closeViewsToggle();
			}
		} );

		// Escape Key
		$( document ).on( 'keyup', function( e ) {
			if ( 27 !== e.which ) {
				return;
			}

			// Close Event Filters if open and escaped
			var $filters_toggle = $( '#tribe-bar-collapse-toggle' );
			if ( $tribebar.hasClass( 'tribe-bar-collapse' ) && $filters_toggle.hasClass( 'tribe-bar-filters-open' ) ) {
				closeFiltersToggle( $filters_toggle );
				$filters_toggle.focus();
			}

			// Close Event Views is open and escaped
			var $views_toggle = $( '#tribe-bar-views-toggle' );
			if ( $tribebar.hasClass( 'tribe-bar-views-open' ) ) {
				closeViewsToggle();
				$views_toggle.focus();
			}
		} );

		$( te ).on( 'tribe_ev_serializeBar', function() {

			// Close Event Filters if open
			var $filters_toggle = $( '#tribe-bar-collapse-toggle' );
			if ( $tribebar.hasClass( 'tribe-bar-collapse' ) && $filters_toggle.hasClass( 'tribe-bar-filters-open' ) ) {
				closeFiltersToggle( $filters_toggle );
				$filters_toggle.focus();
			}

			$( 'form#tribe-bar-form input, form#tribe-bar-form select, #tribeHideRecurrence' ).each( function() {
				var $this = $( this );
				if ( $this.is( '#tribe-bar-date' ) ) {
					let this_val = $this.val();
					let maskKey  = ts.datepicker_format.toString();

					if ( this_val.length ) {
						if ( ts.view === 'month' ) {
							maskKey = "m" + maskKey;
							ts.params[$this.attr( 'name' )]     = tribeUtils.formatDateWithMoment( ts.mdate, "tribeMonthQuery", maskKey );
							ts.url_params[$this.attr( 'name' )] = tribeUtils.formatDateWithMoment( ts.mdate, "tribeMonthQuery", maskKey );
						}
						// If this is not month view, but we came from there, the value of #tribe-bar-date will
						// describe a year and a month: preserve this if so to ensure accuracy of pagination
						else if ( this_val.match( /^[0-9]+[\-\.\/][0-9]+$/ ) ) {
							ts.params[ $this.attr( 'name') ] = ts.url_params[ $this.attr( 'name' ) ] = tribeUtils.formatDateWithMoment( this_val, 'tribeQuery', maskKey );
						}
						// In all other cases, pull the date from the datepicker
						else {
							ts.params[ $this.attr( 'name' ) ]     = tribeUtils.formatDateWithMoment( $this.bootstrapDatepicker( 'getDate' ), 'tribeQuery', maskKey );
							ts.url_params[ $this.attr( 'name' ) ] = tribeUtils.formatDateWithMoment( $this.bootstrapDatepicker( 'getDate' ), 'tribeQuery', maskKey );
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
			let maskKey = ts.datepicker_format.toString();

			$inputs.each( function() {
				var $this = $( this );
				if ( $this.val() && $this.val().length && ! $this.hasClass( 'tribe-no-param' ) ) {
					if ( 'month' !== ts.view  && '0' !== ts.datepicker_format && $this.is( $tribedate ) ) {

						ts.url_params[ $this.attr( 'name' ) ] = tribeUtils.formatDateWithMoment( $this.bootstrapDatepicker( 'getDate' ), "tribeQuery", maskKey );

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
			var redirected = $( '#tribe-bar-views-option-' + td.default_mobile_view ).data( 'redirected' );
			if ( td.redirected_view || redirected ) {
				ts.url_params['tribe_redirected'] = true;
			}

			if ( 'month' === ts.view && $tribedate.length ) {
				const maskKey   = 'm' + ts.datepicker_format.toString();
				const dp_date   = $tribedate.val() || $tribedate.bootstrapDatepicker( 'getDate' );
				const theMoment = tribeUtils.maybeAlterMonthViewDate( dp_date, maskKey );

				ts.url_params['tribe-bar-date'] = tribeUtils.formatDateWithMoment( theMoment, 'tribeQuery' );
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

		$( document ).click( function( e ) {
			// Close the Event Views if open
			if ( $tribebar.hasClass( 'tribe-bar-views-open' ) && ! $.contains( document.getElementById( 'tribe-bar-views' ), e.target ) ) {
				closeViewsToggle();
				$tribebarviewstoggle.focus();
			}

			// Close the Event Filters if open
			var $filters_toggle = $( '#tribe-bar-collapse-toggle' );
			if ( $tribebar.hasClass( 'tribe-bar-collapse' ) && $filters_toggle.hasClass( 'tribe-bar-filters-open' ) && ! $.contains( document.getElementById( 'tribe-bar-filters-wrap' ), e.target ) ) {
				closeFiltersToggle( $filters_toggle );
				$filters_toggle.focus();
			}

			if ( $tribeDropToggle.hasClass( 'open' ) ) {
				$tribeDropToggle.removeClass( 'open' );
				$tribeDropToggleEl.hide();
			}

		} );

		$tribeDropToggleEl.click( function( e ) {
			e.stopPropagation();
		} );

		// @ifdef DEBUG
		dbug && tec_debug.info( 'TEC Debug: tribe-events-bar.js successfully loaded' );
		// @endif
	} );

})( window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_debug );

