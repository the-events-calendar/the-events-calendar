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

		function event_venue(){
			$html = null;
			$gmap = null;
			$location = null;
			$venue = null;
			$post_id = get_the_ID();

			// Get venue or location
			if( tribe_get_venue() || tribe_address_exists( $post_id ) ) { 

				// Get the venue
				if ( tribe_get_venue() ) {
					$venue_name = tribe_get_venue( $post_id );
					$venue = class_exists( 'TribeEventsPro' ) ? sprintf('<a href="%s">%s</a>',
						tribe_get_venue_link( $post_id, false ),
						$venue_name
						) : $venue_name;
				}

				// if venue is provided make sure we add the separator
				$sep = !empty($venue) ? ', ' : '';

				// Get the event address
				if ( tribe_address_exists( $post_id ) ) {
					$gmap = ( get_post_meta( $post_id, '_EventShowMapLink', true ) == 'true' ) ? '<a class="tribe-events-gmap" href="' . tribe_get_map_link() . '" title="' . __('Click to view this event\'s Google Map', 'tribe-events-calendar') . '" target="_blank">'. __( 'Google Map', 'tribe-events-calendar' ) . '</a>' : '';
					$location = sprintf('%s%s <address class="event-address">%s</address>', 
						$sep,
						$gmap,
						tribe_get_full_address( $post_id ) 
						);
				}

				$html = sprintf( '<h3 class="vcard fn org">%s%s</h3>',
					$venue,
					$location
					);
												
			}

			return apply_filters('tribe_event_meta_event_category', $html, $post_id, $venue, $gmap, $location );
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
		'filter_callback' => array('Tribe_Register_Meta', 'event_venue')
		));
	tribe_register_meta( 'tribe_event_distance',array(
		'filter_callback' => array('Tribe_Register_Meta', 'event_category'),
		'show_on_meta' => false
		));

}