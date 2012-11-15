var map, tribe_map_geocoder, geocodes, tribe_map_bounds, markersArray = [], spinner;
tribe_map_geocoder = new google.maps.Geocoder();
tribe_map_bounds = new google.maps.LatLngBounds();

var tribe_map_paged = 1;

jQuery( document ).ready( function ( $ ) {	

	$( '#tribe-geo-location' ).placeholder();
	
	if( GeoLoc.map_view && tribe_get_url_params() ) {
		tribe_do_string = false;
		tribe_pushstate = false;	
		tribe_popping = true;		
		tribe_map_processOption( null, '', tribe_pushstate, tribe_do_string, tribe_popping, tribe_get_url_params() );		
	}	
	
	if( tribe_has_pushstate && GeoLoc.map_view ) {

		// fix any browser that fires popstate on first load incorrectly

		var popped = ('state' in window.history), initialURL = location.href;

		$(window).bind('popstate', function(event) {

			var initialPop = !popped && location.href == initialURL;
			popped = true;

			// if it was an inital load, get out of here

			if ( initialPop ) return;

			// this really is popstate: fire the ajax, send the stored params from the browser, don't overwrite the history

			if( event.state ) {			
				tribe_do_string = false;
				tribe_pushstate = false;	
				tribe_popping = true;
				tribe_params = event.state.tribe_params;				
				tribe_map_processOption( null, '', tribe_pushstate, tribe_do_string, tribe_popping, tribe_params );
			}
		} );
	}
	
	var options = {
		zoom     :5,
		center   :new google.maps.LatLng( GeoLoc.center.max_lat, GeoLoc.center.max_lng ),
		mapTypeId:google.maps.MapTypeId.ROADMAP
	};

	if ( document.getElementById( 'tribe-geo-map' ) ) {
		map = new google.maps.Map( document.getElementById( 'tribe-geo-map' ), options );
		tribe_map_bounds = new google.maps.LatLngBounds();

		var minLatlng = new google.maps.LatLng( GeoLoc.center.min_lat, GeoLoc.center.min_lng );
		tribe_map_bounds.extend( minLatlng );

		var maxLatlng = new google.maps.LatLng( GeoLoc.center.max_lat, GeoLoc.center.max_lng );
		tribe_map_bounds.extend( maxLatlng );

		centerMap();
	}

	$( "#tribe-geo-options" ).on( 'click', 'a', function ( e ) {
		spin_start();
		e.preventDefault();
		$( "#tribe-geo-options a" ).removeClass( 'tribe-option-loaded' );
		$( this ).addClass( 'tribe-option-loaded' );

		$( '#tribe-bar-geoloc-lat' ).val( geocodes[$( this ).attr( 'data-index' )].geometry.location.lat() );
		$( '#tribe-bar-geoloc-lng' ).val( geocodes[$( this ).attr( 'data-index' )].geometry.location.lng() );

		tribe_map_processOption( null );


	} );

	function processGeocoding( location, callback ) {

		var request = {
			address:location
		};

		tribe_map_geocoder.geocode( request, function ( results, status ) {
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
	
	function tribe_generate_map_params() {
		tribe_params = {
			action:'geosearch',				
			paged :tribe_map_paged
		};


		// add any set values from event bar to params. want to use serialize but due to ie bug we are stuck with second	

		$( 'form#tribe-events-bar-form :input[value!=""]' ).each( function () {
			var $this = $( this );
			if( $this.val().length && $this.attr('name') != 'submit-bar' ) {
				tribe_params[$this.attr('name')] = $this.val();					
			}			
		} );

		tribe_params = $.param(tribe_params);

		// check if advanced filters plugin is active

		if( $('#tribe_events_filters_form').length ) {

			// serialize any set values and add to params

			tribe_filter_params = $('form#tribe_events_filters_form :input[value!=""]').serialize();				
			if( tribe_filter_params.length ) {
				tribe_params = tribe_params + '&' + tribe_filter_params;
			}
		}
		return tribe_params;
	}
	
	function tribe_reload_old_browser() {
		tribe_params = tribe_generate_map_params();
		tribe_href_target = tribe_cur_url + '?' + tribe_params;
		window.location = tribe_href_target;
	}


	function tribe_map_processOption( geocode, tribe_href_target, tribe_pushstate, tribe_do_string, tribe_popping, tribe_params ) {
		spin_start();
		deleteMarkers();
		
		if( !tribe_popping ) {
			tribe_params = tribe_generate_map_params();			
			tribe_pushstate = false;
			tribe_do_string = true;	
		}
		
		

			$.post( GeoLoc.ajaxurl, tribe_params, function ( response ) {

				spin_end();
				if ( response.success ) {

					$( "#tribe-geo-results" ).html( response.html );

					if ( response.max_pages > tribe_map_paged ) {
						$( 'a#tribe_map_paged_next' ).show();
					} else {
						$( 'a#tribe_map_paged_next' ).hide();
					}
					if ( tribe_map_paged > 1 ) {
						$( 'a#tribe_map_paged_prev' ).show();
					} else {
						$( 'a#tribe_map_paged_prev' ).hide();
					}

					$.each( response.markers, function ( i, e ) {
						tribe_map_addMarker( e.lat, e.lng, e.title, e.address, e.link );
					} );
					
					if( tribe_has_pushstate ) {

						if( tribe_do_string ) {							
							tribe_href_target = tribe_href_target + '?' + tribe_params;								
							history.pushState({							
								"tribe_params": tribe_params
							}, '', tribe_href_target);															
						}						

						if( tribe_pushstate ) {								
							history.pushState({							
								"tribe_params": tribe_params
							}, '', tribe_href_target);
						}
					
					}

					if ( response.markers.length > 0 ) {
						centerMap();
					}

				}

				spin_end();

			} );			
		
	}

	$( '.tribe-events-loop-nav' ).on( 'click', 'a#tribe_map_paged_next', function ( e ) {
		e.preventDefault();
		if( tribe_has_pushstate ) {			
			tribe_map_paged++;
			tribe_map_processOption( null, tribe_cur_url );
		} else {			
			tribe_reload_old_browser();	
		}
	} );

	$( '.tribe-events-loop-nav' ).on( 'click', 'a#tribe_map_paged_prev', function ( e ) {
		e.preventDefault();
		if( tribe_has_pushstate ) {			
			tribe_map_paged--;
			tribe_map_processOption( null, tribe_cur_url );
		} else {
			tribe_reload_old_browser();
		}
	} );


	if ( GeoLoc.map_view  && $( '#tribe_events_filters_form' ).length ) {
		$( 'form#tribe_events_filters_form' ).bind( 'submit', function ( e ) {
			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				if( tribe_has_pushstate ) {					
					tribe_map_processOption( null, tribe_cur_url );
				} else {
					tribe_reload_old_browser();
				}
			}
		} );
	}

	function tribe_map_addMarker( lat, lng, title, address, link ) {
		var myLatlng = new google.maps.LatLng( lat, lng );

		var marker = new google.maps.Marker( {
			position:myLatlng,
			map     :map,
			title   :title
		} );

		var infoWindow = new google.maps.InfoWindow();

		var content_title = title;
		if ( link ) {
			content_title = $( '<div/>' ).append( $( "<a/>" ).attr( 'href', link ).text( title ) ).html();
		}

		var content = "Event: " + content_title;

		if ( address ) {
			content = content + "<br/>" + "Address: " + address;
		}

		infoWindow.setContent( content );

		google.maps.event.addListener( marker, 'click', function ( event ) {
			infoWindow.open( map, marker );
		} );

		markersArray.push( marker );
		tribe_map_bounds.extend( myLatlng );

	}

	function deleteMarkers() {
		if ( markersArray ) {
			for ( i in markersArray ) {
				markersArray[i].setMap( null );
			}
			markersArray.length = 0;
			tribe_map_bounds = new google.maps.LatLngBounds();
		}
	}

	function centerMap() {

		map.fitBounds( tribe_map_bounds );
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
		tribe_map_paged = 1;
		spin_start();

		var val = $( '#tribe-bar-geoloc' ).val();

		if ( val !== '' && !tribe_geoloc_auto_submit ) {

			if ( GeoLoc.map_view ) {
				deleteMarkers();
				$( "#tribe-geo-results" ).empty();
				$( "#tribe-geo-options" ).hide();
				$( "#tribe-geo-options #tribe-geo-links" ).empty();
			}

			processGeocoding( val, function ( results, selected_index ) {
				geocodes = results;
				// We're not in the map view. Let's submit.
				spin_end();

				var lat = results[0].geometry.location.lat();
				var lng = results[0].geometry.location.lng();

				if ( lat )
					$( '#tribe-bar-geoloc-lat' ).val( lat );

				if ( lng )
					$( '#tribe-bar-geoloc-lng' ).val( lng );

				if ( !GeoLoc.map_view || tribe_events_bar_action == 'change_view' ) {

					tribe_geoloc_auto_submit = true;
					$( 'form#tribe-events-bar-form' ).submit();

				} else { // We're in the map view. Let's ajaxify the form.
					if ( geocodes.length > 1 ) {
						$( "#tribe-geo-options" ).show();

						for ( var i = 0; i < geocodes.length; i++ ) {
							$( "<a/>" ).text( geocodes[i].formatted_address ).attr( "href", "#" ).addClass( 'tribe-geo-option-link' ).attr( 'data-index', i ).appendTo( "#tribe-geo-options #tribe-geo-links" );
							tribe_map_addMarker( geocodes[i].geometry.location.lat(), geocodes[i].geometry.location.lng(), geocodes[i].formatted_address );
						}
						centerMap();


					} else {
						tribe_map_processOption( geocodes[0], tribe_cur_url );
					}
				}
			} );

			return false;
		}

		if ( val === '' ) {
			$( '#tribe-bar-geoloc-lat' ).val( '' );
			$( '#tribe-bar-geoloc-lng' ).val( '' );

			if ( GeoLoc.map_view && tribe_events_bar_action != 'change_view' ) {
				//We can show the map even if we don't get a geo query
				tribe_map_processOption( null, tribe_cur_url );
				spin_end();
				return false;
			}
		}
		return true;

	} );

} );