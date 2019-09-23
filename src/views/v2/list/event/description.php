<?php
/**
 * View: List Single Event Description
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/event/description.php
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
 *
 */
?>
<div class="tribe-events-calendar-list__event-description tribe-common-b2">
	<?php echo tribe_events_get_the_excerpt( $event, wp_kses_allowed_html( 'post' ) ); ?>
</div>
