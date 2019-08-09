<?php
/**
 * View: Month View - Mobile Event Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/mobile-events/mobile-day/mobile-event/title.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 * @var WP_Post $event The event post object, decorated with custom properties from the `tribe_get_event` function.
 *
 * @see tribe_get_event()
 */

$classes = [ 'tribe-events-calendar-month-mobile-events__mobile-event-title' ];

if ( $event->featured ) {
	$classes[] = 'tribe-common-h6';
} else {
	$classes[] = 'tribe-common-h8';
}

?>
<h3 <?php tribe_classes( $classes ); ?>>
	<a
		href="<?php echo esc_url( $event->permalink ) ?>"
		title="<?php echo esc_attr( $event->post_title ) ?>"
		rel="bookmark"
		class="tribe-events-calendar-month-mobile-events__mobile-event-title-link tribe-common-anchor"
	>
		<?php echo wp_kses_post( $event->post_title ) ?>
	</a>
</h3>
