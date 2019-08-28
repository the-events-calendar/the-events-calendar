<?php
/**
 * View: Month View - Mobile Event CTA
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/mobile-events/mobile-day/mobile-event/cta.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */

if ( ! $event->featured ) {
	return;
}

if ( empty( $event->cost ) ) {
	return;
}
?>
<div class="tribe-events-c-small-cta tribe-events-calendar-month-mobile-events__mobile-event-cta">
	<a href="#" class="tribe-events-c-small-cta__link tribe-common-cta tribe-common-cta--alt">
		<?php esc_html_e( 'Buy Now', 'the-events-calendar' ); ?>
	</a>
	<span class="tribe-events-c-small-cta__price">
		<?php echo esc_html( $event->cost ) ?>
	</span>
</div>
