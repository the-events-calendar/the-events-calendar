<?php
/**
 * Week View Nav
 * This file loads the week view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/pro/week/nav.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<h3 class="tribe-events-visuallyhidden"><?php esc_html_e( 'Week Navigation', 'tribe-events-calendar-pro' ); ?></h3>
<ul class="tribe-events-sub-nav">
	<li class="tribe-events-nav-previous">
		<?php echo tribe_events_week_previous_link() ?>
	</li><!-- .tribe-events-nav-previous -->
	<li class="tribe-events-nav-next">
		<?php echo tribe_events_week_next_link() ?>
	</li><!-- .tribe-events-nav-next -->
</ul><!-- .tribe-events-sub-nav -->
