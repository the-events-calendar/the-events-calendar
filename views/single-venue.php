<?php
/**
* The template for a venue.  By default it displays venue information and lists 
* events that occur at the specified venue.
*
* You can customize this view by putting a replacement file of the same name (single-venue.php) in the events/ directory of your theme.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<span class="back"><a href="<?php echo tribe_get_events_link(); ?>"><?php _e('&laquo; Back to Events', TribeEventsPro::PLUGIN_DOMAIN); ?></a></span>								
<div id="tribe-events-event-meta">
	<div style='margin: 0 0 10px 0; float: right;'>
		<?php echo tribe_get_embedded_map(get_the_ID(), '350px', '200px') ?>
	</div>
	<dl class="column location" itemprop="location" itemscope itemtype="http://schema.org/Place">
		<dt><?php _e('Name:', TribeEventsPro::PLUGIN_DOMAIN) ?></dt> 
			<dd itemprop="name"><?php the_title() ?></dd>
		<?php if(tribe_get_phone()) : ?>
		<dt><?php _e('Phone:', TribeEventsPro::PLUGIN_DOMAIN) ?></dt> 
			<dd itemprop="telephone"><?php echo tribe_get_phone(); ?></dd>
		<?php endif; ?>
		
		<?php if( tribe_address_exists( get_the_ID() ) ) : ?>
		<dt>
			<?php _e('Address:', TribeEventsPro::PLUGIN_DOMAIN) ?><br />
			<?php if( get_post_meta( get_the_ID(), '_EventShowMapLink', true ) == 'true' ) : ?>
				<a class="gmap" itemprop="maps" href="<?php tribe_the_map_link() ?>" title="<?php _e('Click to view a Google Map', TribeEventsPro::PLUGIN_DOMAIN); ?>" target="_blank"><?php _e('Google Map', TribeEventsPro::PLUGIN_DOMAIN ); ?></a>
			<?php endif; ?>
		</dt>
			<dd>
			<?php tribe_the_full_address( get_the_ID() ); ?>
			</dd>
		<?php endif; ?>
	</dl>
</div>
<div class='entry'>
   <?php the_content() ?>
</div>
<div id="tribe-events-loop" class="tribe-events-events post-list clearfix upcoming venue-events">
	<?php 
	$venueEvents = tribe_get_events(array('venue'=>get_the_ID(), 'posts_per_page'=> -1)); 
	global $post; 
	$first = true;
	?>					
	<?php if( sizeof($venueEvents) > 0 ): ?>
		<h2 class='tribe-events-cal-title'>Upcoming Events At This Venue</h2>					
		<?php foreach( $venueEvents as $post ): setup_postdata($post);	?>
			<div id="post-<?php the_ID() ?>" <?php post_class($first ? 'tribe-events-event clearfix first': 'tribe-events-event clearfix' ); $first = false; ?> itemscope itemtype="http://schema.org/Event">
				<?php if ( tribe_is_new_event_day() ) : ?>
					<h4 class="event-day"><?php echo tribe_get_start_date( null, false ); ?></h4>
				<?php endif; ?>
				<?php the_title('<h2 class="entry-title" itemprop="name"><a href="' . get_permalink() . '" title="' . the_title_attribute('echo=0') . '" rel="bookmark" itemprop="url">', '</a></h2>'); ?>
				<div class="entry-content tribe-events-event-entry" itemprop="description">
					<?php has_excerpt() ? the_excerpt() : the_content() ?>
				</div> <!-- End tribe-events-event-entry -->
				<div class="tribe-events-event-list-meta">
					<table cellspacing="0">
						<tr>
							<td class="tribe-events-event-meta-desc"><?php _e('Start:', TribeEventsPro::PLUGIN_DOMAIN) ?></td>
							<td class="tribe-events-event-meta-value" itemprop="startDate" content="<?php echo tribe_get_start_date( null, false, 'Y-m-d' ); ?>"><?php echo tribe_get_start_date(); ?></td>
						</tr>
						<tr>
							<td class="tribe-events-event-meta-desc"><?php _e('End:', TribeEventsPro::PLUGIN_DOMAIN) ?></td>
							<td class="tribe-events-event-meta-value" itemprop="endDate" content="<?php echo tribe_get_end_date( null, false, 'Y-m-d' ); ?>"><?php echo tribe_get_end_date(); ?></td>
						</tr>
						<?php
						$cost = tribe_get_cost();
						if ( !empty( $cost ) ) :
						?>
						<tr>
							<td class="tribe-events-event-meta-desc"><?php _e('Cost:', TribeEventsPro::PLUGIN_DOMAIN) ?></td>
							<td class="tribe-events-event-meta-value" itemprop="price"><?php echo $cost; ?></td>
						</tr>
						<?php endif; ?>
					</table>
				</div>
			</div> <!-- End post -->				
		<?php endforeach; ?>						
	<?php endif; ?>
</div>
