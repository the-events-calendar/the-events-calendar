<?php
/**
 * View: Day View - Single Event Featured Image
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/day/event/featured-image.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
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

?>
<div class="tribe-events-calendar-day__event-featured-image-wrapper tribe-common-g-col">
	<a
		href="<?php echo esc_url( $event->permalink ); ?>"
		title="<?php echo esc_attr( get_the_title( $event->ID ) ); ?>"
		rel="bookmark"
		class="tribe-events-calendar-day__event-featured-image-link"
	>
		<img
			src="<?php echo esc_attr( $event->thumbnail->full->url ); ?>"
			<?php if ( ! empty( $event->thumbnail->srcset ) ) : ?>
				srcset="<?php echo esc_attr( $event->thumbnail->srcset ); ?>"
			<?php endif; ?>
			class="tribe-events-calendar-day__event-featured-image"
		/>
	</a>
</div>
