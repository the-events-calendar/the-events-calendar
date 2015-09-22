<?php

$tec = Tribe__Events__Main::instance();

$general_tab_fields = Tribe__Main::array_insert_after_key(
	'info-start',
	$general_tab_fields,
	array(
		// after info-start
		'upsell-heading'                => array(
			'type'        => 'heading',
			'label'       => __( 'Finding & extending your calendar.', 'the-events-calendar' ),
			'conditional' => ( ! defined( 'TRIBE_HIDE_UPSELL' ) || ! TRIBE_HIDE_UPSELL ),
		),
		'finding-heading'               => array(
			'type'        => 'heading',
			'label'       => __( 'Finding your calendar.', 'the-events-calendar' ),
			'conditional' => ( defined( 'TRIBE_HIDE_UPSELL' ) && TRIBE_HIDE_UPSELL ),
		),
		'view-calendar-link'            => array(
			'type' => 'html',
			'html' => '<p>' . __( 'Where\'s my calendar?', 'the-events-calendar' ) . ' <a href="' . esc_url( Tribe__Events__Main::instance()->getLink() ) . '">' . __( 'Right here', 'the-events-calendar' ) . '</a>.</p>',
		),
	)
);

$general_tab_fields = Tribe__Main::array_insert_before_key(
	'multiDayCutoff',
	$general_tab_fields,
	array(
		'postsPerPage'                  => array(
			'type'            => 'text',
			'label'           => __( 'Number of events to show per page', 'the-events-calendar' ),
			'size'            => 'small',
			'default'         => get_option( 'posts_per_page' ),
			'validation_type' => 'positive_int',
		),
		'liveFiltersUpdate'             => array(
			'type'            => 'checkbox_bool',
			'label'           => __( 'Use Javascript to control date filtering', 'the-events-calendar' ),
			'tooltip'         => tribe_get_option( 'tribeDisableTribeBar', false ) == true ? __( 'This option is disabled when "Disable the Event Search Bar" is checked on the Display settings tab.', 'the-events-calendar' ) : __( 'Enable live ajax for datepicker on front end (User submit not required).', 'the-events-calendar' ),
			'attributes'      => tribe_get_option( 'tribeDisableTribeBar', false ) == true ? array( 'disabled' => 'disabled' ) : null,
			'default'         => true,
			'validation_type' => 'boolean',
			'class'           => tribe_get_option( 'tribeDisableTribeBar', false ) == true ? 'tribe-fieldset-disabled' : null,
		),
		'showComments'                  => array(
			'type'            => 'checkbox_bool',
			'label'           => __( 'Show comments', 'the-events-calendar' ),
			'tooltip'         => __( 'Enable comments on event pages.', 'the-events-calendar' ),
			'default'         => false,
			'validation_type' => 'boolean',
		),
		'showEventsInMainLoop'          => array(
			'type'            => 'checkbox_bool',
			'label'           => __( 'Include events in main blog loop', 'the-events-calendar' ),
			'tooltip'         => __( 'Show events with the site\'s other posts. When this box is checked, events will also continue to appear on the default events page.', 'the-events-calendar' ),
			'default'         => false,
			'validation_type' => 'boolean',
		),
		'unprettyPermalinksUrl'         => array(
			'type'        => 'html',
			'label'       => __( 'Events URL slug', 'the-events-calendar' ),
			'html'        => '<p>' . sprintf( __( 'You cannot edit the slug for your events page as you do not have pretty permalinks enabled. The current URL for your events page is <a href="%1$s">%2$s</a>. In order to edit the slug here, <a href="%3$soptions-permalink.php">enable pretty permalinks</a>.', 'the-events-calendar' ), esc_url( $tec->getLink( 'home' ) ), $tec->getLink( 'home ' ), esc_url( trailingslashit( get_admin_url() ) ) ) . '</p>',
			'conditional' => ( '' == get_option( 'permalink_structure' ) ),
		),
		'eventsSlug'                    => array(
			'type'            => 'text',
			'label'           => __( 'Events URL slug', 'the-events-calendar' ),
			'default'         => 'events',
			'validation_type' => 'slug',
			'conditional'     => ( '' != get_option( 'permalink_structure' ) ),
		),
		'current-events-slug'           => array(
			'type'        => 'html',
			'html'        => '<p class="tribe-field-indent tribe-field-description description">' . __( 'The slug used for building the events URL.', 'the-events-calendar' ) . sprintf( __( 'Your current events URL is: %s', 'the-events-calendar' ), '<code><a href="' . esc_url( tribe_get_events_link() ) . '">' . tribe_get_events_link() . '</a></code>' ) . '</p>',
			'conditional' => ( '' != get_option( 'permalink_structure' ) ),
		),
		'ical-info'                     => array(
			'type'             => 'html',
			'display_callback' => ( function_exists( 'tribe_get_ical_link' ) ) ? '<p id="ical-link" class="tribe-field-indent tribe-field-description description">' . __( 'Here is the iCal feed URL for your events:', 'the-events-calendar' ) . ' <code>' . tribe_get_ical_link() . '</code></p>' : '',
			'conditional'      => function_exists( 'tribe_get_ical_link' ),
		),
		'singleEventSlug'               => array(
			'type'            => 'text',
			'label'           => __( 'Single event URL slug', 'the-events-calendar' ),
			'default'         => 'event',
			'validation_type' => 'slug',
			'conditional'     => ( '' != get_option( 'permalink_structure' ) ),
		),
		'current-single-event-slug'     => array(
			'type'        => 'html',
			'html'        => '<p class="tribe-field-indent tribe-field-description description">' . sprintf( __( 'The above should ideally be plural, and this singular.<br />Your single event URL is: %s', 'the-events-calendar' ), '<code>' . trailingslashit( home_url() ) . tribe_get_option( 'singleEventSlug', 'event' ) . '/single-post-name/</code>' ) . '</p>',
			'conditional' => ( '' != get_option( 'permalink_structure' ) ),
		),
	)
);

