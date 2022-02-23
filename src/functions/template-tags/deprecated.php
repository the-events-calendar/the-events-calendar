<?php
/**
 * These are for backwards compatibility with the free The Events Calendar plugin.
 * Don't use them.
 *
 */

/**
 * Prints out data attributes used in the template header tags
 *
 * @deprecated 6.0.0 No longer used by templates / views.
 *
 * @param string|null $current_view
 *
 **@category Events
 */
function tribe_events_the_header_attributes( $current_view = null ) {
	_deprecated_function( __FUNCTION__, '6.0.0' );
	return;
}