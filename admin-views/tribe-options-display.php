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

$views = apply_filters( 'tribe-events-bar-views', array(), FALSE );

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
			'html' => '<p>' . __('Use the options below to apply different page templates to The Events Calendar, which control the layout and appearance of event content on the frontend. The Events Calendar comes with its own Default Events Template, and if your WordPress theme has its own page templates, they\'ll also be available for use in the dropdown below.</p><p>If you aren\'t satisfied with how the calendar looks upon installation, try switching to a different page template and refreshing the frontend to see if the new template integrates better. While we strive to ensure The Events Calendar is flexible enough to integrate smoothly out of the box with as many themes as possible, there are going to be situations where no template is 100% perfect. In these situations we encourage you to check out <a href="http://tri.be/support/documentation/events-calendar-themers-guide/">our themer\'s guide</a> to find what steps can be taken to get the layout where you want it to be.', 'tribe-events-calendar') . '</p>',
		),
		'info-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
		'tribe-form-content-start' => array(
			'type' => 'html',
			'html' => '<div class="tribe-settings-form-wrap">',
		),
		'tribeEventsDisplayTemplateTitle' => array(
			'type' => 'html',
			'html' => '<h3>' . __( 'Template Settings', 'tribe-events-calendar-pro' ) . '</h3>',
		),
		'tribeEventsDisplayTemplateHelperText' => array(
			'type' => 'html',
			'html' => '<p class="description">' . __( 'These include settings that will control various template settings for your events templates.', 'tribe-events-calendar-pro' ) . '</p>',
		),
		'stylesheetOption' => array(
			'type' => 'radio',
			'label' => __( 'Default stylesheet used for events templates', 'tribe-events-calendar' ),
			'default' => 'full',
			'options' => array( 'full' => 'Full Stylesheet (These styles will most likely override some of your current theme styles on events templates)', 'skeleton' => 'Skeleton Stylesheet (These styles shouldn\'t override your current theme styles)' ),
			'validation_type' => 'options',
		),
        'tribeEventsTemplate' => array(
			'type' => 'dropdown_chosen',
		 	'label' => __( 'Events template', 'tribe-events-calendar' ),
			'tooltip' => __( 'Choose a page template to control the look and feel of your various events templates.', 'tribe-events-calendar' ),
			'validation_type' => 'options',
			'size' => 'large',
			'default' => 'default',
			'options' => $template_options
		),
		'tribeEnableViews' => array(
            'type' => 'checkbox_list',
            'label' => __( 'Event layouts', 'tribe-events-calendar' ),
            'default' => array_keys($views_options),
            'options' => $views_options,
            'validation_type' => 'options_multi',
            'can_be_empty' => true,
        ),
        'viewOption' => array(
			'type' => 'dropdown_chosen',
		 	'label' => __( 'Default layout', 'tribe-events-calendar' ),
			'validation_type' => 'options',
			'size' => 'large',
			'default' => 'month',
			'options' => $views_options
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
		'tribe-form-content-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
	)
);