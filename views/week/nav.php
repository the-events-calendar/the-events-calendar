<?php
/**
 * Week View Nav
 * This file loads the week view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/week/nav.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<h3 class="tribe-events-visuallyhidden"><?php _e( 'Week Navigation', 'tribe-events-calendar-pro' ); ?></h3>
<ul class="tribe-events-sub-nav">
	<li class="tribe-events-nav-previous">
		<a <?php tribe_events_the_nav_attributes( 'prev' ); ?> href="<?php echo tribe_get_last_week_permalink(); ?>" rel="prev">&laquo; <?php _e( 'Previous Week', 'tribe-events-calendar-pro' ); ?></a>
	</li><!-- .tribe-events-nav-previous -->
	<li class="tribe-events-nav-next">
		<a <?php tribe_events_the_nav_attributes( 'next' ); ?> href="<?php echo tribe_get_next_week_permalink(); ?>" rel="next"><?php _e( 'Next Week', 'tribe-events-calendar-pro' ); ?> &raquo;</a>
	</li><!-- .tribe-events-nav-next -->
</ul><!-- .tribe-events-sub-nav -->
