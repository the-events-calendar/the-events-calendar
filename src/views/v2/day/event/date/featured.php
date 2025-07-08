<?php
/**
 * View: Day View - Single Event Featured Icon.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/event/date/featured.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTICLE_LINK_HERE}
 *
 * @since 5.1.1
 * @since 6.14.0 Added $icon_description parameter and updated the template to use it for the accessible label.
 *
 * @version 6.14.0
 *
 * @var WP_Post $event            The event post object with properties added by the `tribe_get_event` function.
 * @var string  $icon_description The description of the icon. Used for the accessible label. (optional)
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event->featured ) ) {
	return;
}

if ( empty( $icon_description ) ) {
	$icon_description = __( 'Featured', 'the-events-calendar' );
}
?>
<em class="tribe-events-calendar-day__event-datetime-featured-icon">
	<?php $this->template( 'components/icons/featured', [ 'classes' => [ 'tribe-events-calendar-day__event-datetime-featured-icon-svg' ] ] ); ?>
</em>
<span class="tribe-events-calendar-day__event-datetime-featured-text tribe-common-a11y-visual-hide">
	<?php echo esc_html( $icon_description ); ?>
</span>
