jQuery( document ).ready( function ( $ ) {
	
	function fix_widget_height() {
		var wrapper = $('.tribe-mini-calendar-wrapper');		
		if( $('.tribe-mini-calendar-wrapper.layout-wide').length && $('.tribe-mini-calendar-right').length ) {			
			var right_bar = $('.tribe-mini-calendar-right');
			var w_height = wrapper.height();			
			var rb_height = right_bar.outerHeight();
			rb_height = rb_height + 20;		
		
			if(rb_height > w_height) {				
				wrapper.css('height', rb_height + 'px');
			} else {
				wrapper.css('height', 'auto');
			}
		} else if( $('.tribe-mini-calendar-wrapper.layout-wide').length ) {
			wrapper.css('height', 'auto');
		}
	}

	function change_active_day(obj){
		$('.tribe-mini-calendar .thismonth').removeClass('today');
		obj.parents('.has-events').addClass('today');
	}
	
	
	fix_widget_height();

	$( '.tribe-mini-calendar-wrapper' ).delegate( '.tribe-mini-calendar-nav-link', 'click', function ( e ) {
		e.preventDefault();
		var month_target = $( this ).attr( 'data-month' );

		var params = {
			action   :'tribe-mini-cal',
			eventDate:month_target,
			count    :$( '#tribe-mini-calendar-count' ).val(),
			layout   :$( '#tribe-mini-calendar-layout' ).val(),
			tax_query:$( '#tribe-mini-calendar-tax-query' ).val(),
			nonce    :$( '#tribe-mini-calendar-nonce' ).val()
		};
		
		 $('.tribe-mini-calendar-nav div > span').addClass('active').siblings('#ajax-loading-mini').show();
		
		$.post(
			TribeMiniCalendar.ajaxurl,
			params,
			function ( response ) {
				$( '.tribe-mini-calendar-list-wrapper' ).remove();
				if ( response.success ) {
					
					var $the_content = $( response.html ).contents().filter(function() {return this.nodeType != 3;});
					$('.tribe-mini-calendar-nav div > span').removeClass('active').siblings('#ajax-loading-mini').hide();					
					$( '.tribe-mini-calendar-wrapper' ).empty().html( $the_content );					
					fix_widget_height();
				}
			}
		);
	} );

	$( '.tribe-mini-calendar-wrapper' ).delegate( '.tribe-mini-calendar-day-link', 'click', function ( e ) {
		e.preventDefault();
		var obj = $( this );
		var date = obj.attr( 'data-day' );
		var day = obj.text();
		$( 'h2.tribe-mini-calendar-title' ).text( $( '#tribe-mini-calendar-month-name' ).val() + ' ' + day + ' Events' );	
		change_active_day(obj);
		var params = {
			action   :'tribe-mini-cal-day',
			eventDate:date,
			count    :$( '#tribe-mini-calendar-count' ).val(),
			tax_query:$( '#tribe-mini-calendar-tax-query' ).val(),
			nonce    :$( '#tribe-mini-calendar-nonce' ).val()
		};

		$('.tribe-mini-calendar-nav div > span').addClass('active').siblings('#ajax-loading-mini').show();
		$.post(
			TribeMiniCalendar.ajaxurl,
			params,
			function ( response ) {
				if ( response.success ) {
					$('.tribe-mini-calendar-nav div > span').removeClass('active').siblings('#ajax-loading-mini').hide();
					$( '.tribe-mini-calendar-list-wrapper' ).html( response.html );
					change_active_day(obj);
					fix_widget_height();
				}
			}
		);

	} );
} );
