<?php
/**
 * View: Month View - Mobile Event Featured Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/mobile-events/mobile-day/mobile-event/date/featured.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.1.1
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 *
 * @version 5.3.0
 */

if ( empty( $event->featured ) ) {
	return;
}
?>
<em
	class="tribe-events-calendar-month-mobile-events__mobile-event-datetime-featured-icon"
	title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
>
	<?php $this->template( 'components/icons/featured', [ 'classes' => [ 'tribe-events-calendar-month-mobile-events__mobile-event-datetime-featured-icon-svg' ] ] ); ?>
</em>
<span class="tribe-events-calendar-month-mobile-events__mobile-event-datetime-featured-text">
	<?php esc_html_e( 'Featured', 'the-events-calendar' ); ?>
</span>
