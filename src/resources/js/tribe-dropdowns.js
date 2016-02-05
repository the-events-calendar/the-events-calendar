(function( $, my ) {
	'use strict';

	my.opts = {
		venue: {
			ajax: {
				url: typeof ajaxurl !== 'undefined' ? ajaxurl : tribe_dropdowns.ajaxurl,
				dataType: 'json',
				quietMillis: 250,
				data: function ( term, page ) {
					return {
						action: 'tribe_select2_search_venues',
						q: term,
						page: page
					};
				},
				results: function (data, page) { // parse the results into the format expected by Select2.
					return { results: data.items, more: data.more };
				}
			},
			initSelection: function (element, callback) {
				var $element = $(element);
				callback( { id: $element.val(), text: $element.data( 'text' ) } );
			},
			allowClear: true
		},
		organizer: {
			ajax: {
				url: typeof ajaxurl !== 'undefined' ? ajaxurl : tribe_dropdowns.ajaxurl,
				dataType: 'json',
				quietMillis: 250,
				data: function ( term, page ) {
					return {
						action: 'tribe_select2_search_organizers',
						q: term,
						page: page
					};
				},
				results: function (data, page) { // parse the results into the format expected by Select2.
					return { results: data.items, more: data.more };
				}
			},
			initSelection: function (element, callback) {
				var $element = $(element);
				callback( { id: $element.val(), text: $element.data( 'text' ) } );
			},
			allowClear: true
		}
	};

	my.init = function() {
		my.$venues = $( '.venue-dropdown' ).select2( my.opts.venue );
		my.$organizer = $( '.organizer-dropdown' ).select2( my.opts.organizer );


	};

	$( document ).ready( my.init );
})( jQuery, tribe_dropdowns );
