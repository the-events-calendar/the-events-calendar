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
 * @version 4.9.8
 *
 * @var string $today_date Today's date in the `Y-m-d` format.
 * @var string $day_date The current day date, in the `Y-m-d` format.
 * @var array $day The current day data.{
 *          @type string $date The day date, in the `Y-m-d` format.
 *          @type bool $is_start_of_week Whether the current day is the first day of the week or not.
 *          @type string $year_number The day year number, e.g. `2019`.
 *          @type string $month_number The day year number, e.g. `6` for June.
 *          @type string $day_number The day number in the month, e.g. `11` for June 11th.
 *          @type string $day_url The day url, e.g. `http://yoursite.com/events/2019-06-11/`.
 *          @type int $found_events The total number of events in the day including the ones not fetched due to the per
 *                                  page limit, including the multi-day ones.
 *          @type int $more_events The number of events not showing in the day.
 *          @type array $events The non multi-day events on this day. The format of each event is the one returned by
 *                    the `tribe_get_event` function.
 *          @type array $featured_events The featured events on this day. The format of each event is the one returned
 *                    by the `tribe_get_event` function.
 *          @type array $multiday_events The stack of multi-day events on this day. The stack is a mix of event post
 *                              objects, the format is the one returned from the `tribe_get_event` function, and
 *                              spacers. Spacers are falsy values indicating an empty space in the multi-day stack for
 *                              the day
 *      }
 */

$day_classes = [ 'tribe-events-calendar-month__day' ];
$day_button_classes = [ 'tribe-events-calendar-month__day-cell', 'tribe-events-calendar-month__day-cell--mobile' ];
$day_number = $day['day_number'];
$expanded = 'false';
$selected = 'false';

$day_id = 'tribe-events-calendar-day-' . $day_date;

if ( $today_date === $day_date ) {
	$expanded = 'true';
	$selected = 'true';
	$day_classes[] = 'tribe-events-calendar-month__day--current';
	$day_button_classes[] = 'tribe-events-calendar-month__day-cell--selected';
}

if ( $today_date > $day_date ) {
	$day_classes[] = 'tribe-events-calendar-month__day--past';
}

// Only add id if events exist on the day.
$mobile_day_id = 'tribe-events-calendar-mobile-day-' . $day_date;
?>

<div
	<?php tribe_classes( $day_classes ); ?>
	role="gridcell"
	aria-labelledby="<?php echo esc_attr( $day_id ); ?>"
	data-js="tribe-events-month-grid-cell"
>

	<button
		<?php if ( ! empty( $day['found_events'] ) ) : ?>
			aria-expanded="<?php echo esc_attr( $expanded ); ?>"
			aria-selected="<?php echo esc_attr( $selected ); ?>"
			aria-controls="<?php echo esc_attr( $mobile_day_id ); ?>"
		<?php endif; ?>
		<?php tribe_classes( $day_button_classes ); ?>
		data-js="tribe-events-calendar-month-day-cell-mobile"
		tabindex="-1"
	>
		<h3 class="tribe-events-calendar-month__day-date tribe-common-h6 tribe-common-h--alt">
			<span class="tribe-common-a11y-visual-hide">
				<?php echo esc_html( sprintf( _n( '%s event', '%s events', $day['found_events'], 'the-events-calendar' ), number_format_i18n( $day['found_events'] ) ) ); ?>,
			</span>
			<time
				class="tribe-events-calendar-month__day-date-daynum"
				datetime="<?php echo esc_attr( $day['date'] ); ?>"
			>
				<?php echo esc_html( $day_number ); ?>
			</time>
		</h3>
		<?php if ( ! empty( $day['featured_events'] ) ): ?>
			<em
				class="tribe-events-calendar-month__mobile-events-icon tribe-events-calendar-month__mobile-events-icon--featured"
				aria-label="<?php esc_attr_e( 'Has featured events', 'the-events-calendar' ); ?>"
				title="<?php esc_attr_e( 'Has featured events', 'the-events-calendar' ); ?>"
			>
			</em>
		<?php elseif ( ! empty( $day['found_events'] ) ) : ?>
			<em
				class="tribe-events-calendar-month__mobile-events-icon tribe-events-calendar-month__mobile-events-icon--event"
				aria-label="<?php esc_attr_e( 'Has events', 'the-events-calendar' ); ?>"
				title="<?php esc_attr_e( 'Has events', 'the-events-calendar' ); ?>"
			>
			</em>
		<?php endif ?>
	</button>

	<div
		id="<?php echo esc_attr( $day_id ); ?>"
		class="tribe-events-calendar-month__day-cell tribe-events-calendar-month__day-cell--desktop tribe-common-a11y-hidden"
	>
		<h3 class="tribe-events-calendar-month__day-date tribe-common-h4">
			<span class="tribe-common-a11y-visual-hide">
				<?php echo esc_html( sprintf( _n( '%s event', '%s events', $day['found_events'], 'the-events-calendar' ), number_format_i18n( $day['found_events'] ) ) ); ?>,
			</span>
			<time
				class="tribe-events-calendar-month__day-date-daynum"
				datetime="<?php echo esc_attr( $day['date'] ); ?>"
			>
				<?php if ( ! empty( $day['found_events'] ) ) : ?>
					<a
						href="<?php echo esc_url( $day['day_url'] ); ?>"
						class="tribe-events-calendar-month__day-date-link"
						data-js="tribe-events-view-link"
					>
						<?php echo esc_html( $day_number ); ?>
					</a>
				<?php else : ?>
					<?php echo esc_html( $day_number ); ?>
				<?php endif; ?>
			</time>
		</h3>

		<div class="tribe-events-calendar-month__events">
			<?php $this->template( 'month/calendar-body/day/multiday-events', [
				'day_date'         => $day['date'],
				'multiday_events'  => $day['multiday_events'],
				'is_start_of_week' => $day['is_start_of_week'],
			] ); ?>

			<?php $this->template( 'month/calendar-body/day/calendar-events', [ 'day_events' => $day['events'] ] ); ?>
		</div>

		<?php $this->template( 'month/calendar-body/day/more-events', [ 'more_events' => $day['more_events'], 'more_url' => $day['day_url'] ] ); ?>

	</div>

</div>
