function tribe_get_path( url ) {
	return url.split("?")[0];
}
	
function tribe_get_url_param(tribe_param_name) {
	return decodeURIComponent((new RegExp('[?|&]' + tribe_param_name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null;
}

function tribe_get_url_params() {
	return location.search.substr(1);
}

function tribe_pre_ajax_tests( tribe_ajax_callback ) {		
	
	if( jQuery( '#tribe-bar-geoloc' ).length ) {			
		var tribe_map_val = jQuery( '#tribe-bar-geoloc' ).val();		
		if( tribe_map_val.length ) {	
			tribe_process_geocoding( tribe_map_val, function ( tribe_geoloc_results ) {

				var tribe_geoloc_lat = tribe_geoloc_results[0].geometry.location.lat();
				var tribe_geoloc_lng = tribe_geoloc_results[0].geometry.location.lng();
				if ( tribe_geoloc_lat )
					jQuery( '#tribe-bar-geoloc-lat' ).val( tribe_geoloc_lat );

				if ( tribe_geoloc_lng )
					jQuery( '#tribe-bar-geoloc-lng' ).val( tribe_geoloc_lng );
				
				if ( tribe_ajax_callback && typeof( tribe_ajax_callback ) === "function" ) {  
					if( jQuery( "#tribe_events_filter_item_geofence" ).length )
						jQuery( "#tribe_events_filter_item_geofence" ).show();
					tribe_ajax_callback();  
				}				
			});
		} else {			
			jQuery( '#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng' ).val( '' );			
			if ( tribe_ajax_callback && typeof( tribe_ajax_callback ) === "function" ) { 
				if( jQuery( "#tribe_events_filter_item_geofence" ).length )
					jQuery( "#tribe_events_filter_item_geofence" ).hide();
				tribe_ajax_callback();  
			}			
		}
	} else {
		
		if ( tribe_ajax_callback && typeof( tribe_ajax_callback ) === "function" ) {  
			tribe_ajax_callback();  
		}
	}
}

function tribe_event_tooltips() {
	jQuery( 'body' ).delegate( 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', 'mouseenter',function () {
		// Week View Tooltips
		if ( jQuery( 'body' ).hasClass( 'tribe-events-week' ) ) {
			var bottomPad = jQuery( this ).outerHeight() + 5;
		} else if ( jQuery( 'body' ).hasClass( 'events-gridview' ) ) { // Cal View Tooltips
			var bottomPad = jQuery( this ).find( 'a' ).outerHeight() + 18;
		} else if ( jQuery( 'body' ).is( '.single-tribe_events, .events-list' ) ) { // Single/List View Recurring Tooltips
			var bottomPad = jQuery( this ).outerHeight() + 12;
		}	
		
		// Widget Tooltips
		if ( jQuery( this ).parents( '.tribe-events-calendar-widget' ).length ) {
			var bottomPad = jQuery( this ).outerHeight() - 6;
		}
		jQuery( this ).find( '.tribe-events-tooltip' ).css( 'bottom', bottomPad ).show();
	} ).delegate( 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', 'mouseleave', function () {
			if ( jQuery.browser.msie && jQuery.browser.version <= 9 ) {
				jQuery( this ).find( '.tribe-events-tooltip' ).hide()
			} else {
				jQuery( this ).find( '.tribe-events-tooltip' ).stop( true, false ).fadeOut( 200 );
			}
		} );
}

var tribe_has_pushstate = window.history && window.history.pushState && !navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]|WebApps\/.+CFNetwork)/);
var tribe_cur_url = tribe_get_path( jQuery( location ).attr( 'href' ) );
var tribe_do_string, tribe_popping, tribe_initial_load = false;
var tribe_pushstate = true;	
var tribe_push_counter = 0;
var tribe_href_target, tribe_date, tribe_daypicker_date, tribe_year_month, tribe_params, tribe_filter_params, tribe_url_params, tribe_hash_string, tribe_ajax_callback = '';

jQuery( document ).ready( function ( $ ) {	

	// Global Tooltips
	if ( $( '.tribe-events-calendar' ).length || $( '.tribe-events-grid' ).length || $( '.tribe-events-list' ).length || $( '.tribe-events-single' ).length || $( 'tribe-geo-wrapper' ).length ) {
		tribe_event_tooltips();
	}

} );