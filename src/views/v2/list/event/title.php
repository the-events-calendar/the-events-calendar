<?php
/**
 * View: List View - Single Event Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/event/title.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.3
 *
 */
$event    = $this->get( 'event' );
$event_id = $event->ID;
?>
<h3 class="tribe-events-calendar-list__event-title tribe-common-h6 tribe-common-h5--min-medium">
	<a
		href="<?php echo esc_url( tribe_get_event_link( $event_id ) ); ?>"
		title="<?php the_title_attribute( $event_id ); ?>"
		rel="bookmark"
		class="tribe-events-calendar-list__event-title-link tribe-common-anchor"
	>
		<?php echo get_the_title( $event_id ); ?>
	</a>
</h3>
