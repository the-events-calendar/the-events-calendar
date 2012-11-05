<?php
/**
 * List view wrapper

 * You can recreate an ENTIRELY new list view by doing a template override, and placing
 * a list-view.php file in a tribe-events directory within your theme directory, which
 * will override the /views/list-view.php.
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */
 
if ( !defined('ABSPATH') ) { die('-1'); }

echo apply_filters( 'tribe_events_list_view_before_template', '' );
echo apply_filters( 'tribe_events_list_view_events', '' );
echo apply_filters( 'tribe_events_list_view_after_template', '' );




