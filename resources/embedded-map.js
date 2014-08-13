if ( "function" === typeof jQuery ) jQuery( document ).ready( function($) {
	function setupMap( element, lat, lng ) {
		// We'll use the venue coords to centre the map and for the marker
		var coords = new google.maps.LatLng(lat, lng);

		// Map setup
		var options = {
			zoom: parseInt( tribeEventsProSingleMap.zoom ),
			center: coords
		};

		// Marker setup
		var marker = new google.maps.Marker( {
			position: coords
		} );

		// Create the map and add the marker
		tribeEventsProSingleMap.embedded_map = new google.maps.Map( element, options );
		marker.setMap( tribeEventsProSingleMap.embedded_map );
	}

	// We need the map data array to exist
	if ("undefined" === typeof tribeEventsProSingleMap ) return;

	// Iterate through available map data and try to find each corresponding map placeholder
	$.each( tribeEventsProSingleMap.markers, function( index, value ) {
		var map_holder = document.getElementById( "tribe_events_pro_single_map_" + index );
		if ( null !== map_holder ) setupMap( map_holder, value[0], value[1] );
	});
});