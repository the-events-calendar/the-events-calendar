<?php
/**
 * Address Template
 * The abstracted view of the address module template.
 * This view contains the hooks and filters required to create an effective address view.
 *
 * You can recreate and ENTIRELY new address view (that does not utilize these hooks and filters)
 * by doing a template override, and placing a address.php file in a tribe-events/modules/ directory 
 * within your theme directory, which will override the /views/modules/address.php.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Address_Template')){
	class Tribe_Events_Address_Template extends Tribe_Template_Factory {
		function init(){
			// start address template
			add_filter( 'tribe_events_address_before_template', array( __CLASS__, 'before_template' ), 1, 1 );
	
			// address meta
			add_filter( 'tribe_events_address_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_address_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_address_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );

			// end address template
			add_filter( 'tribe_events_address_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		// Start Address Template
		public function before_template( $post_id ){
			$html = '<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_address_before_template');
		}
		// Address Meta
		public function before_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_address_before_the_meta');
		}
		public function the_meta( $post_id ){
			ob_start();
			
			$postId = get_the_ID();
			
			$address_out = Array();

			if( isset( $includeVenueName ) && $includeVenueName && tribe_get_venue( $postId ) ) {
				$address_out []= '<span itemprop="addressLocality">'. tribe_get_venue( $postId ) .'</span>';
			}
	
			if( tribe_get_address( $postId ) ) {
				$address_out []= '<span itemprop="streetAddress">'. tribe_get_address( $postId ) .'</span>';
			}

			$cityregion = '';
			if( tribe_get_city( $postId ) ) {
				$cityregion .= tribe_get_city( $postId );
			}
			
			if( tribe_get_region( $postId ) ) {
				if( $cityregion != '' ) $cityregion .= ', ';
				$cityregion .= tribe_get_region( $postId );
			}
	
			if( $cityregion != '' ) {
				$address_out []= '<span itemprop="addressRegion">'. $cityregion .'</span>';
			}

			if( tribe_get_zip( $postId ) ) {
				$address_out []= '<span itemprop="postalCode">'. tribe_get_zip( $postId ) .'</span>';
			}

			if( tribe_get_country( $postId ) ) {
				$address_out []= '<span itemprop="addressCountry">'. tribe_get_country( $postId ) .'</span>';
			}
			
			// If we have address bits, let's see 'em
			if ( count( $address_out ) > 0 ) {
				echo implode( ', ', $address_out );
			}		

			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_address_the_meta');
		}
		public function after_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_address_after_the_meta');
		}
		// End Address Template
		public function after_template( $post_id ){
			$html = '</div><!-- address -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_address_after_template');		
		}
	}
	Tribe_Events_Address_Template::init();
}