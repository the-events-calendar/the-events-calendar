var tribe_events_event_editor = tribe_events_event_editor || {};

/**
 * Implements behaviours that are specific to the event editor.
 */
( function ( $, obj ) {
	'use strict';

	/**
	 * Setup our selectors.
	 *
	 * @since 6.0.1
	 */
	obj.selectors = {
		featuredEventCheckbox: 'input[name="feature_event"]',
		stickyInMonthViewCheckbox: 'input[name="EventShowInCalendar"]',
	};

	/**
	 * If the 'feature event' box is checked, automatically check the
	 * sticky-in-month-view box also.
	 *
	 * @since 6.0.1
	 *
	 */
	obj.auto_enable_sticky_field = function () {
		if ( $( this ).prop( 'checked' ) ) {
			$( obj.selectors.stickyInMonthViewCheckbox ).prop( 'checked', true );
		}
	};

	/**
	 * Bind featured events logic.
	 *
	 * @since 6.0.1
	 */
	obj.bindFeaturedEvents = function () {
		$( obj.selectors.featuredEventCheckbox ).on( 'change', obj.auto_enable_sticky_field );
		$( obj ).trigger( 'event-editor-post-init.tribe' );
	};

	/**
	 * Initialize
	 *
	 * @since 6.0.1
	 */
	obj.init = () => {
		obj.bindFeaturedEvents();

		// We need to register core/legacy-widget block to support
		// earlier versions of WP which don't have it registered by default.
		if ( wp.widgets && wp.blocks && ! wp.blocks.getBlockType( 'core/legacy-widget' ) ) {
			wp.widgets.registerLegacyWidgetBlock();
		}
	};

	// Init our main object.
	$( obj.init );
} )( jQuery, tribe_events_event_editor );