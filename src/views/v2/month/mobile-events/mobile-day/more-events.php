<?php
/**
 * View: Month View - More Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/mobile-events/mobile-day/more-events.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.15.6
 *
 * @since 4.9.11
 * @since 6.15.6 Added aria-label to more events link.
 *
 * @var int $more_events The number of events that's not showing in the day cell or in the multi-day stack.
 * @var string $more_url A string with the URL for more events on that day
 * @var string $day_date The current day date, in the `Y-m-d` format.
 */

// Bail if there are no more events to show.
if ( empty( $more_events ) || empty( $more_url ) ) {
	return;
}

// Get the formatted date for screen reader text.
$date_format    = tribe_get_date_option( 'dateWithYearFormat', get_option( 'date_format' ) );
$formatted_date = date_i18n( $date_format, strtotime( $day_date ) );
?>

<div class="tribe-events-calendar-month-mobile-events__more-events tribe-events-c-small-cta tribe-common-b3">
	<a
		href="<?php echo esc_url( $more_url ); ?>"
		class="tribe-events-calendar-month-mobile-events__more-events-link tribe-events-c-small-cta__link tribe-common-cta tribe-common-cta--thin-alt"
		data-js="tribe-events-view-link"
		aria-label="
		<?php
		/* translators: %1$d: number of events, %2$s: formatted date. */
		echo esc_attr( sprintf( _n( '+ %1$d More for %2$s', '+ %1$d More for %2$s', $more_events, 'the-events-calendar' ), $more_events, $formatted_date ) );
		?>
		"
	>
		<?php
		echo esc_html(
			sprintf(
				_n( '+ %d More', '+ %d More', $more_events, 'the-events-calendar' ),
				$more_events
			)
		);
		?>
	</a>
</div>
