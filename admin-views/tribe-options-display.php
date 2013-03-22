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
			'html' => __('<p>Use the options below to change the display of your calendar and event content on the frontend. The Events Calendar comes with its own Default Events Template. If your WordPress theme has its own page templates, they\'ll also be available for use in the dropdown below.</p><p>If you aren\'t satisfied with how the calendar looks upon installation, try switching to a different page template and refreshing the frontend to see if the new template integrates better. While we strive to ensure The Events Calendar is flexible enough to integrate smoothly out of the box with as many themes as possible, there are going to be situations where no template is 100% perfect. In these situations we encourage you to check out <a href="http://tri.be/support/documentation/events-calendar-themers-guide/">our themer\'s guide</a> to find what steps can be taken to get the layout where you want it to be.</p>', 'tribe-events-calendar'),
		),
		'info-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
		'tribe-form-content-start' => array(
			'type' => 'html',
			'html' => '<div class="tribe-settings-form-wrap">',
		),
		'tribeEventsBasicSettingsTitle' => array(
			'type' => 'html',
			'html' => '<h3>' . __( 'Basic Template Settings', 'tribe-events-calendar-pro' ) . '</h3>',
		),
		'stylesheetOption' => array(
			'type' => 'radio',
			'label' => __( 'Default stylesheet used for events templates', 'tribe-events-calendar' ),
			'default' => 'tribe',
			'options' => array( 
				'skeleton' => 'Skeleton Styles (These styles provide a bare minimum level of layout for the more complex templates, and is recommended if you\'re customizing the events template styles)', 
				'full' => 'Full Styles (These styles provide a more complex level of layout and style and should adapt to your theme)', 
				'tribe' => 'Tribe Events Styles (These styles provide a fully designed events theme)'
			),
			'validation_type' => 'options',
		),
        'tribeEventsTemplate' => array(
			'type' => 'dropdown_select2',
		 	'label' => __( 'Events template', 'tribe-events-calendar' ),
			'tooltip' => __( 'Choose a page template to control the appearance of your calendar and event content.', 'tribe-events-calendar' ),
			'validation_type' => 'options',
			'size' => 'large',
			'default' => 'default',
			'options' => $template_options
		),
		'tribeEnableViews' => array(
            'type' => 'checkbox_list',
            'label' => __( 'Enable event views', 'tribe-events-calendar' ),
            'tooltip' => __( 'You must select at least one view.', 'tribe-events-calendar' ),
            'default' => array_keys($views_options),
            'options' => $views_options,
            'validation_type' => 'options_multi'
        ),
        'viewOption' => array(
			'type' => 'dropdown_select2',
		 	'label' => __( 'Default view', 'tribe-events-calendar' ),
			'validation_type' => 'options',
			'size' => 'large',
			'default' => 'month',
			'options' => $views_options
		),
		'tribeEventsAdvancedSettingsTitle' => array(
			'type' => 'html',
			'html' => '<h3>' . __( 'Advanced Template Settings', 'tribe-events-calendar-pro' ) . '</h3>',
		),
		'tribeEventsBeforeHTML' => array(
			'type' => 'wysiwyg',
		 	'label' => __( 'Add HTML before event content', 'tribe-events-calendar' ),
			'tooltip' => __( 'If you are familiar with HTML, you can add additional code before the event template. Some themes may require this to help with styling or layout.', 'tribe-events-calendar' ),
			'validation_type' => 'html'
		),
		'tribeEventsAfterHTML' => array(
			'type' => 'wysiwyg',
		 	'label' => __( 'Add HTML after event content', 'tribe-events-calendar' ),
			'tooltip' => __( 'If you are familiar with HTML, you can add additional code after the event template. Some themes may require this to help with styling or layout.', 'tribe-events-calendar' ),
			'validation_type' => 'html',
		),
		'tribe-form-content-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
	)
);
