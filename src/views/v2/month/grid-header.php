<?php
/**
 * View: Month View - Grid Header
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
// @todo: Check if we're keeping these template tags or how is that we'll be populating data here.
$days_of_week = tribe_events_get_days_of_week();
global $wp_locale;
?>
<header class="tribe-events-calendar-month__header" role="rowgroup">
	<?php foreach ( $days_of_week as $day ) : ?>
			<div
				class="tribe-events-calendar-month__header-column"
				role="columnheader"
				aria-label="<?php echo esc_attr( $day ); ?>"
				><?php echo esc_html( $wp_locale->get_weekday_abbrev( $day ) ); ?></div>
	<?php endforeach; ?>
</header>