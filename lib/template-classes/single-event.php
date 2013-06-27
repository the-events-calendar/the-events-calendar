<?php
/**
 * @for Day Template
 * This file contains the hook logic required to create an effective day grid view.
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Single_Event_Template')){
	/**
	 * Single event template class
	 */
	class Tribe_Events_Single_Event_Template extends Tribe_Template_Factory {

		protected $body_class = 'events-single';

		/**
		 * Setup meta display in this template
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function setup_meta() {

			parent::setup_meta();

			/**
			 * Setup default meta templates
			 * @var array
			 */
			$meta_template_keys = apply_filters('tribe_events_single_event_meta_template_keys', array(
					'tribe_event_date',
					'tribe_event_cost',
					'tribe_event_category',
					'tribe_event_tag',
					'tribe_event_website',
					'tribe_event_origin',
					'tribe_event_venue_name',
					'tribe_event_venue_phone',
					'tribe_event_venue_address',
					'tribe_event_venue_website',
					'tribe_event_organizer_name',
					'tribe_event_organizer_phone',
					'tribe_event_organizer_email',
					'tribe_event_organizer_website',
					'tribe_event_custom_meta'
				));
			$meta_templates = apply_filters('tribe_events_single_event_meta_templates', array(
					'before'=>'',
					'after'=>'',
					'label_before'=>'<dt>',
					'label_after'=>'</dt>',
					'meta_before'=>'<dd class="%s">',
					'meta_after'=>'</dd>'
				));
			tribe_set_the_meta_template( $meta_template_keys, $meta_templates);

			/**
			 * Setup default meta group templates
			 * @var array
			 */
			$meta_group_template_keys = apply_filters( 'tribe_events_single_event_meta_group_template_keys', array(
					'tribe_event_details',
					'tribe_event_venue',
					'tribe_event_organizer'
				));
			$meta_group_templates = apply_filters('tribe_events_single_event_meta_group_templates', array(
					'before'=>'<div class="%s">',
					'after'=>'</div>',
					'label_before'=>'<h3 class="%s">',
					'label_after'=>'</h3>',
					'meta_before'=>'<dl>',
					'meta_after'=>'</dl>'
				));

			tribe_set_the_meta_template( $meta_group_template_keys, $meta_group_templates, 'meta_group');
		}

		/**
		 * Set up the notices for this template
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function set_notices() {

			parent::set_notices();

			// Check if event has passed
			$gmt_offset = ( get_option( 'gmt_offset' ) >= '0' ) ? ' +' . get_option( 'gmt_offset' ) : " " . get_option( 'gmt_offset' );
			$gmt_offset = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $gmt_offset );

			if ( !tribe_is_showing_all() && strtotime( tribe_get_end_date( get_the_ID(), false, 'Y-m-d G:i' ) . $gmt_offset ) <= time() ) {
				TribeEvents::setNotice( 'event-past', __( 'This event has passed.', 'tribe-events-calendar' ) );
			}
		}

	}
}
