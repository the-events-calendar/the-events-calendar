<?php
$generalTab = array(
	'priority' => 10,
	'fields' => array(
		'upsell-heading' => array(
			'type' => 'heading',
			'label' => __('Additional Functionality', 'tribe-events-calendar'),
			'conditional' => ( !defined('TRIBE_HIDE_UPSELL') || !TRIBE_HIDE_UPSELL ),
		),
		'upsell-info' => array(
			'type' => 'html',
			'html' => '<p>'.__('Looking for additional functionality including recurring events, custom meta, community events, ticket sales and more?', 'tribe-events-calendar' ).'</p>'.'<p>'.sprintf(__('Check out the %s.', 'tribe-events-calendar' ), '<a href="'.self::$tribeUrl.'?ref=tec-options'.'">'.__('available Add-Ons', 'tribe-events-calendar').'</a>').'</p>',
			'conditional' => ( !defined('TRIBE_HIDE_UPSELL') || !TRIBE_HIDE_UPSELL ),
		),
		'ical-heading' => array(
			'type' => 'heading',
			'label' => __('iCal', 'tribe-events-calendar'),
			'conditional' => function_exists('tribe_get_ical_link'),
		),
		'ical-info' => array(
			'type' => 'html',
			'html' => (function_exists('tribe_get_ical_link')) ? '<p>'.__('Here is the iCal feed URL for your events:', 'tribe-events-calendar').' '.'<code>'.tribe_get_ical_link().'</code></p>' : '',
			'conditional' => function_exists('tribe_get_ical_link'),
		),
		'donate-link' => array(
			'type' => 'checkbox_bool',
			'label' => __('Donate a link', 'tribe-events-calendar'),
			'tooltip' => __('Are you thinking \'\'Wow, this plugin is amazing! I should say thanks to tribe for all their hard work.\'\' The greatest thanks we could ask for is recognition. Check this box to add a small text only link at the bottom of your calendar pointing to the events calendar project.', 'tribe-events-calendar'),
			'default' => false,
			'validation_type' => 'boolean'
		),
		'settings-heading' => array(
			'type' => 'heading',
			'label' => __('Settings', 'tribe-events-calendar'),
		),
		'viewOption' => array(
			'type' => 'radio',
			'label' => __('Default view for the Events', 'tribe-events-calendar'),
			'tooltip' => __('Determines whether the default events view is a calendar or a list', 'tribe-events-calendar'),
			'default' => 'month',
			'options' => array('month' => 'Calendar', 'upcoming' => 'Event List'),
			'validation_type' => 'options'
		),
		'eventsSlug' => array(
			'type' => 'text',
			'label' => __('Events URL slug', 'tribe-events-calendar'),
			'tooltip' => __('The slug used for building the Events URL', 'tribe-events-calendar'),
			'default' => 'events',
			'validation_type' => 'slug',
			'conditional' => ( '' != get_option('permalink_structure') ),
		 ),
		'slug-info' => array(
			'type' => 'html',
			'html' => '<p class="tribe-field-indent">'.sprintf( __('Your current Events URL is %s', 'tribe-events-calendar' ), '<strong><a href="'.tribe_get_events_link().'">'.tribe_get_events_link().'</a></strong>' ).'</p>',
			'conditional' => ( '' != get_option('permalink_structure') ),
		),
		'singleEventSlug' => array(
			'type' => 'text',
			'label' => __('Single Event URL slug', 'tribe-events-calendar'),
			'tooltip' => __('The slug used for building a single Event URL.', 'tribe-events-calendar', 'tribe-events-calendar'),
			'default' => 'event',
			'validation_type' => 'slug',
			'conditional' => ( '' != get_option('permalink_structure') ),
		 ),
		'single-slug-info' => array(
			'type' => 'html',
			'html' => '<p class="tribe-field-indent">'.sprintf( __('<strong>NOTE:</strong> You <em>cannot</em> use the same slug as above. The above should ideally be plural, and this singular.<br />Your single Event URL is like: <strong>%s</strong>', 'tribe-events-calendar' ), trailingslashit( home_url() ) . tribe_get_option('singleEventSlug', 'event') . '/single-post-name/' ).'</p>',
			'conditional' => ( '' != get_option('permalink_structure') ),
		),
		'postsPerPage' => array(
			'type' => 'text',
			'label' => __('Number of events to show per page in the loop', 'tribe-events-calendar'),
			'tooltip' => __('This is the number of events displayed per page when returning a list of events', 'tribe-events-calendar'),
			'size' => 'small',
			'default' => get_option('posts_per_page'),
			'validation_type' => 'positive_int',
		 ),
		'showComments' => array(
			'type' => 'checkbox_bool',
			'label' => __('Show Comments', 'tribe-events-calendar'),
			'default' => false,
			'validation_type' => 'boolean'
		),
		'multiDayCutoff' => array(
			'type' => 'dropdown',
		 	'label' => __('Multiday Event Cutoff', 'tribe-events-calendar'),
			'tooltip' => __('This is the number of events displayed per page when returning a list of events', 'tribe-events-calendar'),
			'validation_type' => 'options',
			'size' => 'small',
			'default' => '12:00',
			'options' => array('12:00' => '12:00 am', '12:30' => '12:30 am', '01:00' => '01:00 am', '01:30' => '01:30 am', '02:00' => '02:00 am', '02:30' => '02:30 am', '03:00' => '03:00 am', '03:30' => '03:30 am', '04:00' => '04:00 am', '04:30' => '04:30 am', '05:00' => '05:00 am', '05:30' => '05:30 am', '06:00' => '06:00 am', '06:30' => '06:30 am', '07:00' => '07:00 am', '07:30' => '07:30 am', '08:00' => '08:00 am', '08:30' => '08:30 am', '09:00' => '09:00 am', '09:30' => '09:30 am', '10:00' => '10:00 am', '10:30' => '10:30 am', '11:00' => '11:00 am', '11:30' => '11:30 am'),
		),
		'googleEmbedSize' => array(
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
			'class' => 'google-embed-field',
			'validation_type' => 'number_or_percent',
		 ),
		'embedGoogleMapsWidth' => array(
			'type' => 'text',
			'label' => __('Google Maps Embed Width', 'tribe-events-calendar'),
			'size' => 'small',
			'tooltip' => __('number or %', 'tribe-events-calendar'),
			'default' => '100%',
			'class' => 'google-embed-field',
			'validation_type' => 'number_or_percent',
		 ),
		'embedGoogleMapsZoom' => array(
			'type' => 'text',
			'label' => __('Google Maps Default Zoom Level', 'tribe-events-calendar'),
			'tooltip' => __('0 = zoomed-out; 21 = zoomed-in', 'tribe_events_calendar'),
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