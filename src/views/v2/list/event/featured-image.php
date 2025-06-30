<?php
/**
 * View: List View - Single Event Featured Image
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/event/featured-image.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 5.0.0
 * @since TBD Removed link around featured image for accessibility update.
 *
 * @version TBD
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( ! $event->thumbnail->exists ) {
	return;
}

// Always show post title as image alt, if not available fallback to image alt.
$image_alt_attr = ! empty( $event->title )
	? $event->title
	: ( ! empty( $event->thumbnail->alt )
		? $event->thumbnail->alt
		: ''
	);

?>
<div class="tribe-events-calendar-list__event-featured-image-wrapper tribe-common-g-col">
	<img
		src="<?php echo esc_url( $event->thumbnail->full->url ); ?>"
		<?php if ( ! empty( $event->thumbnail->srcset ) ) : ?>
			srcset="<?php echo esc_attr( $event->thumbnail->srcset ); ?>"
		<?php endif; ?>
		alt="<?php echo esc_attr( $image_alt_attr ); ?>"
		class="tribe-events-calendar-list__event-featured-image"
		width="<?php echo esc_attr( $event->thumbnail->full->width ); ?>"
		height="<?php echo esc_attr( $event->thumbnail->full->height ); ?>"
	/>
</div>
