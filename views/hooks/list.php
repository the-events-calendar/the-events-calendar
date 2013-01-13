<?php
/**
 * @for Events List Template
 * This file contains the hook logic required to create an effective event list view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_List_Template')){
	class Tribe_Events_List_Template extends Tribe_Template_Factory {

		private $first = true;
		static $loop_increment = 0;
		static $prev_event_month = null;
		static $prev_event_year = null;

		public static function init(){

			// customize meta items
			tribe_set_the_meta_template( 'tribe_event_venue_name', array(
				'before'=>'',
				'after'=>'',
				'label_before'=>'',
				'label_after'=>'',
				'meta_before'=>'<span class="%s">',
				'meta_after'=>'</span>'
			));
			tribe_set_meta_label( 'tribe_event_venue_address', '' );
			tribe_set_the_meta_template( 'tribe_event_venue_address', array(
				'before'=>'',
				'after'=>'',
				'label_before'=>'',
				'label_after'=>'',
				'meta_before'=>'',
				'meta_after'=>''
			));
			tribe_set_the_meta_visibility( 'tribe_event_venue_gmap_link', false );

			// Our various messages if there are no events for the query
			if ( ! have_posts() ) { // Messages if currently no events
				$tribe_ecp = TribeEvents::instance();
				$is_cat_message = '';
				if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
					$cat = get_term_by( 'slug', get_query_var( 'term' ), $tribe_ecp->get_event_taxonomy() );
					if( tribe_is_upcoming() ) {
						$is_cat_message = sprintf( __( 'listed under %s. Check out past events for this category or view the full calendar.', 'tribe-events-calendar' ), $cat->name );
					} else if( tribe_is_past() ) {
						$is_cat_message = sprintf( __( 'listed under %s. Check out upcoming events for this category or view the full calendar.', 'tribe-events-calendar' ), $cat->name );
					}
				}
				if( tribe_is_day() ) {
					TribeEvents::setNotice( 'events-not-found', sprintf( __( 'No events scheduled for <strong>%s</strong>. Please try another day.', 'tribe-events-calendar' ), date_i18n( 'F d, Y', strtotime( get_query_var( 'eventDate' ) ) ) ) );
				} elseif( tribe_is_upcoming() ) {
					$date = date('Y-m-d', strtotime($tribe_ecp->date));
					if ( $date == date('Y-m-d') ) {
						TribeEvents::setNotice( 'events-not-found', __('No upcoming events ', 'tribe-events-calendar') . $is_cat_message );
					} else {
						TribeEvents::setNotice( 'events-not-found', __('No matching events ', 'tribe-events-calendar') . $is_cat_message );
					}
				} elseif( tribe_is_past() ) {
					TribeEvents::setNotice( 'events-past-not-found', __('No previous events ', 'tribe-events-calendar') . $is_cat_message );
				}
			}

			// Start list template
			add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'before_template' ), 1, 2 );
	
			// Page Title
			add_filter( 'tribe_events_list_the_title', array( __CLASS__, 'the_title' ), 1, 2 );
			
			// List header
			add_filter( 'tribe_events_list_before_header', array( __CLASS__, 'before_header' ), 1, 2 );
			
			// Navigation
			add_filter( 'tribe_events_list_before_header_nav', array( __CLASS__, 'before_header_nav' ), 1, 2 );
			add_filter( 'tribe_events_list_header_nav', array( __CLASS__, 'header_navigation' ), 1, 2 );
			add_filter( 'tribe_events_list_after_header_nav', array( __CLASS__, 'after_header_nav' ), 1, 2 );
			
			add_filter( 'tribe_events_list_after_header', array( __CLASS__, 'after_header' ), 1, 2 );

			// Start list loop
			add_filter( 'tribe_events_list_before_loop', array( __CLASS__, 'before_loop' ), 1, 2 );
			add_filter( 'tribe_events_list_inside_before_loop', array( __CLASS__, 'inside_before_loop' ), 1, 3 );
		
			// Event featured image
			add_filter( 'tribe_events_list_the_event_image', array( __CLASS__, 'the_event_image' ), 1, 2 );

			// Event details start
			add_filter( 'tribe_events_list_before_the_event_details', array( __CLASS__, 'before_the_event_details' ), 1, 2 );

			// Event title
			add_filter( 'tribe_events_list_the_event_title', array( __CLASS__, 'the_event_title' ), 1, 2 );

			// Event content
			add_filter( 'tribe_events_list_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 2 );
			add_filter( 'tribe_events_list_the_content', array( __CLASS__, 'the_content' ), 1, 2 );
			add_filter( 'tribe_events_list_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 2 );
	
			// Event meta
			add_filter( 'tribe_events_list_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 2 );
			add_filter( 'tribe_events_list_the_meta', array( __CLASS__, 'the_meta' ), 1, 2 );
			add_filter( 'tribe_events_list_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 2 );

			// Event details end
			add_filter( 'tribe_events_list_after_the_event_details', array( __CLASS__, 'after_the_event_details' ), 1, 2 );			
	
			// End list loop
			add_filter( 'tribe_events_list_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 1, 2 );
			add_filter( 'tribe_events_list_after_loop', array( __CLASS__, 'after_loop' ), 1, 2 );
	
			// Event notices
			add_filter( 'tribe_events_list_notices', array( __CLASS__, 'notices' ), 1, 1 );

			// List footer
			add_filter( 'tribe_events_list_before_footer', array( __CLASS__, 'before_footer' ), 1, 2 );
			
			// Navigation
			add_filter( 'tribe_events_list_before_footer_nav', array( __CLASS__, 'before_footer_nav' ), 1, 2 );
			add_filter( 'tribe_events_list_footer_nav', array( __CLASS__, 'footer_navigation' ), 1, 2 );
			add_filter( 'tribe_events_list_after_footer_nav', array( __CLASS__, 'after_footer_nav' ), 1, 2 );
			
			add_filter( 'tribe_events_list_after_footer', array( __CLASS__, 'after_footer' ), 1, 2 );

			// End list template
			add_filter( 'tribe_events_list_after_template', array( __CLASS__, 'after_template' ), 1, 2 );

			do_action('tribe_events_list_template_init');
		}
		// Start List Template
		public static function before_template( $content, $post_id ){
			$html = '<div id="tribe-events-content" class="tribe-events-list">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_template');
		}
		public static function the_title( $content, $post_id ){
			$html = sprintf( '<h2 class="tribe-events-page-title">%s</h2>',
				tribe_get_events_title()
				);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_title');
		}
		// List Header
		public static function before_header( $content, $post_id ){
			$html = '<div id="tribe-events-header" data-title="' . wp_title( '&raquo;', false ) . '">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_header');
		}
		// List Navigation
		public static function before_header_nav( $content, $post_id ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Events List Navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_header_nav');
		}
		public static function header_navigation( $content, $post_id ){
			$tribe_ecp = TribeEvents::instance();
			
			// Display Previous Page Navigation
			$html = '<li class="tribe-nav-previous">';
			if(tribe_is_upcoming() && get_previous_posts_link())
				$html .= get_previous_posts_link( __( '&larr; Previous Events', 'tribe-events-calendar' ) );
			elseif(tribe_is_upcoming() && !get_previous_posts_link())
				$html .= '<a href="'. tribe_get_past_link() .'" rel="prev">'. __( '&larr; Previous Events', 'tribe-events-calendar' ) .'</a>';
			elseif(tribe_is_past() && get_next_posts_link()) 
				$html .= get_next_posts_link( __( '&larr; Previous Events', 'tribe-events-calendar' ) );
			$html .= '</li><!-- .tribe-nav-previous -->';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next">';
			if(tribe_is_upcoming() && get_next_posts_link())
				$html .= get_next_posts_link( __( 'Next Events &rarr;', 'tribe-events-calendar' ) );
			elseif(tribe_is_past() && get_previous_posts_link())
				$html .= get_previous_posts_link( __( 'Next Events &rarr;', 'tribe-events-calendar' ) );
			elseif(tribe_is_past() && !get_previous_posts_link()) 
				$html .= '<a href="'. tribe_get_upcoming_link() .'" rel="next">'. __( 'Next Events &rarr;', 'tribe-events-calendar' ) .'</a>';
			
			// Loading spinner
			$html .= '<img class="tribe-ajax-loading tribe-spinner-medium" src="'. trailingslashit( $tribe_ecp->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" />';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_header_nav');
		}
		public static function after_header_nav( $content, $post_id ){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_header_nav');
		}
		public static function after_header( $content, $post_id ){
			$html = '</div><!-- #tribe-events-header -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_header');
		}
		// Start List Loop
		public static function before_loop( $content, $post_id ){
			$html = '<div class="tribe-events-loop hfeed">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_loop');
		}
		public static function inside_before_loop( $content, $post_id, $post ){
			global $wp_query;
			// Get our wrapper classes (for event categories, organizer, venue, and defaults)
			$tribe_string_classes = '';
			$tribe_cat_ids = tribe_get_event_cat_ids( $content, $post_id ); 
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
			
			// added first class for css
			if( ( self::$loop_increment == 0 ) && !tribe_is_day() ){
				$class_string .= ' tribe-first';
			}
			
			// added last class for css
			if( self::$loop_increment == count($wp_query->posts)-1 ){
				$class_string .= ' tribe-last';
			}

			/* Month and year separators */

			$show_separators = apply_filters( 'tribe_events_list_show_separators', true );

			if ( $show_separators ) {
				if ( ( tribe_get_start_date( $post_id, false, 'Y' ) != date( 'Y' ) && self::$prev_event_year != tribe_get_start_date( $post, false, 'Y' ) ) || ( tribe_get_start_date( $post_id, false, 'Y' ) == date( 'Y' ) && self::$prev_event_year != null && self::$prev_event_year != tribe_get_start_date( $post, false, 'Y' ) ) ) {
					echo sprintf( "<span class='tribe_list_separator_year'>%s</span>", tribe_get_start_date( $post, false, 'Y' ) );
				}

				if ( self::$prev_event_month != tribe_get_start_date( $post, false, 'm' ) || ( self::$prev_event_month == tribe_get_start_date( $post, false, 'm' ) && self::$prev_event_year != tribe_get_start_date( $post_id, false, 'Y' ) ) ) {
					echo sprintf( "<span class='tribe_list_separator_month'>%s</span>", tribe_get_start_date( $post, false, 'F' ) );
				}

				self::$prev_event_year  = tribe_get_start_date( $post, false, 'Y' );
				self::$prev_event_month = tribe_get_start_date( $post, false, 'm' );
			}

			$html = '<div id="post-' . get_the_ID() . '" class="' . $class_string . ' tribe-clearfix">';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_list_inside_before_loop' );
		}

		// Event Image
		public static function the_event_image( $content, $post_id ){
			$html ='';
			if ( tribe_event_featured_image() ) {
				$html .= tribe_event_featured_image(null, 'large');
			}
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_event_image');

		}
		// Event Details Begin
		public static function before_the_event_details ( $content, $post_id ){
			$html = '<div class="tribe-events-event-details">';
			if ( tribe_get_cost() ) { // Get our event cost 
				$html .=	'<div class="tribe-events-event-cost"><span>'. tribe_get_cost( null, true ) .'</span></div>';
			 } 				
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_the_event_details'); 
		}							
		// Event Title
		public static function the_event_title( $content, $post_id ){
			$html = '<h2 class="entry-title summary"><a class="url" href="'. tribe_get_event_link() .'" title="'. get_the_title( $post_id ) .'" rel="bookmark">'. get_the_title( $post_id ) .'</a></h2>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_event_title');
		}
		// Event Meta
		public static function before_the_meta( $content, $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_the_meta');
		}
		public static function the_meta( $content, $post_id ){
			ob_start();
		?>
			<div class="tribe-events-event-meta">
				<h3 class="updated published time-details">
					<?php
					global $post;
					if ( !empty( $post->distance ) ) { ?>
						<strong><?php echo '['. tribe_get_distance_with_unit( $post->distance ) .']'; ?></strong>
					<?php } ?>
					<?php echo tribe_events_event_schedule_details(), tribe_events_event_recurring_info_tooltip(); ?>
				</h3>
				<?php // venue display info

				$venue_name = tribe_get_meta( 'tribe_event_venue_name' );
				$venue_address = tribe_get_meta('tribe_event_venue_address');
				
				printf('<h3 class="tribe-venue-details">%s%s%s</h3>',
					'|'.$venue_name.'|',
					( !empty( $venue_name ) && !empty( $venue_address ) ) ? ', ' : '',
					( !empty( $venue_address ) ) ? '|'.$venue_address.'|' : ''
				);

				?>
			</div><!-- .tribe-events-event-meta -->
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_meta');
		}
		public static function after_the_meta( $content, $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_the_meta');
		}			
		// Event Content
		public static function before_the_content( $content, $post_id ){
			$html = '<div class="tribe-list-event-description tribe-content">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_the_content');
		}
		public static function the_content( $content, $post_id ){
			$html = '';
			if (has_excerpt())
				$html .= '<p>'. get_the_excerpt() .'</p>';
			else
				$html .= '<p>'. TribeEvents::truncate(get_the_content(), 80) .'</p>';	
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_content');
		}
		public static function after_the_content( $content, $post_id ){
			$html = '</div><!-- .tribe-list-event-description -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_the_content');
		}		
		// Event Details End
		public static function after_the_event_details ( $content, $post_id ){
			$html = '</div><!-- .tribe-events-event-details -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_the_event_details'); 
		}	

		// End List Loop
		public static function inside_after_loop( $content, $post_id ){

			// internal increment to keep track of position within the loop
			self::$loop_increment++;

			$html = '</div><!-- .hentry .vevent -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_inside_after_loop');
		}
		// Event Notices
		public static function notices( $post_id ) {
			$html = tribe_events_the_notices(false);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_notices');
		}
		public static function after_loop( $content, $post_id ){
			$html = '</div><!-- .tribe-events-loop -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_loop');
		}
		// List Footer
		public static function before_footer( $content, $post_id ){
			$html = '<div id="tribe-events-footer">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_footer');
		}
		// List Navigation
		public static function before_footer_nav( $content, $post_id ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Events List Navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_footer_nav');
		}
		public static function footer_navigation( $content, $post_id ){
			$tribe_ecp = TribeEvents::instance();
			
			// Display Previous Page Navigation
			$html = '<li class="tribe-nav-previous">';
			if(tribe_is_upcoming() && get_previous_posts_link())
				$html .= get_previous_posts_link( __( '&larr; Previous Events', 'tribe-events-calendar' ) );
			elseif(tribe_is_upcoming() && !get_previous_posts_link())
				$html .= '<a href="'. tribe_get_past_link() .'" rel="prev">'. __( '&larr; Previous Events', 'tribe-events-calendar' ) .'</a>';
			elseif(tribe_is_past() && get_next_posts_link()) 
				$html .= get_next_posts_link( __( '&larr; Previous Events', 'tribe-events-calendar' ) );
			$html .= '</li><!-- .tribe-nav-previous -->';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next">';
			if(tribe_is_upcoming() && get_next_posts_link())
				$html .= get_next_posts_link( __( 'Next Events &rarr;', 'tribe-events-calendar' ) );
			elseif(tribe_is_past() && get_previous_posts_link())
				$html .= get_previous_posts_link( __( 'Next Events &rarr;', 'tribe-events-calendar' ) );
			elseif(tribe_is_past() && !get_previous_posts_link()) 
				$html .= '<a href="'. tribe_get_upcoming_link() .'" rel="next">'. __( 'Next Events &rarr;', 'tribe-events-calendar' ) .'</a>';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_footer_nav');
		}
		public static function after_footer_nav( $content, $post_id ){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_footer_nav');
		}
		public static function after_footer( $content, $post_id ){
			$html = '</div><!-- #tribe-events-footer -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_footer');
		}
		// End List Template
		public static function after_template( $hasPosts = false, $post_id ){
			$html = '';
			if (!empty($hasPosts) && function_exists('tribe_get_ical_link')) // iCal Import
				$html .= '<a class="tribe-events-ical tribe-events-button-grey" title="'. __( 'iCal Import', 'tribe-events-calendar' ) .'" href="'. tribe_get_ical_link() .'">'. __( 'iCal Import', 'tribe-events-calendar' ) .'</a>';
				
			$html .= '</div><!-- #tribe-events-content -->';
			$html .= '<div class="tribe-clear"></div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_template');		
		}
	}
	Tribe_Events_List_Template::init();
}
