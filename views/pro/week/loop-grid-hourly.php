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
$hour_format = apply_filters( 'tribe_events_pro_week_hour_format', get_option( 'time_format', 'gA' ) );
?>
<div class="tribe-week-grid-wrapper">
	<div class="tribe-week-grid-outer-wrap tribe-clearfix">
		<div class="tribe-week-grid-inner-wrap">
			<?php
			$multiday_cutoff    = explode( ':', tribe_get_option( 'multiDayCutoff', '00:00' ) );
			$multiday_cutoff[0] = (int) ltrim( $multiday_cutoff[0], '0' );

			for ( $hour = $multiday_cutoff[0]; $hour <= $multiday_cutoff[0] + 23; $hour ++ ) : //If hour is greater than 24, then wrap back around to 0
				if ( $hour >= 24 ) {
					$display_hour = $hour % 24;
				} else {
					$display_hour = $hour;
				}
				?>
				<div class="tribe-week-grid-block" data-hour="<?php echo $display_hour; ?>">
					<div></div>
				</div>
			<?php endfor; ?>
		</div><!-- .tribe-week-grid-inner-wrap -->
	</div><!-- .tribe-week-grid-outer-wrap -->

	<!-- Days of the week & hours & events -->
	<div class="tribe-grid-body clearfix">

		<?php // Hours ?>
		<div class="column tribe-week-grid-hours">
			<?php for ( $hour = $multiday_cutoff[0]; $hour <= $multiday_cutoff[0] + 23; $hour ++ ) : ?>
				<div class="time-row-<?php echo date_i18n( 'gA', mktime( $hour ) ); ?>"><?php echo date_i18n( $hour_format, strtotime( ( $hour % 24 ) . ':00' ) ); ?></div>
			<?php endfor; ?>
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
