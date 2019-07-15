<?php
/**
 * View: Day Single Event Description
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/day/event/description.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */
$event    = $this->get( 'event' );
$event_id = $event->ID;
?>
<div class="tribe-events-calendar-day__event-description tribe-common-b2">
	<?php echo tribe_events_get_the_excerpt( $event, wp_kses_allowed_html( 'post' ) ); ?>
</div>
