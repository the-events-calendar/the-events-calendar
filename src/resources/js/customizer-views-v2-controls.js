/**
 * File customizer-views-v2-controls.js.
 *
 * Plugin Customizer enhancements for a better user experience.
 *
 * Contains handlers to make TEC Customizer controls show/hide asynchronously.
 *
 * Please, for sanity's sake - try to keep controls organized by how they appear in the customizer!
 */

var tribe_customizer_controls = tribe_customizer_controls || {};

( function( $, obj ) {
	// All of these are in the format 'tribe_customizer[section_name][control_name]'!

	/* eslint-disable max-len */
	obj.controls = {
		globalFontSize: 'tribe_customizer[global_elements][font_size]',
		globalFontSizeBase: 'tribe_customizer[global_elements][font_size_base]',
		globalBackgroundColorChoice: 'tribe_customizer[global_elements][background_color_choice]',
		globalBackgroundColor: 'tribe_customizer[global_elements][background_color]',

		eventsBarIconColorChoice: 'tribe_customizer[tec_events_bar][events_bar_icon_color_choice]',
		eventsBarIconColor: 'tribe_customizer[tec_events_bar][events_bar_icon_color]',
		eventsBarBackgroundColorChoice: 'tribe_customizer[tec_events_bar][events_bar_background_color_choice]',
		eventsBarBackgroundColor: 'tribe_customizer[tec_events_bar][events_bar_background_color]',
		eventsBarBorderColorChoice: 'tribe_customizer[tec_events_bar][events_bar_border_color_choice]',
		eventsBarBorderColor: 'tribe_customizer[tec_events_bar][events_bar_border_color]',
		eventsBarButtonColorChoice: 'tribe_customizer[tec_events_bar][find_events_button_color_choice]',
		eventsBarButtonColor: 'tribe_customizer[tec_events_bar][find_events_button_color]',
		// Pro-added Control
		eventsBarViewSelectorBackgroundColorChoice: 'tribe_customizer[tec_events_bar][view_selector_background_color_choice]',
		eventsBarViewSelectorBackgroundColor: 'tribe_customizer[tec_events_bar][view_selector_background_color]',

		monthGridBackgroundColorChoice: 'tribe_customizer[month_view][grid_background_color_choice]',
		monthGridBackgroundColor: 'tribe_customizer[month_view][grid_background_color]',
		monthTooltipBackgroundColor: 'tribe_customizer[month_view][tooltip_background_color]',
		monthMultidayEventBarColorChoice: 'tribe_customizer[month_view][multiday_event_bar_color_choice]',
		monthMultidayEventBarColor: 'tribe_customizer[month_view][multiday_event_bar_color]',

		singleEventTitleColorChoice: 'tribe_customizer[single_event][post_title_color_choice]',
		singleEventTitleColor: 'tribe_customizer[single_event][post_title_color]'
	};
	/* eslint-enable max-len */

	obj.globalFontSizeChange = false;
	obj.globalFontSizeBaseChange = false;

	/**
	 * Reusable function for when we have a color control that is dependent on a radio.
	 * Requires the radio option _value_ for the color control is "custom".
	 *
	 * @param {string} parent Parent selector.
	 * @param {string} child  Child selector.
	 */
	obj.nestedColorControl = function( parent, child ) {
		wp.customize( parent, function( setting ) {
			wp.customize.control( child, function( control ) {
				const slideFunction = function() {
					'custom' === setting.get()
						? control.container.slideDown( 180 )
						: control.container.slideUp( 180 );
				}

				slideFunction();

				setting.bind( slideFunction );
			} );
		} );
	}

	/**
	 * Reusable function for when we have a color control that is dependent on a radio.
	 * Requires the radio option _value_ for the color control is "custom".
	 *
	 * @param {string} parent Parent selector.
	 * @param {string} child  Child selector.
	 */
	obj.invertedNestedColorControl = function( parent, child ) {
		wp.customize( parent, function( setting ) {
			wp.customize.control( child, function( control ) {
				const slideFunction = function() {
					'custom' !== setting.get()
						? control.container.slideDown( 180 )
						: control.container.slideUp( 180 );
				}

				slideFunction();

				setting.bind( slideFunction );
			} );
		} );
	}


	/**
	 * Trigger control functions for the Global Elements section.
	 *
	 * @since 5.9.0
	 */
	obj.handleGlobalElements = function() {
		// Triggers on change of globalFontSizeBase to keep globalFontSize in sync.
		wp.customize( obj.controls.globalFontSizeBase, function( setting ) {
			wp.customize.control( obj.controls.globalFontSize, function( control ) {
				const sync = function() {
					if ( obj.globalFontSizeBaseChange ) {
						return;
					}

					obj.globalFontSizeChange = true;

					if ( setting.get() <= 14 ) {
						control.setting.set( -1 );
					} else if ( setting.get() >= 18 ) {
						control.setting.set( 1 );
					} else {
						control.setting.set( 0 );
					}

					obj.globalFontSizeChange = false;
				};

				sync();
				setting.bind( sync );
			} );
		} );

		// Triggers on change of globalFontSize to keep globalFontSizeBase in sync.
		wp.customize( obj.controls.globalFontSize, function( setting ) {
			wp.customize.control( obj.controls.globalFontSizeBase, function( control ) {

				const sync = function() {
					if ( obj.globalFontSizeChange ) {
						return;
					}

					obj.globalFontSizeBaseChange = true;

					if ( setting.get() < 0 ) {
						control.setting.set( 14 );
					} else if ( setting.get() > 0 ) {
						control.setting.set( 18 );
					} else {
						control.setting.set( 16 );
					}

					obj.globalFontSizeBaseChange = false;
				};

				sync();
				setting.bind( sync );
			} );
		} );

		// Only show the background color control when the background color choice is set to custom.
		obj.nestedColorControl(
			obj.controls.globalBackgroundColorChoice,
			obj.controls.globalBackgroundColor
		);
	};

	/**
	 * Trigger control functions for the Events Bar section.
	 *
	 * @since 5.9.0
	 */
	obj.handleEventsBar = function() {
		// Only show the icon color control when the icon color choice is set to custom.
		obj.nestedColorControl(
			obj.controls.eventsBarIconColorChoice,
			obj.controls.eventsBarIconColor
		);

		// Only show the events bar background color control
		// when the events bar background color choice is set to custom.
		obj.nestedColorControl(
			obj.controls.eventsBarViewSelectorBackgroundColorChoice,
			obj.controls.eventsBarViewSelectorBackgroundColor
		);

		// Only show the events bar view selector background color control
		// when the events bar view selector background color choice is set to custom.
		obj.nestedColorControl(
			obj.controls.eventsBarBackgroundColorChoice,
			obj.controls.eventsBarBackgroundColor
		);

		// Only show the events bar border color control when the events bar border color choice is set to custom.
		obj.nestedColorControl(
			obj.controls.eventsBarBorderColorChoice,
			obj.controls.eventsBarBorderColor
		);

		// Only show the events bar button color control when the events bar button color choice is set to custom.
		obj.nestedColorControl(
			obj.controls.eventsBarButtonColorChoice,
			obj.controls.eventsBarButtonColor
		);
	};

	/**
	 * Trigger control functions for the Month View section.
	 *
	 * @since 5.9.0
	 */
	obj.handleMonthView = function() {
		// Only show the grid background color control when the grid background color choice is set to custom.
		obj.nestedColorControl(
			obj.controls.monthGridBackgroundColorChoice,
			obj.controls.monthGridBackgroundColor
		);

		// Only show the tooltip background color control when the grid background color choice is set to default.
		obj.invertedNestedColorControl(
			obj.controls.monthGridBackgroundColorChoice,
			obj.controls.monthTooltipBackgroundColor
		);

		// Only show the event span color control when the event span color choice is set to custom.
		obj.nestedColorControl(
			obj.controls.monthMultidayEventBarColorChoice,
			obj.controls.monthMultidayEventBarColor
		);
	};

	/**
	 * Trigger control functions for the Single Event section.
	 *
	 * @since 5.9.0
	 */
	obj.handleSingleEvent = function() {
		// Only show the single event title color control when the single event title color choice is set to custom.
		obj.nestedColorControl(
			obj.controls.singleEventTitleColorChoice,
			obj.controls.singleEventTitleColor
		);
	};

	/**
	 * Trigger control functions for each section.
	 *
	 * @since 5.9.0
	 */
	obj.init = function() {
		obj.handleGlobalElements();

		obj.handleEventsBar();

		obj.handleMonthView();

		obj.handleSingleEvent();
	};

	/**
	 * Trigger our init function when customizer is ready.
	 *
	 * @since 5.9.0
	 */
	wp.customize.bind( 'ready', obj.init );

} )( jQuery, tribe_customizer_controls );
