function calendar_toggle( wrapper ) {

	wrapper.find( '.calendar-widget-filters-title' ).hide();
	wrapper.find( '.calendar-widget-filters-operand' ).hide();
	var hidden = wrapper.find( '.calendar-widget-added-filters' );

	if ( hidden.length ) {

		var calendar_filters = hidden.val() ? jQuery.parseJSON( hidden.val() ) : new Object();

		var count = get_term_count( calendar_filters );

		if ( count > 0 ) {
			wrapper.find( '.calendar-widget-filters-title' ).show();
			if ( count > 1 ) {
				wrapper.find( '.calendar-widget-filters-operand' ).show();
			}
		}
	}

}

function calendar_toggle_all() {
	jQuery( '.calendar-widget-filters-container' ).each( function( i, v ) {
		calendar_toggle( jQuery( v ) );
	} );
}

function get_term_count( calendar_filters ) {
	var sum = 0;
	for ( var tax in calendar_filters ) {
		sum += calendar_filters[tax].length;
	}
	return sum;
}

jQuery( document ).ready( function( $ ) {

	var $body = $('body' ),
		select2Opts = {};

	if( $body.is('.wp-customizer') ){
		select2Opts = {
			dropdownCssClass: 'customizer-select2'
		};
	}

	$( 'div.widgets-sortables' ).on( 'sortstop', function() {
		setTimeout( function() {
			$( "#widgets-right select.calendar-widget-add-filter" ).select2(select2Opts);
			calendar_toggle_all();
		}, 600 );
	} );



	$( "#widgets-right select.calendar-widget-add-filter" ).select2(select2Opts);


	$body.on( 'change', 'select.calendar-widget-add-filter', function( e ) {

		$( '.calendar-widget-filters-container' ).show();

		var select = $( this );
		var option = $( this.options[this.selectedIndex] );
		var wrapper = select.parents( '.widget-content' );
		var list = wrapper.find( '.calendar-widget-filter-list' );
		var hidden = wrapper.find( '.calendar-widget-added-filters' );
		var term = option.attr( 'value' );
		var tax = option.closest( 'optgroup' );
		var tax_id = tax.attr( 'id' );
		var tax_name = tax.attr( 'label' );

		if ( parseInt( term ) === 0 ) {
			return;
		}

		var calendar_filters = hidden.val() ? $.parseJSON( hidden.val() ) : new Object();

		if ( !calendar_filters[tax_id] ) {
			calendar_filters[tax_id] = new Array();
		}

		if ( jQuery.inArray( term, calendar_filters[tax_id] ) == -1 ) {
			calendar_filters[tax_id].push( term );

			hidden.val( JSON.stringify( calendar_filters ) );

			var link = $( '<a/>' ).addClass( 'calendar-widget-remove-filter' ).attr( 'data-tax', tax_id ).attr( 'data-term', term ).text( '(remove)' ).attr( 'href', '#' );
			var remove = $( '<span/>' ).append( link );
			var li = $( '<li/>' ).append( 'p' ).text( tax_name + ': ' + option.text() + '   ' ).append( remove );
			list.append( li );

			calendar_toggle( wrapper );
		}

	} );

	$body.on( 'click', '.calendar-widget-remove-filter', function( e ) {

		e.preventDefault();

		var object = $( this );
		var tax_id = object.attr( 'data-tax' );
		var term_id = object.attr( 'data-term' );
		var wrapper = object.parents( '.widget-content' );
		var hidden = wrapper.find( '.calendar-widget-added-filters' );

		var calendar_filters = hidden.val() ? $.parseJSON( hidden.val() ) : new Object();

		if ( calendar_filters[tax_id] ) {
			calendar_filters[tax_id].myremove( term_id );
		}

		hidden.val( JSON.stringify( calendar_filters ) );

		object.closest( 'li' ).remove();

		calendar_toggle( wrapper );

	} );

	Array.prototype.myremove = function() {
		var what, a = arguments, L = a.length, ax;
		while ( L && this.length ) {
			what = a[--L];
			while ( (ax = this.indexOf( what )) != -1 ) {
				this.splice( ax, 1 );
			}
		}
		return this;
	};

	if ( !Array.prototype.indexOf ) {
		Array.prototype.indexOf = function( what, i ) {
			i = i || 0;
			var L = this.length;
			while ( i < L ) {
				if ( this[i] === what ) {
					return i;
				}
				++i;
			}
			return -1;
		}
	}


} );


