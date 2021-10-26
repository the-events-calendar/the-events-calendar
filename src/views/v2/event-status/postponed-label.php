<?php
/**
 * Marker for a postponed event.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/events-status/postponed-label.php
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


if ( 'postponed' !== $event->event_status ) {
	return;
}

$label = apply_filters( 'tribe_ext_events_control_postponed_label', _x( 'Postponed', 'Postponed label', 'the-events-calendar' ), $event->ID, $event );

?>
<span class="tribe-events-status-label tribe-events-status-label--postponed">
	<?php echo esc_html( $label ); ?>
</span>
