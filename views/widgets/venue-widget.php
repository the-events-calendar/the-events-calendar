<h3 class="tribe_widget-title"><?php echo $title; ?></h3>
<div class="tribe-venue-widget-wrapper">
	<div class="tribe-venue-widget-venue">
		<div class="tribe-venue-widget-venue-name">
			<?php echo tribe_get_venue_link($venue_ID); ?>
		</div>
		<?php if (has_post_thumbnail($venue_ID)) { ?>
			<div class="tribe-venue-widget-thumbnail">
				<?php echo get_the_post_thumbnail($venue_ID, 'related-event-thumbnail' ); ?>
			</div>
		<?php } ?>
		<div class="tribe-venue-widget-address">
			<?php echo tribe_get_meta_group( $venue_ID, 'tribe_event_venue' ) ?>
		</div>
	</div>
	<?php if (count($events) == 0) { ?>
	<?php _e('No upcoming events.', 'tribe-events-calendar-pro'); ?>
	<?php } ?>
	<ul class="tribe-venue-widget-list">
		<?php foreach ($events as $event) { ?>
			<li>
				<a href="<?php echo get_permalink($event); ?>">
					<div class="tribe_venue-widget-title"><?php echo get_the_title($event->ID); ?></div>
				</a>
				<?php echo tribe_events_event_schedule_details( $event->ID ) ?>
				<?php if ( tribe_get_cost( $event->ID ) != '' ) { ?>
				<span class="tribe-events-divider">|</span>
					<span class="tribe-events-event-cost">
						<?php echo tribe_get_cost( $event->ID, true ); ?>
					</span>
				<?php } ?>	
			</li>
		<?php } ?>
	</ul>
</div>
