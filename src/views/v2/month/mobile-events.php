<?php
/**
 * View: Month View Mobile Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/mobile-events.php
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
<section class="tribe-events-calendar-month-mobile-events" data-js="tribe-events-calendar-month-mobile-events">

	<?php foreach ( $days as $day_date => $day ) : ?>

		<?php $this->template( 'month/mobile-events/mobile-day', [ 'day' => $day, 'day_date' => $day_date ] ); ?>

	<?php endforeach; ?>

	<?php $this->template( 'month/mobile-events/nav' ); ?>

</section>
