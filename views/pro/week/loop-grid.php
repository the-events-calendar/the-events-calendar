<?php
/**
 * Week View Grid Loop
 * This file sets up the structure for the week view grid loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/week/loop-grid.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<div class="tribe-events-grid hfeed vcalendar clearfix">
	<div class="tribe-grid-header clearfix">
		<div class="column first">
			<span class="tribe-events-visuallyhidden"><?php _e( 'Hours', 'tribe-events-calendar-pro' ); ?></span>
		</div>
		<div class="tribe-grid-content-wrap">
			<?php while ( tribe_events_week_have_days() ) : tribe_events_week_the_day(); ?>
				<div title="<?php tribe_events_week_get_the_date(); ?>" class="column <?php echo tribe_events_week_is_current_today() ? 'tribe-week-today' : ''; ?>">
					<?php if ( tribe_events_is_view_enabled( 'day' ) && tribe_events_current_week_day_has_events() ): ?>
						<a href="<?php echo tribe_get_day_link( tribe_events_week_get_the_date( false ) ); ?>" rel="bookmark"><?php tribe_events_week_get_the_day_display(); ?></a>
					<?php else: ?>
						<?php tribe_events_week_get_the_day_display(); ?>
					<?php endif ?>
				</div><!-- header column -->
			<?php endwhile; ?>
		</div><!-- .tribe-grid-content-wrap -->
	</div><!-- .tribe-grid-header -->
	<?php tribe_get_template_part( 'pro/week/loop', 'grid-allday' ); ?>
	<?php tribe_get_template_part( 'pro/week/loop', 'grid-hourly' ); ?>
</div><!-- .tribe-events-grid -->