<?php
/**
 * @for Photo Template
 * This file contains hooks and functions required to set up the photo view.
 *
 * @package TribeEventsCalendarPro
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') )
	die('-1');

if( !class_exists('Tribe_Events_Pro_Photo_Template')){
	class Tribe_Events_Pro_Photo_Template extends Tribe_PRO_Template_Factory {

		protected $body_class = 'events-photo';
		/**
		 * Array of asset packages needed for this template
		 *
		 * @var array
		 **/
		protected $asset_packages = array( 'ajax-photoview' );

		protected function hooks() {
			parent::hooks();
			add_filter( 'tribe_events_header_attributes',  array( $this, 'header_attributes') );
		}

		/**
		 * Add header attributes for photo view
		 *
		 * @return string
		 * @since 3.0
		 **/
		public function header_attributes($attrs) {
			$attrs['data-view'] = 'photo';
			$attrs['data-baseurl'] = tribe_get_photo_permalink( false );
			return apply_filters('tribe_events_pro_header_attributes', $attrs);
		}


		/**
		 * Add event classes specific to photo view
		 *
		 * @param $classes
		 * @return array
		 * @since 3.0
		 **/
		public function event_classes( $classes ) {
			$classes[] = 'tribe-events-photo-event';
			return $classes;
		}
	}
}