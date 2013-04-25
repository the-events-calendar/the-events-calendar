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

if( !class_exists('Tribe_Events_Photo_Template')){
	class Tribe_Events_Photo_Template extends Tribe_PRO_Template_Factory {

		/**
		 * Array of asset packages needed for this template
		 *
		 * @var array
		 **/
		protected $asset_packages = array( 'ajax-list', 'ajax-photoview' );

		protected function hooks() {
			parent::hooks();
			add_filter( 'tribe_events_event_classes', array( $this, 'event_classes') );
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
		public function event_classes($classes) {
			$classes[] = 'tribe-events-photo-event';
			return $classes;
		}

		/**
		 * Set up the notices for this template
		 *
		 * @return void
		 * @since 3.0
		 **/
		protected function set_notices() {

			parent::set_notices();

			// if no events for photo view (similar to day view)
			if ( empty($search_term) && empty( $wp_query->query_vars['s'] ) && !have_posts() ) { // Messages if currently no events, and no search term
				$tribe_ecp = TribeEvents::instance();
				$is_cat_message = '';
				if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
					$cat = get_term_by( 'slug', get_query_var( 'term' ), $tribe_ecp->get_event_taxonomy() );
					$is_cat_message = sprintf( __( 'listed under %s. Check out events for this category or view the full calendar.', 'tribe-events-calendar' ), $cat->name );
				}

				TribeEvents::setNotice( 'events-not-found', sprintf( __( 'No events scheduled for <strong>%s</strong>. Please try another day.', 'tribe-events-calendar' ), date_i18n( 'F d, Y', strtotime( get_query_var( 'eventDate' ) ) ) ) );
			}

		}
	}
	new Tribe_Events_Photo_Template();
}