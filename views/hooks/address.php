<?php
/**
 * @for Address Module Template
 * This file contains the hook logic required to create an effective address module view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Address_Template')){
	class Tribe_Events_Address_Template extends Tribe_Template_Factory {
		public static function init(){
			// Start address template
			add_filter( 'tribe_events_address_before_template', array( __CLASS__, 'before_template' ), 1, 1 );
	
			// Address meta
			add_filter( 'tribe_events_address_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_address_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_address_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );

			// End address template
			add_filter( 'tribe_events_address_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		// Start Address Template
		public static function before_template( $post_id ){
			$html = '<span class="adr">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_address_before_template');
		}
		// Address Meta
		public static function before_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_address_before_the_meta');
		}
		public static function the_meta( $post_id ){
			// ob_start();
			
			$postId = get_the_ID();	
			$address_out = Array();
			
			// Get our street address
			if( tribe_get_address( $postId ) ) {					
				$address_out []= ' <span class="street-address">'. tribe_get_address( $postId ) .'</span>';
			}
			
			// Get our full region
			$our_province = tribe_get_event_meta( $postId, '_VenueStateProvince', true );
			$our_states = TribeEventsViewHelpers::loadStates();
			$our_full_region = isset( $our_states[$our_province] ) ? $our_states[$our_province] : $our_province;
			
			// Get our city
			if( tribe_get_city( $postId ) ) {
				$address_out []= ' <span class="locality">'. tribe_get_city( $postId ) .'</span>';
				$address_out []='<span class="delimiter">,</span> ';
			}
			
			// Get our region
			if( tribe_get_region( $postId ) ) {
				if(count($address_out))
					$address_out []= ' <abbr class="region tribe-events-abbr" title="'. $our_full_region .'">'. tribe_get_region( $postId ) .'</abbr>';
			}

			// Get our postal code
			if( tribe_get_zip( $postId ) ) {
				$address_out []= ' <span class="postal-code">'. tribe_get_zip( $postId ) .'</span>';
			}

			// Get our country
			if( tribe_get_country( $postId ) ) {
				if(count($address_out))
				$address_out []= ' <span class="country-name">'. tribe_get_country( $postId ) .'</span>';
			}
			
			$html = implode( '', $address_out );
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_address_the_meta');
		}
		public static function after_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_address_after_the_meta');
		}
		// End Address Template
		public static function after_template( $post_id ){
			$html = '</span><!-- .adr -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_address_after_template');		
		}
	}
	Tribe_Events_Address_Template::init();
}
