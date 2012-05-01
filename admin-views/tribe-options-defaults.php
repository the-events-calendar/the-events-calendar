<?php

$organizers = TribeEvents::instance()->get_organizer_info();
$organizer_options = array();
if ( is_array($organizers) && !empty($organizers) ) {
	$organizer_options[0] = __('No Default', 'tribe-events-calendar-pro');
	foreach ($organizers as $organizer) {
		$organizer_options[$organizer->ID] = $organizer->post_title;
	}
}

$venues = TribeEvents::instance()->get_venue_info();
$venue_options = array();
if ( is_array($venues) && !empty($venues) ) {
	$venue_options[0] = __('Use New Venue/No Default', 'tribe-events-calendar-pro');
	foreach ($venues as $venue) {
		$venue_options[$venue->ID] = $venue->post_title;
	}
}

$state_options = TribeEventsViewHelpers::loadStates();
$state_options = array_merge( array( '' => __('Select a State','tribe-events-calendar-pro') ), $state_options );

$country_options = TribeEventsViewHelpers::constructCountries();

$defaultsTab = array(
	'priority' => 30,
	'fields' => array(
		'defaults-heading' => array(
			'type' => 'heading',
			'label' => __('Customize Defaults', 'tribe-events-calendar-pro'),
		),
		'defaults-info' => array(
			'type' => 'html',
			'html' => '<p>'.__('These settings change the default event form. For example, if you set a default venue, this field will be automatically filled in on a new event.', 'tribe-events-calendar-pro').'</p>',
		),
		'defaultValueReplace' => array(
			'type' => 'checkbox_bool',
			'label' => __('Automatically replace empty fields with default values','tribe-events-calendar-pro'),
			'default' => false,
			'validation_type' => 'boolean',
		),
		'eventsDefaultOrganizerID' => array(
			'type' => 'dropdown_chosen',
			'label' => __('Default Organizer for Events','tribe-events-calendar-pro'),
			'default' => false,
			'validation_type' => 'options',
			'options' => $organizer_options,
			'if_empty' => __('No saved organizers yet.','tribe-events-calendar-pro'),
			'can_be_empty' => true,
		),
		'current-default-organizer' => array(
			'type' => 'html',
			'display_callback' => 'tribe_display_saved_organizer',
		),
		'eventsDefaultVenueID' => array(
			'type' => 'dropdown_chosen',
			'label' => __('Default Venue for Events','tribe-events-calendar-pro'),
			'default' => false,
			'validation_type' => 'options',
			'options' => $venue_options,
			'if_empty' => __('No saved venues yet.','tribe-events-calendar-pro'),
			'can_be_empty' => true,
		),
		'current-default-venue' => array(
			'type' => 'html',
			'display_callback' => 'tribe_display_saved_venue',
		),
		'eventsDefaultAddress' => array(
			'type' => 'text',
			'label' => __('Default Address for Events','tribe-events-calendar-pro'),
			'default' => false,
			'class' => 'venue-default-info',
			'validation_type' => 'address',
			'can_be_empty' => true,
		),
		'current-default-address' => array(
			'type' => 'html',
			'class' => 'venue-default-info',
			'display_callback' => 'tribe_display_saved_address',
		),
		'eventsDefaultCity' => array(
			'type' => 'text',
			'label' => __('Default City for Events','tribe-events-calendar-pro'),
			'default' => false,
			'class' => 'venue-default-info',
			'validation_type' => 'city_or_province',
			'can_be_empty' => true,
		),
		'current-default-city' => array(
			'type' => 'html',
			'class' => 'venue-default-info',
			'display_callback' => 'tribe_display_saved_city',
		),
		'eventsDefaultState' => array(
			'type' => 'dropdown_chosen',
			'label' => __('Default State for Events','tribe-events-calendar-pro'),
			'default' => false,
			'class' => 'venue-default-info',
			'validation_type' => 'options',
			'options' => $state_options,
			'can_be_empty' => true,
		),
		'current-default-state' => array(
			'type' => 'html',
			'display_callback' => 'tribe_display_saved_state',
		),
		'eventsDefaultProvince' => array(
			'type' => 'text',
			'label' => __('Default Province for Events','tribe-events-calendar-pro'),
			'default' => false,
			'class' => 'venue-default-info',
			'validation_type' => 'city_or_province',
			'can_be_empty' => true,
		),
		'current-default-province' => array(
			'type' => 'html',
			'class' => 'venue-default-info',
			'display_callback' => 'tribe_display_saved_province',
		),
		'eventsDefaultZip' => array(
			'type' => 'text',
			'label' => __('Default Postal Code/Zip Code for Events','tribe-events-calendar-pro'),
			'default' => false,
			'class' => 'venue-default-info',
			'validation_type' => 'address', // allows for letters, numbers, dashses and spaces only
			'can_be_empty' => true,
		),
		'current-default-zip' => array(
			'type' => 'html',
			'class' => 'venue-default-info',
			'display_callback' => 'tribe_display_saved_zip',
		),
		'defaultCountry' => array(
			'type' => 'dropdown_chosen',
			'label' => __('Default Country for Events','tribe-events-calendar-pro'),
			'default' => false,
			'class' => 'venue-default-info',
			'validation_type' => 'options_with_label',
			'options' => $country_options,
			'can_be_empty' => true,
		),
		'current-default-country' => array(
			'type' => 'html',
			'display_callback' => 'tribe_display_saved_country',
		),
		'eventsDefaultPhone' => array(
			'type' => 'text',
			'label' => __('Default Phone for Events','tribe-events-calendar-pro'),
			'default' => false,
			'class' => 'venue-default-info',
			'validation_type' => 'phone',
			'can_be_empty' => true,
		),
		'current-default-phone' => array(
			'type' => 'html',
			'class' => 'venue-default-info',
			'display_callback' => 'tribe_display_saved_phone',
		),
		'tribeEventsCountries' => array(
			'type' => 'textarea',
			'label' => __('Use a custom list of countries','tribe-events-calendar-pro'),
			'default' => false,
			'validation_type' => 'country_list',
			'tooltip' => __('One country per line in the following format: <br>US, United States <br> UK, United Kingdom. <br> (Replaces the default list.)', 'tribe-events-calendar-pro'),
			'can_be_empty' => true,
		),
	)
);