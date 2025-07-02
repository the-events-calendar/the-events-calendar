<?php
/**
 * View: Month View - Calendar Header
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-header.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.9.10
 *
 */

global $wp_locale;
?>
<thead class="tribe-events-calendar-month__header">
	<tr>
		<?php foreach ( tribe_events_get_days_of_week() as $day ) : ?>
			<th
				class="tribe-events-calendar-month__header-column"
				scope="col"
				aria-label="<?php echo esc_attr( $day ); ?>"
			>
			<div class="tribe-events-calendar-month__header-column-title tribe-common-b3">
				<span class="tribe-events-calendar-month__header-column-title-mobile">
					<?php echo esc_html( $wp_locale->get_weekday_initial( $day ) ); ?>
				</span>
				<span class="tribe-events-calendar-month__header-column-title-desktop tribe-common-a11y-hidden">
					<?php echo esc_html( $wp_locale->get_weekday_abbrev( $day ) ); ?>
				</span>
			</div>
		</th>
		<?php endforeach; ?>
	</tr>
</thead>
