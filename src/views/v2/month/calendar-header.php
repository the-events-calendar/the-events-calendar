<?php
/**
 * View: Month View - Calendar Header
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-header.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */
// @todo: Check if we're keeping these template tags or how is that we'll be populating data here.
$days_of_week = tribe_events_get_days_of_week();
global $wp_locale;
?>
<header role="rowgroup">

	<h2 class="tribe-common-a11y-visual-hide" id="tribe-calendar-header"><?php printf( esc_html__( 'Calendar of %s', 'the-events-calendar' ), tribe_get_event_label_plural() ); ?></h2>

	<div role="row" class="tribe-events-calendar-month__header">
		<?php foreach ( $days_of_week as $day ) : ?>
			<div
				class="tribe-events-calendar-month__header-column"
				role="columnheader"
				aria-label="<?php echo esc_attr( $day ); ?>"
			>
				<h3 class="tribe-events-calendar-month__header-title tribe-common-b3">
					<?php echo esc_html( $wp_locale->get_weekday_abbrev( $day ) ); ?>
				</h3>
			</div>
		<?php endforeach; ?>
	</div>
</header>
