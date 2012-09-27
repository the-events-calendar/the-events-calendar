var map, geocoder, geocodes, bounds, markersArray = [], spinner;
geocoder = new google.maps.Geocoder();
bounds = new google.maps.LatLngBounds();

jQuery( document ).ready( function () {

	var options = {
		zoom:5,
		center:new google.maps.LatLng( 36.77, -119.41 ),
		mapTypeId:google.maps.MapTypeId.ROADMAP
	};

	map = new google.maps.Map( document.getElementById( 'map' ), options );
	bounds = new google.maps.LatLngBounds();

	jQuery( '#search' ).click( function () {

		deleteMarkers();

		jQuery( "#results" ).empty();

		var location = jQuery( '#location' ).val();
		jQuery( "#options" ).hide();
		jQuery( "#options #links" ).empty();

		spin_start();

		processGeocoding( location, processResponse );
		return false;

	} );

	jQuery( "#options" ).on( 'click', 'a', function () {
		spin_start();
		processOption( geocodes[jQuery( this ).attr( 'data-index' )] );
	} );

	function processGeocoding( location, callback ) {

		var request = {
			address:location
		};

		geocoder.geocode( request, function ( results, status ) {

			/*
			 * Possible statuses:
			 * OK
			 * ZERO_RESULTS
			 * OVER_QUERY_LIMIT
			 * REQUEST_DENIED
			 * INVALID_REQUEST
			 */

			console.log( status );

			if ( status == google.maps.GeocoderStatus.OK ) {

				callback( results );
				return results;
			}

			if ( status == google.maps.GeocoderStatus.ZERO_RESULTS ) {

				spin_end();

				return status;
			}

			return status;
		} );
	}

	function processResponse( results, selected_index ) {
		geocodes = results;

		if ( geocodes.length > 1 ) {
			spin_end();
			jQuery( "#options" ).show();

			for ( var i = 0; i < geocodes.length; i++ ) {
				jQuery( "<a/>" ).text( geocodes[i].formatted_address ).attr( "href", "#" ).addClass( 'option_link' ).attr( 'data-index', i ).appendTo( "#options #links" );
				addMarker( geocodes[i] );
			}
			centerMap();

			jQuery( 'html, body' ).animate( {
				scrollTop:jQuery( "#options" ).offset().top
			}, 1000 );

		} else {

			processOption( geocodes[0] );

		}
	}

	function processOption( geocode ) {

		deleteMarkers();
		addMarker( geocode );
		centerMap();

		var data = {
			lat:geocode.geometry.location.lat(),
			lng:geocode.geometry.location.lng(),
			action:'geosearch',
			nonce:GeoLoc.nonce
		};

		jQuery.post( GeoLoc.ajaxurl, data, function ( response ) {

			spin_end();
			jQuery( "#results" ).html( response );

			jQuery( 'html, body' ).animate( {
				scrollTop:jQuery( "#results" ).offset().top
			}, 1000 );

		} );

	}


	function addMarker( geocode ) {

		var marker = new google.maps.Marker( {
			map:map,
			title:geocode.formatted_address
		} );

		marker.setPosition( geocode.geometry.location );


		var infoWindow = new google.maps.InfoWindow();

		content = "Address: " + geocode.formatted_address + "<br />" +
			"Kind: " + geocode.types + "<br />" +
			"Lat: " + geocode.geometry.location.lat() + "<br />" +
			"Lng: " + geocode.geometry.location.lng();

		infoWindow.setContent( content );

		google.maps.event.addListener( marker, 'click', function ( event ) {
			infoWindow.open( map, marker );
		} );

		markersArray.push( marker );
		bounds.extend( geocode.geometry.location );

	}

	function deleteMarkers() {
		if ( markersArray ) {
			for ( i in markersArray ) {
				markersArray[i].setMap( null );
			}
			markersArray.length = 0;
			bounds = new google.maps.LatLngBounds();
		}
	}

	function centerMap() {
		map.fitBounds( bounds );
		if ( map.getZoom() > 13 ) {
			map.setZoom( 13 );
		}

	}

	function spin_start() {
		jQuery( "#loading" ).show();

	}

	function spin_end() {
		jQuery( "#loading" ).hide();
	}

} );


