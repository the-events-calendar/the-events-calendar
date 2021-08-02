var tribe_ignore_events = 'undefined' !== typeof tribe_ignore_events ? tribe_ignore_events : {};

( function( $, data ) {
	"use strict";

	/**
	 * Migration for Legacy Ignored Event
	 */
	$( function() {
		$( '#tribe-migrate-legacy-events' ).on( 'click', function() {
			var $this = $( this ),
				$spinner = $this.next( '.spinner' ),
				$dismiss = $this.parents( '.notice' ).eq( 0 ).find( '.notice-dismiss' ),
				$container = $this.parent();

			$spinner.css( { visibility: 'visible' } );

			$.ajax( ajaxurl, {
				dataType: 'json',
				method: 'POST',
				data: {
					action: 'tribe_convert_legacy_ignored_events'
				},
				success: function ( response, status ) { // eslint-disable-line no-unused-vars
					if ( response.status ) {
						$container.html( response.text );
						setTimeout( function () {
							$dismiss.trigger( 'click' );
						}, 5000 );
					} else {
						$container.before( $( '<p>' ).html( response.text ) );
					}
				},
				complete: function () {
					$spinner.css( { visibility: 'hidden' } );
				}
			} );
		} );
	} );

	/**
	 * Modify Archive page elements
	 */
	$( function(){
		// Verify that all WP variables exists
		if ( -1 !== [ typeof pagenow, typeof typenow, typeof adminpage ].indexOf( 'undefined' ) ) {
			return false;
		}

		// We are not on the correct Page
		if (
			'edit-tribe_events' !== pagenow ||
			'tribe_events' !== typenow ||
			'edit-php' !== adminpage
		) {
			return false;
		}

		if ( 'undefined' === typeof data.archive ) {
			return false;
		}

		var $selects = $( '#bulk-action-selector-top, #bulk-action-selector-bottom' );

		$selects.each( function() {
			var $this = $( this );
			$this.append( $( '<option>', { 'value': 'delete', 'text' : data.archive.delete_label } ) );
		} );

	} );
	/**
	 * Modify Single page elements
	 */
	$( function() {
		// Verify that all WP variables exists
		if ( -1 !== [ typeof pagenow, typeof typenow, typeof adminpage ].indexOf( 'undefined' ) ) {
			return false;
		}

		// We are not on the correct Page
		if ( 'tribe_events' !== pagenow || 'tribe_events' !== typenow || 'post-php' !== adminpage ) {
			return false;
		}

		// We don't expect tribe_ignore_events.single to have been defined on every page load
		// @see Tribe__Events__Ignored_Events::action_assets()
		if ( 'undefined' === typeof data.single ) {
			return false;
		}

		$( '.submitdelete' ).attr( 'title', data.single.link_title ).html( data.single.link_text );
		if ( 'undefined' !== typeof data.single.link_nonce ) {
			$( '#post_status' ).append( $( '<option>', { 'value': 'ignored', 'text' : data.single.link_status } ).prop( 'selected', true ) ); // eslint-disable-line max-len
			$( '#post-status-display' ).html( data.single.link_status );
			$( '.submitdelete' ).attr( 'href', 'post.php?action=delete&post=' + data.single.link_post + '&_wpnonce=' + data.single.link_nonce ); // eslint-disable-line max-len
		}
	} );

}( jQuery, tribe_ignore_events ) );
