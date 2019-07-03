<?php
/**
 * View: Month View - Day
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body/day.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */
// @todo: This is just for presentation purposes, while building the view.
$day_number   = ( $week < 1 ) ? ( $day + 1 ) * ( $week + 1 ) : ( $day + 1 ) + $week * 7;
$month_number = 6;
$month        = $this->get( 'month' );

$day_classes = [ 'tribe-events-calendar-month__day' ];

// @todo: figure out consistent way of formatting ids
$day_id = 'tribe-events-calendar-day-' . $month_number . '-' . $day_number;

if ( $day_number == date( 'd', time() ) ) {
	$day_classes[] = 'tribe-events-calendar-month__day--current';
}

// @todo: figure out consistent way of formatting ids
// only add id if events exist on the day
$mobile_day_id = 'tribe-events-calendar-mobile-day-' . $month_number . '-' . $day_number;

?>

<div
	class="<?php echo esc_attr( implode( ' ', $day_classes ) ) ?>"
	role="gridcell"
	aria-labelledby="<?php echo esc_attr( $day_id ); ?>"
	data-js="tribe-events-month-grid-cell"
>

	<button
		aria-expanded="false" <?php // @todo: only add if events exist on the day ?>
		aria-selected="false" <?php // @todo: only add if events exist on the day ?>
		aria-controls="<?php echo esc_attr( $mobile_day_id ); ?>"
		class="tribe-events-calendar-month__day-cell tribe-events-calendar-month__day-cell--mobile"
		tabindex="-1"
	>
		<h3 class="tribe-events-calendar-month__day-date tribe-common-h6 tribe-common-h6--alt">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<?php echo esc_html( $day_number ); ?>
			</time>
		</h3>
		<?php /* @todo: if day has featured event ?>
			<em
				class="tribe-events-calendar-month__mobile-events-icon tribe-events-calendar-month__mobile-events-icon--featured"
				aria-label="<?php esc_attr_e( 'Has featured events', 'the-events-calendar' ); ?>"
				title="<?php esc_attr_e( 'Has featured events', 'the-events-calendar' ); ?>"
			>
			</em>
		<?php /* @todo: else if day has events */ ?>
			<em
				class="tribe-events-calendar-month__mobile-events-icon tribe-events-calendar-month__mobile-events-icon--event"
				aria-label="<?php esc_attr_e( 'Has events', 'the-events-calendar' ); ?>"
				title="<?php esc_attr_e( 'Has events', 'the-events-calendar' ); ?>"
			>
			</em>
		<?php /* endif */ ?>
	</button>

	<div
		id="<?php echo esc_attr( $day_id ); ?>"
		class="tribe-events-calendar-month__day-cell tribe-events-calendar-month__day-cell--desktop tribe-common-a11y-hidden"
	>
		<h3 class="tribe-events-calendar-month__day-date tribe-common-h4">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<a
					href="#link-to-day-view-if-it-has-events"
					class="tribe-events-calendar-month__day-date-link"
				>
					<?php echo esc_html( $day_number ); ?>
				</a>
			</time>
		</h3>

		<div class="tribe-events-calendar-month__events">
			<?php $this->template( 'month/calendar-body/day/multiday-events', [ 'day' => $day_number, 'month' => $month ] ); ?>

			<?php $this->template( 'month/calendar-body/day/calendar-events', [ 'day' => $day_number, 'month' => $month ] ); ?>
		</div>

		<?php $this->template( 'month/calendar-body/day/more-events', [ 'day' => $day_number, 'month' => $month ] ); ?>

	</div>

</div>
