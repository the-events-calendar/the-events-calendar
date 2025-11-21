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
 * @since 4.9.10
 * @since 6.14.2 Improved accessibility for calendar view [TEC-5211].
 * @since 6.15.11 Improved accessibility by adding better screen reader text. [TEC-5719]
 *
 * @version 6.15.11
 */

global $wp_locale;
?>
<thead class="tribe-events-calendar-month__header">
<tr>
	<?php
	foreach ( tribe_events_get_days_of_week() as $day ) :
		$day_abbrev  = $wp_locale->get_weekday_abbrev( $day );
		$day_initial = $wp_locale->get_weekday_initial( $day );
		?>
		<th
			class="tribe-events-calendar-month__header-column"
			scope="col"
			abbr="<?php echo esc_attr( $day_abbrev ); ?>"
		>
			<div class="tribe-events-calendar-month__header-column-title tribe-common-b3">
					<span aria-hidden="true">
						<?php echo esc_html( $day_initial ); ?>
					</span>
				<span class="screen-reader-text">
						<?php echo esc_html( $day ); ?>
					</span>
			</div>
		</th>
	<?php endforeach; ?>
</tr>
</thead>
