<?php
/**
 * View: Month View - Calenar Event Tooltip Featured Image
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/featured-image.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */
$event = $this->get( 'event' );

if ( ! isset( $event->image ) ) { // @todo: use template tags for images here
	return;
}
?>
<div class="tribe-events-calendar-month__calendar-event-tooltip-featured-image-wrapper">
	<a
		href="<?php echo esc_url( $event->permalink ); ?>"
		title="<?php echo esc_attr( get_the_title( $event->ID ) ); ?>"
		rel="bookmark"
	>
		<div class="tribe-events-calendar-month__calendar-event-tooltip-featured-image tribe-common-c-image tribe-common-c-image--bg">
			<div
				class="tribe-common-c-image__bg"
				style="background-image: url('<?php echo esc_url( $event->image ); ?>');"
				role="img"
				aria-label="<?php echo esc_attr( get_the_title( $event->ID ) ); ?>"
			>
			</div>
		</div>
	</a>
</div>
