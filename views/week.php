<?php
/**
 * Week Grid Template
 * The template for displaying events by week.
 *
 * This view contains the filters required to create an effective week grid view.
 *
 * You can recreate an ENTIRELY new week grid view by doing a template override, and placing
 * a week.php file in a tribe-events/pro/ directory within your theme directory, which
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

<?php /*

	// Separate skeleton / full styles
	// Add tags (id's, classes, tooltip shit, etc update to micro formats? query bits)
	// Xbrowser styles for new views and double check with other themes?
	// Datepicker for week?

*/ ?>

<div id="tribe-events-content" class="tribe-events-week-grid">
	
    <!-- This title is here for ajax loading – do not remove if you want ajax switching between month views -->
    <title><?php wp_title(); ?></title>
      	
	<div id="tribe-events-calendar-header" class="clearfix">
		
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
			<a class="tribe-events-button-off" href="<?php echo tribe_get_gridview_link(); ?>"><?php _e( 'Calendar', 'tribe-events-calendar' ); ?></a>
			<a class="tribe-events-button-off" href=""><?php _e( 'Day View', 'tribe-events-calendar' ); ?></a>
			<a class="tribe-events-button-on" href=""><?php _e( 'Week View', 'tribe-events-calendar' ); ?></a>
		</span><!-- .tribe-events-calendar-buttons -->
			
	</div><!-- #tribe-events-calendar-header -->
	
	
	<?php
		// Tooltips (see how implemented in calendar.php, /hooks/calendar.php, public/template-tags/calendar.php)
		// jQuery bits for placing event height/top & all day centering (basically all styles that are hardcoded
		// in this view need to be done by jQuery)
		// Think about classes/design for recurring events like in prev/next week, etc, highlight today, etc
		// Thinking the "th" bookmarks could link to their dayview counterparts
	 ?>

	<table cellspacing="0" cellpadding="0" class="tribe-events-grid">
	
		<thead>
			<tr>
				<th scope="col" class="tribe-grid-first"><span>Hours</span></th>
				<th title="2012-09-08" scope="col" class="tribe-grid-today"><a href="" rel="bookmark">Sun 9/7</a></th>
				<th title="2012-09-08" scope="col"><a href="" rel="bookmark">Mon 9/8</a></th>
				<th title="2012-09-08" scope="col"><a href="" rel="bookmark">Tue 9/9</a></th>
				<th title="2012-09-08" scope="col"><a href="" rel="bookmark">Wed 9/10</a></th>
				<th title="2012-09-08" scope="col"><a href="" rel="bookmark">Thu 9/11</a></th>
				<th title="2012-09-08" scope="col"><a href="" rel="bookmark">Fri 9/12</a></th>
				<th title="2012-09-08" scope="col"><a href="" rel="bookmark">Sat 9/13</a></th>
			</tr>
		</thead>

		<tbody>
		
			<?php // our dummy row for all day events ?>
			<tr class="tribe-week-dummy-row" style="height: 72px;">
				<td></td>
				<td class="tribe-week-today"></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr><!-- .tribe-week-dummy-row -->
		
			<?php // our all day events row ?>
			<tr class="tribe-week-allday-row">
				<td class="tribe-week-allday-th"><div style="margin-top: -72px;">All Day</div></td>
				<td colspan="7">
					<div style="margin-top: -72px;">
						<table cellpadding="0" cellspacing="0">
							<tbody class="hfeed">
								<tr>
									<td colspan="2">
										<div class="hentry vevent"><a href="" class="entry-title summary url" rel="bookmark">Multiday all day event</a></div>
									</td>
									<td colspan="4">
										<div class="hentry vevent"><a href="" class="entry-title summary url" rel="bookmark">Multiday all day event</a></div>
									</td>
									<td>
										<div class="hentry vevent"><a href="" class="entry-title summary url" rel="bookmark">All day event</a></div>
									</td>
								</tr>
							</tbody><!-- .hfeed -->
						</table>
					</div>
				</td>
			</tr><!-- .tribe-week-allday-row -->
		
			<?php // our week grid ?>
			<tr class="tribe-week-grid-bgd">
				<td></td>
				<td colspan="7">
					<div class="tribe-week-grid-outer-wrap">
						<div class="tribe-week-grid-inner-wrap">
							<div class="tribe-week-grid-block"><div></div></div>
							<div class="tribe-week-grid-block"><div></div></div>
							<div class="tribe-week-grid-block"><div></div></div>
							<div class="tribe-week-grid-block"><div></div></div>
						</div><!-- .tribe-week-grid-inner-wrap -->
					</div><!-- .tribe-week-grid-outer-wrap -->
				</td>
			</tr><!-- .tribe-week-grid-bgd -->
			
			<?php // our actual week grid columns, hours, and events ?>
			<tr class="tribe-week-events-row hfeed">
				<td class="tribe-week-grid-hours">
					<div>7am</div>
					<div>8am</div>
					<div>9am</div>
					<div>10am</div>
				</td><!-- .tribe-week-grid-hours -->
				<td class="tribe-week-grid-col tribe-week-today"></td><!-- .tribe-week-grid-col -->
				<td class="tribe-week-grid-col">
					<div class="tribe-week-grid-col-inner-wrap">
						<div class="tribe-week-grid-event-wrap hentry vevent">
							<div>
								<a href="" class="entry-title summary url" rel="bookmark">MIT Theme Structure Long title</a>
							</div>
						</div><!-- .tribe-week-grid-event-wrap -->
					</div><!-- .tribe-week-grid-col-inner-wrap -->
				</td><!-- .tribe-week-grid-col -->
				<td class="tribe-week-grid-col"></td><!-- .tribe-week-grid-col -->
				<td class="tribe-week-grid-col">
					<div class="tribe-week-grid-col-inner-wrap">
						<div class="tribe-week-grid-event-wrap hentry vevent">
							<div style="height: 60px;">
								<a href="" class="entry-title summary url" rel="bookmark">MIT Theme Structure Long title</a>
							</div>
						</div><!-- .tribe-week-grid-event-wrap -->
						
						<div style="width: 90%; top: 28px; right: 0; left: auto;" class="tribe-week-grid-event-wrap tribe-event-overlapping tribe-event-o-1 hentry vevent">
							<div style="height: 50px;">
								<a href="" class="entry-title summary url" rel="bookmark">Short Title</a>
							</div>
						</div><!-- .tribe-week-grid-event-wrap -->
						
						<div style="width: 80%; top: 48px; right: 0; left: auto;" class="tribe-week-grid-event-wrap tribe-event-overlapping tribe-event-o-2 hentry vevent">
							<div style="height: 50px;">
								<a href="" class="entry-title summary url" rel="bookmark">Short Title</a>
							</div>
						</div><!-- .tribe-week-grid-event-wrap -->
					</div><!-- .tribe-week-grid-col-inner-wrap -->
				</td><!-- .tribe-week-grid-col -->
				<td class="tribe-week-grid-col"></td><!-- .tribe-week-grid-col -->
				<td class="tribe-week-grid-col"></td><!-- .tribe-week-grid-col -->
				<td class="tribe-week-grid-col">
					<div class="tribe-week-grid-col-inner-wrap">
						<div style="width: 80%;" class="tribe-week-grid-event-wrap tribe-event-same-time hentry vevent">
							<div style="height: 60px;">
								<a href="" class="entry-title summary url" rel="bookmark">MIT Theme Structure Long title</a>
							</div>
						</div><!-- .tribe-week-grid-event-wrap -->
						
						<div style="width: 80%; right: 0; left: auto;" class="tribe-week-grid-event-wrap tribe-event-same-time tribe-event-st-1 hentry vevent">
							<div style="height: 60px;">
								<a href="" class="entry-title summary url" rel="bookmark">Short Title</a>
							</div>
						</div><!-- .tribe-week-grid-event-wrap -->
					</div><!-- .tribe-week-grid-col-inner-wrap -->
				</td><!-- .tribe-week-grid-col -->
			</tr><!-- .tribe-week-events-row -->
					
		</tbody>
		
	</table><!-- .tribe-events-grid -->
		
    <?php // iCal Import
    if( function_exists( 'tribe_get_ical_link' ) ): ?>
       	<a class="tribe-events-ical" title="<?php esc_attr_e( 'iCal Import', 'tribe-events-calendar' ); ?>" href="<?php echo tribe_get_ical_link(); ?>"><?php _e( 'iCal Import', 'tribe-events-calendar' ); ?></a>
    <?php endif; ?>
		
</div><!-- #tribe-events-content -->