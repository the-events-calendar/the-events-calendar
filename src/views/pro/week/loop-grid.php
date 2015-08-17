<?php
/**
 * Week View Grid Loop
 * This file sets up the structure for the week view grid loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/pro/week/loop-grid.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}?>

<div class="tribe-events-grid hfeed vcalendar clearfix">
	<div class="tribe-grid-header clearfix">
		<div class="column first">
			<span class="tribe-events-visuallyhidden"><?php esc_html_e( 'Hours', 'tribe-events-calendar-pro' ); ?></span>
		</div>
		<div class="tribe-grid-content-wrap">
			<?php while ( tribe_events_week_have_days() ) : tribe_events_week_the_day(); ?>
				<div title="<?php tribe_events_week_get_the_date(); ?>" class="column <?php echo esc_attr( tribe_events_week_day_header_classes() ); ?>">
					<?php echo tribe_events_week_day_header(); ?>
				</div><!-- header column -->
			<?php endwhile; ?>
		</div><!-- .tribe-grid-content-wrap -->
	</div><!-- .tribe-grid-header -->

	<?php if ( tribe_events_week_has_all_day_events() ) : ?>
		<?php tribe_get_template_part( 'pro/week/loop', 'grid-allday' ); ?>
	<?php endif; ?>

	<?php tribe_get_template_part( 'pro/week/loop', 'grid-hourly' ); ?>

</div><!-- .tribe-events-grid -->
