<?php
/**
 * A an event within a main loop
 * This displays the event description, meta, and optionally, the Google map for the event.
 *
 * You can customize this view by putting a replacement file of the same name
 * (in-loop.php) in the events/ directory of your theme.
 *
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

global $post, $more; $more = false;
?>

	<?php // if ( tribe_is_new_event_day() && !tribe_is_day() ) : ?>
		<h4 class="event-day"><?php echo tribe_get_start_date( $post->ID, true ); ?></h4>
	<?php // endif; ?>
	
	<?php if ( tribe_is_day() && $first ) : $first = false; ?>
		<h4 class="event-day"><?php echo tribe_event_format_date(strtotime(get_query_var('eventDate')), false); ?></h4>
	<?php endif; ?>
	
	<div class="entry-content tribe-events-event-entry" itemprop="description">
		<?php if ( has_excerpt () ): ?>
			<?php the_excerpt(); ?>
		<?php else: ?>
			<?php apply_filters( 'the_content', $post->post_content ); ?>
		<?php endif; ?>
	</div> <!-- .tribe-events-event-entry -->

	<?php // Single event meta ?>
	<div class="tribe-events-event-list-meta" itemprop="location" itemscope itemtype="http://schema.org/Place">
		<table cellspacing="0">
			<?php if ( tribe_is_multiday() || !tribe_get_all_day() ): // // Start & end date ?>
				<tr>
					<td class="tribe-events-event-meta-desc"><?php _e( 'Start:', 'tribe-events-calendar' ); ?></td>
					<td class="tribe-events-event-meta-value" itemprop="startDate" content="<?php echo tribe_get_start_date(); ?>"><?php echo tribe_get_start_date(); ?></td>
				</tr>
				<tr>
					<td class="tribe-events-event-meta-desc"><?php _e( 'End:', 'tribe-events-calendar' ); ?></td>
					<td class="tribe-events-event-meta-value" itemprop="endDate" content="<?php echo tribe_get_end_date(); ?>"><?php echo tribe_get_end_date(); ?></td>
				</tr>
			<?php else: // If all day event, show only start date ?>
				<tr>
					<td class="tribe-events-event-meta-desc"><?php _e( 'Date:', 'tribe-events-calendar' ); ?></td>
					<td class="tribe-events-event-meta-value" itemprop="startDate" content="<?php echo tribe_get_start_date(); ?>"><?php echo tribe_get_start_date(); ?></td>
				</tr>
			<?php endif; ?>

			<?php // Venue
			$venue = tribe_get_venue();
			if ( !empty( $venue ) ) :
			?>
				<tr>
					<td class="tribe-events-event-meta-desc"><?php _e( 'Venue:', 'tribe-events-calendar' ); ?></td>
					<td class="tribe-events-event-meta-value" itemprop="name">
						<? if( class_exists( 'TribeEventsPro' ) ): ?>
							<?php tribe_get_venue_link( get_the_ID(), class_exists( 'TribeEventsPro' ) ); ?>
						<? else: ?>
							<?php echo tribe_get_venue( get_the_ID() ) ?>
						<? endif; ?>
					</td>
				</tr>
			<?php endif; ?>
			
			<?php // Phone
			$phone = tribe_get_phone();
			if ( !empty( $phone ) ) :
			?>
				<tr>
					<td class="tribe-events-event-meta-desc"><?php _e( 'Phone:', 'tribe-events-calendar' ); ?></td>
					<td class="tribe-events-event-meta-value" itemprop="telephone"><?php echo $phone; ?></td>
				</tr>
			<?php endif; ?>
			
			<?php if (tribe_address_exists( get_the_ID() ) ) : // Address ?>
				<tr>
					<td class="tribe-events-event-meta-desc"><?php _e ('Address:', 'tribe-events-calendar' ); ?><br />
					<?php if( get_post_meta( get_the_ID(), '_EventShowMapLink', true ) == 'true' ) : ?>
						<a class="gmap" itemprop="maps" href="<?php echo tribe_get_map_link(); ?>" title="Click to view a Google Map" target="_blank"><?php _e( 'Google Map', 'tribe-events-calendar' ); ?></a>
					<?php endif; ?></td>
					<td class="tribe-events-event-meta-value"><?php echo tribe_get_full_address( get_the_ID() ); ?></td>
				</tr>
			<?php endif; ?>
			
			<?php // Cost
				$cost = tribe_get_cost();
				if ( !empty( $cost ) ) :
			?>
				<tr>
					<td class="tribe-events-event-meta-desc"><?php _e( 'Cost:', 'tribe-events-calendar' ); ?></td>
					<td class="tribe-events-event-meta-value" itemprop="price"><?php echo $cost; ?></td>
				 </tr>
			<?php endif; ?>
			
		</table>
	</div><!-- .tribe-events-event-list-meta -->
	