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

<div id="tribe-events-content" class="day-grid">
	
    <!-- This title is here for ajax loading – do not remove if you want ajax switching between month views -->
    <title><?php wp_title(); ?></title>
      	
	<div id="tribe-events-calendar-header" class="clear fix">
		
		<?php // Month & Year Nav ?>
		<span class="tribe-events-month-nav">
		
			<span class="tribe-events-prev-month">
				<a href="<?php echo tribe_get_previous_month_link(); ?>"> &#x2190; <?php echo tribe_get_previous_month_text(); ?> </a>
			</span><!-- .tribe-events-prev-month -->

			<?php tribe_month_year_dropdowns( "tribe-events-" ); ?>
	
			<span class="tribe-events-next-month">
				<a href="<?php echo tribe_get_next_month_link(); ?>"> <?php echo tribe_get_next_month_text(); ?> &#x2192; </a>
               	<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading" alt="" style="display: none" />
			</span><!-- .tribe-events-next-month -->
		
		</span><!-- .tribe-events-month-nav -->

		<?php // View Buttons ?>
		<span class="tribe-events-calendar-buttons"> 
			<a class="tribe-events-button-off" href="<?php echo tribe_get_listview_link(); ?>"><?php _e( 'Event List', 'tribe-events-calendar' ); ?></a>
			<a class="tribe-events-button-on" href="<?php echo tribe_get_gridview_link(); ?>"><?php _e( 'Calendar', 'tribe-events-calendar' ); ?></a>
		</span><!-- .tribe-events-calendar-buttons -->
			
	</div><!-- #tribe-events-calendar-header -->
	
	
	<?php // Our Content ?>
	
	
		
    <?php // iCal Import
    if( function_exists( 'tribe_get_ical_link' ) ): ?>
       	<a title="<?php esc_attr_e( 'iCal Import', 'tribe-events-calendar' ); ?>" class="ical" href="<?php echo tribe_get_ical_link(); ?>"><?php _e( 'iCal Import', 'tribe-events-calendar' ); ?></a>
    <?php endif; ?>
		
</div><!-- #tribe-events-content -->
