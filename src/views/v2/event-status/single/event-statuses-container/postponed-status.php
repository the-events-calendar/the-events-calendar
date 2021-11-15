<?php
/**
 * Status for a Postponed Event.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/events-status/single/event-statuses-container/postponed-status.php
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
<div class="tribe-events-status-single tribe-events-status-single--postponed">
	<div class="tribe-events-status-single__header">

		<div class="tribe-events-status-single__header tribe-events-status-single__header--bold tribe-events-status-single__header--alert-icon">
			<?php echo esc_html( $status_labels->get_postponed_label() ); ?>
		</div>
		<?php if ( $event->event_status_reason ) { ?>
			<div class="tribe-events-status-single__description">
				<?php echo wp_kses_post( $event->event_status_reason ); ?>
			</div>
		<?php } ?>
	</div>
</div>
