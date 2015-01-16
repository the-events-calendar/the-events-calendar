<?php
/**
 * Week View Grid Hourly Event Loop
 * This file sets up the structure for the week grid hourly event loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/week/loop-grid-hourly.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

tribe_events_week_set_loop_type( 'hourly' );

$multiday_cutoff = tribe_events_pro_get_multiday_cutoff();

?>

<div class="tribe-week-grid-wrapper">
	<div class="tribe-week-grid-outer-wrap tribe-clearfix">
		<div class="tribe-week-grid-inner-wrap">
			<?php tribe_events_pro_setup_week_grid_blocks( $multiday_cutoff ); ?>
		</div><!-- .tribe-week-grid-inner-wrap -->
	</div><!-- .tribe-week-grid-outer-wrap -->

	<!-- Days of the week & hours & events -->
	<div class="tribe-grid-body clearfix">

		<?php // Hours ?>
		<div class="column tribe-week-grid-hours">
			<?php tribe_events_pro_setup_week_grid_hours( $multiday_cutoff ); ?>
		</div>
		<!-- tribe-week-grid-hours -->
		<?php // Content ?>
		<div class="tribe-grid-content-wrap">
			<?php while ( tribe_events_week_have_days() ) : tribe_events_week_the_day(); ?>
				
				<div title="<?php tribe_events_week_get_the_date(); ?>" class="tribe-events-mobile-day column <?php tribe_events_week_column_classes(); ?>">
					<?php foreach ( tribe_events_week_get_hourly() as $event ) : if ( tribe_events_week_setup_event( $event ) ) : ?>
						<?php tribe_get_template_part( 'pro/week/single-event', 'hourly' ); ?>
					<?php endif; endforeach; ?>
				</div><!-- hourly column -->
			
			<?php endwhile; ?>
		</div>
		<!-- .tribe-grid-content-wrap -->
	</div>
	<!-- .tribe-grid-body -->
</div><!-- .tribe-week-grid-wrapper -->
