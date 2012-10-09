var map, geocoder, geocodes, bounds, markersArray = [], spinner;
geocoder = new google.maps.Geocoder();
bounds = new google.maps.LatLngBounds();

jQuery( document ).ready( function () {

	var options = {
		zoom:5,
		center:new google.maps.LatLng( 36.77, -119.41 ),
		mapTypeId:google.maps.MapTypeId.ROADMAP
	};

	if ( document.getElementById( 'map' ) ) {
		map = new google.maps.Map( document.getElementById( 'map' ), options );
		bounds = new google.maps.LatLngBounds();
	}

	jQuery( '#search' ).click( function () {

		deleteMarkers();

		jQuery( "#results" ).empty();

		var location = jQuery( '#location' ).val();
		jQuery( "#options" ).hide();
		jQuery( "#options #links" ).empty();

		spin_start();

		processGeocoding( location, function ( results, selected_index ) {
			geocodes = results;
			if ( geocodes.length > 1 ) {
				spin_end();
				jQuery( "#options" ).show();

				for ( var i = 0; i < geocodes.length; i++ ) {
					jQuery( "<a/>" ).text( geocodes[i].formatted_address ).attr( "href", "#" ).addClass( 'option_link' ).attr( 'data-index', i ).appendTo( "#options #links" );
				}
				centerMap();

				jQuery( 'html, body' ).animate( {
					scrollTop:jQuery( "#options" ).offset().top
				}, 1000 );

			} else {
				processOption( geocodes[0] );
			}
		} );
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


	function processOption( geocode ) {

		deleteMarkers();
		centerMap();

		var data = {
			lat:geocode.geometry.location.lat(),
			lng:geocode.geometry.location.lng(),
			action:'geosearch',
			nonce:GeoLoc.nonce
		};

		jQuery.post( GeoLoc.ajaxurl, data, function ( response ) {

			spin_end();

			if ( response.success ) {

				jQuery( "#results" ).html( response.html );

				jQuery.each( response.markers, function ( i, e ) {
					addMarker( e.lat, e.lng, e.title, e.address, e.link );
				} );

				centerMap();

				jQuery( 'html, body' ).animate( {
					scrollTop:jQuery( "#results" ).offset().top
				}, 1000 );
			}

		} );

	}


	function addMarker( lat, lng, title, address, link ) {
		var myLatlng = new google.maps.LatLng( lat, lng );

		var marker = new google.maps.Marker( {
			position:myLatlng,
			map:map,
			title:title
		} );

		var infoWindow = new google.maps.InfoWindow();

		var content_title = title;
		if ( link ) {
			content_title = jQuery( '<div/>' ).append( jQuery( "<a/>" ).attr( 'href', link ).text( title ) ).html();
		}

		var content = "Event: " + content_title + "<br/>" + "Address: " + address;


		infoWindow.setContent( content );

		google.maps.event.addListener( marker, 'click', function ( event ) {
			infoWindow.open( map, marker );
		} );

		markersArray.push( marker );
		bounds.extend( myLatlng );

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

	var tribe_geoloc_auto_submit = false;
	jQuery( 'form#tribe-events-bar-form' ).bind( 'submit', function () {

		var val = jQuery( '#tribe-bar-geoloc' ).val();
		if ( val !== '' && !tribe_geoloc_auto_submit ) {
			processGeocoding( val, function ( results, selected_index ) {

				var lat = results[0].geometry.location.lat();
				var lng = results[0].geometry.location.lng();

				if ( lat )
					jQuery( '#tribe-bar-geoloc-lat' ).val( lat );

				if ( lng )
					jQuery( '#tribe-bar-geoloc-lng' ).val( lng );

				tribe_geoloc_auto_submit = true;
				jQuery( 'form#tribe-events-bar-form' ).submit();

			} );
			return false;
		}

		if ( val === '' ) {
			jQuery( '#tribe-bar-geoloc-lat' ).val( '' );
			jQuery( '#tribe-bar-geoloc-lng' ).val( '' );
		}

		return true;

	} );


} );


