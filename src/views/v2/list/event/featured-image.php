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

if ( ! $event->thumbnail->exists ) {
	return;
}

?>
<div class="tribe-events-calendar-list__event-featured-image-wrapper tribe-common-g-col">
	<a
		href="<?php echo esc_url( $event->permalink ) ?>"
		title="<?php echo esc_attr( $event->post_title ) ?>"
		rel="bookmark"
		class="tribe-events-calendar-list__event-featured-image-link"
	>
		<div class="tribe-events-calendar-list__event-featured-image tribe-common-c-image tribe-common-c-image--bg">
			<div
				class="tribe-common-c-image__bg"
				style="background-image: url('<?php echo esc_url( $event->thumbnail->full->url ); ?>');"
				role="img"
				aria-label="<?php echo esc_attr( $event->post_title ) ?>"
			>
			</div>
		</div>
	</a>
</div>
