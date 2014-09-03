/**
 * Sets up one or more embedded maps.
 */
if ( "function" === typeof jQuery ) jQuery( document ).ready( function( $ ) {
	var mapHolder,
		position,
		streetAddress,
		venueCoords,
		venueTitle;

	// The tribeEventsSingleMap object must be accessible (as it contains the venue address data etc)
	if ("undefined" === typeof tribeEventsSingleMap ) return;

	/**
	 * Determine whether to use long/lat coordinates (these are preferred) or the venue's street
	 * address.
	 */
	function prepare() {
		if ( false !== venueCoords ) useCoords();
		else useAddress();
	}

	/**
	 * Use long/lat coordinates to position the pin marker.
	 */
	function useCoords() {
		position = new google.maps.LatLng(venueCoords[0], venueCoords[1]);
		initialize();
	}

	/**
	 * Use a street address and Google's geocoder to position the pin marker.
	 */
	function useAddress() {
		var geocoder = new google.maps.Geocoder();

		geocoder.geocode(
			{ "address": streetAddress },
			function ( results, status ) {
				if ( status == google.maps.GeocoderStatus.OK ) {
					position = results[0].geometry.location;
					initialize();
				}
			}
		);
	}

	/**
	 * Setup the map and apply a marker.
	 */
	function initialize() {
		var map = new google.maps.Map( mapHolder, {
			zoom     : parseInt( tribeEventsSingleMap.zoom ),
			center   : position,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		} );

		new google.maps.Marker( {
			map     : map,
			title   : venueTitle,
			position: position
		} );
	}

	// Iterate through available addresses and set up the map for each
	$.each( tribeEventsSingleMap.addresses, function( index, venue ) {
		mapHolder = document.getElementById( "tribe-events-gmap-" + index );
		if ( null !== mapHolder ) {
			streetAddress = "undefined" !== typeof venue.address ? venue.address : false;
			venueCoords   = "undefined" !== typeof venue.coords  ? venue.coords  : false;
			venueTitle    = venue.title;
			prepare();
		}
	});
});