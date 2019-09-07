<?php
/**
 * View: Day View - Single Event Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/day/event/title.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
?>
<h3 class="tribe-events-calendar-day__event-title tribe-common-h6 tribe-common-h5--min-medium">
	<a
		href="<?php echo esc_url( $event->permalink ); ?>"
		title="<?php echo esc_attr( get_the_title( $event->ID ) ); ?>"
		rel="bookmark"
		class="tribe-events-calendar-day__event-title-link tribe-common-anchor-thin"
	>
		<?php echo get_the_title( $event->ID ); ?>
	</a>
</h3>
