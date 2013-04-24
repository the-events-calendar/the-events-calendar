<?php
/**
 *
 *
 * @for Single Venue Template
 * This file contains the hook logic required to create an effective single venue view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( !class_exists( 'Tribe_Events_Pro_Single_Venue_Template' ) ) {
	class Tribe_Events_Pro_Single_Venue_Template extends Tribe_PRO_Template_Factory {

		/**
		 * Set up hooks for this template
		 *
		 * @return void
		 * @since 3.0
		 **/
		function hooks()
		{
			parent::hooks();

			// hide title from list view
			add_filter('tribe_get_events_title', '__return_false');

			add_action( 'tribe_events_single_venue_before_upcoming_events', array( $this, 'setup_upcoming_events' ) );

			// disable venue info from showing on list module (since it's duplicate of this view)
			tribe_set_the_meta_visibility( 'tribe_list_venue_name_address', false );
		}

		/**
		 * Setup the view, query, etc. This happens right before the view file is included
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function setup_view() {

			// setup the template for the meta group
			tribe_set_the_meta_template( 'tribe_event_venue', array(
					'before'=>'',
					'after'=>'',
					'label_before'=>'',
					'label_after'=>'',
					'meta_before'=>'<address class="venue-address">',
					'meta_after'=>'</address>'
				), 'meta_group');
			// setup the template for the meta items
			tribe_set_the_meta_template( array(
					'tribe_event_venue_address',
					'tribe_event_venue_phone',
					'tribe_event_venue_website'
				), array(
					'before'=>'',
					'after'=>'',
					'label_before'=>'',
					'label_after'=>'',
					'meta_before'=>'<span class="%s">',
					'meta_after'=>'</span>'
				));

			// turn off the venue name in the group
			tribe_set_the_meta_visibility( 'tribe_event_venue_name', false);

			// remove the title for the group & meta items
			tribe_set_meta_label('tribe_event_venue', '', 'meta_group');
			tribe_set_meta_label( array( 
				'tribe_event_venue_address' => '',
				'tribe_event_venue_phone' => '',
				'tribe_event_venue_website' => ''
				));

			// set meta item priorities
			tribe_set_meta_priority( array( 
				'tribe_event_venue_address' => 10,
				'tribe_event_venue_phone' => 20,
				'tribe_event_venue_website' => 30
				));

			add_filter('tribe_event_meta_venue_address_gmap', '__return_null', 10);

			// provide for meta actions before loading the template
			do_action('tribe_events_pro_single_venue_meta_init' );

			// Remove the comments template
			add_filter('comments_template', array( $this, 'remove_comments_template' ) );

		}

		/**
		 * Do any setup for upcoming events
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function setup_upcoming_events() {

			// include the list view class for upcoming events
			include_once(TribeEvents::instance()->pluginPath.'views/hooks/list.php');
		}

		public function remove_comments_template($template) {
			remove_filter('comments_template', array($this, 'remove_comments_template') );
			return TribeEvents::instance()->pluginPath . 'admin-views/no-comments.php';
		}

		/**
		 * Filter tribe_get_template_part()
		 *
		 * @return string
		 * @since 3.0
		 **/
		public function filter_template_paths( $file, $template ) {

			parent::filter_template_paths( $file, $template );

			// don't return the tribe bar on venue view
			if ( $template == 'modules/bar.php' ) {
				return false;
			}
			return $file;
		}

	}
	Tribe_Events_Pro_Single_Venue_Template::instance();
}
