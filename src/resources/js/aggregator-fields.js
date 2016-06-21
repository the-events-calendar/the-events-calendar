var tribe_events_aggregator = tribe_events_aggregator || {};

tribe_events_aggregator.fields = {
	// Store the Required Selectors
	slct: {
		container: '.tribe-ea',
		select2: '.tribe-ea-select2',
		help: '.tribe-ea-help',
		fields: '.tribe-ea-field',
	},
	// Store the jQuery elements
	$: {}
};

( function( $, _, my ) {
	'use strict';

	my.init = function() {
		my.$.container = $( my.slct.container );

		my.setup();
	};

	my.setup = function() {
		// Update what fields we currently have to setup
		my.$.fields = my.$.container.find( my.slct.fields );

		my.$.fields.each( function( k, field ){
			var $field = $( field ),
				$container = $field.parents( 'tr' ).eq(0),
				$help = $container.find( my.slct.help ),
				$help_id = _.uniqueId( 'tribe-ea-help-' );

			if ( $field.is( my.slct.select2 ) ) {
				$field.select2({
					data: $field.data( 'options' )
				});
			}

			// Setup Help Bump Down
			$help
				.after( $( '<div>' ).addClass( 'tribe-hidden' ).html( $help.data( 'help' ) ).attr( 'data-trigger', $help_id ) )
				.attr( 'id', $help_id )
				.bumpdown();
		} );
	};

	$( my.init );
} )( jQuery, _, tribe_events_aggregator.fields );
