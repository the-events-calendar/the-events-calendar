<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Events__Advanced_Functions__Register_Meta' ) ) {
	/**
	 * Register Meta Group: Event Details
	 *
	 * @category Events
	 */
	tribe_register_meta_group(
		'tribe_event_details', array(
			'label'   => __( 'Details', 'the-events-calendar' ),
			'classes' => array(
				'before'       => array( 'tribe-events-meta-group tribe-events-meta-group-details' ),
				'label_before' => array( 'tribe-events-single-section-title' )
			)
		)
	);

	/**
	 * Register Meta: Event Date (Start/End or Date)
	 *
	 * @category Events
	 * @group tribe_event_details
	 */
	tribe_register_meta(
		'tribe_event_date', array(
			'classes'         => array( 'meta_before' => array( 'tribe-events-date' ) ),
			'group'           => 'tribe_event_details',
			'priority'        => 10,
			'filter_callback' => array( 'Tribe__Events__Advanced_Functions__Register_Meta', 'event_date' )
		)
	);

	/**
	 * Register Meta: Event Cost
	 *
	 * @category Cost
	 * @group tribe_event_details
	 */
	tribe_register_meta(
		'tribe_event_cost', array(
			'classes'      => array( 'meta_before' => array( 'tribe-events-event-cost' ) ),
			'label'        => __( 'Cost:', 'the-events-calendar' ),
			'priority'     => 20,
			'callback'     => 'tribe_get_formatted_cost',
			'group'        => 'tribe_event_details',
			'show_on_meta' => true,
		)
	);

	/**
	 * Register Meta: Event Categories
	 *
	 * @category Events
	 * @group tribe_event_details
	 */
	tribe_register_meta(
		'tribe_event_category', array(
			'classes'         => array( 'meta_before' => array( 'tribe-events-event-categories' ) ),
			'filter_callback' => array( 'Tribe__Events__Advanced_Functions__Register_Meta', 'event_category' ),
			'priority'        => 30,
			'label'           => null,
			'group'           => 'tribe_event_details',
		)
	);

	/**
	 * Register Meta: Event Tags
	 *
	 * @category Events
	 * @group tribe_event_details
	 */
	tribe_register_meta(
		'tribe_event_tag', array(
			'label'           => __( 'Event Tags:', 'the-events-calendar' ),
			'filter_callback' => array( 'Tribe__Events__Advanced_Functions__Register_Meta', 'event_tag' ),
			'priority'        => 40,
			'group'           => 'tribe_event_details',
		)
	);

	/**
	 * Register Meta: Event Website
	 *
	 * @category Events
	 * @group tribe_event_details
	 */
	tribe_register_meta(
		'tribe_event_website', array(
			'classes'         => array( 'meta_before' => array( 'tribe-events-event-url' ) ),
			'label'           => __( 'Website:', 'the-events-calendar' ),
			'filter_callback' => array( 'Tribe__Events__Advanced_Functions__Register_Meta', 'event_website' ),
			'priority'        => 50,
			'group'           => 'tribe_event_details',
		)
	);

	/**
	 * Register Meta: Event Origin
	 *
	 * @category Events
	 * @group tribe_event_details
	 */
	tribe_register_meta(
		'tribe_event_origin', array(
			'classes'         => array( 'meta_before' => array( 'published', 'tribe-events-event-origin' ) ),
			'label'           => __( 'Origin:', 'the-events-calendar' ),
			'filter_callback' => array( 'Tribe__Events__Advanced_Functions__Register_Meta', 'event_origin' ),
			'priority'        => 60,
			'group'           => 'tribe_event_details',
		)
	);

	/**
	 * Register Meta Group: Event Venue
	 *
	 * @category Venues
	 */
	tribe_register_meta_group(
		'tribe_event_venue', array(
			'label'   => __( 'Venue', 'the-events-calendar' ),
			'classes' => array(
				'before'       => array( 'tribe-events-meta-group tribe-events-meta-group-venue', 'vcard' ),
				'label_before' => array( 'tribe-events-single-section-title' )
			)
		)
	);

	/**
	 * Register Meta: Venue Name
	 *
	 * @category Venues
	 * @group tribe_event_venue
	 */
	tribe_register_meta(
		'tribe_event_venue_name', array(
			'classes'         => array( 'meta_before' => array( 'author', 'fn', 'org' ) ),
			'label'           => '',
			'priority'        => 10,
			'filter_callback' => array( 'Tribe__Events__Advanced_Functions__Register_Meta', 'venue_name' ),
			'group'           => 'tribe_event_venue',
		)
	);

	/**
	 * Register Meta: Venue Phone
	 *
	 * @category Venues
	 * @group tribe_event_venue
	 */
	tribe_register_meta(
		'tribe_event_venue_phone', array(
			'classes'  => array( 'meta_before' => array( 'tel' ) ),
			'label'    => __( 'Phone:', 'the-events-calendar' ),
			'priority' => 20,
			'callback' => 'tribe_get_phone',
			'group'    => 'tribe_event_venue',
		)
	);

	/**
	 * Register Meta: Venue Address
	 *
	 * @category Venues
	 * @group tribe_event_venue
	 */
	tribe_register_meta(
		'tribe_event_venue_address', array(
			'classes'         => array( 'meta_before' => array( 'location' ) ),
			'priority'        => 30,
			'label'           => __( 'Address:', 'the-events-calendar' ),
			'filter_callback' => array( 'Tribe__Events__Advanced_Functions__Register_Meta', 'venue_address' ),
			'group'           => 'tribe_event_venue',
		)
	);

	/**
	 * Register Meta: Venue Website
	 *
	 * @category Venues
	 * @group tribe_event_venue
	 */
	tribe_register_meta(
		'tribe_event_venue_website', array(
			'classes'  => array( 'meta_before' => array( 'url' ) ),
			'label'    => __( 'Website:', 'the-events-calendar' ),
			'priority' => 40,
			'callback' => 'tribe_get_venue_website_link',
			'group'    => 'tribe_event_venue',
		)
	);

	/**
	 * Register Meta Group: Event Organizer
	 *
	 * @category Organizers
	 */
	tribe_register_meta_group(
		'tribe_event_organizer', array(
			'label'   => __( 'Organizer', 'the-events-calendar' ),
			'classes' => array(
				'before'       => array( 'tribe-events-meta-group tribe-events-meta-group-organizer', 'vcard' ),
				'label_before' => array( 'tribe-events-single-section-title' )
			)
		)
	);

	/**
	 * Register Meta: Organizer Name (author)
	 *
	 * @category Organizers
	 * @group tribe_event_organizer
	 */
	tribe_register_meta(
		'tribe_event_organizer_name', array(
			'classes'         => array( 'meta_before' => array( 'fn', 'org' ) ),
			'label'           => '',
			'priority'        => 10,
			'filter_callback' => array( 'Tribe__Events__Advanced_Functions__Register_Meta', 'organizer_name' ),
			'group'           => 'tribe_event_organizer',
		)
	);

	/**
	 * Register Meta: Organizer Phone
	 *
	 * @category Organizers
	 * @group tribe_event_organizer
	 */
	tribe_register_meta(
		'tribe_event_organizer_phone', array(
			'classes'  => array( 'meta_before' => array( 'tel' ) ),
			'label'    => __( 'Phone:', 'the-events-calendar' ),
			'priority' => 20,
			'callback' => 'tribe_get_organizer_phone',
			'group'    => 'tribe_event_organizer',
		)
	);

	/**
	 * Register Meta: Organizer Email
	 *
	 * @category Organizers
	 * @group tribe_event_organizer
	 */
	tribe_register_meta(
		'tribe_event_organizer_email', array(
			'classes'         => array( 'meta_before' => array( 'email' ) ),
			'label'           => __( 'Email:', 'the-events-calendar' ),
			'priority'        => 30,
			'filter_callback' => array( 'Tribe__Events__Advanced_Functions__Register_Meta', 'organizer_email' ),
			'group'           => 'tribe_event_organizer',
		)
	);

	/**
	 * Register Meta: Organizer Website
	 *
	 * @category Organizers
	 * @group tribe_event_organizer
	 */
	tribe_register_meta(
		'tribe_event_organizer_website', array(
			'classes'  => array( 'meta_before' => array( 'url' ) ),
			'label'    => __( 'Website:', 'the-events-calendar' ),
			'priority' => 40,
			'callback' => 'tribe_get_organizer_website_link',
			'group'    => 'tribe_event_organizer',
		)
	);

	/**
	 * Register Meta: Event Title
	 *
	 * @category Events
	 * @group none specified
	 */
	tribe_register_meta(
		'tribe_event_title', array(
			'classes'  => array( 'meta_before' => array( 'tribe-events-meta-event-title', 'summary' ) ),
			'label'    => sprintf( __( '%s:', 'the-events-calendar' ), tribe_get_event_label_singular() ),
			'callback' => array( 'Tribe__Events__Advanced_Functions__Register_Meta', 'the_title' )
		)
	);

	/**
	 * Register Meta: Venue Map
	 *
	 * @category Venues
	 * @group tribe_event_venue
	 */
	tribe_register_meta(
		'tribe_venue_map', array(
			'wrap'            => array(
				'before'       => '',
				'after'        => '',
				'label_before' => '',
				'label_after'  => '',
				'meta_before'  => '<div class="%s">',
				'meta_after'   => '</div>',
			),
			'classes'         => array( 'meta_before' => array( 'tribe-events-venue-map' ) ),
			'label'           => '',
			'priority'        => 10,
			'filter_callback' => array( 'Tribe__Events__Advanced_Functions__Register_Meta', 'venue_map' )
		)
	);

	/**
	 * Register Meta: Venue Map Link
	 *
	 * @category Venues
	 * @group tribe_event_venue
	 */
	tribe_register_meta(
		'tribe_event_venue_gmap_link', array(
			'wrap'            => array(
				'before'       => '',
				'after'        => '',
				'label_before' => '',
				'label_after'  => '',
				'meta_before'  => '',
				'meta_after'   => '',
			),
			'label'           => '',
			'filter_callback' => 'tribe_get_map_link_html',
		)
	);
}
