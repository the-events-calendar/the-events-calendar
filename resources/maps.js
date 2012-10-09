var map, geocoder, geocodes, bounds, markersArray = [], spinner;
geocoder = new google.maps.Geocoder();
bounds = new google.maps.LatLngBounds();

jQuery( document ).ready( function ($) {
	
        $( '#tribe-geo-location' ).placeholder();
	
	var options = {
		zoom:5,
		center:new google.maps.LatLng( 36.77, -119.41 ),
		mapTypeId:google.maps.MapTypeId.ROADMAP
	};

	if ( document.getElementById( 'tribe-geo-map' ) ) {
		map = new google.maps.Map( document.getElementById( 'tribe-geo-map' ), options );
		bounds = new google.maps.LatLngBounds();
	}

	$( '#tribe-geo-search' ).click( function () {

		deleteMarkers();

		$( "#tribe-geo-results" ).empty();

		var location = $( '#tribe-geo-location' ).val();
		$( "#tribe-geo-options" ).hide();
		$( "#tribe-geo-options #tribe-geo-links" ).empty();

		spin_start();

		processGeocoding( location, function ( results, selected_index ) {
			geocodes = results;
			if ( geocodes.length > 1 ) {
				spin_end();
				$( "#tribe-geo-options" ).show();

				for ( var i = 0; i < geocodes.length; i++ ) {
					$( "<a/>" ).text( geocodes[i].formatted_address ).attr( "href", "#" ).addClass( 'tribe-geo-option-link' ).attr( 'data-index', i ).appendTo( "#tribe-geo-options #tribe-geo-links" );
				}
				centerMap();

				$( 'html, body' ).animate( {
					scrollTop:$( "#tribe-geo-options" ).offset().top
				}, 1000 );

			} else {
				processOption( geocodes[0] );
			}
		} );
		return false;

	} );

	$( "#tribe-geo-options" ).on( 'click', 'a', function (e) {
		spin_start();
		e.preventDefault();
		$( "#tribe-geo-options a" ).removeClass( 'tribe-option-loaded' );
		$( this ).addClass( 'tribe-option-loaded' );
		processOption( geocodes[$( this ).attr( 'data-index' )] );
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

		$.post( GeoLoc.ajaxurl, data, function ( response ) {

			spin_end();

			if ( response.success ) {

				$( "#tribe-geo-results" ).html( response.html );

				$.each( response.markers, function ( i, e ) {
					addMarker( e.lat, e.lng, e.title, e.address, e.link );
					
				} );

				centerMap();

				$( 'html, body' ).animate( {
					scrollTop:$( "#tribe-geo-results" ).offset().top
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
			content_title = $( '<div/>' ).append( $( "<a/>" ).attr( 'href', link ).text( title ) ).html();
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
		$( "#tribe-geo-loading" ).show();

	}

	function spin_end() {
		$( "#tribe-geo-loading" ).hide();
	}

	var tribe_geoloc_auto_submit = false;
	$( 'form#tribe-events-bar-form' ).bind( 'submit', function () {

		var val = $( '#tribe-bar-geoloc' ).val();
		if ( val !== '' && !tribe_geoloc_auto_submit ) {
			processGeocoding( val, function ( results, selected_index ) {

				var lat = results[0].geometry.location.lat();
				var lng = results[0].geometry.location.lng();

				if ( lat )
					$( '#tribe-bar-geoloc-lat' ).val( lat );

				if ( lng )
					$( '#tribe-bar-geoloc-lng' ).val( lng );

				tribe_geoloc_auto_submit = true;
				$( 'form#tribe-events-bar-form' ).submit();

			} );
			return false;
		}

		if ( val === '' ) {
			$( '#tribe-bar-geoloc-lat' ).val( '' );
			$( '#tribe-bar-geoloc-lng' ).val( '' );
		}

		return true;

	} );

} );