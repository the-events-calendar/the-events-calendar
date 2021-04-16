<?php
/**
 * View: Latest Past View - Single Event Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/latest-past/event/title.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.1.0
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */
?>
<h3 class="tribe-common-h6 tribe-common-h4--min-mediumtribe-events-calendar-latest-past__event-title">
	<a
		href="<?php echo esc_url( $event->permalink ); ?>"
		title="<?php echo esc_attr( $event->title ); ?>"
		rel="bookmark"
		class="tribe-common-anchor-thin tribe-events-calendar-latest-past__event-title-link"
	>
		<?php
		// phpcs:ignore
		echo $event->title;
		?>
	</a>
</h3>
