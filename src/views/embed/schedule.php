<?php
/**
 * Embed Schedule Details Meta Template
 *
 * The schedule details template for the embed view.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/embed/schedule.php
 *
 * @version 4.2
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<div class="tribe-event-schedule-details">
	<?php echo tribe_events_event_schedule_details() ?>
</div>
