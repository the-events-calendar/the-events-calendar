// tribe function to get specific query var from url
	
function tribe_get_url_param(tribe_param_name) {
	return decodeURIComponent((new RegExp('[?|&]' + tribe_param_name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null;
}

// tribe function to get all query vars from url

function tribe_get_url_params() {
	return location.search.substr(1);
}



// tribe shared ajax tests

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
				if( jQuery( "#tribe_events_filter_item_geofence" ).length ) {
					jQuery('#tribe_events_filter_item_geofence input').prop('checked', false);			
					jQuery( "#tribe_events_filter_item_geofence" ).hide().find('select').prop('selectedIndex',0);
				}
				tribe_ajax_callback();  
			}			
		}
	} else {
		
		if ( tribe_ajax_callback && typeof( tribe_ajax_callback ) === "function" ) {  
			tribe_ajax_callback();  
		}
	}
}

// tribe local storage

var tribe_storage, t_fail, t_uid;
try {
	t_uid = new Date;
	(tribe_storage = window.localStorage).setItem(t_uid, t_uid);
	t_fail = tribe_storage.getItem(t_uid) != t_uid;
	tribe_storage.removeItem(t_uid);
	t_fail && (tribe_storage = false);
} catch(e) {}

// tribe function for resetting forms

jQuery.fn.tribeClearForm = function() {
	return this.each(function() {
		var type = this.type, tag = this.tagName.toLowerCase();
		if (tag == 'form')
			return jQuery(':input',this).tribeClearForm();
		if (type == 'text' || type == 'password' || tag == 'textarea')
			this.value = '';
		else if (type == 'checkbox' || type == 'radio')
			this.checked = false;
		else if (tag == 'select')
			this.selectedIndex = -1;		
	});
};

// tribe global, sorry, we need them for some ping pong

tribe_ev = {};

tribe_ev.fn = {	
	get_params: function() {
		return location.search.substr(1);
	},
	get_url_param: function( tribe_param_name ) {
		return decodeURIComponent((new RegExp('[?|&]' + tribe_param_name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null;
	}, 	 
	parse_string: function( string ) {    
		var map   = {};
		string.replace(/([^&=]+)=?([^&]*)(?:&+|$)/g, function(match, key, value) {
			(map[key] = map[key] || []).push(value);
		});
		return map;
	},
	pre_ajax: function( tribe_ajax_callback ) {		
		
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
					if( jQuery( "#tribe_events_filter_item_geofence" ).length ) {
						jQuery('#tribe_events_filter_item_geofence input').prop('checked', false);			
						jQuery( "#tribe_events_filter_item_geofence" ).hide().find('select').prop('selectedIndex',0);
					}
					tribe_ajax_callback();  
				}			
			}
		} else {
			
			if ( tribe_ajax_callback && typeof( tribe_ajax_callback ) === "function" ) {  
				tribe_ajax_callback();  
			}
		}
	},
	snap: function( container, trigger_parent, trigger ) {		
		jQuery( trigger_parent ).on( 'click', trigger, function ( e ) {
			jQuery('html, body').animate( {scrollTop:jQuery( container ).offset().top - 120}, {duration: 0});
		});
	},
	tooltips: function() {
		
		jQuery( 'body' ).on( 'mouseenter', 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring',function () {
			
			var bottomPad = '';
			if ( jQuery( 'body' ).hasClass( 'tribe-events-week' ) ) {
				bottomPad = jQuery( this ).outerHeight() + 5;
			} else if ( jQuery( 'body' ).hasClass( 'events-gridview' ) ) { // Cal View Tooltips
				bottomPad = jQuery( this ).find( 'a' ).outerHeight() + 18;
			} else if ( jQuery( 'body' ).is( '.single-tribe_events, .events-list' ) ) { // Single/List View Recurring Tooltips
				bottomPad = jQuery( this ).outerHeight() + 12;
			}	
			
			// Widget Tooltips
			if ( jQuery( this ).parents( '.tribe-events-calendar-widget' ).length ) {
				bottomPad = jQuery( this ).outerHeight() - 6;
			}
			jQuery( this ).find( '.tribe-events-tooltip' ).css( 'bottom', bottomPad ).show();
			
		} ).on( 'mouseleave', 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', function () {
			jQuery( this ).find( '.tribe-events-tooltip' ).stop( true, false ).fadeOut( 200 );			
		} );
	},
	url_path: function( url ) {
		return url.split("?")[0];
	}	
}

tribe_ev.tests = {
	pushstate:!!(window.history && history.pushState)
}

tribe_ev.data = {
	cur_url:tribe_ev.fn.url_path( document.URL ),
	ajax_response:{}		
}

tribe_ev.state = {
	do_string:false,
	popping:false,
	pushstate:true,
	initial_load:false,
	paged:1
}

var tribe_has_pushstate = !!(window.history && history.pushState);
var tribe_do_string, tribe_popping, tribe_initial_load = false;
var tribe_pushstate = true;	
var tribe_push_counter = 0;
var tribe_href_target, tribe_date, tribe_daypicker_date, tribe_year_month, tribe_params, tribe_filter_params, tribe_url_params, tribe_hash_string, tribe_ajax_callback = '';
var tribe_ajax_response_object = {};

jQuery( document ).ready( function ( $ ) {	

	/* Let's hide the widget calendar if we find more than one instance */
	$(".tribe-events-calendar-widget").not(":eq(0)").hide();

	// Global Tooltips
	if ( $( '.tribe-events-calendar' ).length || $( '.tribe-events-grid' ).length || $( '.tribe-events-list' ).length || $( '.tribe-events-single' ).length || $( 'tribe-geo-wrapper' ).length ) {
		tribe_ev.fn.tooltips();
	}

	//remove border on list view event before month divider
	if (  $( '.tribe-events-list' ).length ) {
		$('.tribe_list_separator_month').prev('.vevent').addClass('tribe-event-end-month');
	}
} );
