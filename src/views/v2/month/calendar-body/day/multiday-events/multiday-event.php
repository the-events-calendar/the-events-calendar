<?php
/**
 * View: Month View - Multiday Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/multiday-events/multiday-event.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 5.0.0
 * @since 5.1.1 Move icons into separate templates.
 * @since 5.1.1 Split this template into more granular sub-templates.
 *
 * @var string $day_date        The `Y-m-d` date of the day currently being displayed.
 * @var string $today_date      Today's date in the `Y-m-d` format.
 * @var string $grid_start_date The `Y-m-d` date of the day where the grid starts.
 * @var WP_Post $event          An event post object with event-specific properties added from the the `tribe_get_event`
 *                              function.
 * @var bool $is_start_of_week  Whether the current grid day being rendered is the first day of the week or not.
 *
 * @see tribe_get_event() For the format of the event object and its properties.
 *
 * @version 5.1.1
 */

$classes = \Tribe\Events\Views\V2\month_multiday_classes( $event, $day_date, $is_start_of_week, $today_date );

$start_display_date = $event->dates->start_display->format( 'Y-m-d' );

?>
<div class="tribe-events-calendar-month__multiday-event-wrapper">
	<article <?php tec_classes( $classes ); ?> data-event-id="<?php echo esc_attr( $event->ID ); ?>">
		<?php $this->template( 'month/calendar-body/day/multiday-events/multiday-event/hidden', [ 'event' => $event ] ); ?>
		<?php
		$this->template(
			'month/calendar-body/day/multiday-events/multiday-event/bar',
			[
				'event'            => $event,
				'grid_start_date'  => $grid_start_date,
				'is_start_of_week' => $is_start_of_week,
				'day_date'         => $day_date,
			]
		);
		// If the event didn't start today, we're done.
		if (
			( $start_display_date === $day_date )
			|| ( $start_display_date < $grid_start_date && $grid_start_date === $day_date )
		) {
			$this->template( 'month/calendar-body/day/calendar-events/calendar-event/tooltip', [ 'event' => $event ] );
		}
		?>
	</article>
</div>
