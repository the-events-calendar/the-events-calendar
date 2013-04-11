<?php 
/**
 * Calendar Single Day
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

	<div id="tribe-events-daynum-<?php echo $day['daynum'] ?>">

		<?php tribe_events_the_calendar_day_title() ?>

		<?php while ($day['events']->have_posts()) : $day['events']->the_post() ?>
			<?php tribe_get_template_part('calendar/single', 'event') ?>
		<?php endwhile; ?>
	</div>

<?php endif; ?>