<?php
/**
 * View: Month View - Mobile Event Featured Image
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/mobile-events/mobile-day/mobile-event/featured-image.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.9.11
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( ! $event->featured || ! $event->thumbnail->exists ) {
	return;
}

?>
<div class="tribe-events-calendar-month-mobile-events__mobile-event-featured-image-wrapper">
	<img
		src="<?php echo esc_url( $event->thumbnail->full->url ); ?>"
		<?php if ( ! empty( $event->thumbnail->srcset ) ) : ?>
			srcset="<?php echo esc_attr( $event->thumbnail->srcset ); ?>"
		<?php endif; ?>
		<?php if ( ! empty( $event->thumbnail->alt ) ) : ?>
			alt="<?php echo esc_attr( $event->thumbnail->alt ); ?>"
		<?php else : // We need to ensure we have an empty alt tag for accessibility reasons if the user doesn't set one for the featured image ?>
			alt=""
		<?php endif; ?>
		<?php if ( ! empty( $event->thumbnail->title ) ) : ?>
			title="<?php echo esc_attr( $event->thumbnail->title ); ?>"
		<?php endif; ?>
		class="tribe-events-calendar-month-mobile-events__mobile-event-featured-image"
	/>
</div>
