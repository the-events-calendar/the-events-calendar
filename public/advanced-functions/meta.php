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
		'group' => 'tribe_event_details'
		));

}