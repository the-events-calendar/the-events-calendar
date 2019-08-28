<?php
/**
 * View: Month View - Single Event Tooltip CTA
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/cta.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event->cost ) ) {
	return;
}
// @todo @fe make this dynamic depending on the cost.
?>

<div class="tribe-events-c-small-cta tribe-events-calendar-month__calendar-event-tooltip-cta">
	<a href="#" class="tribe-events-c-small-cta__link tribe-common-cta tribe-common-cta--alt">
		<?php esc_html_e( 'Buy Now', 'the-events-calendar' ); ?>
	</a>
	<span class="tribe-events-c-small-cta__price">
		<?php echo esc_html( $event->cost ) ?>
	</span>
</div>
