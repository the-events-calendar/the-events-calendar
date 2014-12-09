<?php
/**
 * Mini Calendar List Loop
 * This file sets up the structure for the list loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/widgets/mini-calendar/list.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<div class="tribe-mini-calendar-list-wrapper">
	<div class="tribe-events-loop hfeed vcalendar">

		<?php while ( have_posts() ) : the_post(); ?>
			<?php do_action( 'tribe_events_mini_cal_list_inside_before_loop' ); ?>

			<!-- Event  -->
			<div class="<?php tribe_events_event_classes() ?>">
				<?php tribe_get_template_part( 'pro/widgets/modules/single-event' ) ?>
			</div><!-- .hentry .vevent -->

			<?php do_action( 'tribe_events_mini_cal_list_inside_after_loop' ); ?>
		<?php endwhile; ?>

	</div><!-- .tribe-events-loop -->
</div> <!-- .tribe-mini-calendar-list-wrapper -->