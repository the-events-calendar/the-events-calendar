<?php
/**
 * View: Month View - Calendar Event Tooltip
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/calendar-events/views/v2/month/event/tooltip.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.3
 *
 */
$event    = $this->get( 'event' );
$event_id = $event->ID;
?>
<div class="tribe-events-tooltip__content">
	<div
		id="tooltip_content-<?php echo esc_attr( $event_id ); ?>"
		class="tribe-events-calendar-month__calendar-event-tooltip"
		role="tooltip"
	>
		<?php $this->template( 'month/calendar-event/tooltip/featured-image', [ 'event' => $event ] ); ?>
		<?php $this->template( 'month/calendar-event/tooltip/description', [ 'event' => $event ] ); ?>
		<?php $this->template( 'month/calendar-event/tooltip/cta', [ 'event' => $event ] ); ?>
	</div>
</div>
