/**
 * Init the tec.main.ece object.
 *
 * @since TBD
 */
window.tec = window.tec || {};
window.tec.main = window.tec.main || {};
window.tec.main.ece = window.tec.main.ece || {};

(function($,obj){
	const $document = $(document);

	/**
	 * Selectors used to attach listeners.
	 *
	 * @since TBD
	 * @type {Object}
	 */
	obj.selectors = {
		container: '[data-js="tribe-events-view"]',
		eventsInDay: '.tribe-events-calendar-month__calendar-event a',
		eventsInToolTip: '.tribe-events-tooltip-theme a',
		moreEventsLink: '.tribe-events-calendar-month__more-events-link',
	};

	/**
	 * Open the event in a new tab.
	 *
	 * @since TBD
	 * @param {Event} e
	 */
	obj.openEventInNewTab = ( e ) => {
		if ( ! e.target.href ) {
			return;
		}

		e.preventDefault();

		window.open( e.target.href, '_blank' );
	};

	/**
	 * Ready function.
	 *
	 * @since TBD
	 * @type {Function}
	 */
	obj.ready = () => {
		if ( ! tribe?.events?.views?.manager?.onLinkClick ) {
			// The script that defines the above is deferred, so we need to wait for it to be loaded.
			setTimeout( () => {
				obj.ready();
			}, 100 );
			return;
		}

		$document.on( 'click', obj.selectors.eventsInDay, obj.openEventInNewTab );
		$document.on( 'click', obj.selectors.eventsInToolTip, obj.openEventInNewTab );

		const moreLinkTargets = $document.find( obj.selectors.moreEventsLink );
		if ( ! moreLinkTargets.length ) {
			return;
		}

		// Remove the AJAX handler from the more link.
		$( obj.selectors.container ).find( obj.selectors.moreEventsLink ).off( 'click.tribeEvents', tribe.events.views.manager.onLinkClick );

		// Add the new handler to the more link.
		moreLinkTargets.each( ( index, element ) => {
			$( element ).on( 'click.tribeEvents', obj.openEventInNewTab );
		} );
	};

	// Init on dom ready.
	$( obj.ready() );
})(jQuery, window.tec.main.ece);
