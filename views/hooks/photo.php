<?php
/**
 * @for Photo Template
 * This file contains the hook logic required to create an effective day grid view.
 *
 * @package TribeEventsCalendarPro
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1');
}

if( !class_exists('Tribe_Events_Photo_Template')){
	class Tribe_Events_Photo_Template extends Tribe_Template_Factory {

		static $timeslots = array();

		public static function init(){
			
			Tribe_PRO_Template_Factory::asset_package('ajax-photoview');			

			add_filter( 'tribe_events_list_show_separators', '__return_false' );

			// Override list methods
			add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'before_template' ), 20, 1);
			add_filter( 'tribe_events_list_header_nav', array( __CLASS__, 'header_navigation' ), 20, 1 );
			add_filter( 'tribe_events_list_before_loop', array( __CLASS__, 'before_loop'), 20, 1);
			add_filter( 'tribe_events_list_inside_before_loop', array( __CLASS__, 'inside_before_loop'), 20, 1);
			add_filter( 'tribe_events_list_the_event_image', '__return_false' );
			add_filter( 'tribe_events_list_the_meta', array( __CLASS__, 'the_meta' ), 20, 1 );
			add_filter( 'tribe_events_list_the_content', array( __CLASS__, 'the_content'), 20, 1);
			add_filter( 'tribe_events_list_footer_nav', array( __CLASS__, 'footer_navigation' ), 20, 1 );
			add_filter( 'tribe_events_list_after_template', array( __CLASS__, 'after_template' ), 20, 1 );
		}
		// Start Photo Template
		public static function before_template( $html ) {
			$html = '<input type="hidden" id="tribe-events-list-hash" value="" />';				
			$html .= '<div id="tribe-events-content" class="tribe-events-list tribe-nav-alt">';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_photo_before_template' );
		}
		// Header Navigation 
		public static function header_navigation( $html ){
			$tribe_ecp = TribeEvents::instance();
			global $wp_query;
			
			$html = '';
			
			// Display Previous Page Navigation
			if ( $wp_query->query_vars['paged'] > 1 ) {
				$html .= '<li class="tribe-nav-previous"><a href="#" class="tribe_paged">' . __( '&laquo; Previous Events' ) . '</a></li>';
			}
			
			// Display Next Page Navigation
			if ( $wp_query->max_num_pages > ( $wp_query->query_vars['paged'] + 1 ) ) {
				$html .= '<li class="tribe-nav-next"><a href="#" class="tribe_paged">' . __( 'Next Events &raquo;' ) . '</a>';			
				$html .= '</li><!-- .tribe-nav-next -->';
			}
			return $html;
		}
		// Start Photo Loop
		public static function before_loop( $pass_through ){
			$html = '<div class="tribe-events-loop hfeed tribe-clearfix" id="tribe-events-photo-events">';
			//$html .='<div id="tribe-photo-loading"><img id="ajax-loading" class="tribe-spinner" src="'. trailingslashit( TribeEvents::instance()->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" /></div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_photo_before_loop');
		}
		public static function inside_before_loop( $pass_through ){
			$post_id = get_the_ID();
			
			// Get our wrapper classes (for event categories, organizer, venue, and defaults)
			$classes = array( 'hentry', 'vevent', 'type-tribe_events', 'tribe-events-photo-event', 'post-' . $post_id, 'tribe-clearfix' );
			$tribe_cat_slugs = tribe_get_event_cat_slugs( $post_id );
			foreach( $tribe_cat_slugs as $tribe_cat_slug ) {
				$classes[] = 'tribe-events-category-'. $tribe_cat_slug;
			}
			if ( $venue_id = tribe_get_venue_id( $post_id ) ) {
				$classes[] = 'tribe-events-venue-'. $venue_id;
			}
			if ( $organizer_id = tribe_get_organizer_id( $post_id ) ) {
				$classes[] = 'tribe-events-organizer-'. $organizer_id;
			}
			$class_string = implode(' ', $classes);

			$html = '<div id="post-'. $post_id .'" class="'. $class_string .'">';

			// show the event featured image
			if ( tribe_event_featured_image() ) {
				$html .= tribe_event_featured_image(null, 'large');
			}
			$html .= '<div class="tribe-events-event-details">';
			return apply_filters('tribe_template_factory_debug', $html , 'tribe_events_day_inside_before_loop');
		}
		// Event Meta
		public static function the_meta( $post_id ){
			ob_start();
		?>
			<div class="tribe-events-event-meta">
				<?php
				global $post;
				echo '<div class="updated published time-details">';
				if ( !empty( $post->distance ) )
					echo '<strong>['. tribe_get_distance_with_unit( $post->distance ) .']</strong>';
				
				echo tribe_events_event_schedule_details(), tribe_events_event_recurring_info_tooltip(); 
				echo '</div>';
				?>
			</div><!-- .tribe-events-event-meta -->
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_meta');
		}
		// Photo Content
		public static function the_content( $post_id ){
			$html = '';
			if ( has_excerpt() )
				$html .= '<p>'. TribeEvents::truncate(get_the_excerpt(), 20) .'</p>';
			else
				$html .= '<p>'. TribeEvents::truncate(get_the_content(), 20) .'</p>';
				
			// Event Categories
			$args = array(
				'before' => '<p class="tribe-event-categories">',
				'sep' => ', ',
				'after' => '</p>'
			);
			$html .= tribe_get_event_taxonomy( get_the_id(), $args );
			
			$html .= '</div><!-- .tribe-events-event-details -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_photo_the_content');
		}
		// Footer Navigation 
		public static function footer_navigation( $html ){
			$tribe_ecp = TribeEvents::instance();
			global $wp_query;
			
			$html = '';
			
			// Display Previous Page Navigation
			if ( $wp_query->query_vars['paged'] > 1 ) {
				$html .= '<li class="tribe-nav-previous"><a href="#" class="tribe_paged">' . __( '&laquo; Previous Events' ) . '</a></li>';
			}
			
			// Display Next Page Navigation
			if ( $wp_query->max_num_pages > ( $wp_query->query_vars['paged'] + 1 ) ) {
				$html .= '<li class="tribe-nav-next"><a href="#" class="tribe_paged">' . __( 'Next Events &raquo;' ) . '</a>';
				$html .= '</li><!-- .tribe-nav-next -->';
			}
			return $html;
		}
		// End Photo Template
		public static function after_template() {
			$tribe_ecp = TribeEvents::instance();
			$html = '<img class="tribe-ajax-loading tribe-spinner photo-loader" src="'. trailingslashit( $tribe_ecp->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" />';
			$html .= '</div>';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_photo_after_template' );
		}
	}
	Tribe_Events_Photo_Template::init();
}