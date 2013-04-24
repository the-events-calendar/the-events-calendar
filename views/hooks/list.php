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

	}
	Tribe_Events_List_Template::instance();
}
