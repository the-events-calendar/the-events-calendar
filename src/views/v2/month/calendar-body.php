<?php
/**
 * View: Month View - Calendar Body
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 * @var array $days An array containing the data for each day on the calendar grid, divided by day.
 *                  Shape `[ <Y-m-d> => [ ...<day_data> ] ]`.
 */
?>

<div class="tribe-events-calendar-month__body" role="rowgroup">

	<?php foreach ( array_chunk( $days, 7, true ) as $week ) : ?>

		<div class="tribe-events-calendar-month__week" role="row" data-js="tribe-events-month-grid-row">

			<?php foreach ( $week as $day_date => $day ) : ?>

				<?php $this->template( 'month/calendar-body/day', [ 'day_date' => $day_date, 'day' => $day ] ); ?>

			<?php endforeach; ?>

		</div>

	<?php endforeach; ?>

</div>
