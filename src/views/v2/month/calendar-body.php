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
 * @version 4.9.4
 *
 */

/**
 * Adding this as a temprorary data structure.
 * @todo: This array should contain the month with real events.
 */
$month = apply_filters( 'tribe_events_views_v2_month_demo_data', [] );

?>
<div class="tribe-events-calendar-month__body" role="rowgroup">

	<?php // @todo: replace this with the actual month days. Using these for(s) for presentation purposes. ?>
	<?php for ( $week = 0; $week < 4; $week++ ) : ?>

		<div class="tribe-events-calendar-month__week" role="row" data-js="tribe-events-month-grid-row">

			<?php for ( $day = 0; $day < 7; $day++ ) : ?>

				<?php $this->template( 'month/calendar-body/day', [ 'day' => $day, 'week' => $week, 'month' => $month ] ); ?>

			<?php endfor; ?>

		</div>

	<?php endfor; ?>

</div>
