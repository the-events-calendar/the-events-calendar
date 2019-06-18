<?php
/**
 * View: Month Event Multiday
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/event-multiday.php
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
$day_number = $this->get( 'day' );

$classes = [ 'tribe-events-calendar-month__event-multiday' ];

// Check if it's featured.
if ( $is_featured = isset( $event->featured ) && $event->featured ) { // @todo: later use tribe( 'tec.featured_events' )->is_featured( $event_id ) or similar
	$classes[] = 'tribe-events-calendar-month__event-multiday--featured';
}

// If it starts today and this week, let's add the left border and set the width
if ( $should_display = $event->start_date == $day_number ) { // @todo:later we can check mm/dd or even year

	// @todo: check if it ends this week or not, and how to split the duration
	$classes[] = 'tribe-events-calendar-month__event-multiday--width-' . $event->duration;

	// if it ends this week, let's add the start class (left border)
	if ( isset( $event->start_this_week ) && $event->start_this_week ) {
		$classes[] = 'tribe-events-calendar-month__event-multiday--start';
	}

	// if it ends this week, let's add the end class (right border)
	$end_this_week = isset( $event->end_this_week ) && $event->end_this_week;
	if ( $end_this_week ) {
		$classes[] = 'tribe-events-calendar-month__event-multiday--end';
	}

} else {
	$classes[] = 'tribe-events-calendar-month__event-multiday--hidden';
}

?>
<div class="tribe-events-calendar-month__event-multiday-wrapper">

	<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-event-id="<?php echo esc_attr( $event->ID ); ?>">
		<time datetime="the-date-and-or-duration" class="tribe-common-a11y-visual-hide">The date and duration</time>
		<a href="#" class="tribe-events-calendar-month__event-multiday-inner">
			<?php if ( $is_featured ) : ?>
				<em
					class="tribe-events-calendar-month__event-multiday-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
					aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
					title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
				></em>
			<?php endif; ?>
			<h3 class="tribe-events-calendar-month__event-multiday-title tribe-common-h8">
				<?php echo $event->title; ?>
			</h3>
		</a>
	</article>

</div>
