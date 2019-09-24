<?php
/**
 * View: Month View - Mobile Event Cost
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/mobile-events/mobile-day/mobile-event/cost.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.9
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event->featured ) ) {
	return;
}

if ( empty( $event->cost ) ) {
	return;
}
?>
<<<<<<< HEAD:src/views/v2/month/mobile-events/mobile-day/mobile-event/cta.php
<div class="tribe-events-c-small-cta tribe-events-calendar-month-mobile-events__mobile-event-cta">
	<?php
	/**
	 * @todo @bordoni fill in buy now url
	 */
	?>
	<a href="#" class="tribe-events-c-small-cta__link tribe-common-cta tribe-common-cta--thin-alt">
		<?php esc_html_e( 'Buy Now', 'the-events-calendar' ); ?>
	</a>
=======
<div class="tribe-events-c-small-cta tribe-common-b3 tribe-events-calendar-month-mobile-events__mobile-event-cost">
>>>>>>> 5f299a2c51a558e84ebfb43592a13187cad64388:src/views/v2/month/mobile-events/mobile-day/mobile-event/cost.php
	<span class="tribe-events-c-small-cta__price">
		<?php echo esc_html( $event->cost ) ?>
	</span>
</div>
