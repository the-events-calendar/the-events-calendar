<?php
/**
 * Week Grid Template
 * The template for displaying events by week.
 *
 * This view contains the filters required to create an effective week grid view.
 *
 * You can recreate an ENTIRELY new week grid view by doing a template override, and placing
 * a week.php file in a tribe-events/ directory within your theme directory, which
 * will override the /views/week.php. 
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
	Mockup: https://central.tri.be/attachments/54643/weekview.1.jpg
	
	Template Tags Needed:
		
	-Week Nav (like the month nav on calendar view)
		<-- Previous Week | Month/Week #?/Year Selector | Next Week -->
		
	-Week View Button (top, right of calendar/list view)
	
	-Tag to output the day dates in the table header
	
	-Tag to output the day hours for the grid, including an "All Day" one at the top (see the mockup)
	
	I'll need your help in coming up with the best solution for all the timeline stuff
	
	(Feel free to let me know if the following would just be default WP tags like the_title, etc)
	
	-Event Title Tag
	-Event Excerpt Tag
	-Event Time Duration Tag (Just like for calendar view)
	-Tag For URL To Event
*/
?>

<div id="tribe-events-content" class="week-grid">
	
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