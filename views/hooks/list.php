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
		public static $loop_increment = 0;
		public static $prev_event_month = null;
		public static $prev_event_year = null;

		protected $asset_packages = array( 'ajax-list' );

		/**
		 * additional hooks for this template
		 *
		 * @return void
		 * @since 3.0
		 **/
		protected function hooks() {

			parent::hooks();

			// Event cost
			add_filter( 'tribe_events_list_the_event_cost', array( __CLASS__, 'the_event_cost' ), 1, 2 );
		}

		public static function the_event_cost( $content, $post_id ) {
			$html= '';
			if ( tribe_get_cost() ) // Get our event cost 
				$html .= '<div class="tribe-events-event-cost"><span>'. tribe_get_cost( null, true ) .'</span></div>';			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_event_cost');		
		}		
	}
	Tribe_Events_List_Template::instance();
}
