<?php 
/**
 * Month Single Event
 * This file contains one event in the month view
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/month/single-event.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<?php 

global $post;
$day = tribe_events_get_current_month_day();
$event_id = "{$post->ID}-{$day['daynum']}";
$start = tribe_get_start_date( $post, FALSE, 'U' );
$end = tribe_get_end_date( $post, FALSE, 'U' );

?>

<div id="tribe-events-event-<?php echo $event_id ?>" class="<?php tribe_events_event_classes() ?>">
	<h3 class="tribe-events-month-event-title summary"><a href="<?php tribe_event_link( $post ); ?>" class="url"><?php the_title() ?></a></h3>
	<div id="tribe-events-tooltip-<?php echo $event_id; ?>" class="tribe-events-tooltip">
		<h4 class="summary"><?php the_title() ?></h4>
		<div class="tribe-events-event-body">

			<?php echo tribe_events_event_schedule_details() ?>
			
			<?php if (has_post_thumbnail() ) : ?>
				<div class="tribe-events-event-thumb"><?php echo the_post_thumbnail(array(90,90));?></div>
			<?php endif; ?>

			<p class="entry-summary description">
				<?php echo get_the_excerpt() ?>
			</p><!-- .entry-summary -->

		</div><!-- .tribe-events-event-body -->
		<span class="tribe-events-arrow"></span>
	</div><!-- .tribe-events-tooltip -->
</div><!-- #tribe-events-event-# -->
