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
$classes_inner = [ 'tribe-events-calendar-month__event-multiday-inner' ];

// Check if it's featured.
if ( $is_featured = isset( $event->featured ) && $event->featured ) { // @todo: later use tribe( 'tec.featured_events' )->is_featured( $event_id ) or similar
	$classes[] = 'tribe-events-calendar-month__event-multiday--featured';
}

// If it starts today and this week, let's add the left border and set the width
if ( $starts_today = $event->start_date == $day_number ) { // @todo:later we can check mm/dd or even year

	// @todo: check if it ends this week or not, in order to split the duration
	$classes[] = 'tribe-events-calendar-month__event-multiday-width-' . $event->duration;

	if ( isset( $event->start_this_week ) && $event->start_this_week ) {
		// adding the left border because it starts today
		$classes_inner[] = 'tribe-events-calendar-month__event-multiday-inner--border-left';
		$classes[] = 'tribe-events-calendar-month__event-multiday--start';
	}

} else {
	$classes[] = 'tribe-events-calendar-month__event-multiday--hidden';
}

// if it ends this week, let's add the right border
$end_this_week = isset( $event->end_this_week ) && $event->end_this_week;
if ( $end_this_week ) {
	$classes_inner[] = 'tribe-events-calendar-month__event-multiday-inner--border-right';
	$classes[] = 'tribe-events-calendar-month__event-multiday--end';
}
?>
<div class="tribe-events-calendar-month__multiday-wrapper">

	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
		<time datetime="the-date-and-or-duration" class="tribe-common-a11y-visual-hide">The date and duration</time>
		<a class="<?php echo esc_attr( implode( ' ', $classes_inner ) ); ?>">
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
	</div>

</div>