<?php
/**
 * Marker for a canceled event.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/events-status/canceled-label.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version TBD
 *
 * @var \WP_Post $event  The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */
namespace Tribe\Events\Event_Status;

if ( 'canceled' !== $event->event_status ) {
	return;
}

$label = apply_filters( 'tribe_ext_events_control_canceled_label', _x( 'Canceled', 'Canceled label', 'the-events-calendar' ), $event->ID, $event );

?>
<span class="tribe-events-status-label tribe-events-status-label--canceled">
	<?php echo esc_html( $label ); ?>
</span>
