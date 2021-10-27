<?php
/**
 * Event Status Container.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/events-status/single/post-statuses.php
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

// Return if no event status.
if ( empty( $event->event_status ) ) {
	return;
}

// Return if no event status reason.
if ( empty( $event->event_status_reason ) ) {
	return;
}

?>
<div class="tribe-common-b2 tribe-events-status-single-container">
	<?php $this->template( "single/{$event->event_status}-status" ); ?>
</div>
