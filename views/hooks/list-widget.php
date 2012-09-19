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

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_PRO_List_Widget_Template')){
	class Tribe_Events_PRO_List_Widget_Template extends Tribe_Template_Factory {
		public static function init(){
			// start address template
			add_filter( 'tribe_events_pro_list_widget_before_template', array( __CLASS__, 'before_template' ), 1, 2 );
	
			// the date
			add_filter( 'tribe_events_pro_list_widget_before_the_date', array( __CLASS__, 'before_the_date' ), 1, 1 );
			add_filter( 'tribe_events_pro_list_widget_the_date', array( __CLASS__, 'the_date' ), 1, 4 );
			add_filter( 'tribe_events_pro_list_widget_after_the_date', array( __CLASS__, 'after_the_date' ), 1, 1 );

			// the title
			add_filter( 'tribe_events_pro_list_widget_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_pro_list_widget_the_title', array( __CLASS__, 'the_title' ), 1, 1 );
			add_filter( 'tribe_events_pro_list_widget_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );

			// the content
			add_filter( 'tribe_events_pro_list_widget_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 1 );
			add_filter( 'tribe_events_pro_list_widget_the_content', array( __CLASS__, 'the_content' ), 1, 2 );
			add_filter( 'tribe_events_pro_list_widget_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 1 );

			// end address template
			add_filter( 'tribe_events_pro_list_widget_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		public function before_template( $event, $class = '' ){
			$html = '<li ' . $class . '>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_before_template');
		}
		public function before_the_date( $event ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_before_the_date');
		}
		public function the_date( $event, $post_id = null, $start, $end ){
			$html = '<div class="when">';
			$html .= tribe_get_start_date( $post_id, $start ); 
			if ( $end && $event->EndDate != '') {
					$html .= '<br/>' . __( 'Ends', 'tribe-events-calendar-pro' ) . ' ';
					$html .= tribe_get_end_date( $post_id );
			}
			if($event->AllDay && $start) {
				$html .= ' <small><em>('.__( 'All Day','tribe-events-calendar-pro' ).')</em></small>';
			} 
			$html .= '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_the_date');
		}
		public function after_the_date( $event ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_after_the_date');
		}
		public function before_the_title( $event ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_before_the_title');
		}
		public function the_title( $post ){
			$html = '<div class="event"><a href="' . tribe_get_event_link( $post ) . '">' . $post->post_title . '</a></div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_the_title');
		}
		public function after_the_title( $event ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_after_the_title');
		}
		public function before_the_content( $event ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_before_the_content');
		}
		public function the_content( $event, $args = array() ){
			extract( $args, EXTR_SKIP );
			$space = false;
			$html = '<div class="loc">';

			if ( $venue && tribe_get_venue() != '') {
				$html .= ( $space ) ? '<br />' : '';
				$html .= tribe_get_venue(); 
				$space = true;
			}

			if ( $address && tribe_get_address() ) {
				$html .= ( $space ) ? '<br />' : '';
				$html .= tribe_get_address();
				$space = true;
			}

			if ( $city && tribe_get_city() != '' ) {
				$html .= ( $space ) ? '<br />' : '';
				$html .= tribe_get_city() . ', ';
				$space = true;
			}
			if ( $region && tribe_get_region() ) {
				$html .= ( !$city ) ? '<br />' : '';
				$space = true;
				$html .= tribe_get_region();
			} else {
				$html = rtrim( $html, ', ' );
			}

			if ( $zip && tribe_get_zip() != '' ) {
				$html .= ( $space ) ? '<br />' : '';
				$html .= tribe_get_zip();
				$space = true;
			}

			if ( $country && tribe_get_country() != '' ) {
				$html .= ( $space ) ? '<br />' : ' ';
				$html .= tribe_get_country(); 
			}

			if ( $phone && tribe_get_phone() != '' ) {
				if( $html ) 
					$html .= '<br/>';

				$html .= tribe_get_phone(); 
			}
			if ( $cost && tribe_get_cost() != '' ) {		
				if( $html ) 
					$html .= '<br/>';
				$html .= __( 'Price:', 'tribe-events-calendar-pro' ) . ' ' . tribe_get_cost(); 
			}

			$html .= '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_the_content');
		}
		public function after_the_content( $event ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_after_the_content');
		}
		public function after_template( $event ){
			$html = '</li>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_list_widget_after_template');
		}
	}
	Tribe_Events_PRO_List_Widget_Template::init();
}