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
	obj.selectors = {
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

	wp.customize.bind( 'ready', function() {
		/*--------- Global Elements ---------*/

		// Triggers on change of globalFontSizeBase to keep globalFontSize in sync.
		wp.customize( obj.selectors.globalFontSizeBase, function( setting ) {
			wp.customize.control( obj.selectors.globalFontSize, function( control ) {
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
		wp.customize( obj.selectors.globalFontSize, function( setting ) {
			wp.customize.control( obj.selectors.globalFontSizeBase, function( control ) {

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
		wp.customize( obj.selectors.globalBackgroundColorChoice, function( setting ) {
			wp.customize.control( obj.selectors.globalBackgroundColor, function( control ) {
				const visibility = function() {
					if ( 'custom' === setting.get() ) {
						control.container.slideDown( 180 );
					} else {
						control.container.slideUp( 180 );
					}
				};

				visibility();
				setting.bind( visibility );
			} );
		} );

		/*--------- Events Bar ---------*/

		// Only show the icon color control when the icon color choice is set to custom.
		wp.customize( obj.selectors.eventsBarIconColorChoice, function( setting ) {
			wp.customize.control( obj.selectors.eventsBarIconColor, function( control ) {
				const visibility = function() {
					if ( 'custom' === setting.get() ) {
						control.container.slideDown( 180 );
					} else {
						control.container.slideUp( 180 );
					}
				};

				visibility();
				setting.bind( visibility );
			} );
		} );

		// Only show the events bar background color control when the events bar background color choice is set to custom.
		wp.customize( obj.selectors.eventsBarViewSelectorBackgroundColorChoice, function( setting ) {
			wp.customize.control( obj.selectors.eventsBarViewSelectorBackgroundColor, function( control ) { /* eslint-disable-line max-len */
				const visibility = function() {
					if ( 'custom' === setting.get() ) {
						control.container.slideDown( 180 );
					} else {
						control.container.slideUp( 180 );
					}
				};

				visibility();
				setting.bind( visibility );
			} );
		} );

		// Only show the events bar view selector background color control
		// when the events bar view selector background color choice is set to custom.
		wp.customize( obj.selectors.eventsBarBackgroundColorChoice, function( setting ) {
			wp.customize.control( obj.selectors.eventsBarBackgroundColor, function( control ) {
				const visibility = function() {
					if ( 'custom' === setting.get() ) {
						control.container.slideDown( 180 );
					} else {
						control.container.slideUp( 180 );
					}
				};

				visibility();
				setting.bind( visibility );
			} );
		} );

		// Only show the events bar border color control when the events bar border color choice is set to custom.
		wp.customize( obj.selectors.eventsBarBorderColorChoice, function( setting ) {
			wp.customize.control( obj.selectors.eventsBarBorderColor, function( control ) {
				const visibility = function() {
					if ( 'custom' === setting.get() ) {
						control.container.slideDown( 180 );
					} else {
						control.container.slideUp( 180 );
					}
				};

				visibility();
				setting.bind( visibility );
			} );
		} );

		// Only show the events bar button color control when the events bar button color choice is set to custom.
		wp.customize( obj.selectors.eventsBarButtonColorChoice, function( setting ) {
			wp.customize.control( obj.selectors.eventsBarButtonColor, function( control ) {
				const visibility = function() {
					if ( 'custom' === setting.get() ) {
						control.container.slideDown( 180 );
					} else {
						control.container.slideUp( 180 );
					}
				};

				visibility();
				setting.bind( visibility );
			} );
		} );

		/*--------- Month View ---------*/

		// Only show the grid background color control when the grid background color choice is set to custom.
		wp.customize( obj.selectors.monthGridBackgroundColorChoice, function( setting ) {
			wp.customize.control( obj.selectors.monthGridBackgroundColor, function( control ) {
				const visibility = function() {
					if ( 'custom' === setting.get() ) {
						control.container.slideDown( 180 );
					} else {
						control.container.slideUp( 180 );
					}
				};

				visibility();
				setting.bind( visibility );
			} );

			wp.customize.control( obj.selectors.monthTooltipBackgroundColor, function( control ) {
				const visibility = function() {
					if ( 'custom' === setting.get() ) {
						control.container.slideUp( 180 );
					} else {
						control.container.slideDown( 180 );
					}
				};

				visibility();
				setting.bind( visibility );
			} );
		} );

		// Only show the event span color control when the event span color choice is set to custom.
		wp.customize( obj.selectors.monthMultidayEventBarColorChoice, function( setting ) {
			wp.customize.control( obj.selectors.monthMultidayEventBarColor, function( control ) {
				const visibility = function() {
					if ( 'custom' === setting.get() ) {
						control.container.slideDown( 180 );
					} else {
						control.container.slideUp( 180 );
					}
				};

				visibility();
				setting.bind( visibility );
			} );
		} );

		/*--------- Single Event ---------*/

		// Only show the single event title color control when the single event title color choice is set to custom.
		wp.customize( obj.selectors.singleEventTitleColorChoice, function( setting ) {
			wp.customize.control( obj.selectors.singleEventTitleColor, function( control ) {
				const visibility = function() {
					if ( 'custom' === setting.get() ) {
						control.container.slideDown( 180 );
					} else {
						control.container.slideUp( 180 );
					}
				};

				visibility();
				setting.bind( visibility );
			} );
		} );

	} );
} )( jQuery, tribe_customizer_controls );
