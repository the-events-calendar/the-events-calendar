<?php 
/**
 * Photo Single Event
 * This file contains one event in the photo view
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/photo/single-event.php
 * *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php 

global $post;

 ?>

 <div class="tribe-events-event-details">

	<!-- Event Title -->
	<?php do_action( 'tribe_events_photo_before_the_event_title' ) ?>
	<h2 class="tribe-events-list-event-title entry-title summary">
		<a class="url" href="<?php echo tribe_get_event_link() ?>" title="<?php the_title() ?>" rel="bookmark">
			<?php the_title() ?>
		</a>
	</h2>
	<?php do_action( 'tribe_events_photo_after_the_event_title' ) ?>

	<!-- Event Meta -->
	<?php do_action( 'tribe_events_photo_before_the_meta' ) ?>
		<div class="tribe-events-event-meta">
			<div class="updated published time-details">
				<?php if ( !empty( $post->distance ) ) : ?>
				<strong>[<?php echo tribe_get_distance_with_unit( $post->distance ) ?>]</strong>
				<?php endif; ?>
				<?php echo tribe_events_event_schedule_details(), tribe_events_event_recurring_info_tooltip(); ?>
			</div>
		</div><!-- .tribe-events-event-meta -->
	<?php do_action( 'tribe_events_photo_after_the_meta' ) ?>

	<!-- Event Content -->
	<?php do_action( 'tribe_events_photo_before_the_content' ) ?>
	<?php the_excerpt() ?>
	<?php do_action('tribe_events_photo_after_the_content') ?>

<?php echo tribe_get_event_taxonomy( array('before' => '<p class="tribe-event-categories">', 'sep' => ', ', 'after' => '</p>') ) ?>

</div><!-- /.tribe-events-event-details -->