$general_tab_fields = Tribe__Main::array_insert_before_key(
	'tribeEventsMiscellaneousTitle',
	$general_tab_fields,
	array(
		'amalgamateDuplicates'          => array(
			'type'        => 'html',
			'html'        => '<fieldset class="tribe-field tribe-field-html"><legend>' . __( 'Duplicate Venues &amp; Organizers', 'the-events-calendar' ) . '</legend><div class="tribe-field-wrap">' . Tribe__Events__Amalgamator::migration_button( __( 'Merge Duplicates', 'the-events-calendar' ) ) . '<p class="tribe-field-indent description">' . __( 'You might find duplicate venues and organizers when updating The Events Calendar from a pre-3.0 version. Click this button to automatically merge identical venues and organizers.', 'the-events-calendar' ) . '</p></div></fieldset><div class="clear"></div>',
			'conditional' => ( Tribe__Settings_Manager::get_option( 'organizer_venue_amalgamation', 0 ) < 1 ),
		),
	)
);

$general_tab_fields = Tribe__Main::array_insert_before_key(
	'tribeEventsMiscellaneousTitle',
	$general_tab_fields,
	array(
		'tribeEventsDisplayTitle'       => array(
			'type' => 'html',
			'html' => '<h3>' . __( 'Map Settings', 'the-events-calendar' ) . '</h3>',
		),
		'embedGoogleMaps'               => array(
			'type'            => 'checkbox_bool',
			'label'           => __( 'Enable Google Maps', 'the-events-calendar' ),
			'tooltip'         => __( 'Check to enable maps for events and venues.', 'the-events-calendar' ),
			'default'         => true,
			'class'           => 'google-embed-size',
			'validation_type' => 'boolean',
		),
		'embedGoogleMapsZoom'           => array(
			'type'            => 'text',
			'label'           => __( 'Google Maps default zoom level', 'the-events-calendar' ),
			'tooltip'         => __( '0 = zoomed out; 21 = zoomed in.', 'the-events-calendar' ),
			'size'            => 'small',
			'default'         => 10,
			'class'           => 'google-embed-field',
			'validation_type' => 'number_or_percent',
		),
	)
);

return $general_tab_fields;
