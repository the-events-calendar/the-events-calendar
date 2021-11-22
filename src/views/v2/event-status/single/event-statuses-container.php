<?php
/**
 * Event Status Container.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/events-status/single/event-statuses-container.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 5.11.0
 *
 * @var \WP_Post      $event         The event post object with properties added by the `tribe_get_event` function.
 * @var Status_Labels $status_labels An instance of the statuses handler.
 *
 * @see     tribe_get_event() For the format of the event object.
 */
namespace Tribe\Events\Event_Status;

// Return if no event status.
if ( empty( $event->event_status ) ) {
	return;
}

?>
<div class="tribe-common-b2 tribe-events-status-single-notice">
	<?php $this->template( "event-status/single/event-statuses-container/{$event->event_status}-status", [ 'status_labels' => $status_labels ] ); ?>
</div>
