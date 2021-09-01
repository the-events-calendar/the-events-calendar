<?php
/**
 * View: Month View - Day Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.9.0
 *
 * @var string $today_date Today's date in the `Y-m-d` format.
 * @var string $day_date The current day date, in the `Y-m-d` format.
 * @var array $day The current day data.{
 *          @type string $date The day date, in the `Y-m-d` format.
 *          @type bool $is_start_of_week Whether the current day is the first day of the week or not.
 *          @type string $year_number The day year number, e.g. `2019`.
 *          @type string $month_number The day year number, e.g. `6` for June.
 *          @type string $day_number The day number in the month with leading 0, e.g. `11` for June 11th.
 *          @type string $day_url The day url, e.g. `http://yoursite.com/events/2019-06-11/`.
 *          @type int $found_events The total number of events in the day including the ones not fetched due to the per
 *                                  page limit, including the multi-day ones.
 *          @type int $more_events The number of events not showing in the day.
 *          @type array $events The non multi-day events on this day. The format of each event is the one returned by
 *                    the `tribe_get_event` function. Does not include the below events.
 *          @type array $featured_events The featured events on this day. The format of each event is the one returned
 *                    by the `tribe_get_event` function.
 *          @type array $multiday_events The stack of multi-day events on this day. The stack is a mix of event post
 *                              objects, the format is the one returned from the `tribe_get_event` function, and
 *                              spacers. Spacers are falsy values indicating an empty space in the multi-day stack for
 *                              the day
 *      }
 */

$day_button_classes = [ 'tribe-events-calendar-month__day-cell', 'tribe-events-calendar-month__day-cell--mobile' ];
$day_number = $day['day_number'];
$expanded = 'false';

$day_id = 'tribe-events-calendar-day-' . $day_date;

if ( $today_date === $day_date ) {
	$expanded = 'true';
	$day_button_classes[] = 'tribe-events-calendar-month__day-cell--selected';
}

// Only add id if events exist on the day.
$mobile_day_id = 'tribe-events-calendar-mobile-day-' . $day['year_number'] . '-' . $day['month_number'] . '-' . $day['day_number'];

$events_label_singular = tribe_get_event_label_singular_lowercase();
$events_label_plural   = tribe_get_event_label_plural_lowercase();

$num_events_label = sprintf(
	/* translators: %1$s: number of events, %2$s: event (singular), %3$s: events (plural). */
	_n( '%1$s %2$s', '%1$s %3$s', $day['found_events'], 'the-events-calendar' ),
	number_format_i18n( $day['found_events'] ),
	$events_label_singular,
	$events_label_plural
);
?>

<button
	aria-expanded="<?php echo esc_attr( $expanded ); ?>"
	aria-controls="<?php echo esc_attr( $mobile_day_id ); ?>"
	<?php tribe_classes( $day_button_classes ); ?>
	data-js="tribe-events-calendar-month-day-cell-mobile"
	tabindex="-1"
>
	<h3 class="tribe-events-calendar-month__day-date tribe-common-h6 tribe-common-h--alt">
		<span class="tribe-common-a11y-visual-hide">
			<?php echo esc_html( $num_events_label ); ?>,
		</span>
		<time
			class="tribe-events-calendar-month__day-date-daynum"
			datetime="<?php echo esc_attr( $day['date'] ); ?>"
		>
			<?php echo esc_html( $day_number ); ?>
		</time>
	</h3>
	<?php $this->template( 'month/calendar-body/day/date-extras', [ 'day_date' => $day_date, 'day' => $day ] ); ?>
</button>
