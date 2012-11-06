<?php

// Don't load directly
if ( !defined('ABSPATH') ) die('-1');

if( class_exists( 'Tribe_Meta_Factory' ) ) {

	class Tribe_Register_Meta {
		function the_title(){
			return get_the_title( get_the_ID() );
		}
		function event_date(){
			$template = array(
				'before'=>'',
				'after'=>'',
				'label_before'=>'<dt>',
				'label_after'=>'</dt>',
				'meta_before'=>'<dd class="tribe-events-event-cost">',
				'meta_after'=>'</dd>'
				);
			if ( tribe_get_start_date() !== tribe_get_end_date() ) { 
				// Start & end date 
				$html = Tribe_Meta_Factory::template( 
					__( 'Start:', 'tribe-events-calendar'), 
					sprintf('<abbr class="tribe-events-abbr" title="%s">%s</abbr>', 
						tribe_get_end_date( null, false, TribeDateUtils::DBDATEFORMAT ), 
						tribe_get_end_date()
						), 
					$template );
				$html .= Tribe_Meta_Factory::template( 
					__( 'End:', 'tribe-events-calendar'), 
					sprintf('<abbr class="tribe-events-abbr" title="%s">%s</abbr>', 
						tribe_get_end_date( null, false, TribeDateUtils::DBDATEFORMAT ), 
						tribe_get_end_date()
						), 
					$template );
			} else {
				// If all day event, show only start date
				$html = Tribe_Meta_Factory::template( 
					__( 'Date:', 'tribe-events-calendar'), 
					sprintf('<abbr class="tribe-events-abbr" title="%s">%s</abbr>', 
						tribe_get_start_date( null, false, TribeDateUtils::DBDATEFORMAT ), 
						tribe_get_start_date()
						), 
					$template );
			}
			return apply_filters('tribe_event_meta_event_date', $html );
		}

		function event_category(){
			$post_id = get_the_ID();
			$args = array(
				'before' => '<dd class="tribe-event-categories">',
				'sep' => ', ',
				'after' => '</dd>',
				'label' => __( 'Category', 'tribe-events-calendar' ),
				'label_before' => '<dt>',
				'label_after' => '</dt>',
				'wrap_before' => '',
				'wrap_after' => ''
			);
			// Event categories 
			return apply_filters('tribe_event_meta_event_category', tribe_get_event_categories( $post_id, $args ));
		}
	}

	tribe_register_meta_group( 'tribe_event_details', array(
		'label' => ''
		));

	tribe_register_meta( 'tribe_event_title', array(
		'wrap' => array(
			'before'=>'',
			'after'=>'',
			'label_before'=>'<dt>',
			'label_after'=>'</dt>',
			'meta_before'=>'<dd class="summary">',
			'meta_after'=>'</dd>'
			),
		'label' => __( 'Event:', 'tribe-events-calendar' ),
		'callback' => array('Tribe_Register_Meta', 'the_title'),
		'group' => 'tribe_event_details'
		));

	tribe_register_meta( 'tribe_event_date', array(
		'group' => 'tribe_event_details',
		'filter_callback' => array('Tribe_Register_Meta', 'event_date')
		));

	tribe_register_meta( 'tribe_event_cost', array(
		'wrap' => array(
			'before'=>'',
			'after'=>'',
			'label_before'=>'<dt>',
			'label_after'=>'</dt>',
			'meta_before'=>'<dd class="tribe-events-event-cost">',
			'meta_after'=>'</dd>'
			),
		'label' => __( 'Cost:', 'tribe-events-calendar' ),
		'callback' => 'tribe_get_cost',
		'group' => 'tribe_event_details',
		'show_on_meta' => true
		));

	tribe_register_meta( 'tribe_event_category', array(
		'filter_callback' => array('Tribe_Register_Meta', 'event_category'),
		'group' => 'tribe_event_details'
		));

	tribe_register_meta( 'tribe_list_venue_name_address',array(
		'filter_callback' => array('Tribe_Register_Meta', 'event_category')
		));
	tribe_register_meta( 'tribe_event_distance',array(
		'filter_callback' => array('Tribe_Register_Meta', 'event_category'),
		'show_on_meta' => false
		));

}