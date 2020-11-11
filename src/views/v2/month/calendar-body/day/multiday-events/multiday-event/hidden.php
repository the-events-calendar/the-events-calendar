<?php
/**
 * View: Month View - Multiday Event Hidden
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/multiday-events/multiday-event/hidden.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://m.tri.be/1aiy
 *
 * @since 5.1.1
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 *
 * @version 5.1.1
 */

?>
<div class="tribe-events-calendar-month__multiday-event-hidden">
	<?php $this->template( 'month/calendar-body/day/multiday-events/multiday-event/hidden/date', [ 'event' => $event ] ); ?>
	<?php $this->template( 'month/calendar-body/day/multiday-events/multiday-event/hidden/link', [ 'event' => $event ] ); ?>
</div>
