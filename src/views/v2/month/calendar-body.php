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
 */
?>
<div class="tribe-events-calendar-month__body" role="rowgroup">

	<?php // @todo: replace this with the actual month days. Using these for(s) for presentation purposes. ?>
	<?php for ( $week = 0; $week < 4; $week++ ) : ?>

		<div class="tribe-events-calendar-month__week" role="row" data-js="tribe-events-month-grid-row">

			<?php for ( $day_number = 0; $day_number < 7; $day_number++ ) : ?>

				<?php // @todo: When the BE is ready, we shouldn't send the $week here, it's now being sent to calculate the day number for the FE presentation. ?>
				<?php $this->template( 'month/calendar-body/day', [ 'day_number' => $day_number, 'week' => $week ] ); ?>

			<?php endfor; ?>

		</div>

	<?php endfor; ?>

</div>
