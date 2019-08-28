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
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */
$time_format = tribe_get_time_format();
?>

<div class="tribe-events-calendar-month__calendar-event-datetime">
	<?php if ( ! empty( $event->featured ) ) : ?>
		<em
			class="tribe-events-calendar-month__calendar-event-datetime-featured tribe-common-svgicon tribe-common-svgicon--featured"
			aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ) ?>"
			title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ) ?>"
		>
		</em>
	<?php endif; ?>
	<time datetime="<?php echo esc_attr( $event->dates->start->format( 'H:i' ) ) ?>">
		<?php echo esc_html( $event->dates->start->format( $time_format ) ) ?>
	</time>
	<span class="tribe-events-calendar-month__calendar-event-datetime-separator"> - </span>
	<time datetime="<?php echo esc_attr($event->dates->end->format( 'H:i' )) ?>">
		<?php echo esc_html( $event->dates->end->format( $time_format ) ) ?>
	</time>
	<?php // @todo @fe this should be moved to PRO. ?>
	<?php if ( ! empty( $event->recurring ) ) : ?>
		<em
			class="tribe-events-calendar-month__calendar-event-datetime-recurring tribe-common-svgicon tribe-common-svgicon--recurring"
			aria-label="<?php esc_attr_e( 'Recurring', 'the-events-calendar' ) ?>"
			title="<?php esc_attr_e( 'Recurring', 'the-events-calendar' ) ?>"
		>
		</em>
	<?php endif; ?>
</div>
