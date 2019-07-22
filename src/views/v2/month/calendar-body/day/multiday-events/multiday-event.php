<?php
/**
 * View: Month View - Multiday Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body/day/multiday-events/multiday-event.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 * @var string $day_date The `Y-m-d` date of the day currently being displayed.
 * @var WP_Post $event An event post object with event-specific properties added from the the `tribe_get_event`
 *                     function.
 *
 * @see tribe_get_event() For the format of the event object and its properties.
 *
 */

$should_display = $event->dates->start->format( 'Y-m-d' ) === $day_date;

$classes = [ 'tribe-events-calendar-month__multiday-event' ];

// @todo @fe move class configuration to template tag

if ( $event->featured ) {
	$classes[] = 'tribe-events-calendar-month__multiday-event--featured';
}

// If it starts today and this week, let's add the left border and set the width.
if ( $should_display ) {

	// @todo @fe: check if it ends this week or not, and how to split the duration
	$classes[] = 'tribe-events-calendar-month__multiday-event--width-' . $event->multiday;

	// If it ends this week, let's add the start class (left border).
	if ( $event->starts_this_week ) {
		$classes[] = 'tribe-events-calendar-month__multiday-event--start';
	}

	// If it ends this week, let's add the end class (right border).
	if ( $event->ends_this_week ) {
		$classes[] = 'tribe-events-calendar-month__multiday-event--end';
	}

} else {
	$classes[] = 'tribe-events-calendar-month__multiday-event--hidden';
}

?>
<div class="tribe-events-calendar-month__multiday-event-wrapper">

	<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-event-id="<?php echo esc_attr( $event->ID ); ?>">
		<time datetime="the-date-and-or-duration" class="tribe-common-a11y-visual-hide">The date and duration</time>
		<a href="#" class="tribe-events-calendar-month__multiday-event-inner">
			<?php if ( $event->featured ) : ?>
				<em
					class="tribe-events-calendar-month__multiday-event-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
					aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
					title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
				></em>
			<?php endif; ?>
			<h3 class="tribe-events-calendar-month__multiday-event-title tribe-common-h8">
				<?php echo esc_html( get_the_title( $event->ID ) ) ?>
			</h3>
		</a>
	</article>

</div>
