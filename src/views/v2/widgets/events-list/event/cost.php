<?php
/**
 * Widget: Events List Event Cost
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/widgets/events-list/event/cost.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://m.tri.be/1aiy
 *
 * @version 5.2.1
 *
 * @var WP_Post            $event   The event post object with properties added by the `tribe_get_event` function.
 * @var array<string,bool> $display Associative array of display settings for event meta.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event->cost ) || empty( $display['cost'] ) ) {
	return;
}
?>
<div class="tribe-events-widget-events-list__event-cost tribe-common-b2">
	<span class="tribe-events-widget-events-list__event-cost-price">
		<?php echo esc_html( $event->cost ); ?>
	</span>
</div>
