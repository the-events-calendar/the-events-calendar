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
	/**
	 * List view template class
	 */
	class Tribe_Events_List_Template extends Tribe_Template_Factory {
		protected $body_class = 'events-list';
		protected $asset_packages = array( 'ajax-list' );

		protected function hooks() {
			parent::hooks();
			if ( tribe_is_showing_all() ) {
				add_filter('tribe_get_template_part_path_modules/bar.php', '__return_false');
			}
		}
	}
}
