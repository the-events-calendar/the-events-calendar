var encoded_address;

function initialize() {
	var myOptions = {
		zoom     : parseInt( tribeEventsEmbeddedMap.zoom ),
		center   : encoded_address,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};

	var map = new google.maps.Map( document.getElementById( "tribe-events-gmap" ), myOptions );

	new google.maps.Marker( {
			map     : map,
			title   : tribeEventsEmbeddedMap.title,
			position: encoded_address
	} );
}

function codeAddress( address ) {
	var geocoder = new google.maps.Geocoder();

	geocoder.geocode(
		{ 'address': tribeEventsEmbeddedMap.address },
		function ( results, status ) {
			if ( status == google.maps.GeocoderStatus.OK ) {
				encoded_address = results[0].geometry.location;
				initialize();
			}
		}
	);
}

codeAddress();