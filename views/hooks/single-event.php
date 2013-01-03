<?php
/**
 * @for Single Event Template
 * This file contains the hook logic required to create an effective single event view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Single_Event_Template')){
	class Tribe_Events_Single_Event_Template extends Tribe_Template_Factory {
		public static function init(){

			// Check if event has passed
			$gmt_offset = (get_option('gmt_offset') >= '0' ) ? ' +' . get_option('gmt_offset') : " " . get_option('gmt_offset');
		 	$gmt_offset = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $gmt_offset );
		 	if ( strtotime( tribe_get_end_date( get_the_ID(), false, 'Y-m-d G:i' ) . $gmt_offset ) <= time() ) { 
		 		TribeEvents::setNotice( 'event-past', __('This event has passed.', 'tribe-events-calendar') );
		 	} 

			// Start single template
			add_filter( 'tribe_events_single_event_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			add_filter( 'tribe_events_single_event_featured_image', array(__CLASS__,'featured_image'), 1, 1);

			// Event title
			add_filter( 'tribe_events_single_event_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_title', array( __CLASS__, 'the_title' ), 1, 2 );
			add_filter( 'tribe_events_single_event_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );

			// Event notices
			add_filter( 'tribe_events_single_event_notices', array( __CLASS__, 'notices' ), 1, 1 );

			// Event content
			add_filter( 'tribe_events_single_event_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_content', array( __CLASS__, 'the_content' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 1 );

			// Event meta
			add_filter( 'tribe_events_single_event_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_event_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );

			// Event pagination
			add_filter( 'tribe_events_single_event_before_pagination', array( __CLASS__, 'before_pagination' ), 1, 1 );
			add_filter( 'tribe_events_single_event_pagination', array( __CLASS__, 'pagination' ), 1, 1 );
			add_filter( 'tribe_events_single_event_after_pagination', array( __CLASS__, 'after_pagination' ), 1, 1 );

			// End single template
			add_filter( 'tribe_events_single_event_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		// Start Single Template
		public static function before_template( $post_id ){
			$html = '<div id="tribe-events-content" class="tribe-events-single">';
			$html .= '<p class="tribe-events-back"><a href="' . tribe_get_events_link() . '" rel="bookmark">'. __( '&laquo; Back to Events', 'tribe-events-calendar-pro' ) .'</a></p>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_template');
		}
		public static function featured_image( $post_id ){
			$html = '';
			if ( tribe_event_featured_image() ) {
				$html .= tribe_event_featured_image(null, 'full');
			}
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_featured_image');
		}
		// Event Title
		public static function before_the_title( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_title');
		}
		public static function the_title( $post_id ){
			$html = the_title('<h2 class="entry-title summary">','</h2>', false);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_the_title');
		}
		public static function after_the_title( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_title');
		}
		// Event Notices
		public static function notices( $post_id ) {
			$html = tribe_events_the_notices(false);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_notices');
		}
		// Event Content
		public static function before_the_content( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_content');
		}
		public static function the_content( $post_id ){
			ob_start();

			// Single event content ?>
			<div class="tribe-event-schedule tribe-clearfix">
				<h2><?php echo tribe_events_event_schedule_details(), tribe_events_event_recurring_info_tooltip(); ?><?php 	if ( tribe_get_cost() ) :  echo '<span class="tribe-divider">|</span><span class="tribe-event-cost">'. tribe_get_cost() .'</span>'; endif; ?></h2>
				
				<?php // iCal/gCal links
				if ( function_exists( 'tribe_get_single_ical_link' ) || function_exists( 'tribe_get_gcal_link' ) ) { ?>
					<div class="tribe-event-cal-links">
				<?php // iCal link
				if ( function_exists( 'tribe_get_single_ical_link' ) ) {
					echo '<a class="tribe-events-ical tribe-events-button-grey" href="' . tribe_get_single_ical_link() . '">' . __( 'iCal Import', 'tribe-events-calendar' ) . '</a>';
				}
				// gCal link
				if ( function_exists( 'tribe_get_gcal_link' ) ) {
					echo  '<a class="tribe-events-gcal tribe-events-button-grey" href="' . tribe_get_gcal_link() . '" title="' . __( 'Add to Google Calendar', 'tribe-events-calendar' ) . '">' . __( '+ Google Calendar', 'tribe-events-calendar' ) . '</a>';
				}
					echo '</div><!-- .tribe-event-cal-links -->';
				} ?>
			</div>
			
			<div class="entry-content description">

				<?php // Event content
				the_content(); ?>

			</div><!-- .description -->

			<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_the_content');
		}
		public static function after_the_content( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_content');
		}		
		// Event Meta
		public static function before_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_meta');
		}
		public static function the_meta( $post_id ){
			
			// If pro, show venue w/ link 
			$tribe_event_custom_fields = ( class_exists( 'TribeEventsPro' ) && function_exists( 'tribe_the_custom_fields' ) ) ? tribe_get_custom_fields( get_the_ID() ) : '' ;

			$html = '<div class="tribe-events-event-meta tribe-clearfix">';

			// Event Details
			$html .= tribe_get_meta_group( 'tribe_event_details' );
		
			// Venue Logic
			// When there is no map or no map + no custom fields, 
			// show the venue info up top 
			if ( ! tribe_embed_google_map( get_the_ID() ) && 
					 tribe_address_exists( get_the_ID() ) || 
					 (! tribe_embed_google_map( get_the_ID() ) && empty($tribe_event_custom_fields)) ) {

				// Venue Details
				$html .= tribe_get_meta_group( 'tribe_event_venue' );

			} // End Venue

			// Organizer Details
			if ( tribe_has_organizer() ) {
				$html .= tribe_get_meta_group( 'tribe_event_organizer' );
			} // End Organizer
			
			// Event Custom Fields
			if ( $tribe_event_custom_fields ) { 
				$html .= tribe_get_meta_group('tribe_event_group_custom_meta');
			} // End Custom Fields

			if ( tribe_embed_google_map( get_the_ID() ) && 
				 tribe_address_exists( get_the_ID() ) && 
				 empty($tribe_event_custom_fields) && 
				 !tribe_has_organizer() ) { 

				$html .= sprintf('%s<div class="tribe-events-meta-column">%s</div>',
					tribe_get_meta_group( 'tribe_event_venue' ),
					tribe_get_meta('tribe_venue_map')
					);

			} 

			$html .= '</div><!-- .tribe-events-event-meta -->';

			if ( tribe_embed_google_map( get_the_ID() ) && 
				 tribe_address_exists( get_the_ID() ) && 
				 ( $tribe_event_custom_fields || tribe_has_organizer() ) ) {
				 // If there's a venue map and custom fields or organizer, show venue details in this seperate section 
				
				$html .= sprintf('<div class="tribe-event-single-section tribe-events-event-meta tribe-clearfix">%s%s</div>',
					tribe_get_meta_group( 'tribe_event_venue' ),
					tribe_get_meta('tribe_venue_map')
					);
			}

			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_the_meta');
		}
		public static function after_the_meta( $post_id ){
			$html = '';
			// Event Tickets - todo separate this into the tickets
			if ( function_exists( 'tribe_get_ticket_form' ) && tribe_get_ticket_form() ) {
				$html .= tribe_get_ticket_form();
			}
			if ( class_exists( 'TribeEventsPro' ) ): // If pro, show venue w/ link 
				ob_start();
					tribe_single_related_events();
				$html .= ob_get_clean();
			endif; 			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_meta');
		}	
		// Event Pagination
		public static function before_pagination( $post_id ) {
			$html = '<div class="tribe-events-loop-nav">';
			$html .= '<h3 class="tribe-visuallyhidden">'. __( 'Event navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-clearfix">';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_before_pagination' );
		}
		public static function pagination( $post_id ) {
			$html = '<li class="tribe-nav-previous">' . tribe_get_prev_event_link( '&laquo; %title%' ) . '</li>';
			$html .= '<li class="tribe-nav-next">' . tribe_get_next_event_link( '%title% &raquo;' ) . '</li>';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_pagination' );
		}
		public static function after_pagination( $post_id ) {
			$html = '</ul></div><!-- .tribe-events-loop-nav -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_single_event_after_pagination' );
		}	
		// After Single Template
		public static function after_template( $post_id ){
			$html = '</div><!-- #tribe-events-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_template');
		}
	}
	Tribe_Events_Single_Event_Template::init();
}
