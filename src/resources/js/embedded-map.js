/**
 * Sets up one or more embedded maps.
 */
if ( "function" === typeof jQuery ) jQuery( function( $ ) {
	var mapHolder,
		position,
		venueObject,
		venueAddress,
		venueCoords,
		venueTitle;

	// The tribeEventsSingleMap object must be accessible (as it contains the venue address data etc)
	if ( 'undefined' === typeof tribeEventsSingleMap ) {
		return;
	}

	/**
	 * Determine whether to use long/lat coordinates (these are preferred) or the venue's street
	 * address.
	 */
	function prepare() {
		if ( false !== venueCoords ) {
			useCoords();
		} else {
			useAddress();
		}
	}

	/**
	 * Use long/lat coordinates to position the pin marker.
	 */
	function useCoords() {
		position = new google.maps.LatLng( venueCoords[0], venueCoords[1] );
		initialize();
	}

	/**
	 * Use a street address and Google's geocoder to position the pin marker.
	 */
	function useAddress() {
		var geocoder = new google.maps.Geocoder();

		geocoder.geocode(
			{ "address": venueAddress },
			function ( results, status ) {
				if ( status == google.maps.GeocoderStatus.OK ) { // eslint-disable-line eqeqeq
					position = results[0].geometry.location;
					initialize();
				}
			}
		);
	}

	/**
	 * Setup the map and apply a marker.
	 *
	 * Note that for each individual map, the actual map object can be accessed via the
	 * tribeEventsSingleMap object. In simple cases (ie, where there is only one map on
	 * the page) that means you can access it and change its properties via:
	 *
	 *     tribeEventsSingleMap.addresses[0].map
	 *
	 * Where there are multiple maps - such as in a custom list view with a map per
	 * event - tribeEventsSingleMap.addresses can be iterated through and changes made
	 * on an map-by-map basis.
	 */
	function initialize() {
		var mapOptions = {
			zoom     : parseInt( tribeEventsSingleMap.zoom ),
			center   : position,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		}
		venueObject.map = new google.maps.Map( mapHolder, mapOptions );

		var marker = {
			map     : venueObject.map,
			title   : venueTitle,
			position: position
		};

		/**
		 * Trigger a new event when the Map is created in order to allow Users option to customize the map by listening
		 * to the correct event and having an instance of the Map variable available to modify if required.
		 *
		 * @param {Object} map An instance of the Google Map.
		 * @param {Element} el The DOM Element where the map is attached.
		 * @param {Object} options The initial set of options for the map.
		 *
		 * @since 4.6.10
		 *
		 */
		$( 'body' ).trigger( 'map-created.tribe', [ venueObject.map, mapHolder, mapOptions ] );


		// If we have a Map Pin set, we use it
		if ( 'undefined' !== tribeEventsSingleMap.pin_url && tribeEventsSingleMap.pin_url ) {
			marker.icon = tribeEventsSingleMap.pin_url;
		}

		new google.maps.Marker( marker );
	}

	// Iterate through available addresses and set up the map for each
	$.each( tribeEventsSingleMap.addresses, function( index, venue ) {
		mapHolder = document.getElementById( "tribe-events-gmap-" + index );
		if ( null !== mapHolder ) {
			venueObject  = "undefined" !== typeof venue ? venue: {};
			venueAddress = "undefined" !== typeof venue.address ? venue.address : false;
			venueCoords  = "undefined" !== typeof venue.coords  ? venue.coords  : false;
			venueTitle   = venue.title;
			prepare();
		}
	});
});
