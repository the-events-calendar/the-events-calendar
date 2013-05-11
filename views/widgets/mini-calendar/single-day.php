<?php 
/**
 * Mini Calendar Single Day
 * This file contains one day in the calendar grid
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/calendar/single-day.php
 * *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php 

$day = tribe_events_get_current_calendar_day();

 ?>

<?php if ($day['date'] != 'previous' && $day['date'] != 'next') : ?>

	<div id="daynum-<?php echo $day['daynum'] ?>">
		<?php 
			if ( $day['total_events'] > 0 ) : ?>
				<?php tribe_events_the_mini_calendar_day_link(); ?>
			<?php endif; ?>
	</div>

<?php endif; ?>