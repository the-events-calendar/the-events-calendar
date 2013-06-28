<?php
/**
 * Single Venue Template
 * The template for a venue. By default it displays venue information and lists 
 * events that occur at the specified venue.
 *
 * You can customize this view by putting a replacement file of the same name
 * (single-venue.php) in the tribe-events/ directory of your theme.
 *
 * @package TribeEventsCalendarPro
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */
 
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
 
// Get some bits
$venue_id = get_the_id();
?>

<?php // Back button ?>
<span class="back"><a href="<?php echo tribe_get_events_link(); ?>"><?php _e( '&laquo; Back to Events', 'tribe-events-calendar-pro' ); ?></a></span>

<?php // Single venue meta ?>							
<div id="tribe-events-event-meta">

	<?php // Map ?>
	<div style="margin: 0 0 10px 0; float: right;">
		<?php echo tribe_get_embedded_map( get_the_ID(), '350px', '200px' ); ?>
 	</div>
	
	<?php // Location ?>
 	<dl class="column location" itemscope itemtype="http://schema.org/Place">

		<dt class="venue-label venue-label-name"><?php _e( 'Name:', 'tribe-events-calendar-pro' ); ?></dt> 
		<dd itemprop="name" class="venue-meta venue-meta-name"><?php the_title(); ?></dd>
		
		<?php if( tribe_get_phone() ) : // Venue phone ?>
			<dt class="venue-label venue-label-phone"><?php _e( 'Phone:', 'tribe-events-calendar-pro' ); ?></dt> 
 			<dd itemprop="telephone" class="venue-meta venue-meta-phone"><?php echo tribe_get_phone(); ?></dd>
 		<?php endif; ?>
 		
		<?php if( tribe_address_exists( get_the_ID() ) ) : // Venue address ?>
			<dt class="venue-label venue-label-address">
				<?php _e( 'Address:', 'tribe-events-calendar-pro' ); ?><br />
				<?php if( get_post_meta( get_the_ID(), '_EventShowMapLink', true ) == 'true' ) : ?>
					<a class="gmap" itemprop="maps" href="<?php echo tribe_get_map_link(); ?>" title="<?php _e( 'Click to view a Google Map', 'tribe-events-calendar-pro' ); ?>" target="_blank"><?php _e( 'Google Map', 'tribe-events-calendar-pro' ); ?></a>
				<?php endif; ?>
			</dt>
 			<dd class="venue-meta venue-meta-address">
				<?php echo tribe_get_full_address( get_the_ID() ); ?>
 			</dd>
 		<?php endif; ?>
		
		<?php if ( get_the_content() != '' ): // Venue content ?>
			<dt class="venue-label venue-label-description"><?php _e( 'Description:', 'tribe-events-calendar-pro' ); ?></dt>
			<dd class="venue-meta venue-meta-description"><?php the_content(); ?></dd>
 		<?php endif ?>
		
	</dl><!-- .column -->
	
</div><!-- #tribe-events-event-meta -->

<?php // Venue Loop ?>
 <div id="tribe-events-loop" class="tribe-events-events post-list clearfix upcoming venue-events">

 	<?php  
	$venueEvents = tribe_get_events( array( 'venue'=>get_the_ID(), 'eventDisplay' => 'upcoming', 'posts_per_page' => -1 ) ); 
 	global $post; 
 	$first = true;
 	?>					
 	<?php if( sizeof($venueEvents) > 0 ): ?>
	
		<h2 class="tribe-events-cal-title"><?php _e( 'Upcoming Events At This Venue', 'tribe-events-calendar-pro' ); ?></h2>
						
		<?php foreach( $venueEvents as $post ): setup_postdata( $post ); // Venue ?>
		
			<div id="post-<?php the_ID(); ?>" <?php post_class( $first ? 'tribe-events-event clearfix first': 'tribe-events-event clearfix' ); $first = false; ?> itemscope itemtype="http://schema.org/Event">
			
 				<?php if ( tribe_is_new_event_day() ) : ?>
 					<h4 class="event-day"><?php echo tribe_get_start_date( null, false ); ?></h4>
 				<?php endif; ?>
				
				<?php the_title( '<h2 class="entry-title" itemprop="name"><a href="' . tribe_get_event_link() . '" title="' . the_title_attribute( 'echo=0' ) . '" rel="bookmark" itemprop="url">', '</a></h2>' ); ?>
				
 				<div class="entry-content tribe-events-event-entry" itemprop="description">
 					<?php has_excerpt() ? the_excerpt() : the_content() ?>
				</div><!-- .tribe-events-event-entry -->
				
 				<div class="tribe-events-event-list-meta">
 					<table>
 						<?php if (tribe_is_multiday()): ?>
							<tr>
								<td class="tribe-events-event-meta-desc"><?php _e( 'Start:', 'tribe-events-calendar-pro' ); ?></td>
								<td class="tribe-events-event-meta-value"><meta itemprop="startDate" content="<?php echo tribe_get_start_date( null, false, 'Y-m-d' ); ?>" /><?php echo tribe_get_start_date(); ?></td>
							</tr>
							<tr>
								<td class="tribe-events-event-meta-desc"><?php _e( 'End:', 'tribe-events-calendar-pro' ); ?></td>
								<td class="tribe-events-event-meta-value"><meta itemprop="endDate" content="<?php echo tribe_get_end_date( null, false, 'Y-m-d' ); ?>" /><?php echo tribe_get_end_date(); ?></td>
							</tr>
 						<?php else: ?>
							<tr>
								<td class="tribe-events-event-meta-desc"><?php _e( 'Date:', 'tribe-events-calendar-pro' ); ?></td>
								<td class="tribe-events-event-meta-value"><meta itemprop="startDate" content="<?php echo tribe_get_start_date( null, false, 'Y-m-d' ); ?>" /><?php echo tribe_get_start_date(); ?></td>
							</tr>
 						<?php endif; ?>
 						<?php
 						$cost = tribe_get_cost();
 						if ( !empty( $cost ) ) :
 						?>
							<tr>
								<td class="tribe-events-event-meta-desc"><?php _e( 'Cost:', 'tribe-events-calendar-pro' ); ?></td>
								<td class="tribe-events-event-meta-value" itemprop="price"><?php echo $cost; ?></td>
							</tr>
 						<?php endif; ?>
 					</table>				
				</div><!-- .tribe-events-event-list-meta -->
				
			</div><!-- #post -->
							
 		<?php endforeach; ?>						
 	<?php endif; ?>
 	<?php // Reset the post and id to the venue post before comments template shows up.
 	$post = get_post($venue_id); 
 	global $id;
	$id = $venue_id; ?>
	
</div><!-- #tribe-events-loop -->