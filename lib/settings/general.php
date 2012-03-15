<?php
$generalTab = array(
	'priority' => 10,
	'fields' => array(
		'upsell-heading' => array(
			'type' => 'heading',
			'label' => 'Additional Functionality',
		),
		'upsell-info' => array(
			'type' => 'html',
			'html' => '<p>'.__('Looking for additional functionality including recurring events, custom meta, community events, ticket sales and more?', 'tribe-events-calendar' ).'</p>'.'<p>'.sprintf(__('Check out the %s.', 'tribe-events-calendar' ), '<a href="'.self::$tribeUrl.'?ref=tec-options'.'">'.__('available Add-Ons', 'tribe-events-calendar').'</a>').'</p>',
		),		'ical-heading' => array(
			'type' => 'heading',
			'label' => 'iCal',
		),
		'ical-info' => array(
			'type' => 'html',
			'html' => '<p>'.__('Here is the iCal feed URL for your events:', 'tribe-events-calendar').' '.'<code>'.tribe_get_ical_link().'</code></p>',
		),
		'settings-heading' => array(
			'type' => 'heading',
			'label' => 'Settings'
		),
		'viewOption' => array(
			'type' => 'radio',
			'label' => __('Default view for the Events', 'tribe-events-calendar'),
			'tooltip' => __('Determines whether the default events view is a calendar or a list', 'tribe-events-calendar'),
			'default' => 'month',
			'options' => array('month' => 'Calendar', 'upcoming' => 'Event List'),
			'validation_type' => 'options'
		),
		'postsPerPage' => array(
			'type' => 'text',
			'label' => __('Number of events to show per page in the loop', 'tribe-events-calendar'),
			'tooltip' => __('This is the number of events displayed per page when returning a list of events', 'tribe-events-calendar'),
			'size'=> 'small',
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
			'validation_type' => 'boolean'
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

