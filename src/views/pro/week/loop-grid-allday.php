<?php
/**
 * Week View Grid All Day Event Loop
 * This file sets up the structure for the week grid all day event loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/pro/week/loop-grid-allday.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<div class="tribe-grid-allday clearfix">
	<div class="column first">
		<span><?php esc_html_e( 'All Day', 'tribe-events-calendar-pro' ); ?></span>
	</div>
	<div class="tribe-grid-content-wrap">
		<?php while ( tribe_events_week_have_days() ) : tribe_events_week_the_day(); ?>
		<div title="<?php tribe_events_week_get_the_date(); ?>" class="<?php tribe_events_week_column_classes(); ?>">
			<?php

			$day = tribe_events_week_get_current_day();

			if ( empty( $day['all_day_events'] ) ) : ?>
				<div class="tribe-event-placeholder " data-event-id="">&nbsp;</div>
			<?php else : ?>
				<?php foreach ( $day['all_day_events'] as $event ) : ?>
					<?php tribe_get_template_part( 'pro/week/single-event', 'allday', array( 'event' => $event ) ); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div><!-- allday column -->
		<?php endwhile; ?>
	</div><!-- .tribe-grid-content-wrap -->
</div><!-- .tribe-grid-allday -->
