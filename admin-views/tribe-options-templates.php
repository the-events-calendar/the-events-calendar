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

$templatesTab = array(
	'priority' => 20,
	'fields' => array(
		'info-start' => array(
			'type' => 'html',
			'html' => '<div id="modern-tribe-info">'
		),
		'info-box-title' => array(
			'type' => 'html',
			'html' => '<h2>' . __('Template Settings', 'tribe-events-calendar') . '</h2>',
		),
		'info-box-description' => array(
			'type' => 'html',
			'html' => '<p>' . __('You can apply different page templates to The Events Calendar. Page templates control the layout of individual pages. The Events Calendar comes with a Default Events Template. However, you can apply any page template that is available in your WordPress Theme. If you are having problems getting your Events Calendar to display correctly, switching the page template may solve the problem.</p><p>We make every effort to ensure that the Plugin is compatible with as many Themes as possible but there may be situations in which none of the below templating options will look 100% perfect. Check out our <a href="http://tri.be/support/documentation/events-calendar-themers-guide/">our themer\'s guide</a> to figure out what approach is best for you.', 'tribe-events-calendar') . '</p>',
		),
		'info-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
		'tribeEventsTemplate' => array(
			'type' => 'dropdown_chosen',
		 	'label' => __( 'Events Template', 'tribe-events-calendar' ),
			'tooltip' => __( 'Choose a page template to control the look and feel of your calendar.', 'tribe-events-calendar' ),
			'validation_type' => 'options',
			'size' => 'large',
			'default' => 'default',
			'options' => $template_options,
		),
		'tribeEventsBeforeHTML' => array(
			'type' => 'textarea',
		 	'label' => __( 'Add HTML before calendar', 'tribe-events-calendar' ),
			'tooltip' => __( 'If you are familiar with CSS you can use this box to add extra divs before the calendar list. Some Themes may require this to help with styling.', 'tribe-events-calendar' ),
			'validation_type' => 'html',
			'size' => 'large',
		),
		'tribeEventsAfterHTML' => array(
			'type' => 'textarea',
		 	'label' => __( 'Add HTML after calendar', 'tribe-events-calendar' ),
			'tooltip' => __( 'If you are familiar with CSS you can use this box to add extra divs after the calendar list. Some Themes may require this to help with styling.', 'tribe-events-calendar' ),
			'validation_type' => 'html',
			'size' => 'large',
		),
	),
);