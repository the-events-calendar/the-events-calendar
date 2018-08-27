<?php
/**
 * Embed Cost Meta Template
 *
 * The cost template for the embed view.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/embed/cost.php
 *
 * @version 4.2
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! tribe_get_cost() ) {
	return;
}
?>
<div class="tribe-events-event-cost">
	<?php do_action( 'tribe_events_embed_before_the_cost_value' ); ?>
	<span><?php echo tribe_get_cost( null, true ); ?></span>
	<?php do_action( 'tribe_events_embed_after_the_cost_value' ); ?>
</div>
