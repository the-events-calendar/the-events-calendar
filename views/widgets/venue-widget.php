<h3 class="tribe_widget-title"><?php echo $title; ?></h3>
<div class="tribe_venue-widget-wrapper">
	<div class="tribe_venue-widget-venue">
		<div class="tribe_venue-widget-venue-name">
			<?php echo tribe_get_venue_link($venue_ID); ?>
		</div>
		<?php if (has_post_thumbnail($venue_ID)) { ?>
			<div class="tribe_venue-widget-thumbnail">
				<?php echo get_the_post_thumbnail($venue_ID, 'related-event-thumbnail' ); ?>
			</div>
		<?php } ?>
		<div class="tribe_venue-widget-address">
			<?php echo tribe_get_meta_group( $venue_ID, 'tribe_event_venue' ) ?>
		</div>
		<hr>
	</div>
	<?php if (count($events) == 0) { ?>
	<?php _e('No upcoming events.', 'tribe-events-calendar-pro'); ?>
	<?php } ?>
	<ul class="tribe_venue-widget-list">
		<?php foreach ($events as $event) { ?>
			<li>
				<a href="<?php echo get_permalink($event); ?>">
					<div class="tribe_venue-widget-title"><?php echo get_the_title($event->ID); ?></div>
				</a>
			</li>
		<?php } ?>
	</ul>
</div>
