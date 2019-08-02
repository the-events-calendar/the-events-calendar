<?php
/**
 * View: Month View - Calendar Event Tooltip
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */
?>
<div
	class="tribe-events-calendar-month__calendar-event-tooltip"
	data-js="tribe-events-tooltip-content"
	role="tooltip"
>
	<div id="tribe-events-tooltip-content-<?php echo esc_attr( $event->ID ); ?>">
		<?php $this->template( 'month/calendar-body/day/calendar-events/calendar-event/tooltip/featured-image', [ 'event' => $event ] ); ?>
		<?php $this->template( 'month/calendar-body/day/calendar-events/calendar-event/tooltip/description', [ 'event' => $event ] ); ?>
		<?php $this->template( 'month/calendar-body/day/calendar-events/calendar-event/tooltip/cta', [ 'event' => $event ] ); ?>
	</div>
</div>
