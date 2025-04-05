<?php
/**
 * View: Month View - Day
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.9.0
 *
 * @since 5.9.0 Divided the day template into two sub-templates, for date and cell, since it allows for better customization.
 * @since 5.3.0 Introduced.
 *
 * @var string       $today_date   Today's date in the `Y-m-d` format.
 * @var string       $day_date     The current day date, in the `Y-m-d` format.
 * @var DateTime     $request_date The request date for the view.
 * @var array<mixed> $day          The current day data. {
 *          @type string $date             The day date, in the `Y-m-d` format.
 *          @type bool   $is_start_of_week Whether the current day is the first day of the week or not.
 *          @type string $year_number      The day year number, e.g. `2019`.
 *          @type string $month_number     The day year number, e.g. `6` for June.
 *          @type string $day_number       The day number in the month with leading 0, e.g. `11` for June 11th.
 *          @type string $day_url          The day url, e.g. `http://yoursite.com/events/2019-06-11/`.
 *          @type int    $found_events     The total number of events in the day including the ones not fetched due to
 *                                             the per page limit, including the multi-day ones.
 *          @type int    $more_events      The number of events not showing in the day.
 *          @type array  $events           The non multi-day events on this day. The format of each event is the one
 *                                             returned by the `tribe_get_event` function. Does not include the below events.
 *          @type array  $featured_events  The featured events on this day. The format of each event is the one returned
 *                                             by the `tribe_get_event` function.
 *          @type array  $multiday_events  The stack of multi-day events on this day. The stack is a mix of event post
 *                                             objects, the format is the one returned from the `tribe_get_event` function,
 *                                             and spacers. Spacers are falsy values indicating an empty space in the
 *                                             multi-day stack for the day
 *      }
 */
// Some static implementations might not have a $request_date - use $today_date.
if ( empty( $request_date ) ) {
	$request_date = Tribe__Date_Utils::build_date_object( $today_date );
}

$day_classes = \Tribe\Events\Views\V2\month_day_classes( $day, $day_date, $request_date, $today_date );
$day_id = 'tribe-events-calendar-day-' . $day_date;
?>

<div
	<?php tribe_classes( $day_classes ); ?>
	role="gridcell"
	aria-labelledby="<?php echo esc_attr( $day_id ); ?>"
	data-js="tribe-events-month-grid-cell"
>
	<?php $this->template( 'month/calendar-body/day/date', [ 'day_date' => $day_date, 'day' => $day ] ); ?>
	<?php $this->template( 'month/calendar-body/day/cell', [ 'day_date' => $day_date, 'day' => $day ] ); ?>
</div>
