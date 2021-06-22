/**
 * File customize-controls.js.
 *
 * Plugin Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

var tribe_customizer_controls = tribe_customizer_controls || {};

( function( $, obj ) {
	// All of these are in the format 'tribe_customizer[section_name][control_name]'!
	obj.selectors = {
		fontSize: 'tribe_customizer[global_elements][font_size]',
		fontSizeBase: 'tribe_customizer[global_elements][font_size_base]',
		backgroundColor: 'tribe_customizer[global_elements][background_color]',
		backgroundColorChoice: 'tribe_customizer[global_elements][background_color_choice]',
		gridBackgroundColor: 'tribe_customizer[month_view][grid_background_color]',
		gridBackgroundColorChoice: 'tribe_customizer[month_view][grid_background_color_choice]',
		tooltipBackgroundColor: 'tribe_customizer[month_view][tooltip_background_color]',
		multidayEventBar: 'tribe_customizer[month_view][multiday_event_bar_color]',
		multidayEventBarChoice: 'tribe_customizer[month_view][multiday_event_bar_color_choice]',
		eventsBarIconColor: 'tribe_customizer[tec_events_bar][events_bar_icon_color]',
		eventsBarIconColorChoice: 'tribe_customizer[tec_events_bar][events_bar_icon_color_choice]',
		eventsBarBackgroundColor: 'tribe_customizer[tec_events_bar][events_bar_background_color]',
		eventsBarBackgroundColorChoice: 'tribe_customizer[tec_events_bar][events_bar_background_color_choice]',
		eventsBarBorderColor: 'tribe_customizer[tec_events_bar][events_bar_border_color]',
		eventsBarBorderColorChoice: 'tribe_customizer[tec_events_bar][events_bar_border_color_choice]',
		eventsBarButtonColor: 'tribe_customizer[tec_events_bar][find_events_button_color]',
		eventsBarButtonColorChoice: 'tribe_customizer[tec_events_bar][find_events_button_color_choice]',
	};

	obj.fontSizeChange = false;
	obj.fontSizeBaseChange = false;

	wp.customize.bind( 'ready', function() {
		// Only show the background color control when the background color choice is set to custom.
		wp.customize( obj.selectors.backgroundColorChoice, function( setting ) {
			wp.customize.control( obj.selectors.backgroundColor, function( control ) {
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

		// Only show the grid background color control when the grid background color choice is set to custom.
		wp.customize( obj.selectors.gridBackgroundColorChoice, function( setting ) {
			wp.customize.control( obj.selectors.gridBackgroundColor, function( control ) {
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

			wp.customize.control( obj.selectors.tooltipBackgroundColor, function( control ) {
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
		wp.customize( obj.selectors.multidayEventBarChoice, function( setting ) {
			wp.customize.control( obj.selectors.multidayEventBar, function( control ) {
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

		// Triggers on change of fontSizeBase to keep fontSize in sync.
		wp.customize( obj.selectors.fontSizeBase, function( setting ) {
			wp.customize.control( obj.selectors.fontSize, function( control ) {
				const sync = function() {
					if ( obj.fontSizeBaseChange ) {
						return;
					}

					obj.fontSizeChange = true;

					if ( setting.get() < 16 ) {
						control.setting.set( -1 );
					} else if ( setting.get() > 16 ) {
						control.setting.set( 1 );
					} else {
						control.setting.set( 0 );
					}

					obj.fontSizeChange = false;
				};

				sync();
				setting.bind( sync );
			} );
		} );

		// Triggers on change of fontSize to keep fontSizeBase in sync.
		wp.customize( obj.selectors.fontSize, function( setting ) {
			wp.customize.control( obj.selectors.fontSizeBase, function( control ) {
				const fontSizeControl = setting.findControls( obj.selectors.fontSize );
				const datalist = fontSizeControl[ 0 ].elements[ 0 ].element[ 0 ].previousElementSibling;

				const sync = function() {
					if ( obj.fontSizeChange ) {
						return;
					}

					obj.fontSizeBaseChange = true;
					let multiplier = 1;

					if ( setting.get() < 0 ) {
						// Slide to small.
						multiplier = datalist.querySelector( '[label=small]' ).value;
					} else if ( setting.get() > 0 ) {
						// Slide to large.
						multiplier = datalist.querySelector( '[label=large]' ).value;
					}

					const newVal = 16 * parseFloat( multiplier );

					control.setting.set( newVal );

					obj.fontSizeBaseChange = false;
				};

				sync();
				setting.bind( sync );
			} );
		} );

	} );
} )( jQuery, tribe_customizer_controls );
