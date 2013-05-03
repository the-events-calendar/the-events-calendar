<?php 
/**
 * Week Grid Loop
 * This file sets up the structure for the week grid loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/week/loop-grid.php
 * *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
// echo '<pre>';
// print_r( Tribe_Events_Pro_Week_Template::$events->hourly );
// echo '</pre>';

?>
<div class="tribe-events-grid hfeed vcalendar clearfix">
	<div class="tribe-grid-header clearfix">
		<div class="column first">
			<span class="tribe-events-visuallyhidden"><?php _e( 'Hours', 'tribe-events-calendar-pro' ); ?></span>
		</div>
		<div class="tribe-grid-content-wrap">
			<?php foreach( tribe_events_week_get_days() as $day ) : ?>
			<div title="<?php echo $day->date; ?>" class="column <?php echo ($day->today) ? 'tribe-week-today' : ''; ?>">
				<a href="<?php echo tribe_get_day_permalink( $day->date ); ?>" rel="bookmark"><?php echo $day->display; ?></a>
			</div><!-- header column -->
			<?php endforeach; ?>
		</div><!-- .tribe-grid-content-wrap -->
	</div><!-- .tribe-grid-header -->
	<?php tribe_get_template_part('week/loop', 'grid-allday'); ?>
	<?php tribe_get_template_part('week/loop', 'grid-hourly'); ?>
</div><!-- .tribe-events-grid -->
