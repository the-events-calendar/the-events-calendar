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
	<div class="tribe-events-calendar-month__calendar-event-tooltip-featured-image tribe-common-c-image tribe-common-c-image--bg">
		<a
			href="#"
			title="<?php echo esc_attr( $event->title ); ?>"
			rel="bookmark"
		>
			<div
				class="tribe-common-c-image__bg"
				style="background-image: url('<?php echo esc_attr( $event->image ); ?>');"
				role="img"
				aria-label="alt text here"
			>
			</div>
		</a>
	</div>
</div>
