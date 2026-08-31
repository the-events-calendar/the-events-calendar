<?php
/**
 * View: Day View - Single Event Recurring Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/event/recurring.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1aiy
 *
 * @since TBD
 *
 * @version TBD
 *
 * @var WP_Post $event            The event post object with properties added by the `tribe_get_event` function.
 * @var string  $icon_description The description of the icon. Used for the accessible label. (optional)
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event->recurring ) ) {
	return;
}

if ( empty( $icon_description ) ) {
	$icon_description = __( 'Recurring', 'the-events-calendar' );
}
?>
<a
	href="<?php echo esc_url( $event->permalink_all ); ?>"
	class="tribe-events-calendar-day__event-datetime-recurring-link"
>
	<span class="tribe-events-calendar-day__event-datetime-recurring-icon">
		<?php $this->template( 'components/icons/recurring', [ 'classes' => [ 'tribe-events-calendar-day__event-datetime-recurring-icon-svg' ] ] ); ?>
	</span>
	<span class="tribe-events-calendar-day__event-datetime-recurring-text tribe-common-a11y-visual-hide">
		<?php echo esc_html( $icon_description ); ?>
	</span>
</a>
