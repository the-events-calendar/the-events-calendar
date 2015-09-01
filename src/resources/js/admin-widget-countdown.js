( function( $ ) {
	'use strict';

	var tribeWidget = {
		setup: function( event, widget ){
			var $widget = $( widget );

			$widget.find( '.tribe-select2' ).select2();
		}
	};


	$( document ).on( {
		'widget-updated widget-added': tribeWidget.setup,
		'ready': function( event ){
			// This ensures that we setup corretly the widgets that are already in place
			$( '.tribe-widget-countdown-container' ).each( tribeWidget.setup );
		}
	} );
}( jQuery.noConflict() ) );