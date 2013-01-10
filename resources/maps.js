var map, tribe_map_geocoder, geocodes, tribe_map_bounds, markersArray = [], spinner;
tribe_map_geocoder = new google.maps.Geocoder();
tribe_map_bounds = new google.maps.LatLngBounds();

var tribe_map_paged = 1;

function tribe_process_geocoding( location, callback ) {

	var request = {
		address:location
	};

	tribe_map_geocoder.geocode( request, function ( results, status ) {
		if ( status == google.maps.GeocoderStatus.OK ) {
			callback( results );
			return results;
		}

		if ( status == google.maps.GeocoderStatus.ZERO_RESULTS ) {
			if( GeoLoc.map_view ) {
				spin_end();
			}
			return status;
		}

		return status;
	} );
}

jQuery( document ).ready( function ( $ ) {
	
	function tribe_test_location() {
		
		if( $( '#tribe-bar-geoloc' ).length ) {
			var tribe_map_val = $( '#tribe-bar-geoloc' ).val();
			if( tribe_map_val.length ) {
				if( $( "#tribe_events_filter_item_geofence" ).length )
					$( "#tribe_events_filter_item_geofence" ).show();
			} else {
				if( $( "#tribe_events_filter_item_geofence" ).length ) 
					$( "#tribe_events_filter_item_geofence" ).hide();
				if( $( '#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng' ).length )
					$( '#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng' ).val( '' );		
			}
		}
	}

	tribe_test_location();	

	$( '#tribe-geo-location' ).placeholder();	
	
	if( tribe_has_pushstate && GeoLoc.map_view ) {
		
		var initial_url = location.href;
		
		if( tribe_storage )
			tribe_storage.setItem( 'tribe_initial_load', 'true' );	

		$(window).bind('popstate', function(event) {

			var initial_load = '';
			
			if( tribe_storage )
				initial_load = tribe_storage.getItem( 'tribe_initial_load' );	
			
			var state = event.originalEvent.state;

			if( state ) {			
				tribe_do_string = false;
				tribe_pushstate = false;	
				tribe_popping = true;
				tribe_params = state.tribe_params;
				tribe_pre_ajax_tests( function() { 				
					tribe_map_processOption( null, '', tribe_pushstate, tribe_do_string, tribe_popping, tribe_params );
				});
			} else if( tribe_storage && initial_load !== 'true' ){				
				window.location = initial_url;
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
	
	if( GeoLoc.map_view ) {
		
		var tribe_is_paged = tribe_get_url_param('tribe_paged');
		if( tribe_is_paged ) {
			tribe_map_paged = tribe_is_paged;
		}		
		$( 'body' ).addClass( 'events-list' );
		tribe_ev.fn.tooltips();
	}
	
	
	
	if( GeoLoc.map_view && tribe_get_url_params() ) {
		
		var tribe_in_params = tribe_get_url_params();
		if ( tribe_in_params.toLowerCase().indexOf("geosearch") >= 0 ) {} else
			tribe_in_params += '&action=geosearch';
		if ( tribe_in_params.toLowerCase().indexOf("tribe_paged") >= 0 ) {} else
			tribe_in_params += '&tribe_paged=1';
					
		tribe_do_string = false;
		tribe_pushstate = false;	
		tribe_popping = true;	
		tribe_pre_ajax_tests( function() { 
			tribe_map_processOption( null, '', tribe_pushstate, tribe_do_string, tribe_popping, tribe_in_params );	
		});
	} else if( GeoLoc.map_view ){
		
		tribe_do_string = false;
		tribe_pushstate = false;	
		tribe_popping = false;
		tribe_initial_load = true;
		tribe_pre_ajax_tests( function() { 
			tribe_map_processOption( null, '', tribe_pushstate, tribe_do_string, tribe_popping, '', tribe_initial_load );			
		});
	}

	$( "#tribe-geo-options" ).on( 'click', 'a', function ( e ) {
		spin_start();
		e.preventDefault();
		$( "#tribe-geo-options a" ).removeClass( 'tribe-option-loaded' );
		$( this ).addClass( 'tribe-option-loaded' );
		
		$( '#tribe-bar-geoloc' ).val( $( this ).text() );
		$( '#tribe-bar-geoloc-lat' ).val( geocodes[$( this ).attr( 'data-index' )].geometry.location.lat() );
		$( '#tribe-bar-geoloc-lng' ).val( geocodes[$( this ).attr( 'data-index' )].geometry.location.lng() );		
		
		
		if( tribe_has_pushstate ) {
			tribe_pre_ajax_tests( function() { 			
				tribe_map_processOption( null, '' );
				$( "#tribe-geo-options" ).hide();
			});
		} else {			
			tribe_pre_ajax_tests( function() { 
				tribe_reload_old_browser();
			});
		}

	} );
	
	tribe_ev.fn.snap( '#tribe-geo-wrapper', '#tribe-geo-wrapper', '#tribe-events-footer .tribe-nav-previous a, #tribe-events-footer .tribe-nav-next a' );
		
	function tribe_generate_map_params() {
		tribe_params = {
			action:'geosearch',				
			tribe_paged :tribe_map_paged
		};


		// add any set values from event bar to params. want to use serialize but due to ie bug we are stuck with second	

		$( 'form#tribe-bar-form :input[value!=""]' ).each( function () {
			var $this = $( this );
			if( $this.val().length && !$this.hasClass('tribe-no-param') ) {
				if( $this.is(':checkbox') ) {
					if( $this.is(':checked') ) {
						tribe_params[$this.attr('name')] = $this.val();	
					}
				} else {
					tribe_params[$this.attr('name')] = $this.val();	
				}					
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


	function tribe_map_processOption( geocode, tribe_href_target, tribe_pushstate, tribe_do_string, tribe_popping, tribe_params, tribe_initial_load ) {
		spin_start();
		deleteMarkers();
		
		if( !tribe_popping ) {
			tribe_params = tribe_generate_map_params();			
			tribe_pushstate = false;
			if( !tribe_initial_load ) {
				tribe_do_string = true;	
			} 			
		}	

			$.post( GeoLoc.ajaxurl, tribe_params, function ( response ) {

				spin_end();
				if ( response.success ) {
					
					if( tribe_storage ) {
						tribe_storage.setItem( 'tribe_initial_load', 'false' );
						tribe_storage.setItem( 'tribe_current_post_count', response.total_count );
					}	
					
					tribe_ev.data.ajax_response = {
						'type':'tribe_events_ajax',
						'post_count':parseInt(response.total_count),
						'view':'map',
						'max_pages':response.max_pages,
						'page':tribe_map_paged,
						'timestamp':new Date().getTime()
					};

					$( "#tribe-geo-results" ).html( response.html );					
					$( "#tribe-events-content" ).parent().removeAttr('id').find('.tribe-events-page-title').remove();	
					$( "#tribe-geo-results #tribe-events-header, #tribe-geo-results #tribe-events-footer" ).remove();	

					if ( response.max_pages > tribe_map_paged ) {
						$( 'li.tribe-nav-next a' ).show();
					} else {
						$( 'li.tribe-nav-next a' ).hide();
					}
					if ( tribe_map_paged > 1 ) {
						$( 'li.tribe-nav-previous a' ).show();
					} else {
						$( 'li.tribe-nav-previous a' ).hide();
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
	
	if ( GeoLoc.map_view ) {
		
		$( '#tribe-geo-wrapper' ).on( 'click', 'li.tribe-nav-next a', function ( e ) {
			e.preventDefault();
			tribe_map_paged++;
			if( tribe_has_pushstate ) {
				tribe_pre_ajax_tests( function() { 			
					tribe_map_processOption( null, tribe_cur_url );
				});
			} else {			
				tribe_pre_ajax_tests( function() { 
					tribe_reload_old_browser();
				});
			}
		} );

		$( '#tribe-geo-wrapper' ).on( 'click', 'li.tribe-nav-previous a', function ( e ) {
			e.preventDefault();
			tribe_map_paged--;
			if( tribe_has_pushstate ) {			
				tribe_pre_ajax_tests( function() { 			
					tribe_map_processOption( null, tribe_cur_url );
				});
			} else {
				tribe_pre_ajax_tests( function() { 
					tribe_reload_old_browser();
				});
			}
		} );
		
	}
	
	function tribe_events_bar_mapajax_actions(e) {
		if ( tribe_events_bar_action != 'change_view' ) {
			e.preventDefault();
			tribe_map_paged = 1;
			if( tribe_has_pushstate ) {	
				tribe_pre_ajax_tests( function() { 						
					tribe_map_processOption( null, tribe_cur_url );
				});
			} else {
				tribe_pre_ajax_tests( function() { 						
					tribe_reload_old_browser();
				});
			}

		}
	}

	if ( GeoLoc.map_view  && $( 'form#tribe-bar-form' ).length ) {
		
		
//		$( 'form#tribe-bar-form' ).bind( 'submit', function ( e ) {
//			tribe_events_bar_mapajax_actions(e);	
//		} );
//		
		$( '.tribe-bar-settings button[name="settingsUpdate"]' ).bind( 'click', function (e) {		
			tribe_events_bar_mapajax_actions(e);
			$( '#tribe-events-bar [class^="tribe-bar-button-"]' )
				.removeClass( 'open' )
				.next( '.tribe-bar-drop-content' )
				.hide();
		} );		
	}
	
	if( GeoLoc.map_view  && $('#tribe_events_filters_form').length ) {
		$( 'form#tribe_events_filters_form' ).bind( 'submit', function ( e ) {			
			tribe_events_bar_mapajax_actions(e);			
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
		$( '#tribe-events-footer, #tribe-events-header' ).find('.tribe-ajax-loading').show();
	}

	function spin_end() {
		$( '#tribe-events-footer, #tribe-events-header' ).find('.tribe-ajax-loading').hide();
	}
	if ( GeoLoc.map_view ) {
	
		$( 'form#tribe-bar-form' ).bind( 'submit', function () {	
			if ( tribe_events_bar_action != 'change_view' ) {				
				tribe_map_paged = 1;
				spin_start();

				var val = $( '#tribe-bar-geoloc' ).val();

				if ( val !== '' ) {

					deleteMarkers();
					$( "#tribe-geo-results" ).empty();
					$( "#tribe-geo-options" ).hide();
					$( "#tribe-geo-options #tribe-geo-links" ).empty();			

					tribe_process_geocoding( val, function ( results, selected_index ) {
						geocodes = results;

						spin_end();

						var lat = results[0].geometry.location.lat();
						var lng = results[0].geometry.location.lng();

						if ( lat )
							$( '#tribe-bar-geoloc-lat' ).val( lat );

						if ( lng )
							$( '#tribe-bar-geoloc-lng' ).val( lng );

						if ( geocodes.length > 1 ) {
							$( "#tribe-geo-options" ).show();

							for ( var i = 0; i < geocodes.length; i++ ) {
								$( "<a/>" ).text( geocodes[i].formatted_address ).attr( "href", "#" ).addClass( 'tribe-geo-option-link' ).attr( 'data-index', i ).appendTo( "#tribe-geo-options #tribe-geo-links" );
								tribe_map_addMarker( geocodes[i].geometry.location.lat(), geocodes[i].geometry.location.lng(), geocodes[i].formatted_address );
							}
							tribe_test_location();	
							centerMap();


						} else {
							if( tribe_has_pushstate ) {	
								tribe_test_location();	
								tribe_map_processOption( geocodes[0], tribe_cur_url );
							} else {								
								tribe_reload_old_browser();
							}						
						}

					} );

					return false;
				}

				if ( val === '' ) {
					$( '#tribe-bar-geoloc-lat' ).val( '' );
					$( '#tribe-bar-geoloc-lng' ).val( '' );
					$("#tribe-geo-options").hide();
					//We can show the map even if we don't get a geo query
					if( tribe_has_pushstate ) {	
						tribe_test_location();	
						tribe_map_processOption( null, tribe_cur_url );
					} else {
						tribe_reload_old_browser();
					}	
					spin_end();
					return false;
					
				}
				return true;
			}
		} );
	}

} );