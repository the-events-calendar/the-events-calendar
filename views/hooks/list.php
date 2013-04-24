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

		protected $asset_packages = array( 'ajax-list' );

	}
	Tribe_Events_List_Template::instance();
}
