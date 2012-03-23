<?php
$generalTab = array(
	'priority' => 10,
	'fields' => array(
		'info-start' => array(
			'type' => 'html',
			'html' => '<div id="modern-tribe-info"><img src="'.plugins_url('resources/images/modern-tribe.png', dirname(__FILE__)).'" alt="Modern Tribe Inc." title="Modern Tribe Inc.">'
		),
		'upsell-heading' => array(
			'type' => 'heading',
			'label' => __('Add functionality to The Events Calendar', 'tribe-events-calendar'),
			'conditional' => ( !defined('TRIBE_HIDE_UPSELL') || !TRIBE_HIDE_UPSELL ),
		),
		'upsell-info' => array(
			'type' => 'html',
			'html' => '<p>'.__('Looking for additional functionality including recurring events, custom meta, community events, ticket sales and more?', 'tribe-events-calendar' ).'<br><a href="'.self::$tribeUrl.'shop/?utm_source=generaltab&utm_medium=promolink&utm_campaign=plugin'.'">'.__('Check out the available Add-Ons', 'tribe-events-calendar').'</a></p>',
			'conditional' => ( !defined('TRIBE_HIDE_UPSELL') || !TRIBE_HIDE_UPSELL ),
		),
		'donate-link-heading' => array(
			'type' => 'heading',
			'label' => __('We hope our plugin is helping you out.', 'tribe-events-calendar'),
		),
		'donate-link-info' => array(
			'type' => 'html',
			'html' => '<p>'.__('Are you thinking "Wow, this plugin is amazing! I should say thanks to Modern Tribe for all their hard work." The greatest thanks we could ask for is recognition. Add a small text only link at the bottom of your calendar pointing to The Events Calendar project.', 'tribe-events-calendar').'<br><a href="'.plugins_url('resources/images/donate-link-screenshot.jpg', dirname(__FILE__)).'" class="thickbox">'.__('See an example of the link', 'tribe-events-calendar').'</a></p>',
			'conditional' => !class_exists('TribeEventsPro'),
		),
		'donate-link-pro-info' => array(
			'type' => 'html',
			'html' => '<p>'.__('Are you thinking "Wow, this plugin is amazing! I should say thanks to Modern Tribe for all their hard work." The greatest thanks we could ask for is recognition. Add a small text only link at the bottom of your calendar pointing to The Events Calendar project.', 'tribe-events-calendar').'<br><a href="'.plugins_url('resources/images/donate-link-pro-screenshot.jpg', dirname(__FILE__)).'" class="thickbox">'.__('See an example of the link', 'tribe-events-calendar').'</a></p>',
			'conditional' => class_exists('TribeEventsPro'),
		),
		'donate-link' => array(
			'type' => 'checkbox_bool',
			'label' => __('Show Events Calendar Link', 'tribe-events-calendar'),
			'default' => false,
			'validation_type' => 'boolean',
		),
		'info-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
		'viewOption' => array(
			'type' => 'radio',
			'label' => __('Default view for the Events', 'tribe-events-calendar'),
			'tooltip' => __('Determines whether the default events view is a calendar or a list.', 'tribe-events-calendar'),
			'default' => 'month',
			'options' => array('month' => 'Calendar', 'upcoming' => 'Event List'),
			'validation_type' => 'options'
		),
		'eventsSlug' => array(
			'type' => 'text',
			'label' => __('Events URL slug', 'tribe-events-calendar'),
			'default' => 'events',
			'validation_type' => 'slug',
			'conditional' => ( '' != get_option('permalink_structure') ),
		 ),
		'current-events-slug' => array(
			'type' => 'html',
			'display_callback' => 'tribe_display_current_events_slug',
			'conditional' => 	( '' != get_option('permalink_structure') ),
		),
		'ical-info' => array(
			'type' => 'html',
			'display_callback' => 'tribe_display_current_ical_link',
			'conditional' => function_exists('tribe_get_ical_link'),
		),
		'singleEventSlug' => array(
			'type' => 'text',
			'label' => __('Single Event URL slug', 'tribe-events-calendar'),
			'default' => 'event',
			'validation_type' => 'slug',
			'conditional' => ( '' != get_option('permalink_structure') ),
		 ),
		'current-single-event-slug' => array(
			'type' => 'html',
			'display_callback' => 'tribe_display_current_single_event_slug',
			'conditional' => 	( '' != get_option('permalink_structure') ),
		),
		'postsPerPage' => array(
			'type' => 'text',
			'label' => __('Number of events to show per page in the loop', 'tribe-events-calendar'),
			'tooltip' => __('This is the number of events displayed per page when returning a list of events.', 'tribe-events-calendar'),
			'size' => 'small',
			'default' => get_option('posts_per_page'),
			'validation_type' => 'positive_int',
		 ),
		'showComments' => array(
			'type' => 'checkbox_bool',
			'label' => __('Show Comments', 'tribe-events-calendar'),
			'tooltip' => __('Enables commenting on your single event view.', 'tribe-events-calendar'),
			'default' => false,
			'validation_type' => 'boolean'
		),
		'multiDayCutoff' => array(
			'type' => 'dropdown',
		 	'label' => __('Multiday Event Cutoff', 'tribe-events-calendar'),
			'tooltip' => __('For multi-day events, hide the last day from grid view if it ends on or before this time.', 'tribe-events-calendar'),
			'validation_type' => 'options',
			'size' => 'small',
			'default' => '12:00',
			'options' => array('12:00' => '12:00 am', '12:30' => '12:30 am', '01:00' => '01:00 am', '01:30' => '01:30 am', '02:00' => '02:00 am', '02:30' => '02:30 am', '03:00' => '03:00 am', '03:30' => '03:30 am', '04:00' => '04:00 am', '04:30' => '04:30 am', '05:00' => '05:00 am', '05:30' => '05:30 am', '06:00' => '06:00 am', '06:30' => '06:30 am', '07:00' => '07:00 am', '07:30' => '07:30 am', '08:00' => '08:00 am', '08:30' => '08:30 am', '09:00' => '09:00 am', '09:30' => '09:30 am', '10:00' => '10:00 am', '10:30' => '10:30 am', '11:00' => '11:00 am', '11:30' => '11:30 am'),
		),
		'embedGoogleMaps' => array(
			'type' => 'checkbox_bool',
			'label' => __('Enable Google Maps', 'tribe-events-calendar'),
			'tooltip' => __('If you don\'t have this turned on, your event listings won\'t have the backend map preview or frontend embedded map.', 'tribe-events-calendar'),
			'default' => false,
			'class' => 'google-embed-size',
			'validation_type' => 'boolean'
		),
		'embedGoogleMapsHeight' => array(
			'type' => 'text',
			'label' => __('Google Maps Embed Height', 'tribe-events-calendar'),
			'size' => 'small',
			'default' => 350,
			'tooltip' => __('Enter a number.', 'tribe-events-calendar'),
			'class' => 'google-embed-field',
			'validation_type' => 'positive_int',
		 ),
		'embedGoogleMapsWidth' => array(
			'type' => 'text',
			'label' => __('Google Maps Embed Width', 'tribe-events-calendar'),
			'size' => 'small',
			'tooltip' => __('Enter a number or %.', 'tribe-events-calendar'),
			'default' => '100%',
			'class' => 'google-embed-field',
			'validation_type' => 'number_or_percent',
		 ),
		'embedGoogleMapsZoom' => array(
			'type' => 'text',
			'label' => __('Google Maps Default Zoom Level', 'tribe-events-calendar'),
			'tooltip' => __('0 = zoomed-out; 21 = zoomed-in.', 'tribe_events_calendar'),
			'size' => 'small',
			'default' => 10,
			'class' => 'google-embed-field',
			'validation_type' => 'number_or_percent',
		 ),
		'debugEvents' => array(
			'type' => 'checkbox_bool',
			'label' => __('Debug Mode', 'tribe-events-calendar'),
			'tooltip' => sprintf( __('Enable this option to log debug information. By default this will log to your server PHP error log. If you\'d like to see the log messages in your browser, then we recommend that you install the %s and look for the "Tribe" tab in the debug output.', 'tribe-events-calendar'), '<a href="http://wordpress.org/extend/plugins/debug-bar/" target="_blank">'.__('Debug Bar Plugin', 'tribe-events-calendar').'</a>' ),
			'default' => false,
			'validation_type' => 'boolean'
		),
	),
);