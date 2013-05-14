<?php 
/**
 * Calendar Single Day
 * This file contains one day in the calendar grid
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/month/single-day.php
 * *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php 

$day = tribe_events_get_current_month_day();

 ?>

<?php if ($day['date'] != 'previous' && $day['date'] != 'next') : ?>

	<!-- Day Header -->
	<div id="tribe-events-daynum-<?php echo $day['daynum'] ?>">

		<?php tribe_events_the_month_day_header() ?>

	</div>

	<!-- Events List -->
	<?php while ($day['events']->have_posts()) : $day['events']->the_post() ?>
		<?php tribe_get_template_part('month/single', 'event') ?>
	<?php endwhile; ?>

	<!-- View More -->
	<?php if ($day['view_more']) : ?>
		<div class="tribe-events-viewmore">
			<a href="<?php echo $day['view_more'] ?>">View All <?php echo $day['total_events'] ?> &raquo;</a>
		</div>
	<?php endif ?>

<?php endif; ?>