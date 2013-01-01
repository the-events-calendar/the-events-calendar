<?php

$template_options = array(
	'' => __( 'Default Events Template', 'tribe-events-calendar' ),
	'default' => __( 'Default Page Template', 'tribe-events-calendar' ),
);
$templates = get_page_templates();
ksort( $templates );
foreach ( array_keys( $templates ) as $template ) {
	$template_options[$templates[$template]] = $template;
}

remove_filter( 'tribe-events-bar-views', array( TribeEvents::instance(), 'remove_hidden_views' ), 9999, 1 );
$views = apply_filters( 'tribe-events-bar-views', array() );

$views_options = array();
foreach( $views as $view ) {
	$views_options[$view['displaying']] = $view['anchor'];
}

$displayTab = array(
	'priority' => 20,
	'fields' => array(
		'info-start' => array(
			'type' => 'html',
			'html' => '<div id="modern-tribe-info">'
		),
		'info-box-title' => array(
			'type' => 'html',
			'html' => '<h2>' . __('Display Settings', 'tribe-events-calendar') . '</h2>',
		),
		'info-box-description' => array(
			'type' => 'html',
			'html' => '<p>' . __('You can apply different page templates to The Events Calendar. Page templates control the layout of individual pages. The Events Calendar comes with a Default Events Template. However, you can apply any page template that is available in your WordPress Theme. If you are having problems getting your Events Calendar to display correctly, switching the page template may solve the problem.</p><p>We make every effort to ensure that the Plugin is compatible with as many Themes as possible but there may be situations in which none of the below templating options will look 100% perfect. Check out our <a href="http://tri.be/support/documentation/events-calendar-themers-guide/">our themer\'s guide</a> to figure out what approach is best for you.', 'tribe-events-calendar') . '</p>',
		),
		'info-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
		'tribe-form-content-start' => array(
			'type' => 'html',
			'html' => '<div class="tribe-settings-form-wrap">',
		),
		'tribeEventsDisplayThemeTitle' => array(
			'type' => 'html',
			'html' => '<h3>' . __( 'General Theme Settings', 'tribe-events-calendar-pro' ) . '</h3>',
		),
		'tribeEventsDisplayThemeHelperText' => array(
			'type' => 'html',
			'html' => '<p class="description">' . __( 'These include general settings that will control various theme settings for your events templates.', 'tribe-events-calendar-pro' ) . '</p>',
		),
		'postsPerPage' => array(
			'type' => 'text',
			'label' => __( 'Number of events to show per page.', 'tribe-events-calendar' ),
			'size' => 'small',
			'default' => get_option( 'posts_per_page' ),
			'validation_type' => 'positive_int',
		 ),
		'showComments' => array(
			'type' => 'checkbox_bool',
			'label' => __( 'Show Comments', 'tribe-events-calendar' ),
			'tooltip' => __( 'Enable commenting on an event.', 'tribe-events-calendar' ),
			'default' => false,
			'validation_type' => 'boolean',
		),
		'showEventsInMainLoop' => array(
			'type' => 'checkbox_bool',
			'label' => __( 'Show Events In Main Loop?', 'tribe-events-calendar' ),
			'tooltip' => __( 'Shows events in the main loop with other posts.' ),
			'default' => false,
			'validation_type' => 'boolean',
		),
		'tribeEventsDisplayTemplateTitle' => array(
			'type' => 'html',
			'html' => '<h3>' . __( 'Template Settings', 'tribe-events-calendar-pro' ) . '</h3>',
		),
		'tribeEventsDisplayTemplateHelperText' => array(
			'type' => 'html',
			'html' => '<p class="description">' . __( 'These include settings that will control various template settings for your events templates.', 'tribe-events-calendar-pro' ) . '</p>',
		),
        'tribeEventsTemplate' => array(
			'type' => 'dropdown_chosen',
		 	'label' => __( 'Events Template', 'tribe-events-calendar' ),
			'tooltip' => __( 'Choose a page template to control the look and feel of your various events templates.', 'tribe-events-calendar' ),
			'validation_type' => 'options',
			'size' => 'large',
			'default' => 'default',
			'options' => $template_options,
		),
		'viewOption' => array(
			'type' => 'radio',
			'label' => __( 'The default template used for your events.', 'tribe-events-calendar' ),
			'default' => 'month',
			'options' => array( 'month' => 'Calendar', 'upcoming' => 'Event List' ),
			'validation_type' => 'options',
		),
		'hideViews' => array(
            'type' => 'checkbox_list',
            'label' => __( 'Which templates would you like used to display your events?', 'tribe-events-calendar' ),
            'default' => false,
            'options' => $views_options,
            'validation_type' => 'options_multi',
            'can_be_empty' => true,
        ),
		'tribeEventsBeforeHTML' => array(
			'type' => 'textarea',
		 	'label' => __( 'Add HTML before event templates', 'tribe-events-calendar' ),
			'tooltip' => __( 'If you are familiar with HTML you can use this input to do things like add additional markup before the event templates. Some themes may require this to help with styling or layout.', 'tribe-events-calendar' ),
			'validation_type' => 'html',
			'size' => 'large',
		),
		'tribeEventsAfterHTML' => array(
			'type' => 'textarea',
		 	'label' => __( 'Add HTML after event templates', 'tribe-events-calendar' ),
			'tooltip' => __( 'If you are familiar with HTML you can use this input to do things like add additional markup after the event templates. Some themes may require this to help with styling or layout.', 'tribe-events-calendar' ),
			'validation_type' => 'html',
			'size' => 'large',
		),
		'tribeEventsDisplayTitle' => array(
			'type' => 'html',
			'html' => '<h3>' . __( 'Front-end Display Settings', 'tribe-events-calendar-pro' ) . '</h3>',
		),
		'tribeEventsDisplayHelperText' => array(
			'type' => 'html',
			'html' => '<p class="description">' . __( 'These include settings that will control the front-end styles and various functionality in your events templates.', 'tribe-events-calendar-pro' ) . '</p>',
		),
		'stylesheetOption' => array(
			'type' => 'radio',
			'label' => __( 'Default stylesheet used for events templates', 'tribe-events-calendar' ),
			'default' => 'full',
			'options' => array( 'full' => 'Full Stylesheet (These styles will most likely override some of your current theme styles on events templates)', 'skeleton' => 'Skeleton Stylesheet (These styles shouldn\'t override your current theme styles)' ),
			'validation_type' => 'options',
		),
		'embedGoogleMaps' => array(
			'type' => 'checkbox_bool',
			'label' => __( 'Enable Google Maps', 'tribe-events-calendar' ),
			'tooltip' => __( 'Check to enable maps for events and venues in the front-end.', 'tribe-events-calendar' ),
			'default' => true,
			'class' => 'google-embed-size',
			'validation_type' => 'boolean',
		),
		'tribe-form-content-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
	)
);