<?php
/**
 * Events Pro List Widget Template
 * This is the template for the output of the events list widget. 
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 *
 * You can customize this view by putting a replacement file of the same name
 * (/widgets/list-widget.php) in the tribe-events/ directory of your theme.
 *
 * When the template is loaded, the following vars are set: $start, $end, $venue, 
 * $address, $city, $state, $province'], $zip, $country, $phone, $cost
 *
 * @return string
 *
 * @package TribeEventsCalendarPro
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */

// Vars set:
// '$event->AllDay',
// '$event->StartDate',
// '$event->EndDate',
// '$event->ShowMapLink',
// '$event->ShowMap',
// '$event->Cost',
// '$event->Phone',

// TO-DO - add vevent class to $classes

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Pro_List_Widget_Template')){
	class Tribe_Events_Pro_List_Widget_Template extends Tribe_Template_Factory {
		public static function init(){
			// Start list widget template
			add_filter( 'tribe_events_pro_list_widget_before_template', array( __CLASS__, 'before_template' ), 1, 2 );
	
			// The date
			add_filter( 'tribe_events_pro_list_widget_before_the_date', array( __CLASS__, 'before_the_date' ), 1, 1 );
			add_filter( 'tribe_events_pro_list_widget_the_date', array( __CLASS__, 'the_date' ), 1, 4 );
			add_filter( 'tribe_events_pro_list_widget_after_the_date', array( __CLASS__, 'after_the_date' ), 1, 1 );

			// The title
			add_filter( 'tribe_events_pro_list_widget_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_pro_list_widget_the_title', array( __CLASS__, 'the_title' ), 1, 1 );
			add_filter( 'tribe_events_pro_list_widget_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );

			// The content
			add_filter( 'tribe_events_pro_list_widget_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 1 );
			add_filter( 'tribe_events_pro_list_widget_the_content', array( __CLASS__, 'the_content' ), 1, 2 );
			add_filter( 'tribe_events_pro_list_widget_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 1 );

			// End list widget template
			add_filter( 'tribe_events_pro_list_widget_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		// Start List Widget Template
		public static function before_template( $event, $class = '' ){
			$html = '<li ' . $class . '>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_before_template');
		}
		// Event Dates
		public static function before_the_date( $event ){
			$html = '<div class="duration">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_before_the_date');
		}
		public static function the_date( $event, $post_id = null, $start = true, $end = true ){
			$html = '<abbr class="tribe-events-abbr updated published dtstart" title="'. tribe_get_start_date( $post_id, false, TribeDateUtils::DBDATEFORMAT ) .'">'. tribe_get_start_date( $post_id, $start ) .'</abbr><!-- .dtstart -->';
			
			if( !tribe_is_multiday( $post_id ) && !tribe_get_all_day( $post_id ) ) {
				$html .= ' &ndash; <br/><abbr class="tribe-events-abbr dtend" title="'. tribe_get_end_date( $post_id, false, TribeDateUtils::DBDATEFORMAT ) .'">'. tribe_get_end_date( $post_id, false, 'g:i a' ) .'</abbr><!-- .dtend -->';
			} elseif( !tribe_get_all_day( $post_id ) || ( tribe_is_multiday( $post_id ) && tribe_get_all_day( $post_id ) ) ) {
				$html .= ' &ndash; <br/><abbr class="tribe-events-abbr dtend" title="'. tribe_get_end_date( $post_id, false, TribeDateUtils::DBDATEFORMAT ) .'">'. tribe_get_end_date( $post_id, $end ) .'</abbr><!-- .dtend -->';
			}

			if( tribe_get_all_day( $post_id ) )
				$html .= ' <small><em>('. __('All Day','tribe-events-calendar') .')</em></small>';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_the_date');
		}
		public static function after_the_date( $event ){
			$html = '</div><!-- .duration -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_after_the_date');
		}
		// Event Title
		public static function before_the_title( $event ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_before_the_title');
		}
		public static function the_title( $post ){
			$html = '<p class="entry-title summary"><a href="' . tribe_get_event_link( $post ) . '" rel="bookmark">' . $post->post_title . '</a></p><!-- .summary -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_the_title');
		}
		public static function after_the_title( $event ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_after_the_title');
		}
		// Event Content
		public static function before_the_content( $event ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_before_the_content');
		}
		public static function the_content( $event, $args = array() ){
			extract( $args, EXTR_SKIP );
			$space = false;
			$html = '';
			
			// Get our venue
			if ( $venue && tribe_get_venue() != '') {
				$html .= ( $space ) ? '<br />' : '';
				$html .= '<span class="fn org">'. tribe_get_venue() .'</span>'; 
				$space = true;
			}
			// Get our organizer
			if ( $organizer && tribe_get_organizer() != '') {
				$html .= ( $space ) ? '<br />' : '';
				$html .= '<span>'. tribe_get_organizer() .'</span>'; 
				$space = true;
			}
			// Get our address
			if ( $address && tribe_get_address() ) {
				$html .= ( $space ) ? '<br />' : '';
				$html .= '<span class="street-address">'. tribe_get_address() .'</span>';
				$space = true;
			}
			// Get our city
			if ( $city && tribe_get_city() != '' ) {
				$html .= ( $space ) ? '<br />' : '';
				$html .= '<span class="locality">'. tribe_get_city() .'</span>, ';
				$space = true;
			}
			// Get our region
			if ( $region && tribe_get_region() ) {
				$html .= ( $space && !$city ) ? '<br />' : '';
				$html .= '<span class="region">'. tribe_get_region() .'</span>';
				$space = true;
			} else {
				$html = rtrim( $html, ', ' );
			}
			// Get our zip
			if ( $zip && tribe_get_zip() != '' ) {
				$html .= ( $space ) ? '<br />' : '';
				$html .= '<span class="postal-code">'. tribe_get_zip() .'</span>';
				$space = true;
			}
			// Get our country
			if ( $country && tribe_get_country() != '' ) {
				$html .= ( $space ) ? '<br />' : ' ';
				$html .= '<span class="country-name">'. tribe_get_country() .'</span>';
				$space = true;
			}
			// Get our phone
			if ( $phone && tribe_get_phone() != '' ) {
				if( $html ) 
					$html .= '<br/>';

				$html .= '<span class="tel">'. tribe_get_phone() .'</span>'; 
			}
			// Get our cost
			if ( $cost && tribe_get_cost() != '' ) {		
				if( $html ) 
					$html .= '<br/>';
				$html .= __( 'Price:', 'tribe-events-calendar-pro' ) . ' ' . tribe_get_cost( null, true ); 
			}
			
			$html = !empty( $html ) ? '<div class="vcard adr location">'. $html .'</div><!-- .location -->' : '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_the_content');
		}
		public static function after_the_content( $event ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_after_the_content');
		}
		// End List Widget Template
		public static function after_template( $event ){
			$html = '</li><!-- .vevent -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_after_template');
		}
	}
	Tribe_Events_Pro_List_Widget_Template::init();
}