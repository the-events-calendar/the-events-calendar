<?php

// Don't load directly
if ( !defined( 'ABSPATH' ) ) die( '-1' );

if ( ! class_exists( 'Tribe_Register_Meta_Pro' ) ) {

	class Tribe_Register_Meta_Pro {

		function venue_name( $html, $meta_id ){
			global $_tribe_meta_factory;
			$post_id = get_the_ID();
			$name = '<a href="'.tribe_get_venue_link( $post_id, false ) .'">'.tribe_get_venue($post_id).'</a>';
			$html = empty( $name ) ? $html :  Tribe_Meta_Factory::template(
				$_tribe_meta_factory->meta[$meta_id]['label'],
				$name,
				$meta_id );
			return apply_filters( 'tribe_event_pro_meta_venue_name', $html, $meta_id );
		}

		function organizer_name( $html, $meta_id ){
			global $_tribe_meta_factory;
			$post_id = get_the_ID();
			$name = tribe_get_organizer_link( $post_id, true, false );
			$html = empty( $name ) ? $html :  Tribe_Meta_Factory::template(
				$_tribe_meta_factory->meta[$meta_id]['label'],
				$name,
				$meta_id );
			return apply_filters( 'tribe_event_pro_meta_organizer_name', $html, $meta_id );
		}

		function custom_meta( $meta_id ){
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
			'label' => __( 'Other', 'tribe-events-calendar' ),
			'classes' => array(
				'before'=>array('tribe-events-meta-group'),
				'label_before'=>array('tribe-event-single-section-title'))
		) );

	/**
	 * Register Meta: Event Custom Meta
	 *
	 * @group tribe_event_custom_meta
	 */
	tribe_register_meta( 'tribe_event_custom_meta', array(
			'label' => '',
			'priority' => 10,
			'filter_callback' => array( 'Tribe_Register_Meta_Pro', 'custom_meta' ),
			'group' => 'tribe_event_group_custom_meta'
		) );

}