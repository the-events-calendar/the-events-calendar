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
		$('select').selectability();

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

		var $tribebar = $( '#tribe-bar-form' ),
			$tribedate = $( '#tribe-bar-date' ),
			$tribe_header = $( '#tribe-events-header' ),
			start_day = 0;

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

		if ( !$( '.tribe-events-week-grid' ).length ) {

			if ( ts.view !== 'month' ) {

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
					else if ( ts.view === 'day' && $tribedate.val().length !== 0 ) {
						$tribedate.val( tribeDateFormat( $tribedate.val(), ts.datepicker_format ) );
					}
				}

				// @ifdef DEBUG
				dbug && debug.info( 'TEC Debug: bootstrapDatepicker was just initialized in "tribe-events-bar.js" on:', $tribedate );
				// @endif

				td.datepicker_opts = {
					weekStart         : start_day,
					format   : date_format,
					autoclose: true
				};

				$tribedate.bootstrapDatepicker( td.datepicker_opts );
			}
		}

		$tribedate.blur( function() {
			if ( $tribedate.val() === '' && $( '.datepicker.dropdown-menu' ).is( ':hidden' ) && tt.live_ajax() && tt.pushstate ) {
				ts.date = td.cur_date;
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
			$( '#tribe-events-bar' ).addClass( 'tribe-has-settings' );
		}
		if ( $( '#tribe-events-bar .hasDatepicker' ).length ) {
			$( '#tribe-events-bar' ).addClass( 'tribe-has-datepicker' );
		}

		// Implement views links
		function format( view ) {
			return '<span class="tribe-icon-' + $.trim( view.text.toLowerCase() ) + '">' + view.text + '</span>';
		}

		// Implement placeholder
		$( 'input[name*="tribe-bar-"]' ).placeholder();


		// Trigger Mobile Change
		tf.maybe_default_view_change();

//		// change views with select (for skeleton styles)
//		$tribebar.on( 'change', '.tribe-bar-views-select', function( e ) {
//			e.preventDefault();
//			var $this = $( "option:selected", this );
//
//			var target = $this.data( 'view' );
//
//			ts.cur_url = $( 'option[data-view=' + target + ']' ).val();
//			ts.view_target = $( 'select[name=tribe-bar-view] option[value="' + ts.cur_url + '"]' ).data( 'view' );
//			tribe_events_bar_action = 'change_view';
//			tribe_events_bar_change_view();
//
//		} );

		$tribebar.on( 'click', '#tribe-bar-collapse-toggle', function() {
			$( this ).toggleClass( 'tribe-bar-filters-open' );
			$( '.tribe-bar-filters' ).slideToggle( 'fast' );
		} );

		// Wrap date inputs with a parent container
		$( 'label[for="tribe-bar-date"], input[name="tribe-bar-date"]' ).wrapAll( '<div id="tribe-bar-dates" />' );

		// Add our date bits outside of our filter container
		$( '#tribe-bar-filters' ).before( $( '#tribe-bar-dates' ) );

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
							ts.params[$this.attr( 'name' )] = tribeDateFormat( $this.bootstrapDatepicker( 'getDate' ), "tribeQuery" );
							ts.url_params[$this.attr( 'name' )] = tribeDateFormat( $this.bootstrapDatepicker( 'getDate' ), "tribeQuery" );
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

			if ( ts.view === 'month' && $tribedate.length ) {
				var dp_date = $tribedate.val(),
					day = tf.get_day();

				if ( ts.datepicker_format !== '0' ) {
					if ( day.length ) {
						dp_date = tribeDateFormat( $tribedate.bootstrapDatepicker( 'getDate' ), 'tribeMonthQuery' );
						$tribedate.val( dp_date + day );
					}
					else {
						$tribedate.val( '' );
					}

				}
				else {
					if ( dp_date.length === 7 ) {
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
			var $forms = $( document.getElementById( 'tribe-bar-form' ) ).add( document.getElementById( 'tribe_events_filters_wrapper' ) ),
				$inputs = $forms.find( 'input, select' );

			$inputs.each( function() {
				var $this = $( this );
				if ( $this.val() && $this.val().length && !$this.hasClass( 'tribe-no-param' ) ) {
					if ( ts.view !== 'month' && '0' !== ts.datepicker_format && $this.is( $tribedate ) ) {

						ts.url_params[ $this.attr( 'name' ) ] = tribeDateFormat( $this.bootstrapDatepicker( 'getDate' ), 'tribeQuery' );

					}
					else {
						if ( $this.is( ':checkbox' ) ) {
							if ( $this.is( ':checked' ) ) {
								ts.url_params[ $this.attr( 'name' ) ] = $this.val();
							}
						}
						else {
							ts.url_params[ $this.attr( 'name' ) ] = $this.val();
						}
					}
				}
			} );

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
			$( '#tribe-bar-views' ).removeClass( 'tribe-bar-views-open' );
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

(function (factory) {
	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		factory(require('jquery'));
	} else {
		factory(jQuery);
	}
})(function ($) {
	'use strict';

	var KEY_ENTER = 13,
		KEY_SPACE = 32,
		KEY_LEFT = 37,
		KEY_UP = 38,
		KEY_RIGHT = 39,
		KEY_DOWN = 40,
		KEY_ESCAPE = 27;

	$.fn.selectability = function() {
		this.each(function() {
			var $this = $(this);

			if (!$this.data('selectability')) {
				$this.data('selectability', new Selectability($this));
			}
		});

		return this;
	};

	var idgen = (function () {
		var i = 0;
		return function () {
			var id;
			do {
				id = "selectability#id" + (i++);
			} while (document.getElementById(id));

			return id;
		}
	})();

	function Selectability(element) {
		this.element = element;

		this.buildElements();
		this.stealLabel();
		this.synchronizeAttributes();
		this.populateText();
		this.registerHandlers();

		element
		.attr('tabindex', -1)
		.addClass('selectability-offscreen');
	}

	Selectability.prototype.buildElements = function () {
		this.textbox = $('<div></div>')
		.attr({
			role: 'textbox',
			tabindex: -1,
			'aria-readonly': 'true'
		});

		this.listbox = $('<div></div>')
		.attr({
			role: 'listbox',
			tabindex: -1,
			'aria-multiselectable': 'false'
		});
		console.log(this.listbox);

		this.combobox = $('<div></div>')
		.addClass('selectability')
		.attr({
			role: 'combobox application',
			tabindex: 0,
			'aria-expanded': 'false'
		});

		this.combobox
		.append(this.textbox)
		.append(this.listbox);

		this.element
		.before(this.combobox);

	};

	Selectability.prototype.stealLabel = function () {
		var id = this.element.attr('id'),
			label = this.element.attr('aria-label');

		if (label) {
			this.combobox.attr('aria-label', label);
			return;
		}

		if (!id) {
			return;
		}

		var ids = [];
		$('label[for]')
		.filter(function () { return $(this).attr('for') === id })
		.each(function () {
			var $this = $(this),
				autogen = idgen();

			$this.removeAttr('for');
			$this.attr('id', autogen);
			ids.push(autogen);
		});

		this.combobox.attr('aria-labelledby', ids.join(' '));
	};

	Selectability.prototype.populateText = function (event) {
		if (event && event.selectability) {
			return;
		}

		var selected = this.element.find(':selected');
		if (selected.length) {
			this.textbox.text(selected.attr('label') || selected.text());
		}
	}

	Selectability.prototype.synchronizeAttributes = function () {
		if (this.element.prop('multiple')) {
			throw new Error('Can only bind to single selection widgets');
		}

		this.disabled = !!this.element.prop('disabled');
		this.combobox.attr({
			'aria-disabled': this.disabled,
			'aria-required': !!this.element.prop('required')
		});
	};

	Selectability.prototype.registerHandlers = function () {
		this.observeProperties();
		this.registerEvents();
	};

	Selectability.prototype.observeProperties = function () {
		var synchronize = $.proxy(this.synchronizeAttributes, this),
			observer = window.MutationObserver
				|| window.MozMutationObserver
				|| window.WebkitMutationObserver;

		if (observer) {
			this.observer = new observer(synchronize);
			this.observer.observe(this.element[0], {
				attributes: true,
				attributeFilter: ['multiple', 'disabled', 'required']
			});
		} else {
			this.element.on({
				'propertychange.selectability': synchronize,
				'focus.selectability': synchronize
			});
		}

		this.element.change($.proxy(this.populateText, this));
	};

	Selectability.prototype.registerEvents = function () {
		var selectability = this;
		this.combobox.on({
			focusout: function (event) {
				var elt = this;
				setTimeout(function () {
					/*
					 * So *after* the focusout event chain has run its course, we look to
					 * see what happens with document.activeElement.
					 *
					 * If whatever got the focus after we were notified doesn't live in the
					 * selectability DOM tree, close the combobox.
					 *
					 * All this, because 'event.relatedTarget' is a DOM3 spec and jQuery
					 * only patches it for mouse events.
					 *
					 * sigh.
					 */
					if (!$.contains(elt, document.activeElement)) {
						selectability.closeCombobox();
					}
				}, 0);
			},
			click: $.proxy(this.comboboxClick, this),
			keydown: $.proxy(this.comboboxKeydown, this)
		});

		this.listbox.on({
			click: $.proxy(this.listboxClick, this),
			keydown: $.proxy(this.listboxKeydown, this)
		});
	}

	Selectability.prototype.comboboxClick = function() {
		if (!this.disabled) {
			this.toggleCombobox();
		}
	};

	Selectability.prototype.comboboxKeydown = function(event) {
		if (this.disabled) {
			return;
		}

		switch (event.which) {
		case KEY_ENTER:
		case KEY_SPACE:
			if (this.combobox.attr('aria-expanded') === 'true') {
				this.closeCombobox();
				this.combobox.focus();
				event.preventDefault();
				return false;
			}

		case KEY_UP:
		case KEY_DOWN:
		case KEY_LEFT:
		case KEY_RIGHT:
			if (this.combobox.attr('aria-expanded') !== 'true') {
				this.openCombobox();
			}
			this.active.focus();
			event.preventDefault();
			return false;

		case KEY_ESCAPE:
			this.closeCombobox();
			this.combobox.focus();
			event.preventDefault();
			return false;
		}
	};

	Selectability.prototype.listboxClick = function(event) {
		var $target = $(event.target);
		if ($target.attr('role') !== 'option') {
			return;
		}

		if ($target.attr('aria-disabled') === 'true') {
			return;
		}

		this.setActive($(event.target));
		this.closeCombobox();


		event.preventDefault();
		return false;
	};

	Selectability.prototype.setActive = function(active) {
		var index = this.listbox.find('[role=option]').index(active),
			value = this.element.find('option').eq(index).val(),
			prev = this.element.val(),
			event = $.Event('change', {
				val: value,
				selectability: true
			});

		// nothing to do
		if (this.element.val() === value) {
			return;
		}

		// some frameworks read element.val() instead of the event value
		// so we populate the value and restore it (see below) if the event is canceled
		this.element.val(value);

		try {
			// work around event handlers throwing exceptions
			this.element.trigger(event);
		} finally {
			// promote 'change' to a cancelable event
			if (!event.isDefaultPrevented()) {
				this.active = active;
				this.textbox.text(active.attr('label') || active.text());
			} else {
				// if the event is prevented, restore the old value
				this.element.val(prev);
			}
		}
	};

	Selectability.prototype.listboxKeydown = function(event) {
		switch (event.which) {
		case KEY_ENTER:
		case KEY_SPACE:
			this.setActive($(event.target));
			this.closeCombobox();
			this.combobox.focus();

			event.preventDefault();
			return false;

		case KEY_LEFT:
		case KEY_UP:
			event.preventDefault();
			this.moveFocusUp();
			return false;

		case KEY_RIGHT:
		case KEY_DOWN:
			event.preventDefault();
			this.moveFocusDown();
			return false;
		}
	};

	Selectability.prototype.toggleCombobox = function() {
		if (this.combobox.attr('aria-expanded') === 'true') {
			this.closeCombobox();
		} else {
			this.openCombobox();

			// We may have an empty select widget, so we can't always depend on
			// `active' being defined
			if (this.active) {
				this.active.focus();
			}
		}
	};

	Selectability.prototype.closeCombobox = function() {
		this.active = null;
		this.listbox.empty();
		this.combobox.attr('aria-expanded', false);
	};

	Selectability.prototype.openCombobox = function() {
		this.populateListbox();
		this.combobox.attr('aria-expanded', true);
	};

	Selectability.prototype.populateListbox = function() {
		this.populateText();

		var children = walk.call(this, this.element.children()).children();
		this.listbox.append(children);
		return;

		function walk (elements) {
			var node = $('<div></div>'),
				This = this;

			$.each(elements, function (i, element) {
				var inner = $('<div></div>');
				element = $(element);

				if (element.is('option')) {
					inner
					.text(element.attr('label') || element.text())
					.attr({
						role: 'option',
						tabindex: -1,
						'aria-disabled': !!element.prop('disabled'),
						'aria-selected': element.val() === This.element.val()
					})
					.appendTo(node);

					if (element.prop('disabled')) {
						inner.removeAttr('tabindex');
					} else if (element.val() === This.element.val()) {
						This.active = inner;
					}
				} else if (element.is('optgroup')) {
					var children = walk.call(This, element.children());
					if (children.children().length) {
						var label = $('<div></div>')
						.text(element.attr('label'))
						.attr({
							role: 'heading',
							id: idgen()
						});

						inner
						.attr({
							role: 'group',
							'aria-labelledby': label.attr('id')
						})
						.append(children.prepend(label).children())
						.appendTo(node);
					}
				}
			});

			return node;
		}
	};

	Selectability.prototype.moveFocusUp = function() {
		var options = this.listbox.find('[role=option]');

		for (var i = options.index(this.active[0]) - 1; i >= 0; i--) {
			var option = $(options[i]);

			if (option.attr('aria-disabled') === 'false') {
				this.active.attr('aria-selected', 'false');

				this.active = option;
				this.active
				.attr('aria-selected', true)
				.focus();
				break;
			}
		}
	};

	Selectability.prototype.moveFocusDown = function() {
		var options = this.listbox.find('[role=option]');

		for (var i = options.index(this.active[0]) + 1; i < options.length; i++) {
			var option = $(options[i]);

			if (option.attr('aria-disabled') === 'false') {
				this.active.attr({
					'aria-selected': 'false'
				});

				this.active = option;
				this.active
				.attr('aria-selected', true)
				.focus();
				break;
			}
		}
	};



});

