<?php
/**
 * Mini Calendar Single Day
 * This file contains one day in the mini calendar grid
 *
	 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/pro/widgets/mini-calendar/single-day.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<?php

$day = tribe_events_get_current_month_day();

?>

<?php if ( $day['date'] != 'previous' && $day['date'] != 'next' ) : ?>

	<div id="daynum-<?php echo esc_attr( $day['daynum'] ); ?>">
		<?php tribe_events_the_mini_calendar_day_link(); ?>
	</div>

<?php
endif;
