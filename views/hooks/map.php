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

if ( !defined('ABSPATH') ) 
	die('-1');

if( !class_exists('Tribe_Events_Map_Template')){
	class Tribe_Events_Map_Template extends Tribe_PRO_Template_Factory {

		/**
		 * Array of asset packages needed for this template
		 *
		 * @var array
		 **/
		protected $asset_packages = array( 'ajax-list' );

		/**
		 * Set up hooks for map view
		 *
		 * @return void
		 * @since 3.0
		 **/
		protected function hooks() {
			parent::hooks();
			add_filter( 'tribe_events_header_attributes',  array( $this, 'header_attributes') );
		}

		/**
		 * Add header attributes for map view
		 *
		 * @return string
		 * @since 3.0
		 **/
		function header_attributes($attrs) {
			$attrs['data-view'] = 'map';
			$attrs['data-baseurl'] = tribe_get_mapview_link();
			return apply_filters('tribe_events_pro_header_attributes', $attrs);
		}

		/**
		 * Filter tribe_get_template_part()
		 *
		 * @return string
		 * @since 3.0
		 **/
		public function filter_template_paths( $file, $template ) {
			
			$file = parent::filter_template_paths( $file, $template );

			// Don't include google map in ajax response
			if ( $template == 'map/gmap-container.php' && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return false;
			}
			return $file;
		}

	}
	Tribe_Events_Map_Template::instance();
}