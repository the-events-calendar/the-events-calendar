<?php
/**
 * View: List View - Single Event Featured Image
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/event/featured-image.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

$event_id = $event->ID;

if ( ! has_post_thumbnail( $event_id ) ) {
	return;
}

?>
<div class="tribe-events-calendar-list__event--featured-image">
	<?php echo get_the_post_thumbnail( $event_id, 'large' ); ?>
</div>