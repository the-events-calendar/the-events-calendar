if ( "function" === typeof jQuery ) jQuery( document ).ready( function($) {
	function setup_map( element, lat, lng ) {
		// We'll use the venue coords to centre the map and for the marker
		var coords = new google.maps.LatLng(lat, lng);

		// Map setup
		var options = {
			zoom: parseInt( tribe_events_pro_single_map.zoom ),
			center: coords
		};

		// Marker setup
		var marker = new google.maps.Marker( {
			position: coords
		} );

		// Create the map and add the marker
		tribe_events_pro_single_map.embedded_map = new google.maps.Map( element, options );
		marker.setMap( tribe_events_pro_single_map.embedded_map );
	}

	// We need the map data array to exist
	if ("undefined" === typeof tribe_events_pro_single_map ) return;

	// Iterate through available map data and try to find each corresponding map placeholder
	$.each( tribe_events_pro_single_map.markers, function( index, value ) {
		var map_holder = document.getElementById( "tribe_events_pro_single_map_" + index );
		if ( null !== map_holder ) setup_map( map_holder, value[0], value[1] );
	});
});