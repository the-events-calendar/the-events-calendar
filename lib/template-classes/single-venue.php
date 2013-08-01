<?php
/**
 * @for Single Venue Template
 * This file contains hooks and functions required to set up the single venue view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( !class_exists( 'Tribe_Events_Pro_Single_Venue_Template' ) ) {
	class Tribe_Events_Pro_Single_Venue_Template extends Tribe_PRO_Template_Factory {

		protected $asset_packages = array( 'jquery-placeholder' );

		protected $body_class = 'tribe-events-venue';

		protected $comments_off = true;
		
		/**
		 * Set up hooks for this template
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function hooks() {
			parent::hooks();

			add_action( 'tribe_events_single_venue_before_upcoming_events', array( $this, 'setup_upcoming_events' ) );

			add_filter( 'tribe_get_template_part_templates', array( $this, 'remove_list_navigation' ), 10, 3 );
		}

		/**
		 * Setup meta display in this template
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function setup_meta() {

			parent::setup_meta();

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

			add_filter( 'tribe_event_meta_venue_address_gmap', '__return_false' );

			// disable venue info from showing on list module (since it's duplicate of this view)
			tribe_set_the_meta_visibility( 'tribe_list_venue_name_address', false );
		}

		/**
		 * Do any setup for upcoming events
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function setup_upcoming_events() {

			// include the list view class for upcoming events
			tribe_initialize_view( 'list' );
			tribe_set_the_meta_visibility( 'tribe_event_venue_name', false );
			tribe_set_the_meta_visibility( 'tribe_event_venue_address', false );

		}
		
		/**
		 * Remove navigation from the list view included.
		 *
		 * @param array $templates The templates to include.
		 * @param string $slug The slug referencing the template.
		 * @param string $name The name of the specific template.
		 * @return array The new array of templates to include.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function remove_list_navigation( $templates, $slug, $name ) {
			if ( $slug == 'list/nav' ) {
				$templates = array();
			}
			return $templates;
		}

	}
}
