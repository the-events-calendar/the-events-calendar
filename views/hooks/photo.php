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

if ( !defined('ABSPATH') ) { die('-1');
}

if( !class_exists('Tribe_Events_Photo_Template')){
	class Tribe_Events_Photo_Template extends Tribe_Template_Factory {

		static $timeslots = array();

		public static function init(){
			
			Tribe_PRO_Template_Factory::asset_package('ajax-photoview');			

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

			// Set excerpt length & more
			add_action( 'tribe_events_list_before_loop', array(__CLASS__, 'setup_excerpt') );

			// Reset excerpt length & more
			add_action( 'tribe_events_list_after_loop', array(__CLASS__, 'reset_excerpt') );
		}

		/**
		 * Set up excerpt to how we like it in list view
		 *
		 * @return void
		 * @since 3.0
		 **/
		public static function setup_excerpt() {
			add_filter( 'excerpt_length', array(__CLASS__, 'excerpt_length') );
			add_filter( 'excerpt_more', array(__CLASS__, 'excerpt_more') );
		}

		/**
		 * Reset excerpt to how it used to be
		 *
		 * @return void
		 * @since 3.0
		 **/
		public static function reset_excerpt() {
			remove_filter( 'excerpt_length', array(__CLASS__, 'excerpt_length') );
			remove_filter( 'excerpt_more', array(__CLASS__, 'excerpt_more') );
		}

		/**
		 * Set excerpt length used in list view
		 *
		 * @return void
		 * @since 
		 **/
		public static function excerpt_length() {
			return 30;
		}

		/**
		 * Set excerpt more used in list view
		 *
		 * @return void
		 * @since 
		 **/
		public static function excerpt_more() {
			return '&hellip;';
		}

	}
	Tribe_Events_Photo_Template::init();
}