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
 * @version TBD
 *
 */

global $wp_locale;
?>
<header class="tribe-events-calendar-month__header" role="rowgroup">

	<h2 class="tribe-common-a11y-visual-hide" id="tribe-events-calendar-header">
		<?php printf( esc_html__( 'Calendar of %s', 'the-events-calendar' ), tribe_get_event_label_plural() ); ?>
	</h2>

	<div role="row" class="tribe-events-calendar-month__header-row">
		<?php foreach ( tribe_events_get_days_of_week() as $day ) : ?>
			<div
				class="tribe-events-calendar-month__header-column"
				role="columnheader"
				aria-label="<?php echo esc_attr( $day ); ?>"
			>
				<h3 class="tribe-events-calendar-month__header-column-title tribe-common-b3">
					<span class="tribe-events-calendar-month__header-column-title-mobile">
						<?php echo esc_html( $wp_locale->get_weekday_initial( $day ) ); ?>
					</span>
					<span class="tribe-events-calendar-month__header-column-title-desktop tribe-common-a11y-hidden">
						<?php echo esc_html( $wp_locale->get_weekday_abbrev( $day ) ); ?>
					</span>
				</h3>
			</div>
		<?php endforeach; ?>
	</div>
</header>
