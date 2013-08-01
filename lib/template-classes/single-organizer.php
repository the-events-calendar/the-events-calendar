<?php
/**
 * @for Single organizer Template
 * This file contains hooks and functions required to set up the single organizer view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( !class_exists( 'Tribe_Events_Pro_Single_Organizer_Template' ) ) {
	class Tribe_Events_Pro_Single_Organizer_Template extends Tribe_PRO_Template_Factory {


		protected $comments_off = true;


		/**
		 * Set up hooks for this template
		 *
		 * @return void
		 * @since 3.0
		 **/
		protected function hooks() {

			parent::hooks();

			add_action( 'tribe_events_single_organizer_before_upcoming_events', array( $this, 'setup_upcoming_events' ) );
			
			add_filter( 'tribe_get_template_part_templates', array( $this, 'remove_list_navigation' ), 10, 3 );

		}

		/**
		 * Setup meta display in this template
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function setup_meta() {

			// setup the template for the meta group
			tribe_set_the_meta_template( 'tribe_event_organizer', array(
					'before'=>'',
					'after'=>'',
					'label_before'=>'',
					'label_after'=>'',
					'meta_before'=>'<address class="organizer-address">',
					'meta_after'=>'</address>',
					'meta_separator' => '<span class="tribe-events-divider">|</span>'
				), 'meta_group');

			// setup the template for the meta items
			tribe_set_the_meta_template( array(
					'tribe_event_organizer_phone',
					'tribe_event_organizer_email',
					'tribe_event_organizer_website'
				), array(
					'before'=>'',
					'after'=>'',
					'label_before'=>'',
					'label_after'=>'',
					'meta_before'=>'<span class="%s">',
					'meta_after'=>'</span>'
				));

			// remove the title for the group & meta items
			tribe_set_meta_label('tribe_event_organizer', '', 'meta_group');
			tribe_set_meta_label( array( 
				'tribe_event_organizer_phone' => '',
				'tribe_event_organizer_email' => '',
				'tribe_event_organizer_website' => ''
				));

			// turn off the organizer name in the group
			tribe_set_the_meta_visibility( 'tribe_event_organizer_name', false);
		}

		/**
		 * Do any setup for upcoming events
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function setup_upcoming_events() {
			tribe_set_the_meta_visibility( 'tribe_event_organizer', false, 'meta_group');
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
