/* global tribe, tribe_events_customizer_live_preview_js_config */

/**
 * File customizer-views-v2-controls.js.
 *
 * Plugin Customizer enhancements for a better user experience.
 *
 * Contains handlers to make TEC Customizer preview reload changes asynchronously.
 */
//tribe_events_customizer_live_preview_js_config

 var tribe_customizer_preview = tribe_customizer_preview || {};

 ( function( $, api, obj ) {
	// All of these are in the format 'tribe_customizer[section_name][control_name]'!
	obj.selectors = {
		globalBackgroundColor: 'tribe_customizer[global_elements][background_color]',
		globalBackgroundColorChoice: 'tribe_customizer[global_elements][background_color_choice]',
		globalFontSizeBase: 'tribe_customizer[global_elements][font_size_base]',
		globalEventTitleColor: 'tribe_customizer[global_elements][event_title_color]',
		globalAccentColor: 'tribe_customizer[global_elements][accent_color]',
		globalEventDateColor: 'tribe_customizer[global_elements][event_date_time_color]',
		globalFontFamily: 'tribe_customizer[global_elements][font_family]',


		eventsBarBackgroundColor: 'tribe_customizer[tec_events_bar][events_bar_background_color]',
		eventsBarBackgroundColorChoice: 'tribe_customizer[tec_events_bar][events_bar_background_color_choice]',
		eventsBarBorderColor: 'tribe_customizer[tec_events_bar][events_bar_border_color]',
		eventsBarBorderColorChoice: 'tribe_customizer[tec_events_bar][events_bar_border_color_choice]',
		eventsBarButtonColor: 'tribe_customizer[tec_events_bar][find_events_button_color]',
		eventsBarButtonColorChoice: 'tribe_customizer[tec_events_bar][find_events_button_color_choice]',
		eventsBarIconColor: 'tribe_customizer[tec_events_bar][events_bar_icon_color]',
		eventsBarIconColorChoice: 'tribe_customizer[tec_events_bar][events_bar_icon_color_choice]',

		monthGridBackgroundColor: 'tribe_customizer[month_view][grid_background_color]',
		monthGridBackgroundColorChoice: 'tribe_customizer[month_view][grid_background_color_choice]',
		monthMultidayEventBar: 'tribe_customizer[month_view][multiday_event_bar_color]',
		monthMultidayEventBarChoice: 'tribe_customizer[month_view][multiday_event_bar_color_choice]',
		monthTooltipBackgroundColor: 'tribe_customizer[month_view][tooltip_background_color]',
	};

	obj.customProps = {
		globalBackgroundColor: '--tec-color-background-events',
		globalEventTitleColor: [
			'--tec-color-text-events-title',
			'--tec-color-text-event-title',
		],
		globalEventDateColor: [
			'--tec-color-text-event-date',
			'--tec-color-text-event-date-secondary',
		],
		globalAccentColor: [
			'--tec-color-accent-primary',
			'--tec-color-accent-primary-hover',
			'--tec-color-accent-primary-multiday',
			'--tec-color-accent-primary-multiday-hover',
			'--tec-color-accent-primary-active',
			'--tec-color-accent-primary-background',
			'--tec-color-background-secondary-datepicker',
			'--tec-color-accent-primary-background-datepicker',
			'--tec-color-button-primary',
			'--tec-color-button-primary-hover',
			'--tec-color-button-primary-active',
			'--tec-color-button-primary-background',
			'--tec-color-day-marker-current-month',
			'--tec-color-day-marker-current-month-hover',
			'--tec-color-day-marker-current-month-active',
		],
		globalFontSizeBase: [
			'--tec-font-family-sans-serif',
			'--tec-font-family-base',
		],
		globalFontFamily: '',
		globalFontSizeKeys: [ 11, 12, 14, 16, 18, 20, 22, 24, 28, 32, 42, ],

		eventsBarBackgroundColor: '',
		eventsBarBorderColor: '',
		eventsBarButtonColor: '',
		eventsBarIconColor: '',

		monthGridBackgroundColor: '',
		monthMultidayEventBar: '',
		monthTooltipBackgroundColor: '',
	};

	/* Global Elements */

	//Update events background color...
	api( obj.selectors.globalBackgroundColor, function( value ) {
		// Bind to the value change
		value.bind( function( to ) {
			// Grab all affected regions
			const root = document.querySelectorAll( tribe_events_customizer_live_preview_js_config.selector );

			// Loop through them and set the var for them individually.
			root.forEach( function( tribeElement ) {
				tribeElement.style.setProperty( obj.customProps.globalBackgroundColor, to );
			  } );
		} );
	} );


	api( obj.selectors.globalBackgroundColorChoice, function( value ) {
		value.bind( function( to ) {
			const root = document.querySelectorAll( '.tribe-events, .tribe-common' );

			if ( 'transparent' ===  to) {
				// Handle the default
				root.forEach(function( tribeElement ) {
					tribeElement.style.setProperty( obj.customProps.globalBackgroundColor, to );
				} );
			} else {
				// Handle the switch to "custom" immediately.
				root.forEach(function(tribeElement) {
					let backgroundColor =  api( obj.selectors.globalBackgroundColor ).get();
					tribeElement.style.setProperty( obj.customProps.globalBackgroundColor, backgroundColor );
				} );
			}
		} );
	} );

	// Font Family
	api( obj.selectors.globalFontFamily, function( value ) {
		// Bind to the value change
		value.bind( function( to ) {
			// Grab all affected regions
			const root = document.querySelectorAll( tribe_events_customizer_live_preview_js_config.selector );

			if ( 'theme' ===  to) {
				to = 'inherit'
			} else {
				to = tribe_events_customizer_live_preview_js_config.default_font;
			}

			// Loop through them and set the var for them individually.
			root.forEach( function( tribeElement ) {
				tribeElement.style.setProperty( obj.customProps.globalFontFamily, to );
			  } );
		} );
	} );

} )( jQuery, wp.customize, tribe_customizer_preview );
