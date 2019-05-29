<?php
/**
 * View: Month View - Day
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/day.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
// @todo: This is just for presentation purposes, while building the view.
$day_number   = ( $week < 1 ) ? ( $day + 1 ) * ( $week + 1 ) : ( $day + 1 ) + $week * 7;
$month_number = 5;

$day_title_classes = [ 'tribe-events-calendar-month__day-date' ];
$day_id = 'tribe-events-calendar-day-' . $month_number . '-' . $day_number;

// @todo: check if we use classes here or if we wrap the day block directly with the classes (we also have `.tribe-events-calendar-month__day-date--current`).
if ( $day_number == date( 'd', time() ) ) {
	$day_title_classes[] = 'tribe-events-calendar-month__day-date--current';
}
?>

<div class="tribe-events-calendar-month__day" role="gridcell" aria-labelledby="<?php echo esc_attr( $day_id ); ?>">

	<button
		aria-expanded="false"
		aria-controls="the-content-id"
		class="tribe-events-calendar-month__day-cell tribe-events-calendar-month__day-cell--mobile"
		tabindex="-1"
	>
		<h3 class="<?php echo esc_attr( implode( ' ', $day_title_classes ) ); ?> tribe-common-h6">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<time datetime="YYYY-MM-DD">
				<?php echo esc_html( $day_number ); ?>
			</time>
		</h3>
		<?php /* @todo: if day has featured event ?>
			<em
				class="tribe-events-calendar-month__mobile-events-icon tribe-events-calendar-month__mobile-events-icon--featured tribe-common-svgicon tribe-common-svgicon--featured"
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
	</button>

	<div class="tribe-events-calendar-month__day-cell tribe-events-calendar-month__day-cell--desktop tribe-common-a11y-hidden">
		<h3 class="<?php echo esc_attr( implode( ' ', $day_title_classes ) ); ?> tribe-common-h4" id="<?php echo esc_attr( $day_id ); ?>">
			<span class="tribe-common-a11y-visual-hide">X events, </span>
			<a href="#link-to-day-view-if-it-has-events">
				<time datetime="YYYY-MM-DD">
					<?php echo esc_html( $day_number ); ?>
				</time>
			</a>
		</h3>
		<!-- Events for this day will be listed here -->
	</div>
</div>
