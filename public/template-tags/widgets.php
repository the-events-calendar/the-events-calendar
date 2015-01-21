<?php
/**
 * The Events Calendar Template Tags
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'TribeEvents' ) ) {

	/**
	 * A wrapper for tribe_get_events with options from a given widget instance.
	 *
	 * @return array WP_Posts of the retrieved events.
	 **/
	function tribe_get_list_widget_events() {
		return apply_filters( 'tribe_get_list_widget_events', TribeEventsListWidget::$posts );
	}
}