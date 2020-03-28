<?php
/**
 * View: Month View - Mobile Event Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/mobile-events/mobile-day/mobile-event/title.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 5.0.0
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

$classes = [ 'tribe-events-calendar-month-mobile-events__mobile-event-title', 'tribe-common-h7' ];

?>
<h3 <?php tribe_classes( $classes ); ?>>
	<a
		href="<?php echo esc_url( $event->permalink ) ?>"
		title="<?php echo esc_attr( $event->title ) ?>"
		rel="bookmark"
		class="tribe-events-calendar-month-mobile-events__mobile-event-title-link tribe-common-anchor"
	>
		<?php
		// phpcs:ignore
		echo $event->title;
		?>
	</a>
</h3>
