<?php
/**
 *
 *
 * @for Single Venue Template
 * This file contains the hook logic required to create an effective single venue view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( !class_exists( 'Tribe_Events_Pro_Single_Venue_Template' ) ) {
	class Tribe_Events_Pro_Single_Venue_Template extends Tribe_Template_Factory {
		public static function init() {
			// Start single venue template
			add_filter( 'tribe_events_single_venue_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// Start single venue
			add_filter( 'tribe_events_single_venue_before_venue', array( __CLASS__, 'before_venue' ), 1, 1 );

			// Venue featured image
			add_filter( 'tribe_events_single_venue_image', array( __CLASS__, 'the_venue_image' ), 1, 1 );			

			// Venue map
			add_filter( 'tribe_events_single_venue_map', array( __CLASS__, 'the_map' ), 1, 1 );

			// Venue meta
			add_filter( 'tribe_events_single_venue_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_venue_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_venue_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );

			// End single venue
			add_filter( 'tribe_events_single_venue_after_venue', array( __CLASS__, 'after_venue' ), 1, 1 );

			// load up the event list
			add_filter( 'tribe_events_single_venue_upcoming_events', array( __CLASS__, 'upcoming_events' ), 1, 1 );

			// End single venue template
			add_filter( 'tribe_events_single_venue_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		// Start Single Venue Template
		public static function before_template( $post_id ) {
			$html = '<div id="tribe-events-content" class="tribe-events-venue">';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_template' );
		}
		// Start Single Venue
		public static function before_venue( $post_id ) {
			$html = '<div class="tribe-events-event-meta">';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_venue' );
		}
		// Venue Image
		public function the_venue_image( $post_id ){
			$html ='';
			if ( tribe_event_featured_image() ) {
				$html .= tribe_event_featured_image();
			}
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_image');
		}		
		// Venue Map
		public static function the_map( $post_id ) {
			$html = '<div class="tribe-events-map-wrap">';
			$html .= tribe_get_embedded_map( get_the_ID(), '350px', '200px' );
			$html .= '</div><!-- .tribe-events-map-wrap -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_map' );
		}
		// Venue Meta
		public static function before_the_meta( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_the_meta' );
		}
		public static function the_meta( $post_id ) {
			ob_start();
?>

			<?php if ( tribe_address_exists( get_the_ID() ) ) : // Venue address ?>
					<?php if ( get_post_meta( get_the_ID(), '_EventShowMapLink', true ) == 'true' ) : ?>
					<a class="tribe-events-gmap" href="<?php echo tribe_get_map_link(); ?>" title="<?php _e( 'Click to view a Google Map', 'tribe-events-calendar-pro' ); ?>" target="_blank"><?php _e( 'Google Map', 'tribe-events-calendar' ); ?></a>
					<?php endif; ?>
 					<address class="venue-address">
						<span><?php echo tribe_get_address( get_the_ID() ); ?></span>
						<span class="venue-location"><?php echo tribe_get_city( get_the_ID() ); ?> <?php echo tribe_get_stateprovince( get_the_ID() ); ?> <?php echo tribe_get_country( get_the_ID() ); ?></span>
						<?php if ( tribe_get_phone() ) : // Venue phone ?>
 							<span class="venue-phone"><?php echo tribe_get_phone(); ?></span>
 						<?php endif; ?>		
					</address>
 			<?php endif; ?>
			<?php if ( get_the_content() != '' ): // Venue content ?>
				<div class="venue-description">	
					<?php the_content(); ?>
				</div>	
 			<?php endif ?>
<?php
			$html = ob_get_clean();
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_the_meta' );
		}
		public static function after_the_meta( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_the_meta' );
		}
		// End Single Venue
		public static function after_venue( $post_id ) {
			$html = '</div><!-- .tribe-events-event-meta -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_venue' );
		}

		// Event List View
		public static function upcoming_events( $venue_id ) {
			global $post;
			$args = array(
				'venue' => $post->ID,
				'eventDisplay' => 'upcoming' );

			$html = sprintf( '<h3 class="tribe-events-upcoming">%s <span>%s</span></h3> %s',
				__( 'Upcoming events at', 'tribe-events-calendar-pro' ),
				$post->post_title,
				tribe_include_view_list( $args )
				);
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_upcoming_events' );
		}

		// End Single Venue Template
		public static function after_template( $post_id ) {
			$html = '</div><!-- #tribe-events-content -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_template' );
		}
	}
	Tribe_Events_Pro_Single_Venue_Template::init();
}
