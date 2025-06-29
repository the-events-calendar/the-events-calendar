<?php
/**
 * View: Month View - Calendar Body
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.9.8
 *
 * @var array $days An array containing the data for each day on the calendar grid, divided by day.
 *                  Shape `[ <Y-m-d> => [ ...<day_data> ] ]`.
 */

?>

<tbody class="tribe-events-calendar-month__body">
	<?php foreach ( array_chunk( $days, 7, true ) as $week ) : ?>
		<tr class="tribe-events-calendar-month__week" data-js="tribe-events-month-grid-row">
			<?php foreach ( $week as $day_date => $day ) : ?>

				<?php $this->template( 'month/calendar-body/day', [ 'day_date' => $day_date, 'day' => $day ] ); ?>

			<?php endforeach; ?>
		</tr>
	<?php endforeach; ?>
</tbody>
