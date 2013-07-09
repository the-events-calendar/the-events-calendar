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
	'fields' =>  apply_filters( 'tribe_display_settings_tab_fields', array(
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
			'html' => sprintf(
				__('<p>The settings below control the display of your calendar. If things don\'t look right, try switching between the three style sheet options or pick a page template from your theme.</p><p>There are going to be situations where no out-of-the-box template is 100&#37; perfect. Check out our <a href="%s">our themer\'s guide</a> for instructions on custom modifications. Want to create a new view? Grab a copy of the <a href="%s">Sample Agenda View plugin from Github</a></p>', 'tribe-events-calendar' ),
				TribeEvents::$tribeUrl . 'support/documentation/events-calendar-themers-guide/?utm_medium=plugin-tec&utm_source=generaltab&utm_campaign=in-app',
				'https://github.com/moderntribe/tribe-events-agenda-view'
			),
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
			'html' => '<h3>' . __( 'Basic Template Settings', 'tribe-events-calendar' ) . '</h3>',
		),
		'stylesheetOption' => array(
			'type' => 'radio',
			'label' => __( 'Default stylesheet used for events templates', 'tribe-events-calendar' ),
			'default' => 'tribe',
			'options' => array(
				'skeleton' => __( 'Skeleton Styles', 'tribe-events-calendar' ) .
					'<p class=\'description tribe-style-selection\'>' .
					__('Only includes enough css to achieve complex layouts like calendar and week view.', 'tribe-events-calendar' ) .
					'</p>',
				'full' => __( 'Full Styles', 'tribe-events-calendar' ) .
					'<p class=\'description tribe-style-selection\'>' .
					__( 'More detailed styling, tries to grab styles from your theme.', 'tribe-events-calendar' ) .
					'</p>',
				'tribe' => __( 'Tribe Events Styles', 'tribe-events-calendar' ) .
					'<p class=\'description tribe-style-selection\'>' .
					__( 'A fully designed and styled theme for your events pages.', 'tribe-events-calendar' ) .
					'</p>',
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
		'tribeDisableTribeBar' => array(
					'type' => 'checkbox_bool',
					'label' => __( 'Disable the Event Search Bar', 'tribe-events-calendar' ),
					'tooltip' => __( 'Check this to use the classic header.', 'tribe-events-calendar' ),
					'default' => false,
					'validation_type' => 'boolean',
		),
		'monthEventAmount' => array(
			'type' => 'text',
		 	'label' => __( 'Month view events per day', 'tribe-events-calendar' ),
		 	'tooltip' => __( 'Allow more than the default 3 events per day in month view.', 'tribe-events-calendar' ),
			'validation_type' => 'positive_int',
			'size' => 'small',
			'default' => '3'
		),
		'tribeEventsAdvancedSettingsTitle' => array(
			'type' => 'html',
			'html' => '<h3>' . __( 'Advanced Template Settings', 'tribe-events-calendar' ) . '</h3>',
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
	)
);
