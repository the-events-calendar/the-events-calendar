<?php
/**
 * View: Month View - Calendar Event Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/calendar-events/calendar-event/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.13
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 * @var obj     $date_formats Object containing the date formats.
 *
 * @see tribe_get_event() For the format of the event object.
 */

$time_format      = tribe_get_time_format();
$display_end_date = $event->dates->start_display->format( 'H:i' ) !== $event->dates->end_display->format( 'H:i' );
?>
<div class="tribe-events-calendar-month__calendar-event-datetime">
	<?php if ( ! empty( $event->featured ) ) : ?>
		<em
			class="tribe-events-calendar-month__calendar-event-datetime-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
			aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ) ?>"
			title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ) ?>"
		>
		</em>
	<?php endif; ?>
	<time datetime="<?php echo esc_attr( $event->dates->start_display->format( 'H:i' ) ); ?>">
		<?php echo esc_html( $event->dates->start_display->format( $time_format ) ); ?>
	</time>
	<?php if ( $display_end_date ) : ?>
		<span class="tribe-events-calendar-month__calendar-event-datetime-separator"><?php echo esc_html( $date_formats->time_range_separator ); ?></span>
		<time datetime="<?php echo esc_attr($event->dates->end_display->format( 'H:i' ) ); ?>">
			<?php echo esc_html( $event->dates->end_display->format( $time_format ) ); ?>
		</time>
	<?php endif; ?>
	<?php $this->template( 'month/calendar-body/day/calendar-events/calendar-event/date/meta', [ 'event' => $event ] ); ?>
</div>
