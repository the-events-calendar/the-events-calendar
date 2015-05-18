jQuery( document ).ready( function( $ ) {

	var $widget_wrapper = $( '.tribe-mini-calendar-wrapper' ).parent();

	function fix_widget_height() {
		var wrapper = $( '.tribe-mini-calendar-wrapper' );
		if ( $( '.tribe-mini-calendar-wrapper.layout-wide' ).length && $( '.tribe-mini-calendar-right' ).length ) {
			var right_bar = $( '.tribe-mini-calendar-right' );
			var w_height = wrapper.height();
			var rb_height = right_bar.outerHeight();
			rb_height = rb_height + 20;

			if ( rb_height > w_height ) {
				wrapper.css( 'height', rb_height + 'px' );
			}
			else {
				wrapper.css( 'height', 'auto' );
			}
		}
		else if ( $( '.tribe-mini-calendar-wrapper.layout-wide' ).length ) {
			wrapper.css( 'height', 'auto' );
		}
	}

	function change_active_day( obj ) {
		$( '.tribe-mini-calendar .tribe-events-thismonth' ).removeClass( 'tribe-mini-calendar-today' );
		obj.parents( '.tribe-events-has-events' ).addClass( 'tribe-mini-calendar-today' );
	}


	fix_widget_height();


	var date = new Date(),
		day = date.getDate().toString(),
		month = (date.getMonth() + 1).toString(),
		year = date.getFullYear().toString(),
		current = year + '-' + (month[1] ? month : '0' + month[0]) + '-' + (day[1] ? day : '0' + day[0]),
		current_ym = current.slice( 0, 7 );

	$widget_wrapper
		.on( 'click', '.tribe-mini-calendar-nav-link', function( e ) {
			e.preventDefault();

			var $this = $( this );

			var $current_calendar = $this.closest( '.tribe-mini-calendar' );
			var $current_calendar_wrapper = $this.closest( '.tribe-mini-calendar-wrapper' );

			var month_target = $this.attr( 'data-month' ),
				target_ym = month_target.slice( 0, 7 );

			if ( current_ym == target_ym ) {
				month_target = current;
			}

			var params = {
				action   : 'tribe-mini-cal',
				eventDate: month_target,
				count    : $current_calendar.data( 'count' ),
				tax_query: $current_calendar.data( 'tax-query' ),
				nonce    : $current_calendar.data( 'nonce' )
			};
			$current_calendar.find( '.tribe-mini-calendar-nav div > span' ).addClass( 'active' ).siblings( '#ajax-loading-mini' ).show();

			$.post(
				TribeMiniCalendar.ajaxurl,
				params,
				function( response ) {
					$current_calendar.find( '.tribe-mini-calendar-list-wrapper' ).remove();
					if ( response.success ) {
						var $the_content = $.parseHTML( response.html );
						$current_calendar.find( '.tribe-mini-calendar-nav div > span' ).removeClass( 'active' ).siblings( '#ajax-loading-mini' ).hide();
						$current_calendar_wrapper.replaceWith( $the_content );
						fix_widget_height();
					}
				}
			);
		} )
		.on( 'click', '.tribe-mini-calendar-day-link', function( e ) {
			e.preventDefault();
			var $this = $( this );
			var date = $this.attr( 'data-day' );
			var day = $this.text();
			var $current_calendar = $this.closest( '.tribe-mini-calendar' );
			var $current_calendar_events_list = $current_calendar.parent().siblings( '.tribe-mini-calendar-list-wrapper' );

			$( 'h2.tribe-mini-calendar-title' ).text( $( '#tribe-mini-calendar-month-name' ).val() + ' ' + day + ' Events' );
			change_active_day( $this );
			var params = {
				action   : 'tribe-mini-cal-day',
				eventDate: date,
				count    : $current_calendar.data( 'count' ),
				tax_query: $current_calendar.data( 'tax-query' ),
				nonce    : $current_calendar.data( 'nonce' )
			};

			$current_calendar.find( '.tribe-mini-calendar-nav div > span' ).addClass( 'active' ).siblings( '#ajax-loading-mini' ).show();
			$.post(
				TribeMiniCalendar.ajaxurl,
				params,
				function( response ) {
					if ( response.success ) {
						var $the_content = '';
						if ( $.isFunction( $.fn.parseHTML ) ) {
							$the_content = $.parseHTML( response.html );
						}
						else {
							$the_content = response.html;
						}
						$current_calendar.find( '.tribe-mini-calendar-nav div > span' ).removeClass( 'active' ).siblings( '#ajax-loading-mini' ).hide();
						$current_calendar_events_list.replaceWith( $the_content );
						fix_widget_height();
					}
				}
			);
		} );
} );
