<?php
/**
 * Single Venue Template
 * The template for a venue. By default it displays venue information and lists 
 * events that occur at the specified venue.
 *
 * This view contains the filters required to create an effective single venue view.
 *
 * You can recreate an ENTIRELY new single venue view by doing a template override, and placing
 * a single-venue.php file in a tribe-events/pro/ directory within your theme directory, which
 * will override the /views/single-venue.php. 
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */
 
if ( !defined('ABSPATH') ) { die('-1'); }

$venue_id = get_the_ID();

?>

<div id="tribe-events-content" class="tribe-events-venue">

	<p class="tribe-events-back"><a href="<?php echo tribe_get_events_link() ?>" rel="bookmark"><?php _e( '&larr; Back to Events', 'tribe-events-calendar-pro' ) ?></a></p>

	<div class="tribe-events-venue-meta tribe-clearfix">

		<?php if ( tribe_embed_google_map( $venue_id ) ) : ?>
			<!-- Venue Map -->
			<div class="tribe-events-map-wrap">
				<?php echo tribe_get_embedded_map( $venue_id, '350px', '200px' ); ?>
			</div><!-- .tribe-events-map-wrap -->
		<?php endif; ?>

		<!-- Venue Title -->
		<?php do_action('tribe_events_single_venue_before_title') ?>
		<?php the_title('<h2 class="entry-title summary">','</h2>'); ?>
		<?php do_action('tribe_events_single_venue_after_title') ?>

		<?php if ( tribe_show_google_map_link( $venue_id ) ) : ?>
			<!-- Google Map Link -->
			<?php echo tribe_get_meta('tribe_event_venue_gmap_link'); ?>
		<?php endif; ?>

		<!-- Venue Meta -->
		<?php do_action('tribe_events_single_venue_before_the_meta') ?>
		<?php echo tribe_get_meta_group( 'tribe_event_venue' ) ?>
		<?php do_action('tribe_events_single_venue_after_the_meta') ?>

		<!-- Venue Description -->
		<div class="tribe-venue-description tribe-events-content">
			<?php the_content(); ?>
		</div>
			
		<!-- Venue Featured Image -->
		<?php tribe_event_featured_image(null, 'full') ?>

	</div><!-- .tribe-events-event-meta -->

	<!-- Upcoming event list -->
	<?php do_action('tribe_events_single_venue_before_upcoming_events') ?>
	<?php echo tribe_venue_upcoming_events() ?>
	<?php do_action('tribe_events_single_venue_after_upcoming_events') ?>
	
</div><!-- #tribe-events-content -->