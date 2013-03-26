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

			// setup the template for the meta group
			tribe_set_the_meta_template( 'tribe_event_venue', array(
					'before'=>'',
					'after'=>'',
					'label_before'=>'',
					'label_after'=>'',
					'meta_before'=>'<address class="venue-address">',
					'meta_after'=>'</address>'
				), 'meta_group');
			// setup the template for the meta items
			tribe_set_the_meta_template( array(
					'tribe_event_venue_address',
					'tribe_event_venue_phone',
					'tribe_event_venue_website'
				), array(
					'before'=>'',
					'after'=>'',
					'label_before'=>'',
					'label_after'=>'',
					'meta_before'=>'<span class="%s">',
					'meta_after'=>'</span>'
				));

			// turn off the venue name in the group
			tribe_set_the_meta_visibility( 'tribe_event_venue_name', false);

			// remove the title for the group & meta items
			tribe_set_meta_label('tribe_event_venue', '', 'meta_group');
			tribe_set_meta_label( array( 
				'tribe_event_venue_address' => '',
				'tribe_event_venue_phone' => '',
				'tribe_event_venue_website' => ''
				));

			// set meta item priorities
			tribe_set_meta_priority( array( 
				'tribe_event_venue_address' => 10,
				'tribe_event_venue_phone' => 20,
				'tribe_event_venue_website' => 30
				));

			add_filter('tribe_event_meta_venue_address_gmap', '__return_null', 10);

			// disable venue info from showing on list module (since it's duplicate of this view)
			tribe_set_the_meta_visibility( 'tribe_list_venue_name_address', false );

			// provide for meta actions before loading the template
			do_action('tribe_events_pro_single_venue_meta_init' );

			// Remove the title from the list view
			add_filter( 'tribe_events_list_the_title', '__return_null', 2, 1 );

			// Remove the comments template
			add_filter('comments_template', array(__CLASS__, 'remove_comments_template') );

			// Start single venue template
			add_filter( 'tribe_events_single_venue_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// Start single venue
			add_filter( 'tribe_events_single_venue_before_venue', array( __CLASS__, 'before_venue' ), 1, 1 );
			
			// Venue Title
			add_filter( 'tribe_events_single_venue_the_title', array( __CLASS__, 'the_title' ), 1, 1 );

			// Venue map
			add_filter( 'tribe_events_single_venue_map', array( __CLASS__, 'the_map' ), 1, 1 );

			// Venue meta
			add_filter( 'tribe_events_single_venue_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_venue_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_venue_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );
			
			// Venue Featued Image
			add_filter( 'tribe_events_single_venue_featured_image', array( __CLASS__, 'featured_image' ), 1, 1 );

			// End single venue
			add_filter( 'tribe_events_single_venue_after_venue', array( __CLASS__, 'after_venue' ), 1, 1 );

			// load up the event list
			add_filter( 'tribe_events_single_venue_upcoming_events', array( __CLASS__, 'upcoming_events' ), 1, 1 );

			// End single venue template
			add_filter( 'tribe_events_single_venue_after_template', array( __CLASS__, 'after_template' ), 1, 1 );

			// Remove header / footer navigation
			add_filter( 'tribe_events_list_before_header', '__return_false' );
			add_filter( 'tribe_events_list_before_header_nav', '__return_false' );
			add_filter( 'tribe_events_list_header_nav', '__return_false' );
			add_filter( 'tribe_events_list_after_header_nav', '__return_false' );
			add_filter( 'tribe_events_list_after_header', '__return_false' );
			add_filter( 'tribe_events_list_before_footer', '__return_false' );
			add_filter( 'tribe_events_list_before_footer_nav', '__return_false' );
			add_filter( 'tribe_events_list_footer_nav', '__return_false' );
			add_filter( 'tribe_events_list_after_footer_nav', '__return_false' );
			add_filter( 'tribe_events_list_after_footer', '__return_false' );
		}

		public static function remove_comments_template($template) {
			remove_filter('comments_template', array(__CLASS__, 'remove_comments_template') );
			return TribeEvents::instance()->pluginPath . 'admin-views/no-comments.php';
		}

		// Start Single Venue Template
		public static function before_template( $post_id ) {
			$html = '<div id="tribe-events-content" class="tribe-events-venue">';
						$html .= '<p class="tribe-events-back"><a href="' . tribe_get_events_link() . '" rel="bookmark">'. __( '&larr; Back to Events', 'tribe-events-calendar-pro' ) .'</a></p>';			
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_template' );
		}
		// Venue Title
		public static function the_title( $post_id ){
			$html = the_title('<h2 class="entry-title summary">','</h2>', false);
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_the_title' );
		}
		// Start Single Venue
		public static function before_venue( $post_id ) {
			$html = '<div class="tribe-events-venue-meta tribe-clearfix">';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_venue' );
		}		
		// Venue Map
		public static function the_map( $post_id ) {
			$html = '';
			if ( tribe_address_exists( get_the_ID() ) && tribe_embed_google_map( get_the_ID() ) && tribe_get_option( 'embedGoogleMaps' ) ) {
			$html = '<div class="tribe-events-map-wrap">';
			$html .= tribe_get_embedded_map( get_the_ID(), '350px', '200px' );
			$html .= '</div><!-- .tribe-events-map-wrap -->';
			}	
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_map' );
		}
		// Venue Meta
		public static function before_the_meta( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_the_meta' );
		}

		public static function the_meta( $post_id ) {

			$content = get_the_content();
			$content = apply_filters('the_content', $content);
			$content = str_replace(']]>', ']]&gt;', $content);

			$html = sprintf('%s%s%s',
				( get_post_meta( get_the_ID(), '_VenueShowMapLink', true ) !== 'false' ) ? tribe_get_meta('tribe_event_venue_gmap_link'): '',
				tribe_get_meta_group( 'tribe_event_venue' ),
				!empty($content) ? '<div class="tribe-venue-description tribe-content">' . $content . '</div>' : ''
				);
			/*
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
 						<?php if ( tribe_get_venue_website_link() ) : // Venue website ?>
 							<span class="vcard website"><?php echo tribe_get_venue_website_link(); ?></span>
 						<?php endif; ?>		
					</address>
 			<?php endif; ?>
			<?php if ( get_the_content() != '' ): // Venue content ?>
				<div class="venue-description">	
					<?php the_content(); ?>
				</div>	
 			<?php endif ?> 			
<?php
			$html .= ob_get_clean();

			*/
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_the_meta' );
		}
		public static function after_the_meta( $post_id ) {
			$html = '';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_the_meta' );
		}
		// Venue Featued Image
		public static function featured_image( $post_id ){
			$html = '';
			if ( tribe_event_featured_image() ) {
				$html .= tribe_event_featured_image(null, 'full');
			} 
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_featured_image');
		}
		// End Single Venue
		public static function after_venue( $post_id ) {
			$html = '</div><!-- .tribe-events-event-meta -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_venue' );
		}
		// Event List View
		public static function upcoming_events( $venue_id ) {

			// turn off the venue group
			tribe_set_the_meta_visibility( 'tribe_event_venue', false, 'meta_group');

			global $post;
			$args = array(
				'venue' => $post->ID,
				'eventDisplay' => 'upcoming' );

			$html = sprintf( 
				tribe_include_view_list( $args )
				);

			// housekeeping: turn on the venue meta group before we leave
			tribe_set_the_meta_visibility( 'tribe_event_venue', true, 'meta_group');
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
