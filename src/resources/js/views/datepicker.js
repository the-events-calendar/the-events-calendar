/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since TBD
 *
 * @type {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Datepicker Object in the Global Tribe variable
 *
 * @since TBD
 *
 * @type {PlainObject}
 */
tribe.events.views.datepicker = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since TBD
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} obj tribe.events.views.manager
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	var $document = $( document );

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		topBar: '[data-js="tribe-events-top-bar"]',
		button: '[data-js="tribe-events-top-bar-datepicker-button"]',
		buttonOpenClass: '.tribe-events-c-top-bar__datepicker-button--open',
	};

	/**
	 * Object of state
	 *
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.state = {
		initialized: false,
	};

	/**
	 * Handle datepicker show event
	 *
	 * @since TBD
	 *
	 * @param {Event} event event object for 'show' event
	 *
	 * @return {void}
	 */
	obj.handleShow = function( event ) {
		var $datepickerButton = $( event.target );

		$datepickerButton.toggleClass( obj.selectors.buttonOpenClass.className() );

		if ( ! $datepickerButton.hasClass( obj.selectors.buttonOpenClass.className() ) ) {
			$datepickerButton.bootstrapDatepicker( 'hide' );
		}
	};

	/**
	 * Handle datepicker hide event
	 *
	 * @since TBD
	 *
	 * @param {Event} event event object for 'hide' event
	 *
	 * @return {void}
	 */
	obj.handleHide = function( event ) {
		$( event.target ).removeClass( obj.selectors.buttonOpenClass.className() );
	};

	/**
	 * Initialize datepicker JS
	 *
	 * @since TBD
	 *
	 * @param {Event} event event object for 'afterSetup.tribeEvents' event
	 * @param {integer} index jQuery.each index param from 'afterSetup.tribeEvents' event
	 * @param {jQuery} $container jQuery object of view container
	 * @param {object} data data object passed from 'afterSetup.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) {
		// if data.slug = 'month', then minViewMode = 'months'
		var $datepickerButton = $container.find( obj.selectors.button );

		$datepickerButton
			.bootstrapDatepicker( {
				container: $datepickerButton.closest( obj.selectors.topBar ),
				minViewMode: 1,
				orientation: 'bottom',
				showOnFocus: false,
			} )
			.on( 'changeMonth', function( e ) {
				console.log(e);
			} )
			.on( 'show', obj.handleShow )
			.on( 'hide', obj.handleHide );
	};

	/**
	 * Initialize datepicker i18n
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.initDatepickerI18n = function() {
		var tribeL10nDatatables = window.tribe_l10n_datatables || {};
		var datepickerI18n = tribeL10nDatatables.datepicker || {};

		datepickerI18n.dayNames &&
			( $.fn.bootstrapDatepicker.dates.en.days = datepickerI18n.dayNames );
		datepickerI18n.dayNamesShort &&
			( $.fn.bootstrapDatepicker.dates.en.daysShort = datepickerI18n.dayNamesShort );
		datepickerI18n.dayNamesMin &&
			( $.fn.bootstrapDatepicker.dates.en.daysMin = datepickerI18n.dayNamesMin );
		datepickerI18n.monthNames &&
			( $.fn.bootstrapDatepicker.dates.en.months = datepickerI18n.monthNames );
		datepickerI18n.monthNamesMin &&
			( $.fn.bootstrapDatepicker.dates.en.monthsShort = datepickerI18n.monthNamesMin );
		datepickerI18n.today &&
			( $.fn.bootstrapDatepicker.dates.en.today = datepickerI18n.today );
		datepickerI18n.clear &&
			( $.fn.bootstrapDatepicker.dates.en.clear = datepickerI18n.clear );
	};

	/**
	 * Initialize datepicker to jQuery object
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.initDatepicker = function() {
		if ( $.fn.datepicker && $.fn.datepicker.noConflict ) {
			var datepicker = $.fn.datepicker.noConflict();
			$.fn.bootstrapDatepicker = datepicker;

			obj.initDatepickerI18n();
		}

		obj.state.initialized = true;
	};

	/**
	 * Handles the initialization of the Datepicker when Document is ready
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		obj.initDatepicker();

		if ( obj.state.initialized ) {
			$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.init );

			/**
			 * @todo: do below for ajax events
			 */
			// on 'beforeAjaxBeforeSend.tribeEvents' event, remove all listeners
			// on 'afterAjaxError.tribeEvents', add all listeners
		}
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.datepicker );
