<?php
/**
 * Reason container for a postponed event.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/events-status/status-label/postponed-label.php
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

if ( 'postponed' !== $event->event_status ) {
	return;
}

?>
<span class="tribe-events-status-label__text tribe-events-status-label__text--postponed">
	<?php echo esc_html( $status_labels->get_postponed_label() ); ?>
</span>
