<?php
/**
 * View: Month View - Multiday Event Hidden
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/multiday-events/multiday-event/hidden.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 * @var WP_Post $event An event post object with event-specific properties added from the the `tribe_get_event`
 *
 * @see tribe_get_event() For the format of the event object and its properties.
 *
 */



?>
<div class="tribe-events-calendar-month__multiday-event-hidden">
	<?php $this->template( 'month/calendar-body/day/multiday-events/multiday-event/date', [ 'event' => $event ] ); ?>
	<a
		href="<?php echo esc_url( $event->permalink ); ?>"
		class="tribe-events-calendar-month__multiday-event-hidden-link"
		data-js="tribe-events-tooltip"
		data-tooltip-content="#tribe-events-tooltip-content-<?php echo esc_attr( $event->ID ); ?>"
		aria-describedby="tribe-events-tooltip-content-<?php echo esc_attr( $event->ID ); ?>"
	>
		<?php $this->template( 'month/calendar-body/day/multiday-events/multiday-event/hidden/featured', [ 'event' => $event ] ); ?>
		<h3 class="tribe-events-calendar-month__multiday-event-hidden-title tribe-common-h8">
			<?php
			// phpcs:ignore
			echo $event->title;
			?>
		</h3>
	</a>
</div>