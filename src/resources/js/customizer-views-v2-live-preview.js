/**
 * File customizer-views-v2-controls.js.
 *
 * Plugin Customizer enhancements for a better user experience.
 *
 * Contains handlers to make TEC Customizer preview reload changes asynchronously.
 */

 var tribe_customizer_preview = tribe_customizer_preview || {};

 ( function( $, api, obj ) {
	// All of these are in the format 'tribe_customizer[section_name][control_name]'!
	obj.selectors = {
		backgroundColor: 'tribe_customizer[global_elements][background_color]',
		backgroundColorChoice: 'tribe_customizer[global_elements][background_color_choice]',
		eventsBarBackgroundColor: 'tribe_customizer[tec_events_bar][events_bar_background_color]',
		eventsBarBackgroundColorChoice: 'tribe_customizer[tec_events_bar][events_bar_background_color_choice]',
		eventsBarBorderColor: 'tribe_customizer[tec_events_bar][events_bar_border_color]',
		eventsBarBorderColorChoice: 'tribe_customizer[tec_events_bar][events_bar_border_color_choice]',
		eventsBarButtonColor: 'tribe_customizer[tec_events_bar][find_events_button_color]',
		eventsBarButtonColorChoice: 'tribe_customizer[tec_events_bar][find_events_button_color_choice]',
		eventsBarIconColor: 'tribe_customizer[tec_events_bar][events_bar_icon_color]',
		eventsBarIconColorChoice: 'tribe_customizer[tec_events_bar][events_bar_icon_color_choice]',
		fontSize: 'tribe_customizer[global_elements][font_size]',
		fontSizeBase: 'tribe_customizer[global_elements][font_size_base]',
		gridBackgroundColor: 'tribe_customizer[month_view][grid_background_color]',
		gridBackgroundColorChoice: 'tribe_customizer[month_view][grid_background_color_choice]',
		multidayEventBar: 'tribe_customizer[month_view][multiday_event_bar_color]',
		multidayEventBarChoice: 'tribe_customizer[month_view][multiday_event_bar_color_choice]',
		tooltipBackgroundColor: 'tribe_customizer[month_view][tooltip_background_color]',
	};

	obj.cssStrings = {
		backgroundColor: '--tec-color-background-events',
		backgroundColorChoice: '--tec-color-background-events',
		eventsBarBackgroundColor: '',
		eventsBarBackgroundColorChoice: '',
		eventsBarBorderColor: '',
		eventsBarBorderColorChoice: '',
		eventsBarButtonColor: '',
		eventsBarButtonColorChoice: '',
		eventsBarIconColor: '',
		eventsBarIconColorChoice: '',
		fontSize: '',
		fontSizeBase: '',
		gridBackgroundColor: '',
		gridBackgroundColorChoice: '',
		multidayEventBar: '',
		multidayEventBarChoice: '',
		tooltipBackgroundColor: '',
	};

	//Update events background color...
	api( obj.selectors.backgroundColor, function( value ) {
		// Bind to the value change
		value.bind( function( to ) {
			// Grab all affected regions
			let root = document.querySelectorAll( '.tribe-events, .tribe-common' );

			// Loop through them and set the var for them individually.
			root.forEach( function( tribeElement ) {
				tribeElement.style.setProperty( obj.cssStrings.backgroundColor, to );
			  } );
		} );
	} );


	api( obj.selectors.backgroundColorChoice, function( value ) {
		value.bind( function( to ) {
			let root = document.querySelectorAll( '.tribe-events, .tribe-common' );

			if ( 'transparent' ===  to) {
				// Handle the default
				root.forEach(function( tribeElement ) {
					tribeElement.style.setProperty( obj.cssStrings.backgroundColor, to );
				} );
			} else {
				// Handle the switch to "custom" immediately.
				root.forEach(function(tribeElement) {
					let backgroundColor =  api( obj.selectors.backgroundColor ).get();
					tribeElement.style.setProperty( obj.cssStrings.backgroundColor, backgroundColor );
				} );
			}
		} );
	} );

} )( jQuery, wp.customize, tribe_customizer_preview );
