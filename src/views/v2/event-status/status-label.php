<?php

/**
 * Event Status label for event.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/events-status/status-label.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version TBD
 *
 * @var \WP_Post      $event         The event post object with properties added by the `tribe_get_event` function.
 * @var Status_Labels $status_labels An instance of the statuses handler.
 *
 * @see     tribe_get_event() For the format of the event object.
 */
namespace Tribe\Events\Event_Status;

$h = 1;

$b = $h;

if ( ! in_array( $event->event_status, [ 'canceled', 'postponed' ] ) ) {
	return;
}

?>
<span class="tribe-events-status__label-wrapper">
	<?php $this->template( "event-status/{$event->event_status}-label" ); ?>
</span>
