<?php
/**
 * Status for a Postponed Event.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/events-status/single/postponed-status.php
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

if ( 'postponed' !== $event->event_status ) {
	return;
}

if ( empty( $event->event_status_reason ) ) {
	return;
}

?>
<div class="tribe-events-status-single__notice tribe-events-status-single__notice-postponed">
	<div class="tribe-events-status-text">

		<div class="tribe-events-status-single__notice-header tribe-events-status-text--red tribe-events-status-text--bold tribe-events-status-text--alert-icon">
			<?php echo esc_html( $status_labels->get_postponed_label() ); ?>
		</div>

		<div class="tribe-events-status-single__notice-description">
			<?php echo wp_kses_post( $event->event_status_reason ); ?>
		</div>
	</div>
</div>
