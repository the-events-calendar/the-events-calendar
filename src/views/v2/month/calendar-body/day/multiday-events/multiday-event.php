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
 * @var bool $is_start_of_week Whether the current grid day being rendered is the first day of the week or not.
 *
 * @see tribe_get_event() For the format of the event object and its properties.
 *
 */
use Tribe__Date_Utils as Dates;

/*
 * To keep the calendar accessible, in the context of a week, we'll print the event only on either its first day
 * or the first day of the week.
 */
$should_display = $event->dates->start->format( 'Y-m-d' ) === $day_date
                  || $is_start_of_week;

$classes = [ 'tribe-events-calendar-month__multiday-event' ];

// @todo @fe move class configuration to template tag

if ( $event->featured ) {
	$classes[] = 'tribe-events-calendar-month__multiday-event--featured';
}

// If it starts today and this week, let's add the left border and set the width.
if ( $should_display ) {

	/*
	 * The "duration" here is how many days the event will take this week, not in total.
	 * The two values might be the same but they will differ for events that last more than one week.
	 */
	$classes[] = 'tribe-events-calendar-month__multiday-event--width-' . $event->this_week_duration;

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
	<article <?php tribe_classes( $classes ); ?> data-event-id="<?php echo esc_attr( $event->ID ); ?>">
		<time
			datetime="<?php echo esc_attr( $event->dates->start->format( Dates::DBDATEFORMAT ) ); ?>"
			class="tribe-common-a11y-visual-hide"
		>
			<?php echo esc_attr( $event->dates->start->format( Dates::DBDATEFORMAT ) ); ?>
		</time>
		<a href="<?php echo esc_url( $event->permalink ) ?>" class="tribe-events-calendar-month__multiday-event-inner">
			<?php if ( $event->featured ) : ?>
				<em
					class="tribe-events-calendar-month__multiday-event-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
					aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
					title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
				></em>
			<?php endif; ?>
			<h3 class="tribe-events-calendar-month__multiday-event-title tribe-common-h8">
				<?php echo wp_kses_post( $event->title ) ?>
			</h3>
		</a>
	</article>

</div>
