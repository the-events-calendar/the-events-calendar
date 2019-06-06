<?php
/**
 * View: Month View
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.3
 *
 */

$events = $this->get( 'events' );

/*
	Adding this as a temprorary data structure.
	@todo: This array should contain the month with real events.
*/
$month = apply_filters( 'tribe_events_views_v2_month_demo_data', [] );

?>

<div class="tribe-common-l-container tribe-events-l-container">

	<?php $this->template( 'events-bar' ); ?>

	<?php $this->template( 'top-bar' ); ?>

	<div class="tribe-events-calendar-month" role="grid" aria-labelledby="tribe-calendar-header" aria-readonly="true">

		<?php $this->template( 'month/grid-header' ); ?>

		<div class="tribe-events-calendar-month__body" role="rowgroup">

			<?php // @todo: replace this with the actual month days. Using these for(s) for presentation purposes. ?>
			<?php for ( $week = 0; $week < 4; $week++ ) : ?>

				<div class="tribe-events-calendar-month__week" role="row">

					<?php for ( $day = 0; $day < 7; $day++ ) : ?>

						<?php $this->template( 'month/day', [ 'day' => $day, 'week' => $week, 'month' => $month ] ); ?>

					<?php endfor; ?>

				</div>

			<?php endfor; ?>

		</div>

	</div>

</div>
