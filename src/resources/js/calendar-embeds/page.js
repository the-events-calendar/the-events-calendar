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
		eventsInDay: '.tribe-events-calendar-month__calendar-event a',
		eventsInToolTip: '.tribe-events-tooltip-theme a',
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
		$document.on( 'click', obj.selectors.eventsInDay, obj.openEventInNewTab );
		$document.on( 'click', obj.selectors.eventsInToolTip, obj.openEventInNewTab );
	};

	// Init on dom ready.
	$( obj.ready() );
})(jQuery, window.tec.main.ece);
