<?php
/**
 * Marker for a postponed event.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-control/postponed-label.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTICLE_LINK_HERE}
 *
 * @version TBD
 *
 * @var WP_Post $event  The event post object with properties added by the `tribe_get_event` function.
 * @var string  $status The event status.
 *
 * @see tribe_get_event() For the format of the event object.
 */
namespace Tribe\Events\Event_Status;

use WP_Post;

if ( 'postponed' !== $event->event_status ) {
	return;
}

$label = apply_filters( 'tribe_ext_events_control_postponed_label', _x( 'Postponed', 'Postponed label', 'tribe-ext-events-control' ), $event->ID, $event );

?>
<span class="tribe-ext-events-control-status-label tribe-ext-events-control-status-label--postponed">
	<?php echo esc_html( $label ); ?>
</span>
