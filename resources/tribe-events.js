function tribe_get_path( url ) {
	return url.split("?")[0];
}
	
function tribe_get_url_param(name) {
	return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null;
}

function tribe_event_tooltips() {
	jQuery( '.tribe-events-calendar, .tribe-events-grid, .tribe-events-list, .tribe-events-single' ).delegate( 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', 'mouseenter',function () {
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

jQuery( document ).ready( function ( $ ) {

	// Global Tooltips
	if ( $( '.tribe-events-calendar' ).length || $( '.tribe-events-grid' ).length || $( '.tribe-events-list' ).length || $( '.tribe-events-single' ).length ) {
		tribe_event_tooltips();
	}

} );