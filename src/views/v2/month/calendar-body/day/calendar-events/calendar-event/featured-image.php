<?php
/**
 * View: Month View - Calendar Event Featured Image
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/calendar-events/calendar-event/featured-image.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 5.0.0
 * @since 6.14.2 Removed link around featured image for accessibility update.
 *
 * @version 6.14.2
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( ! $event->featured || ! $event->thumbnail->exists ) {
	return;
}
?>
<div class="tribe-events-calendar-month__calendar-event-featured-image-wrapper">
	<img
		class="tribe-events-calendar-month__calendar-event-featured-image"
		src="<?php echo esc_url( $event->thumbnail->full->url ); ?>"
		<?php if ( ! empty( $event->thumbnail->srcset ) ) : ?>
			srcset="<?php echo esc_attr( $event->thumbnail->srcset ); ?>"
		<?php endif; ?>
		<?php if ( ! empty( $event->thumbnail->alt ) ) : ?>
			alt="<?php echo esc_attr( $event->thumbnail->alt ); ?>"
		<?php else : ?>
			alt=""
		<?php endif; ?>
		<?php if ( ! empty( $event->thumbnail->title ) ) : ?>
			title="<?php echo esc_attr( $event->thumbnail->title ); ?>"
		<?php endif; ?>
		class="tribe-events-calendar-month__calendar-event-featured-image"
		<?php if ( ! empty( $event->thumbnail->full->width ) ) : ?>
			width="<?php echo esc_attr( $event->thumbnail->full->width ); ?>"
		<?php endif; ?>
		<?php if ( ! empty( $event->thumbnail->full->height ) ) : ?>
			height="<?php echo esc_attr( $event->thumbnail->full->height ); ?>"
		<?php endif; ?>
	/>
</div>
