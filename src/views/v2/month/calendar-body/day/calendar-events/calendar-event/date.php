<?php
/**
 * View: Month View - Calendar Event Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body/day/calendar-events/calendar-event/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */
$event = $this->get( 'event' );
$is_featured  = isset( $event->featured ) && $event->featured;
$is_recurring = isset( $event->recurring ) && $event->recurring;
?>
<div class="tribe-events-calendar-month__calendar-event-datetime">
	<time datetime="14:00">2pm</time>
	<span class="tribe-events-calendar-month__calendar-event-datetime-separator"> - </span>
	<time datetime="18:00">6pm</time>
	<?php if ( $is_featured ) : ?>
		<em
			class="tribe-events-calendar-month__calendar-event-datetime-featured tribe-common-svgicon tribe-common-svgicon--featured"
			aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ) ?>"
			title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ) ?>"
		>
		</em>
	<?php endif; ?>
	<?php if ( $is_recurring ) : ?>
		<em
			class="tribe-events-calendar-month__calendar-event-datetime-recurring tribe-common-svgicon tribe-common-svgicon--recurring"
			aria-label="<?php esc_attr_e( 'Recurring', 'the-events-calendar' ) ?>"
			title="<?php esc_attr_e( 'Recurring', 'the-events-calendar' ) ?>"
		>
		</em>
	<?php endif; ?>
</div>
