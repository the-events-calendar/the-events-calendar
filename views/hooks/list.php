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

			global $wp_query;

			// Look for a search query
			if( !empty( $wp_query->query_vars['s'] )){
				$search_term = $wp_query->query_vars['s'];
			} else if( !empty($_POST['tribe-bar-search'])) {
				$search_term = $_POST['tribe-bar-search'];
			}

			// Search term based notices
			if( !empty($search_term) && !have_posts() ) {
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'There  were no results found for <strong>"%s"</strong>.', 'tribe-events-calendar' ), $search_term ) );
			}

			// Our various messages if there are no events for the query
			else if ( empty($search_term) && empty( $wp_query->query_vars['s'] ) && !have_posts() ) { // Messages if currently no events, and no search term
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

			// Event title
			add_filter( 'tribe_events_list_the_event_title', array( __CLASS__, 'the_event_title' ), 1, 2 );

			// Event meta
			add_filter( 'tribe_events_list_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 2 );
			add_filter( 'tribe_events_list_the_meta', array( __CLASS__, 'the_meta' ), 1, 2 );
			add_filter( 'tribe_events_list_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 2 );

			// Event featured image
			add_filter( 'tribe_events_list_the_event_image', array( __CLASS__, 'the_event_image' ), 1, 2 );

			// Event content
			add_filter( 'tribe_events_list_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 2 );
			add_filter( 'tribe_events_list_the_content', array( __CLASS__, 'the_content' ), 1, 2 );
			add_filter( 'tribe_events_list_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 2 );
	
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
			add_filter( 'tribe_events_list_after_template', array( __CLASS__, 'after_template' ), 1, 3 );

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
			if ( tribe_is_upcoming() ) {
				$html = '<div id="tribe-events-header" data-title="' . wp_title( '&raquo;', false ) . '" data-baseurl="' . tribe_get_listview_link( false ) . '">';
			} elseif( tribe_is_past() ) {
				$html = '<div id="tribe-events-header" data-title="' . wp_title( '&raquo;', false ) . '" data-baseurl="' . tribe_get_listview_past_link( false ) . '">';
			} else {
				$html = '<div id="tribe-events-header" data-title="' . wp_title( '&raquo;', false ) . '">';
			}
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
			$html = '';
			
			// Only show the header navigation if posts exist.
			if ( have_posts() ) {
				// Left Navigation
				if( tribe_is_past() ) {
					$html = '<li class="tribe-events-nav-next tribe-events-nav-left tribe-events-past">';
					if( get_next_posts_link() ) {
						$html .= '<a href="' . tribe_get_past_link() . '">&laquo; ' . __( 'Previous Events', 'tribe-events-calendar' ) . '</a>';
					}
					$html .= '</li><!-- .tribe-events-nav-previous -->';
				} elseif ( tribe_is_upcoming() ) {
					if( get_previous_posts_link() ) {
						$html = '<li class="tribe-events-nav-previous tribe-events-nav-left">';
						$html .= '<a href="'. tribe_get_upcoming_link() .'" rel="pref">&laquo; '. __( 'Previous Events', 'tribe-events-calendar' ) .'</a>';
					} elseif( !get_previous_posts_link() ) {
						$html = '<li class="tribe-events-nav-previous tribe-events-nav-left tribe-events-past">';
						$html .= '<a href="'. tribe_get_past_link() .'" rel="pref">'. __( '&laquo; Previous Events', 'tribe-events-calendar' ) .'</a>';
					}
					$html .= '</li><!-- .tribe-events-nav-previous -->';
				}
				// Right Navigation
				if( tribe_is_past() ) {
					if( get_query_var( 'paged' ) > 1 ) {
						$html .= '<li class="tribe-events-nav-previous tribe-events-nav-right tribe-events-past">';
						$html .= '<a href="'. tribe_get_past_link() .'" rel="pref">'. __( 'Next Events &raquo;', 'tribe-events-calendar' ) .'</a>';
					} elseif( !get_previous_posts_link() ) {
						$html .= '<li class="tribe-events-nav-previous tribe-events-nav-right">';
						$html .= '<a href="'. tribe_get_upcoming_link() .'" rel="next">'. __( 'Next Events &raquo;', 'tribe-events-calendar' ) .'</a>';
					}
					$html .= '</li><!-- .tribe-events-nav-previous -->';
				} elseif ( tribe_is_upcoming() ) {
					$html .= '<li class="tribe-events-nav-next tribe-events-nav-right">';
					if( get_next_posts_link() ) 
						$html .= '<a href="'. tribe_get_upcoming_link() .'" rel="next">'. __( 'Next Events &raquo;', 'tribe-events-calendar' ) .'</a>';
					$html .= '</li><!-- .tribe-events-nav-previous -->';
				}
				
				// Loading spinner
				$html .= '<img class="tribe-events-ajax-loading tribe-events-spinner-medium" src="'. trailingslashit( $tribe_ecp->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" />';
				$html .= '</li><!-- .tribe-events-nav-next -->';
			}
			
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
			$html = '<div class="tribe-events-loop heed">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_loop');
		}
		public static function inside_before_loop( $content, $post_id, $post ){
			global $wp_query;

			// Get our wrapper classes (for event categories, organizer, venue, and defaults)
			$classes = array( 'hentry', 'vevent', 'type-tribe_events', 'post-' . $post->ID, 'tribe-clearfix' );
			$tribe_cat_slugs = tribe_get_event_cat_slugs( $post->ID );
			foreach( $tribe_cat_slugs as $tribe_cat_slug ) {
				$classes[] = 'tribe-events-category-'. $tribe_cat_slug;
			}
			if ( $venue_id = tribe_get_venue_id( $post->ID ) ) {
				$classes[] = 'tribe-events-venue-'. $venue_id;
			}
			if ( $organizer_id = tribe_get_organizer_id( $post->ID ) ) {
				$classes[] = 'tribe-events-organizer-'. $organizer_id;
			}
			// added first class for css
			if( ( self::$loop_increment == 0 ) && !tribe_is_day() ) {
				$classes[] = 'tribe-events-first';
			}
			// added last class for css
			if( self::$loop_increment == count($wp_query->posts)-1 ) {
				$classes[] = 'tribe-events-last';
			}
			$class_string = implode(' ', $classes);

			/* Month and year separators */

			$show_separators = apply_filters( 'tribe_events_list_show_separators', true );

			if ( $show_separators ) {
				if ( ( tribe_get_start_date( $post, false, 'Y' ) != date( 'Y' ) && self::$prev_event_year != tribe_get_start_date( $post, false, 'Y' ) ) || ( tribe_get_start_date( $post, false, 'Y' ) == date( 'Y' ) && self::$prev_event_year != null && self::$prev_event_year != tribe_get_start_date( $post, false, 'Y' ) ) ) {
					echo sprintf( "<span class='tribe-events-list-separator-year'>%s</span>", tribe_get_start_date( $post, false, 'Y' ) );
				}

				if ( self::$prev_event_month != tribe_get_start_date( $post, false, 'm' ) || ( self::$prev_event_month == tribe_get_start_date( $post, false, 'm' ) && self::$prev_event_year != tribe_get_start_date( $post, false, 'Y' ) ) ) {
					echo sprintf( "<span class='tribe-events-list-separator-month'><span>%s</span></span>", tribe_get_start_date( $post, false, 'F Y' ) );
				}

				self::$prev_event_year  = tribe_get_start_date( $post, false, 'Y' );
				self::$prev_event_month = tribe_get_start_date( $post, false, 'm' );
			}

			$html = '<div id="post-' . get_the_ID() . '" class="'. $class_string .'">';
			if ( tribe_get_cost() ) // Get our event cost 
				$html .= '<div class="tribe-events-event-cost"><span>'. tribe_get_cost( null, true ) .'</span></div>';
				return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_list_inside_before_loop' );
		}							
		// Event Title
		public static function the_event_title( $content, $post_id ){
			global $post;
			$html = '';
			if ( !empty( $post->distance ) )
				$html = '<span class="tribe-events-distance">['. tribe_get_distance_with_unit( $post->distance ) .']</span>';
			$html .= '<h2 class="entry-title summary"><a class="url" href="'. tribe_get_event_link() .'" title="'. get_the_title( $post_id ) .'" rel="bookmark">'. get_the_title( $post_id ) .'</a></h2>';
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
				<?php

				echo '<div class="updated published time-details">';
				echo tribe_events_event_schedule_details(), tribe_events_event_recurring_info_tooltip(); 
				echo '</div>';
				
				// Venue display info
				$venue_name = tribe_get_meta( 'tribe_event_venue_name' );
				$venue_address = tribe_get_meta( 'tribe_event_venue_address' );
				
				if( !empty( $venue_name ) && !empty( $venue_address ) )
					printf('<div class="tribe-events-venue-details">%s%s%s</div>',
						$venue_name,
						( !empty( $venue_name ) && !empty( $venue_address ) ) ? ', ' : '',
						( !empty( $venue_address ) ) ? $venue_address : ''
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
		// Event Image
		public static function the_event_image( $content, $post_id ){
			$html ='';
			if ( tribe_event_featured_image() ) {
				$html .= tribe_event_featured_image(null, 'large');
			}
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_event_image');

		}		
		// Event Content
		public static function before_the_content( $content, $post_id ){
			$html = '<div class="tribe-events-list-event-description tribe-events-content entry-summary">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_the_content');
		}
		public static function the_content( $content, $post_id ){
			$html = '';
			if ( has_excerpt() )
				$html .= '<p>'. TribeEvents::truncate( $post_id->post_excerpt, 80 ) .'</p>';	
			else
				$html .= '<p>'. TribeEvents::truncate( get_the_content(), 80 ) .'</p>';
			
			$html .= '<a href="'. tribe_get_event_link() .'" class="tribe-events-read-more">'. __('Find out more', 'tribe-events-calendar') . ' &raquo;</a>';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_content');
		}
		public static function after_the_content( $content, $post_id ){
			$html = '</div><!-- .tribe-events-list-event-description -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_the_content');
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
			if( get_previous_posts_link() || tribe_get_past_link() || tribe_get_upcoming_link() ) {
				$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Events List Navigation', 'tribe-events-calendar' ) .'</h3>';
				$html .= '<ul class="tribe-events-sub-nav">';
			}
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_footer_nav');
		}
		public static function footer_navigation( $content, $post_id ){
			$tribe_ecp = TribeEvents::instance();
			$html = '';
			
			// Left Navigation
			if( tribe_is_past() ) {
				$html = '<li class="tribe-events-nav-next tribe-events-nav-left tribe-events-past">';
				if( get_next_posts_link() ) {
					$html .= '<a href="' . tribe_get_past_link() . '">'. __( '&laquo; Previous Events', 'tribe-events-calendar' ) . '</a>';
				}
				$html .= '</li><!-- .tribe-events-nav-previous -->';
			} elseif ( tribe_is_upcoming() ) {
				if( get_previous_posts_link() ) {
					$html = '<li class="tribe-events-nav-previous tribe-events-nav-left">';
					$html .= get_previous_posts_link( __( '&laquo; Previous Events', 'tribe-events-calendar' ) );
				} elseif( !get_previous_posts_link() ) {
					$html = '<li class="tribe-events-nav-previous tribe-events-nav-left tribe-events-past">';
					$html .= '<a href="'. tribe_get_past_link() .'" rel="prev">'. __( '&laquo; Previous Events', 'tribe-events-calendar' ) .'</a>';
				}
				$html .= '</li><!-- .tribe-events-nav-previous -->';
			}
			
			// Right Navigation
			if( tribe_is_past() ) {
				if( get_previous_posts_link() ) {
					$html .= '<li class="tribe-events-nav-previous tribe-events-nav-right tribe-events-past">';
					$html .= get_previous_posts_link( __( 'Next Events &raquo;', 'tribe-events-calendar' ) );
				} elseif( !get_previous_posts_link() ) {
					$html .= '<li class="tribe-events-nav-previous tribe-events-nav-right">';
					$html .= '<a href="'. tribe_get_upcoming_link() .'" rel="next">'. __( 'Next Events &raquo;', 'tribe-events-calendar' ) .'</a>';
				}
				$html .= '</li><!-- .tribe-events-nav-previous -->';
			} elseif ( tribe_is_upcoming() ) {
				$html .= '<li class="tribe-events-nav-next tribe-events-nav-right">';
				if( get_next_posts_link() ) 
					$html .= '<a href="'. tribe_get_upcoming_link() .'" rel="next">'. __( 'Next Events &raquo;', 'tribe-events-calendar' ) .'</a>';
				$html .= '</li><!-- .tribe-events-nav-previous -->';
			}
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_footer_nav');
		}
		public static function after_footer_nav( $content, $post_id ){
			if( get_previous_posts_link() || tribe_get_past_link() || tribe_get_upcoming_link() )
				$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_footer_nav');
		}
		public static function after_footer( $content, $post_id ){
			$html = '</div><!-- #tribe-events-footer -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_footer');
		}
		// End List Template
		public static function after_template( $html, $hasPosts = false, $post_id ){
			$html = '';
			$disable_ical = apply_filters( 'tribe_events_list_show_ical_link', true );
			if ( $disable_ical && !empty($hasPosts) && function_exists('tribe_get_ical_link')) // iCal Import
				$html .= '<a class="tribe-events-ical tribe-events-button" title="'. __( 'iCal Import', 'tribe-events-calendar' ) .'" href="'. tribe_get_ical_link() .'">'. __( '+ iCal Import', 'tribe-events-calendar' ) .'</a>';
				
			$html .= '</div><!-- #tribe-events-content -->';
			$html .= '<div class="tribe-clear"></div>';
			$html .= tribe_events_promo_banner( false );
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_template');		
		}
	}
	Tribe_Events_List_Template::init();
}