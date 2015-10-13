var tribe_events_this_week = tribe_events_this_week || {};

tribe_events_this_week.ajax = {
	event: {}
};

( function( $, my ) {
	'use strict';

	my.init = function() {
		this.init_ajax();
	};

	/**
	 * initialize the recurrence behaviors and UI
	 */
	my.init_ajax = function() {
		this.$widget_wrapper = $( '.tribe-this-week-widget-wrapper' ).parent();

		this.$widget_wrapper
			.on( 'click', '.tribe-this-week-nav-link', this.event.ajax );

	};

	my.event.ajax = function( e ) {
		e.preventDefault();

		//Setup Variables
		var $this = $( this );
		var $this_week_widget = $this.closest( '.tribe-this-week-widget-wrapper' );

		//Show Loading Ajax
		$this_week_widget.find( '.tribe-this-week-widget-weekday-wrapper' ).css( 'opacity', .25 );
		$this_week_widget.find( '.tribe-events-ajax-loading' ).addClass( 'tribe-events-active-spinner' );

		//Setup Query Start Date
		var $week_target = $this_week_widget.data( 'prev-date' );
		if (  $this.hasClass( 'nav-next' ) ) {
			$week_target = $this_week_widget.data( 'next-date' );
		}

		var params = {
			action          : 'tribe_this_week',
			start_date      : $week_target,
			count           : $this_week_widget.data( 'count' ),
			layout          : $this_week_widget.data( 'layout' ),
			tax_query       : $this_week_widget.data( 'tax-query' ),
			hide_weekends   : $this_week_widget.data( 'hide-weekends' ),
			nonce           : $this_week_widget.data( 'nonce' )
		};

		$.post(
			tribe_this_week.ajaxurl,
			params,
			function( response ) {
				if ( response.success ) {
					var $the_content = $.parseHTML( response.html );
					$this_week_widget.replaceWith( $the_content );
				}
			}
		);
	};

	$( function() {
		my.init();
	} );

} )( jQuery, tribe_events_this_week.ajax );