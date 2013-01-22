// tribe local storage

var tribe_storage, t_fail, t_uid;
try {
	t_uid = new Date;
	(tribe_storage = window.localStorage).setItem(t_uid, t_uid);
	t_fail = tribe_storage.getItem(t_uid) != t_uid;
	tribe_storage.removeItem(t_uid);
	t_fail && (tribe_storage = false);
} catch(e) {}

// live ajax timer 

var tribe_ajax_timer;

// jquery functions

jQuery.fn.tribe_clear_form = function() {
	return this.each(function() {
		var type = this.type, tag = this.tagName.toLowerCase();
		if (tag == 'form')
			return jQuery(':input',this).tribe_clear_form();
		if (type == 'text' || type == 'password' || tag == 'textarea')
			this.value = '';
		else if (type == 'checkbox' || type == 'radio')
			this.checked = false;
		else if (tag == 'select')
			this.selectedIndex = 0;		
	});
};

// tribe global, we need them for some ping pong

tribe_ev = {};

tribe_ev.fn = {	
	disable_inputs: function( parent, type ) {
		jQuery( parent ).find( type ).prop('disabled', true);
		if( jQuery( parent ).find('.select2-container').length ) {			
			jQuery( parent ).find('.select2-container').each( function() {
				var s2_id = jQuery(this).attr('id');
				var $this = jQuery('#' + s2_id);
				$this.select2("disable");
			});			
		}
	},
	disable_empty: function( parent, type ) {
		jQuery( parent ).find( type ).each(function(){ 
			if ( jQuery(this).val() === '' ){ 
				jQuery(this).prop('disabled', true); 
			} 
		});			
	},
	enable_inputs: function( parent, type ) {		
		jQuery( parent ).find( type ).prop('disabled', false);		
		if( jQuery( parent ).find('.select2-container').length ) {			
			jQuery( parent ).find('.select2-container').each( function() {
				var s2_id = jQuery(this).attr('id');
				var $this = jQuery('#' + s2_id);
				$this.select2("enable");
			});			
		}
	},
	get_day: function() {
		var dp_day = '';
		if( jQuery('#tribe-bar-date').length ) {
			var dp_date = jQuery('#tribe-bar-date').val();
			if( dp_date.length )
				dp_day = dp_date.slice(-3);
		}
		return dp_day;
	},	
	get_params: function() {
		return location.search.substr(1);
	},
	get_url_param: function( tribe_param_name ) {
		return decodeURIComponent((new RegExp('[?|&]' + tribe_param_name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null;
	}, 
	hide_settings: function(){
		jQuery( '#tribe-events-bar [class^="tribe-bar-button-"]' )
			.removeClass( 'open' )
			.next( '.tribe-bar-drop-content' )
			.hide();
	},
	in_params: function( params, term ) {
		return params.toLowerCase().indexOf( term );
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
	set_form: function( params ){
		jQuery('body').addClass('tribe-reset-on');
		
		var has_sliders = false;
		var has_select2 = false;
		
		if( jQuery('#tribe_events_filters_form').length ) {
			var $form = jQuery('#tribe_events_filters_form');			

			$form.tribe_clear_form();

			if( $form.find('.select2-container').length ) {
				
				has_select2 = true;
				
				jQuery( '#tribe_events_filters_form .select2-container' ).select2("val", {});				
			}

			if( $form.find('.ui-slider').length ) {
				
				has_sliders = true;
				
				jQuery( '#tribe_events_filters_form .ui-slider' ).each( function() {
					var s_id = jQuery(this).attr('id');
					var $this = jQuery('#' + s_id);
					var $input = $this.prev();
					var $display = $input.prev();
					var settings = $this.slider( "option" );

					$this.slider("values", 0, settings.min);
					$this.slider("values", 1, settings.max);
					$display.text( settings.min + " - " + settings.max ); 
					$input.val('');				
				});
			}			
		}
		
		if( jQuery('#tribe-bar-form').length ) {			
			jQuery('#tribe-bar-form').tribe_clear_form();
		}
		
		params = tribe_ev.fn.parse_string( params );			
		
		jQuery.each( params, function( key,value ) {
			if( key !== 'action' ) {	
				var name = decodeURI( key );	
				var $target = '';
				if( value.length === 1 ) {						
					if ( jQuery('[name="' + name + '"]').is('input[type="text"], input[type="hidden"]') ) {
						jQuery('[name="' + name + '"]').val( value );	
					} else if( jQuery('[name="' + name + '"][value="' + value + '"]').is(':checkbox, :radio')) {
						jQuery('[name="' + name + '"][value="' + value + '"]').prop("checked", true);	
					} else if( jQuery('[name="' + name + '"]').is('select') ) {
						jQuery('select[name="' + name + '"] option[value="' + value + '"]').attr('selected',true);						
					}										
				} else {										
					for ( var i = 0; i < value.length; i++ ) {						
						$target = jQuery('[name="' + name + '"][value="' + value[i] + '"]');						
						if ( $target.is(':checkbox, :radio') ) {							
							$target.prop("checked", true);							
						} else {
							jQuery('select[name="' + name + '"] option[value="' + value[i] + '"]').attr('selected',true);							
						}						
					}
				}						
			}					
		});
	
		if( has_sliders ) {			
			jQuery( '#tribe_events_filters_form .ui-slider' ).each( function() {
				var s_id = jQuery(this).attr('id');
				var $this = jQuery('#' + s_id);
				var $input = $this.prev();				
				var range = $input.val().split('-');
				
				if( range[0] !== '' ) {									
					var $display = $input.prev();
					
					$this.slider("values", 0, range[0]);
					$this.slider("values", 1, range[1]);
					$display.text( range[0] + " - " + range[1] ); 				
					$this.slider('refresh');
				}				
			});
		}
		
		if( has_select2 ) {			
			jQuery( '#tribe_events_filters_form .select2-container' ).each( function() {
				var s2_id = jQuery(this).attr('id');
				var $this = jQuery('#' + s2_id);
				$this.next().trigger("change");
			});			
		}
				
		jQuery('body').removeClass('tribe-reset-on');
	},
	setup_ajax_timer: function( callback ) {
		clearTimeout( tribe_ajax_timer );
		if( !tribe_ev.tests.reset_on() ){					
			tribe_ajax_timer = setTimeout(function(){  
				callback(); 
			}, 500);
		}
	},
	snap: function( container, trigger_parent, trigger ) {		
		jQuery( trigger_parent ).on( 'click', trigger, function ( e ) {
			jQuery('html, body').animate( {scrollTop:jQuery( container ).offset().top - 120}, {duration: 0});
		});
	},
	spin_hide: function() {
		jQuery( '#tribe-events-footer, #tribe-events-header' ).find('.tribe-ajax-loading').hide();
	},
	spin_show: function() {
		jQuery( '#tribe-events-footer, #tribe-events-header' ).find('.tribe-ajax-loading').show();
	},
	tooltips: function() {
		
		jQuery( 'body' ).on( 'mouseenter', 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring',function () {
			
			var bottomPad = '';
			if ( jQuery( 'body' ).hasClass( 'tribe-events-week' ) ) {				
				
				var $this = jQuery( this );	
				var $wrapper = jQuery('.tribe-week-grid-wrapper');
				var $tip = $this.find( '.tribe-events-tooltip' );
				var $parent = $this.parent();
				var $container = $parent.parent();
				
				var pwidth = Math.ceil($container.width());
				var cwidth = Math.ceil($this.width());
				var twidth = Math.ceil($tip.outerWidth());				
				var gheight = $wrapper.height();
				
				var scroll = $wrapper.scrollTop();
				var coffset = $parent.position();	
				var poffset = $this.position();				
				var ptop = Math.ceil(poffset.top);
				var toffset = scroll - ptop;
				
				var wcheck;
				var theight;
				var available;
				
				bottomPad = $this.outerHeight() + 12;								
				
				if( $this.parents('.tribe-grid-allday').length ) {
					$tip.css( 'bottom', bottomPad ).show();					
				} else {
					if( $parent.hasClass('tribe-events-right') ){	
						if( !$tip.hasClass('hovered') ) {
							$tip.attr('data-ow', twidth).addClass('hovered');							
						}
						wcheck = Math.ceil(coffset.left) - 20;
						if( twidth >= wcheck ) 
							twidth = wcheck;
						else if( $tip.attr('data-ow') > wcheck )
							twidth = wcheck;
						else
							twidth = $tip.attr('data-ow');
						
						$tip.addClass('.tribe-tooltip-right').css( {'right':cwidth + 20, 'bottom':'auto', 'width': twidth + 'px'} );
						
						theight = $tip.height();
						
						if (toffset >= 0) {
							toffset = toffset + 5;
						} else {
							available = toffset + gheight;
							if( theight > available )
								toffset = available - theight - 8;
							else 
								toffset = 5;
						}	
						
						$tip.css("top", toffset).show();
					} else {
						if( !$tip.hasClass('hovered') ) {
							$tip.attr('data-ow', twidth).addClass('hovered');							
						}
						wcheck = pwidth - cwidth - Math.ceil(coffset.left);
						if( twidth >= wcheck ) 
							twidth = wcheck;
						else if( $tip.attr('data-ow') > wcheck )
							twidth = wcheck;
						else
							twidth = $tip.attr('data-ow');
						
						$tip.addClass('.tribe-tooltip-left').css( {'left':cwidth + 20, 'bottom':'auto', 'width': twidth + 'px'} ).show().hide();
						
						theight = $tip.height();
						
						if (toffset >= 0) {
							toffset = toffset + 5;
						} else {
							available = toffset + gheight;
							if( theight > available )
								toffset = available - theight - 8;
							else 
								toffset = 5;
						}	
						
						$tip.css("top", toffset).show();
					}
				}
								
			} else if ( jQuery( 'body' ).hasClass( 'events-gridview' ) ) { // Cal View Tooltips
				bottomPad = jQuery( this ).find( 'a' ).outerHeight() + 18;
			} else if ( jQuery( 'body' ).is( '.single-tribe_events, .events-list' ) ) { // Single/List View Recurring Tooltips
				bottomPad = jQuery( this ).outerHeight() + 12;
			}	
			
			// Widget Tooltips
			if ( jQuery( this ).parents( '.tribe-events-calendar-widget' ).length ) {
				bottomPad = jQuery( this ).outerHeight() - 6;
			}
			if ( !jQuery( 'body' ).hasClass( 'tribe-events-week' ) ) {
				jQuery( this ).find( '.tribe-events-tooltip' ).css( 'bottom', bottomPad ).show();
			}
			
		} ).on( 'mouseleave', 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', function () {
			jQuery( this ).find( '.tribe-events-tooltip' ).stop( true, false ).fadeOut( 200 );			
		} );
	},
	update_picker: function( date ){
		if( jQuery().datepicker && jQuery("#tribe-bar-date").length )
			jQuery("#tribe-bar-date").datepicker("setDate", date );
		else if( jQuery("#tribe-bar-date").length )
			jQuery("#tribe-bar-date").val( date );
	},
	url_path: function( url ) {
		return url.split("?")[0];
	}	
}

tribe_ev.tests = {	
	live_ajax: function() {
		if( jQuery('body').hasClass('tribe-filter-live') )
			return true;
		else 
			return false;
	},
	map_view: function(){
		if( typeof GeoLoc !== 'undefined' && GeoLoc.map_view ) 
			return true;		
		else
			return false;		
	},
	pushstate:!!(window.history && history.pushState),
	reset_on: function(){
		if( jQuery('body').hasClass('tribe-reset-on') )
			return true;
		else
			return false;
	}
}

tribe_ev.data = {
	ajax_response:{},
	cur_url:tribe_ev.fn.url_path( document.URL ),
	initial_url:tribe_ev.fn.url_path( document.URL ),
	params:tribe_ev.fn.get_params()		
}

tribe_ev.state = {
	ajax_running:false,
	date:'',
	do_string:false,
	initial_load:true,
	paged:1,
	params:{},
	popping:false,
	pushstate:true,
	pushcount:0,
	url_params:{}	
}

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
