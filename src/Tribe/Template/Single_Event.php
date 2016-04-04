<?php
/**
 * @for     Single Event Template
 * This file contains the hook logic required to create an effective single event view.
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Template__Single_Event' ) ) {
	/**
	 * Single event template class
	 */
	class Tribe__Events__Template__Single_Event extends Tribe__Events__Template_Factory {

		protected $body_class = 'events-single';

		public function hooks() {
			parent::hooks();

			// google data markup
			add_action( 'wp_head', array( $this, 'google_data_markup' ) );

		}

		public function google_data_markup() {
			$event_markup = new Tribe__Events__Google_Data_Markup__Event();
			$html = apply_filters( 'tribe_google_data_markup_json', $event_markup->script_block() );
			echo $html;
		}

		/**
		 * Setup meta display in this template
		 *
		 **/
		public function setup_meta() {

			parent::setup_meta();

			/**
			 * Setup default meta templates
			 * @var array
			 */
			$meta_template_keys = apply_filters(
				'tribe_events_single_event_meta_template_keys', array(
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
					'tribe_event_custom_meta',
				)
			);
			$meta_templates     = apply_filters(
				'tribe_events_single_event_meta_templates', array(
					'before'       => '',
					'after'        => '',
					'label_before' => '<dt>',
					'label_after'  => '</dt>',
					'meta_before'  => '<dd class="%s">',
					'meta_after'   => '</dd>',
				)
			);
			tribe_set_the_meta_template( $meta_template_keys, $meta_templates );

			/**
			 * Setup default meta group templates
			 * @var array
			 */
			$meta_group_template_keys = apply_filters(
				'tribe_events_single_event_meta_group_template_keys', array(
					'tribe_event_details',
					'tribe_event_venue',
					'tribe_event_organizer',
				)
			);
			$meta_group_templates     = apply_filters(
				'tribe_events_single_event_meta_group_templates', array(
					'before'       => '<div class="%s">',
					'after'        => '</div>',
					'label_before' => '<h3 class="%s">',
					'label_after'  => '</h3>',
					'meta_before'  => '<dl>',
					'meta_after'   => '</dl>',
				)
			);

			tribe_set_the_meta_template( $meta_group_template_keys, $meta_group_templates, 'meta_group' );
		}

		/**
		 * Set up the notices for this template
		 *
		 **/
		public function set_notices() {
			parent::set_notices();
			$events_label_singular_lowercase = tribe_get_event_label_singular_lowercase();

			global $post;

			// Check if event has passed
			$gmt_offset = ( get_option( 'gmt_offset' ) >= '0' ) ? ' +' . get_option( 'gmt_offset' ) : ' ' . get_option( 'gmt_offset' );
			$gmt_offset = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $gmt_offset );

			if ( ! tribe_is_showing_all() && strtotime( tribe_get_end_date( $post, false, 'Y-m-d G:i' ) . $gmt_offset ) <= time() ) {
				Tribe__Notices::set_notice( 'event-past', sprintf( esc_html__( 'This %s has passed.', 'the-events-calendar' ), $events_label_singular_lowercase ) );
			}
		}
	}
}
