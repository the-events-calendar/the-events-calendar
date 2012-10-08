<?php
/**
 * Day Grid Template
 * The template for displaying events by day.
 *
 * This view contains the filters required to create an effective day grid view.
 *
 * You can recreate an ENTIRELY new day grid view by doing a template override, and placing
 * a day.php file in a tribe-events/ directory within your theme directory, which
 * will override the /views/day.php. 
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

echo apply_filters('tribe_events_day_pagination', '');

/*
	Mockup: https://central.tri.be/attachments/54709/dayview.3.jpg
	
	Template Tags Needed:
		
	-Day Nav (like the month nav on calendar view)
		<-- Previous Day | Month/Day/Year Selector | Next Day -->
		
	-Day View Button (top, right of calendar/list view)
	
	-Tag to output the day date in the container header (not gonna use a table for this view I don't think)
	
	-Tag to output the hours that have events starting at that hour within the day grid, as like a section header, basically what
	we did for Conference, including an All Day section header (see the mockup)
	
	(Feel free to let me know if the following would just be default WP tags like the_title, etc)
	
	-Event Title Tag
	-Event Excerpt Tag
	-Event Venue Name Tag
	-Event Categories Tag (with a comma separator)
	-Event Time Duration Tag (All Day or else 11am-1pm)
	-Tag For URL To Event
*/

// Basic Markup
// Think through extensible future
// Styles?
// Get hooked up?

// nav, buttons, grid, content, popups
?>
