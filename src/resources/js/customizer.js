( function( $, wp ){

	console.log( tribe_events_customizer_fields );

	var customizer = {
		fields: tribe_events_customizer_fields,
		getBodyClass: function( value, place ){
			return 'tribe-' + place + '-theme-' + value;
		},
		change: function( section, setting, value ){
			if ( 'undefined' === typeof tribe_events_customizer[ section ] ){
				return false;
			}

			if ( 'undefined' === typeof tribe_events_customizer[ section ][ setting ] ){
				return false;
			}

			tribe_events_customizer[ section ][ setting ] = value;

			return tribe_events_customizer;
		},
		applyCSS: function(){
			var $template = $( '#tmpl-tribe_events_customizer_css' );
				template_raw = $template.text(),
				template = _.template( template_raw ),
				$css = $( '#tribe_events_customizer_css' );

			$css.html( template( tribe_events_customizer ) );
		}
	};

	// All Color Settings
	$.each( customizer.fields, function( section, settings ) {
		$.each( settings, function( index, setting ) {
			wp.customize( 'tribe_events_customizer[' + section + '][' + setting + ']', function( value ) {
				value.bind( function( newval ) {
					customizer.change( section, setting, newval );
					customizer.applyCSS()
				} );
			} );
		} );
	} );

	// Now the Specfic Settings
	wp.customize( 'tribe_events_customizer[general_theme][base_color_scheme]', function( value ) {
		value.bind( function( newval, oldval ) {
			$( 'body' ).removeClass( customizer.getBodyClass( oldval, 'global' ) ).addClass( customizer.getBodyClass( newval, 'global' ) );
		} );
	} );
	wp.customize( 'tribe_events_customizer[widget][color_scheme]', function( value ) {
		value.bind( function( newval, oldval ) {
			$( 'body' ).removeClass( customizer.getBodyClass( oldval, 'widget' ) ).addClass( customizer.getBodyClass( newval, 'widget' ) );
		} );
	} );
} )( window.jQuery, window.wp );