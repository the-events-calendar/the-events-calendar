<?php
/**
 * Status for a Canceled Event.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/events-status/single/canceled-status.php
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

if ( 'canceled' !== $event->event_status ) {
	return;
}

?>
<div class="tribe-events-status-single__notice tribe-events-status-single__notice-canceled">
	<div class="tribe-events-status__text">

		<div class="tribe-events-status-single__notice-header tribe-events-status__text-red tribe-events-status__text-bold tribe-events-status__text-alert-icon">
			<?php echo esc_html( $status_labels->get_canceled_label() ); ?>
		</div>
		<?php if ( $event->event_status_reason ) { ?>
			<div class="tribe-events-status-single__notice-description">
				<?php echo wp_kses_post( $event->event_status_reason ); ?>
			</div>
		<?php } ?>
	</div>
</div>
