<?php 
/**
 * Week Grid All Day Event Loop
 * This file sets up the structure for the week grid all day event loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/week/loop-grid-allday.php
 * *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

tribe_events_week_set_loop_type( 'allday' );

?>
<div class="tribe-grid-allday clearfix">
	<div class="column first">
		<span><?php _e( 'All Day', 'tribe-events-calendar-pro' ); ?></span>
	</div>
	<div class="tribe-grid-content-wrap">
		<?php foreach( tribe_events_week_get_days() as $day_id => $day ) : tribe_events_week_setup_current_day( $day_id ); ?>
		<div title="<?php echo $day->date; ?>" class="column <?php tribe_events_week_column_classes(); ?>">
			<?php foreach( tribe_events_week_get_all_day_map() as $all_day_cols ) : tribe_events_week_setup_event( $all_day_cols[ $day_id ] ); ?>
				<?php tribe_get_template_part('week/single-event', 'allday'); ?>
			<?php endforeach; ?>
		</div><!-- allday column -->
		<?php endforeach; ?>
	</div><!-- .tribe-grid-content-wrap -->
</div><!-- .tribe-grid-allday -->