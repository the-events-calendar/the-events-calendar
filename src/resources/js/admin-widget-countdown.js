( function( $ ) {
	'use strict';

	var tribeWidget = {
		setup: function( event, widget ){
			var $widget = $( widget );

			$widget.find( '.tribe-select2' ).each( function(){
				var $this = $( this ),
					args = {};

				if ( $this.hasClass('select2-container') ){
					return;
				}

				if ( 1 === $this.data( 'noSearch' ) ){
					args.minimumResultsForSearch = Infinity;
				}

				$this.on( 'open', function( event ){
					$( '.select2-drop' ).css( 'z-index', 10000000 );
				} ).select2( args );
			} );

			$widget.on( 'change', '.js-tribe-condition', function(){
				var $this = $( this ),
					field = $this.data( 'tribeConditionalField' ),
					$conditionals = $widget.find( '.js-tribe-conditional' ).filter( '[data-tribe-conditional-field="' + field + '"]' ),
					value = $this.val();

				// First hide all conditionals
				$conditionals.hide()

				// Now Apply any stuff that must be "conditional" on hide
				.each( function(){
					var $conditional = $( this );

					if ( $conditional.hasClass( 'tribe-select2' ) ){
						$conditional.prev( '.select2-container' ).hide();
					}
				} )

				// Find the matching values
				.filter( '[data-tribe-conditional-value="' + value + '"]' ).show()

				// Apply showing with "conditions"
				.each( function(){
					var $conditional = $( this );

					if ( $conditional.hasClass( 'tribe-select2' ) ){
						$conditional.hide().prev( '.select2-container' ).show();
					}
				} );
			} );


			// Only happens on Widgets Admin page
			if ( ! $( 'body' ).hasClass( 'wp-customizer' ) ){
				if ( $.isNumeric( event ) || 'widget-updated' === event.type ){
					$widget.find( '.js-tribe-condition' ).trigger( 'change' );
				}
			}

		}
	};

	$( document ).on( {
		'widget-added widget-synced widget-updated': tribeWidget.setup,
		'ready': function( event ){
			// Prevents problems on Customizer
			if ( $( 'body' ).hasClass( 'wp-customizer' ) ){
				return;
			}

			// This ensures that we setup corretly the widgets that are already in place
			$( '.tribe-widget-countdown-container' ).each( tribeWidget.setup );
		}
	} );
}( jQuery.noConflict() ) );