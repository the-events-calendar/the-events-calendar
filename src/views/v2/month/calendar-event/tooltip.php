<?php
/**
 * View: Month View - Single Event Tooltip
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/calendar-events/views/v2/month/event/tooltip.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
// $event    = $this->get( 'event' );
// $event_id = $event->ID;
?>
<div class="tribe-events-calendar-month__calendar-event-tooltip">
	<?php $this->template( 'month/calendar-event/tooltip/featured-image', [ 'event' => $event ] ); ?>
	<?php $this->template( 'month/calendar-event/tooltip/description', [ 'event' => $event ] ); ?>
	<?php /* RSVP Ticket CTA goes here */ ?>
</div>
