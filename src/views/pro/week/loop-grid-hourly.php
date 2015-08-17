<?php
/**
 * Week View Grid Hourly Event Loop
 * This file sets up the structure for the week grid hourly event loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/pro/week/loop-grid-hourly.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$hours = tribe_events_week_get_hours();

?>
<div class="tribe-week-grid-wrapper tribe-scroller">
	<div class="scroller-content">
		<div class="tribe-week-grid-outer-wrap tribe-clearfix">
			<div class="tribe-week-grid-inner-wrap">
				<?php foreach ( $hours as $hour => $formatted_hour ) : ?>
					<div class="tribe-week-grid-block" data-hour="<?php echo esc_attr( $hour ); ?>">
						<div></div>
					</div>
				<?php endforeach; ?>
			</div><!-- .tribe-week-grid-inner-wrap -->
		</div><!-- .tribe-week-grid-outer-wrap -->

		<!-- Days of the week & hours & events -->
		<div class="tribe-grid-body clearfix">

			<?php // Hours ?>
			<div class="column tribe-week-grid-hours">
				<?php foreach ( $hours as $hour => $formatted_hour ) : ?>
					<div class="time-row-<?php echo esc_attr( date_i18n( 'gA', mktime( intval( $hour ) ) ) ); ?>"><?php echo esc_html_e( $formatted_hour ) ?></div>
				<?php endforeach; ?>
			</div><!-- tribe-week-grid-hours -->

			<div class="tribe-grid-content-wrap">
				<?php while ( tribe_events_week_have_days() ) : tribe_events_week_the_day(); ?>
					<div title="<?php tribe_events_week_get_the_date(); ?>" class="tribe-events-mobile-day column <?php tribe_events_week_column_classes(); ?>">
						<?php

						$day = tribe_events_week_get_current_day();

						foreach ( $day['hourly_events'] as $event ) : ?>
							<?php tribe_get_template_part( 'pro/week/single-event', 'hourly', array( 'event' => $event ) ); ?>
						<?php endforeach; ?>

					</div><!-- hourly column -->
				<?php endwhile; ?>
			</div><!-- .tribe-grid-content-wrap -->
		</div><!-- .tribe-grid-body -->
	</div>
</div><!-- .tribe-week-grid-wrapper -->
