<?php
/**
 * Day Grid Template
 * The template for displaying events by day.
 *
 * This view contains the filters required to create an effective day grid view.
 *
 * You can recreate an ENTIRELY new day grid view by doing a template override, and placing
 * a day.php file in a tribe-events/pro/ directory within your theme directory, which
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
	

	// Separate skeleton / full styles
	// Add tags
	// Xbrowser styles for new views and double check with other themes?	
	// Hit Tim about doing date picker for both of these?
	
	// Cut into hooks/filters last

*/

echo apply_filters('tribe_events_day_before_template', '');

	// daily header (navigation)
	echo apply_filters( 'tribe_events_day_the_header', '');

	echo apply_filters( 'tribe_events_day_before_loop', '');

	if ( have_posts() ) {

		while ( have_posts() ) {
			the_post();
			echo apply_filters( 'tribe_events_day_inside_before_loop', '');
			echo apply_filters( 'tribe_events_day_the_event', '');
			echo apply_filters( 'tribe_events_day_inside_after_loop', '');
		}
	}

	?>
	
	<div id="tribe-events-header">';
		<h3 class="tribe-events-visuallyhidden"><?php _e( 'Calendar Month Navigation', 'tribe-events-calendar' ); ?></h3>
		<ul class="tribe-events-sub-nav">
			<li class="tribe-events-nav-prev">'
				<a href="<?php tribe_get_previous_month_link(); ?>" rel="pref">&#x2190; <?php tribe_get_previous_month_text(); ?></a>
			</li><!-- .tribe-events-prev-next -->
			<li><?php tribe_month_year_dropdowns( "tribe-events-" ); ?></li>
			<li class="tribe-events-nav-next">
				<a href="<?php tribe_get_next_month_link(); ?>" rel="next"> <?php tribe_get_next_month_text(); ?> &#x2192;</a>
				<img src="<?php esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading" alt="Loading events" />
			</li><!-- .tribe-events-nav-next -->
		</ul><!-- .tribe-events-sub-nav -->
	</div><!-- #tribe-events-header -->	
	
	<?php // Our Content ?>
			<tr>
				<td>
				<h3>All Day</h3>
				
				<div class="hentry vevent">
					<h4 class="entry-title summary"><a href="#" class="url" rel="bookmark">Intro to Spinning</a></h4>
					<p class="updated published"><abbr class="tribe-events-abbr dtstart" title="2010-09-13">All Day</abbr></p>
					<p class="location"><a href="" rel="bookmark">Room Name</a></p>
					<p class="entry-content description">I saw for the first time the earth's shape. I could easily see the shores of continents, islands, great rivers, folds of the terrain, large bodies of water.</p>
					<ul class="tribe-events-grid-meta">
						<li><a href="" rel="tag">Category A</a>,</li>
						<li><a href="" rel="tag">Category B</a></li>
					</ul>
				</div><!-- .hentry .vevent -->
				</td>
			</tr>
			
			<tr>
				<td>
				<h3>7:00 AM</h3>
				
				<div class="hentry vevent">
					<h4 class="entry-title summary"><a href="#" class="url" rel="bookmark">Intro to Spinning</a></h4>
					<p class="updated published">
						<abbr class="tribe-events-abbr dtstart" title="2010-09-13">7am</abbr>
						-
						<abbr class="tribe-events-abbr dtend" title="2010-09-13">9am</abbr>
					</p>
					<p class="location"><a href="" rel="bookmark">Room Name</a></p>
					<p class="entry-content description">I saw for the first time the earth's shape. I could easily see the shores of continents, islands, great rivers, folds of the terrain, large bodies of water.</p>
					<ul class="tribe-events-grid-meta">
						<li><a href="" rel="tag">Category A</a>,</li>
						<li><a href="" rel="tag">Category B</a></li>
					</ul>
				</div><!-- .hentry .vevent -->
				
				<div class="hentry vevent">
					<h4 class="entry-title summary"><a href="#" class="url" rel="bookmark">Intro to Spinning</a></h4>
					<p class="updated published">
						<abbr class="tribe-events-abbr dtstart" title="2010-09-13">7am</abbr>
						-
						<abbr class="tribe-events-abbr dtend" title="2010-09-13">12pm</abbr>
					</p>
					<p class="location"><a href="" rel="bookmark">Room Name With a Really Really Really Long Room Name For Testing</a></p>
					<p class="entry-content description">I saw for the first time the earth's shape. I could easily see the shores of continents, islands, great rivers, folds of the terrain, large bodies of water.</p>
					<ul class="tribe-events-grid-meta">
						<li><a href="" rel="tag">Category A</a>,</li>
						<li><a href="" rel="tag">Category B</a></li>
					</ul>
				</div><!-- .hentry .vevent -->
				</td>
			</tr>
			
			<tr>
				<td>
				<h3>11:00 AM</h3>
				
				<div class="hentry vevent">
					<h4 class="entry-title summary"><a href="#" class="url" rel="bookmark">Intro to Spinnin and an example of a really really really long title to demonstrate what this looks like</a></h4>
					<p class="updated published">
						<abbr class="tribe-events-abbr dtstart" title="2010-09-13">11am</abbr>
						-
						<abbr class="tribe-events-abbr dtend" title="2010-09-13">12pm</abbr>
					</p>
					<p class="location"><a href="" rel="bookmark">Room Name</a></p>
					<p class="entry-content description">I saw for the first time the earth's shape. I could easily see the shores of continents, islands, great rivers, folds of the terrain, large bodies of water.</p>
					<ul class="tribe-events-grid-meta">
						<li><a href="" rel="tag">Category A</a>,</li>
						<li><a href="" rel="tag">Category B</a>,</li>
						<li><a href="" rel="tag">Category B</a>,</li>
						<li><a href="" rel="tag">Category B</a>,</li>
						<li><a href="" rel="tag">Category B</a>,</li>
						<li><a href="" rel="tag">Category B</a>,</li>
						<li><a href="" rel="tag">Category B</a>,</li>
						<li><a href="" rel="tag">Category B</a>,</li>
						<li><a href="" rel="tag">Category B</a>,</li>
						<li><a href="" rel="tag">Category B</a>,</li>
						<li><a href="" rel="tag">Category B</a>,</li>
						<li><a href="" rel="tag">Category B</a>,</li>
						<li><a href="" rel="tag">Category B</a></li>
					</ul>
				</div><!-- .hentry .vevent -->
				</td>
			</tr>
		
		
    <?php
    echo apply_filters('tribe_events_day_after_loop', '');

echo apply_filters('tribe_events_day_after_template', '');
