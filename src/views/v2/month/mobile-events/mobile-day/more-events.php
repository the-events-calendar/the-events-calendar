<?php
/**
 * View: Month View - More Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/mobile-events/mobile-day/more-events.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.11
 *
 * @var int $more_events The number of events that's not showing in the day cell or in the multi-day stack.
 * @var string $more_url A string with the URL for more events on that day
 */

// Bail if there are no more events to show.
if ( empty( $more_events ) || empty( $more_url ) ) {
	return;
}
?>

<div class="tribe-events-calendar-month-mobile-events__more-events tribe-events-c-small-cta tribe-common-b3">
	<a
		href="<?php echo esc_url( $more_url ); ?>"
		class="tribe-events-calendar-month-mobile-events__more-events-link tribe-events-c-small-cta__link tribe-common-cta tribe-common-cta--thin-alt"
		data-js="tribe-events-view-link"
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
