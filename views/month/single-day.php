<?php
/**
 * Month View Single Day
 * This file contains one day in the month grid
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/month/single-day.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<?php

$day = tribe_events_get_current_month_day();

?>

<?php if ( $day['date'] != 'previous' && $day['date'] != 'next' ) : ?>

	<!-- Day Header -->
	<div id="tribe-events-daynum-<?php echo $day['daynum'] ?>">

		<?php if ( $day['total_events'] > 0 && tribe_events_is_view_enabled( 'day' ) ) { ?>
			<a href="<?php echo tribe_get_day_link( $day['date'] ) ?>"><?php echo $day['daynum'] ?></a>
		<?php } else { ?>
			<?php echo $day['daynum'] ?>
		<?php } ?>

	</div>

	<!-- Events List -->
	<?php while ($day['events']->have_posts()) : $day['events']->the_post() ?>
		<?php tribe_get_template_part('month/single', 'event') ?>
	<?php endwhile; ?>

	<!-- View More -->
	<?php if ( $day['view_more'] && tribe_events_is_view_enabled( 'day' ) ) : ?>
		<div class="tribe-events-viewmore">
			<a href="<?php echo $day['view_more'] ?>">View All <?php echo $day['total_events'] ?> &raquo;</a>
		</div>
	<?php endif ?>

<?php endif; ?>