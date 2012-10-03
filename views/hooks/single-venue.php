<?php
/**
 * @for Single Venue Template
 * This file contains the hook logic required to create an effective single venue view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */
 
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Pro_Single_Venue_Template')){
	class Tribe_Events_Pro_Single_Venue_Template extends Tribe_Template_Factory {
		public static function init(){
			// Start single venue template
			add_filter( 'tribe_events_single_venue_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// Start single venue
			add_filter( 'tribe_events_single_venue_before_venue', array( __CLASS__, 'before_venue' ), 1, 1 );
	
			// Venue map
			add_filter( 'tribe_events_single_venue_map', array( __CLASS__, 'the_map' ), 1, 1 );
		
			// Venue meta
			add_filter( 'tribe_events_single_venue_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_venue_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_venue_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );

			// End single venue
			add_filter( 'tribe_events_single_venue_after_venue', array( __CLASS__, 'after_venue' ), 1, 1 );
	
			// Start upcoming event loop
			add_filter( 'tribe_events_single_venue_event_before_loop', array( __CLASS__, 'event_before_loop' ), 1, 2 );
	
			// Venue loop title
			add_filter( 'tribe_events_single_venue_event_loop_title', array( __CLASS__, 'event_loop_title' ), 1, 2 );
			
			add_filter( 'tribe_events_single_venue_event_inside_before_loop', array( __CLASS__, 'event_inside_before_loop' ), 1, 2);
			
			// Event start date
			add_filter( 'tribe_events_single_venue_event_the_start_date', array( __CLASS__, 'event_the_start_date' ), 1, 2 );
			
			// Event title
			add_filter( 'tribe_events_single_venue_event_the_title', array( __CLASS__, 'event_the_title' ), 1, 2 );

			// Event content
			add_filter( 'tribe_events_single_venue_event_before_the_content', array( __CLASS__, 'event_before_the_content' ), 1, 2 );
			add_filter( 'tribe_events_single_venue_event_the_content', array( __CLASS__, 'event_the_content' ), 1, 2 );
			add_filter( 'tribe_events_single_venue_event_after_the_content', array( __CLASS__, 'event_after_the_content' ), 1, 2 );
			
			// Event meta
			add_filter( 'tribe_events_single_venue_event_before_the_meta', array( __CLASS__, 'event_before_the_meta' ), 1, 2 );
			add_filter( 'tribe_events_single_venue_event_the_meta', array( __CLASS__, 'event_the_meta' ), 1, 2 );
			add_filter( 'tribe_events_single_venue_event_after_the_meta', array( __CLASS__, 'event_after_the_meta' ), 1, 2 );
		
			add_filter( 'tribe_events_single_venue_event_inside_after_loop', array( __CLASS__, 'event_inside_after_loop' ), 1, 2 );
			
			// End upcoming event loop
			add_filter( 'tribe_events_single_venue_event_after_loop', array( __CLASS__, 'event_after_loop' ), 1, 2 );
	
			// End single venue template
			add_filter( 'tribe_events_single_venue_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		// Start Single Venue Template
		public function before_template( $post_id ){
			$html = '<div id="tribe-events-content" class="tribe-events-venue">';
			$html .= '<p class="tribe-events-back"><a href="' . tribe_get_events_link() . '" rel="bookmark">'. __('&laquo; Back to Events', 'tribe-events-calendar-pro') .'</a></p>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_template');
		}
		// Start Single Venue
		public function before_venue( $post_id ){
			$html = '<div class="tribe-events-event-meta">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_venue');
		}
		// Venue Map
		public function the_map( $post_id ){
			$html = '<div class="tribe-events-map-wrap">';
			$html .= tribe_get_embedded_map( get_the_ID(), '350px', '200px' );
			$html .= '</div><!-- .tribe-events-map-wrap -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_map');
		}
		// Venue Meta
		public function before_the_meta( $post_id ){
			$html = '<dl class="tribe-events-column">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_before_the_meta');
		}
		public function the_meta( $post_id ){
			ob_start();
		?>
			
			<dt><?php echo __( 'Name:', 'tribe-events-calendar-pro' ); ?></dt> 
			<dd class="vcard fn org"><?php the_title(); ?></dd>
		
			<?php if( tribe_get_phone() ) : // Venue phone ?>
				<dt><?php echo __( 'Phone:', 'tribe-events-calendar-pro' ); ?></dt> 
 				<dd class="vcard tel"><?php echo tribe_get_phone(); ?></dd>
 			<?php endif; ?>
 		
			<?php if( tribe_address_exists( get_the_ID() ) ) : // Venue address ?>
				<dt><?php echo __( 'Address:', 'tribe-events-calendar-pro' ); ?><br />
					<?php if( get_post_meta( get_the_ID(), '_EventShowMapLink', true ) == 'true' ) : ?>
					<a class="tribe-events-gmap" href="<?php echo tribe_get_map_link(); ?>" title="<?php _e( 'Click to view a Google Map', 'tribe-events-calendar-pro' ); ?>" target="_blank"><?php _e( 'Google Map', 'tribe-events-calendar' ); ?></a>
					<?php endif; ?>
				</dt>
 				<dd>
					<?php echo tribe_get_full_address( get_the_ID() ); ?>
 				</dd>
 			<?php endif; ?>
		
			<?php if ( get_the_content() != '' ): // Venue content ?>
				<dt><?php echo __( 'Description:', 'tribe-events-calendar-pro' ); ?></dt>
				<dd class="entry-content"><?php the_content(); ?></dd>
 			<?php endif ?>			
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_the_meta');
		}
		public function after_the_meta( $post_id ){
			$html = '</dl><!-- .tribe-events-column -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_the_meta');
		}
		// End Single Venue
		public function after_venue( $post_id ){
			$html = '</div><!-- .tribe-events-event-meta -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_venue');
		}
		// Start Upcoming Event Loop
		public function event_before_loop( $post_id ){
			$html = '<div class="tribe-events-loop hfeed">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_event_before_loop');
		}
		// Venue Loop Title
		public function event_loop_title( $post_id ){
			$html = '<h2 class="tribe-events-page-title">'. __( 'Upcoming Events At This Venue', 'tribe-events-calendar-pro' ) .'</h2>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_event_loop_title');
		}
		
		public function event_inside_before_loop( $post_id, $event ){
		 	// Get our wrapper classes (for event categories, organizer, venue, and defaults)
			$tribe_string_classes = '';
			$tribe_cat_ids = tribe_get_event_cat_ids( $post_id ); 
			foreach( $tribe_cat_ids as $tribe_cat_id ) { 
				$tribe_string_classes .= 'tribe-events-category-'. $tribe_cat_id .' '; 
			}
			$tribe_string_wp_classes = '';
			$allClasses = get_post_class(); 
			foreach ($allClasses as $class) { 
				$tribe_string_wp_classes .= $class . ' '; 
			}
			$tribe_classes_default = 'hentry vevent '. $tribe_string_wp_classes;
			$tribe_classes_venue = tribe_get_venue_id() ? 'tribe-events-venue-'. tribe_get_venue_id() : '';
			$tribe_classes_organizer = tribe_get_organizer_id() ? 'tribe-events-organizer-'. tribe_get_organizer_id() : '';
			$tribe_classes_categories = $tribe_string_classes;
			$class_string = $tribe_classes_default .' '. $tribe_classes_venue .' '. $tribe_classes_organizer .' '. $tribe_classes_categories;
			
			$html = '<div id="post-'. get_the_ID() .'" class="'. $class_string .'">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_event_inside_before_loop');
		}
			
		// Event Start Date
		public function event_the_start_date( $post_id, $event ){
			global $post;
			$post = $event;
			setup_postdata($post);
			$html = '';
			if(tribe_is_new_event_day())
 				$html .= '<h3><abbr class="tribe-events-abbr updated published dtstart" title="'. tribe_get_start_date( $post_id, false, TribeDateUtils::DBDATEFORMAT ) .'">'. tribe_get_start_date( $post_id, false ) .'</abbr></h3>';
 			wp_reset_postdata();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_event_the_start_date');
		}
		// Event Title
		public function event_the_title( $post_id, $event ){
			global $post;
			$post = $event;
			setup_postdata($post);
			$html = '<h2 class="entry-title summary"><a class="url" href="'. tribe_get_event_link() .'" title="'. get_the_title() .'" rel="bookmark">'. get_the_title() .'</a></h2>';
			wp_reset_postdata();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_event_the_title');
		}
		// Event Content
		public function event_before_the_content( $post_id, $event ){
			$html = '<div class="entry-content description">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_event_before_the_content');
		}
		public function event_the_content( $post_id, $event ){
			global $post;
			$post = $event;
			setup_postdata($post);
			$html = '';
			if (has_excerpt())
				$html .= '<p>'. TribeEvents::truncate($post->post_excerpt) .'</p>';
			else
				$html .= '<p>'. TribeEvents::truncate(get_the_content(), 40) .'</p>';
			wp_reset_postdata();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_event_the_content');
		}
		public function event_after_the_content( $post_id, $event ){
			$html = '</div><!-- .entry-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_event_after_the_content');
		}
		// Event Meta
		public function event_before_the_meta( $post_id, $event ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_event_before_the_meta');
		}
		public function event_the_meta( $post_id, $event ){
			global $post;
			$post = $event;
			setup_postdata($post);
			ob_start();
?>
			<div class="tribe-events-event-meta">
				<dl>
 				<?php if ( tribe_is_multiday() || !tribe_get_all_day() ) : // Get our event dates ?>
					
					<dt><?php _e( 'Start:', 'tribe-events-calendar' ); ?></dt>
					<dd class="updated published dtstart">
						<abbr class="tribe-events-abbr" title="<?php echo tribe_get_start_date( null, false, TribeDateUtils::DBDATEFORMAT ); ?>"><?php echo tribe_get_start_date(); ?></abbr>	
					</dd><!-- .dtstart -->
					
					<dt><?php _e( 'End:', 'tribe-events-calendar' ); ?></dt>
					<dd class="dtend">
						<abbr class="tribe-events-abbr" title="<?php echo tribe_get_end_date( null, false, TribeDateUtils::DBDATEFORMAT ); ?>"><?php echo tribe_get_end_date(); ?></abbr>	
					</dd><!-- .dtend -->

				<?php else: ?>
					
					<dt><?php _e( 'Date:', 'tribe-events-calendar' ); ?></dt>
					<dd class="updated published dtstart">
						<abbr class="tribe-events-abbr" title="<?php echo tribe_get_start_date( null, false, TribeDateUtils::DBDATEFORMAT ); ?>"><?php echo tribe_get_start_date(); ?></abbr>
					</dd><!-- .dtstart -->	
					
				<?php endif; ?>
				
				<?php if ( tribe_get_cost() ) { // Get our event cost ?>
					<dt><?php _e( 'Cost:', 'tribe-events-calendar' ); ?></dt>
					<dd class="tribe-events-event-cost">
						<?php echo tribe_get_cost(); ?>
					</dd><!-- .tribe-events-event-cost -->
				<?php } ?>
 				</dl>
 			</div><!-- tribe-events-event-meta -->			
<?php
			$html = ob_get_clean();
			wp_reset_postdata();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_event_the_meta');
		}
		public function event_after_the_meta( $post_id, $event ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_event_after_the_meta');
		}
		
		public function event_inside_after_loop( $post_id, $event ){
			$html = '</div><!-- .hentry .vevent -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_event_inside_after_loop');
		}
		
		// End Upcoming Event Loop
		public function event_after_loop( $post_id ){
			$html = '</div><!-- .tribe-events-loop -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_event_after_loop');
		}	
		// End Single Venue Template
		public function after_template( $post_id ){
			$html = '</div><!-- #tribe-events-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_venue_after_template');
		}
	}
	Tribe_Events_Pro_Single_Venue_Template::init();
}