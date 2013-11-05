<?php

// Don't load directly
if ( !defined( 'ABSPATH' ) ) die( '-1' );

if ( ! class_exists( 'Tribe_Register_Meta_Pro' ) ) {

	class Tribe_Register_Meta_Pro {

		/**
		 * Responsible for displaying a user's custom recurrence pattern description.
		 *
		 * @param string $meta_id The meta group this is in.
		 * @return string The custom description.
		 * @author Timothy Wood
		 * @since 3.0
		 */
		public static function custom_recurrence_description( $meta_id ){
			global $_tribe_meta_factory;
			$post_id = get_the_ID();
			$recurrence_meta = TribeEventsRecurrenceMeta::getRecurrenceMeta( $post_id );
			$recurrence_description = !empty($recurrence_meta['recCustomRecurrenceDescription']) ? $recurrence_meta['recCustomRecurrenceDescription'] : tribe_get_recurrence_text( $post_id );
			$html = tribe_is_recurring_event( $post_id ) ? Tribe_Meta_Factory::template(
				$_tribe_meta_factory->meta[$meta_id]['label'],
				$recurrence_description,
				$meta_id ) : '';
			return apply_filters( 'tribe_event_pro_meta_custom_recurrence_description', $html );
		}

		/**
		 * Render the name of the venue (with the link).
		 *
		 * @param string $html The current venue name.
		 * @param string $meta_id The meta group this is in.
		 * @return string The modified/linked venue name.
		 * @author Timothy Wood
		 * @since 3.0
		 */
		public static function venue_name( $html, $meta_id ){
			global $_tribe_meta_factory;
			$post_id = get_the_ID();
			$name = tribe_get_venue($post_id);
			$link = !empty($name) ? '<a href="'.tribe_get_venue_link( $post_id, false ) .'">'.$name.'</a>' : '';
			$html = empty( $link ) ? $html :  Tribe_Meta_Factory::template(
				$_tribe_meta_factory->meta[$meta_id]['label'],
				$link,
				$meta_id );
			return apply_filters( 'tribe_event_pro_meta_venue_name', $html, $meta_id );
		}

		/**
		 * Render the name of the organizer (with the link).
		 *
		 * @param string $html The current organizer name.
		 * @param string $meta_id The meta group this is in.
		 * @return string The modified/linked organizer name.
		 * @author Timothy Wood
		 * @since 3.0
		 */
		public static function organizer_name( $html, $meta_id ){
			global $_tribe_meta_factory;
			$post_id = get_the_ID();
			$name = tribe_get_organizer_link( $post_id, true, false );
			$html = empty( $name ) ? $html :  Tribe_Meta_Factory::template(
				$_tribe_meta_factory->meta[$meta_id]['label'],
				$name,
				$meta_id );
			return apply_filters( 'tribe_event_pro_meta_organizer_name', $html, $meta_id );
		}

		/**
		 * Returns custom meta. 
		 *
		 * @param string $meta_id The meta group this is in.
		 * @return string The custom meta.
		 * @author Timothy Wood
		 * @since 3.0
		 */
		public static function custom_meta( $meta_id ){
			global $_tribe_meta_factory;
			$fields = tribe_get_custom_fields( get_the_ID() );
			$custom_meta = '';
		  	foreach ($fields as $label => $value) {
				$custom_meta .= Tribe_Meta_Factory::template(
				$label,
				$value,
				$meta_id );
			}
			return apply_filters( 'tribe_event_pro_meta_custom_meta', $custom_meta);
		}
	}

	/**
	 * Register Meta Group: Event Custom Meta
	 */
	tribe_register_meta_group( 'tribe_event_group_custom_meta', array(
		'label' => __( 'Other', 'tribe-events-calendar-pro' ),
		'classes' => array(
			'before'=>array('tribe-events-meta-group tribe-events-meta-group-other'),
			'label_before'=>array('tribe-events-single-section-title'))
	) );

	/**
	 * Register Meta: Event Custom Meta
	 *
	 * @group tribe_event_custom_meta
	 */
	tribe_register_meta( 'tribe_event_custom_meta', array(
		'label' => '',
		'priority' => 60,
		'filter_callback' => array( 'Tribe_Register_Meta_Pro', 'custom_meta' ),
		'group' => 'tribe_event_group_custom_meta'
	) );

	/**
	 * Register Meta: Event Recurrence Description
	 *
	 * @group tribe_event_custom_meta
	 */
	tribe_register_meta( 'tribe_event_custom_recurrence_description', array(
		'label' => __('Recurrence Pattern:', 'tribe-events-calendar-pro'),
		'priority' => 15,
		'wrap' => array(
				'before'=>'',
				'after'=>'',
				'label_before'=>'<dt>',
				'label_after'=>'</dt>',
				'meta_before'=>'<dd class="%s">',
				'meta_after'=>'</dd>'
			),
		'filter_callback' => array( 'Tribe_Register_Meta_Pro', 'custom_recurrence_description' ),
		'group' => 'tribe_event_details'
	) );

}
