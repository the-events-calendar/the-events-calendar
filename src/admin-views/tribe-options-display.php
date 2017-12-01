<?php

$tec = Tribe__Events__Main::instance();

$template_options = array(
	''        => esc_html__( 'Default Events Template', 'the-events-calendar' ),
	'default' => esc_html__( 'Default Page Template', 'the-events-calendar' ),
);
$templates        = get_page_templates();
ksort( $templates );
foreach ( array_keys( $templates ) as $template ) {
	$template_options[ $templates[ $template ] ] = $template;
}

/**
 * Filter the array of views that are registered for the tribe bar
 * @param array array() {
 *     Array of views, where each view is itself represented by an associative array consisting of these keys:
 *
 *     @type string $displaying         slug for the view
 *     @type string $anchor             display text (i.e. "List" or "Month")
 *     @type string $event_bar_hook     not used
 *     @type string $url                url to the view
 * }
 * @param boolean
 */
$views = apply_filters( 'tribe-events-bar-views', array(), false );

$views_options = array();
foreach ( $views as $view ) {
	$views_options[ $view['displaying'] ] = $view['anchor'];
}

$display_tab_fields = Tribe__Main::array_insert_before_key(
	'tribe-form-content-start',
	$display_tab_fields,
	array(
		'info-start'                         => array(
			'type' => 'html',
			'html' => '<div id="modern-tribe-info">',
		),
		'info-box-title'                     => array(
			'type' => 'html',
			'html' => '<h2>' . __( 'Display Settings', 'the-events-calendar' ) . '</h2>',
		),
		'info-box-description'               => array(
			'type' => 'html',
			'html' => '<p>'
				. __( 'The settings below control the display of your calendar. If things don\'t look right, try switching between the three style sheet options or pick a page template from your theme.', 'the-events-calendar' )
				. '</p> <p>'
				. sprintf(
					__( 'There are going to be situations where no out-of-the-box template is 100&#37; perfect. Check out our <a href="%s">our themer\'s guide</a> for instructions on custom modifications.', 'the-events-calendar' ),
					Tribe__Main::$tec_url . 'knowledgebase/themers-guide/?utm_medium=plugin-tec&utm_source=generaltab&utm_campaign=in-app'
				)
				. '</p>',
		),
		'info-end'                           => array(
			'type' => 'html',
			'html' => '</div>',
		),
	)
);


$display_tab_fields = Tribe__Main::array_insert_before_key(
	'datepickerFormat',
	$display_tab_fields,
	array(
		'dateWithYearFormat'                 => array(
			'type'            => 'text',
			'label'           => esc_html__( 'Date with year', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Enter the format to use for displaying dates with the year. Used when displaying a date in a future year.', 'the-events-calendar' ),
			'default'         => get_option( 'date_format' ),
			'size'            => 'medium',
			'validation_type' => 'html',
		),
		'dateTimeSeparator'                  => array(
			'type'            => 'text',
			'label'           => esc_html__( 'Date time separator', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Enter the separator that will be placed between the date and time, when both are shown.', 'the-events-calendar' ),
			'default'         => ' @ ',
			'size'            => 'small',
			'validation_type' => 'html',
		),
	)
);

$display_tab_fields = Tribe__Main::array_insert_after_key(
	'dateWithYearFormat',
	$display_tab_fields,
	array(
		'dateWithoutYearFormat'              => array(
			'type'            => 'text',
			'label'           => esc_html__( 'Date without year', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Enter the format to use for displaying dates without a year. Used when showing an event from the current year.', 'the-events-calendar' ),
			'default'         => 'F j',
			'size'            => 'medium',
			'validation_type' => 'html',
		),
		'monthAndYearFormat'                 => array(
			'type'            => 'text',
			'label'           => esc_html__( 'Month and year format', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Enter the format to use for dates that show a month and year only. Used on month view.', 'the-events-calendar' ),
			'default'         => 'F Y',
			'size'            => 'medium',
			'validation_type' => 'html',
		),
	)
);

$display_tab_fields = Tribe__Main::array_insert_after_key(
	'dateTimeSeparator',
	$display_tab_fields,
	array(
		'timeRangeSeparator'                 => array(
			'type'            => 'text',
			'label'           => esc_html__( 'Time range separator', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Enter the separator that will be used between the start and end time of an event.', 'the-events-calendar' ),
			'default'         => ' - ',
			'size'            => 'small',
			'validation_type' => 'html',
		),
	)
);

$display_tab_fields = Tribe__Main::array_insert_before_key(
	'tribeEventsDateFormatSettingsTitle',
	$display_tab_fields,
	array(
		'tribeEventsBasicSettingsTitle'      => array(
			'type' => 'html',
			'html' => '<h3>' . __( 'Basic Template Settings', 'the-events-calendar' ) . '</h3>',
		),
		'stylesheetOption'                   => array(
			'type'            => 'radio',
			'label'           => __( 'Default stylesheet used for events templates', 'the-events-calendar' ),
			'default'         => 'tribe',
			'options'         => array(
				'skeleton' => __( 'Skeleton Styles', 'the-events-calendar' ) .
								'<p class=\'description tribe-style-selection\'>' .
								__( 'Only includes enough css to achieve complex layouts like calendar and week view.', 'the-events-calendar' ) .
								'</p>',
				'full'     => __( 'Full Styles', 'the-events-calendar' ) .
								'<p class=\'description tribe-style-selection\'>' .
								__( 'More detailed styling, tries to grab styles from your theme.', 'the-events-calendar' ) .
								'</p>',
				'tribe'    => __( 'Tribe Events Styles', 'the-events-calendar' ) .
								'<p class=\'description tribe-style-selection\'>' .
								__( 'A fully designed and styled theme for your events pages.', 'the-events-calendar' ) .
								'</p>',
			),
			'validation_type' => 'options',
		),
		'tribeEventsTemplate'                => array(
			'type'            => 'dropdown',
			'label'           => __( 'Events template', 'the-events-calendar' ),
			'tooltip'         => __( 'Choose a page template to control the appearance of your calendar and event content.', 'the-events-calendar' ),
			'validation_type' => 'options',
			'size'            => 'large',
			'default'         => 'default',
			'options'         => $template_options,
		),
		'tribeEnableViews'                   => array(
			'type'            => 'checkbox_list',
			'label'           => __( 'Enable event views', 'the-events-calendar' ),
			'tooltip'         => __( 'You must select at least one view.', 'the-events-calendar' ),
			'default'         => array_keys( $views_options ),
			'options'         => $views_options,
			'validation_type' => 'options_multi',
		),
		'viewOption'                         => array(
			'type'            => 'dropdown',
			'label'           => __( 'Default view', 'the-events-calendar' ),
			'validation_type' => 'options',
			'size'            => 'large',
			'default'         => 'month',
			'options'         => $views_options,
		),
		'tribeDisableTribeBar'               => array(
			'type'            => 'checkbox_bool',
			'label'           => __( 'Disable the Event Search Bar', 'the-events-calendar' ),
			'tooltip'         => __( 'Check this to use the classic header.', 'the-events-calendar' ),
			'default'         => false,
			'validation_type' => 'boolean',
		),
		'monthEventAmount'                   => array(
			'type'            => 'text',
			'label'           => __( 'Month view events per day', 'the-events-calendar' ),
			'tooltip'         => sprintf( __( 'Change the default 3 events per day in month view. To impose no limit, you may specify -1. Please note there may be performance issues if you allow too many events per day. <a href="%s">Read more</a>.', 'the-events-calendar' ), 'https://m.tri.be/rh' ),
			'validation_type' => 'int',
			'size'            => 'small',
			'default'         => '3',
		),
		'enable_month_view_cache' => array(
			'type'            => 'checkbox_bool',
			'label'           => __( 'Enable the Month View Cache', 'the-events-calendar' ),
			'tooltip'         => sprintf( __( 'Check this to cache your month view HTML in transients, which can help improve calendar speed on sites with many events. <a href="%s">Read more</a>.', 'the-events-calendar' ), 'https://m.tri.be/18di' ),
			'default'         => true,
			'validation_type' => 'boolean',
		),
	)
);

$display_tab_fields = Tribe__Main::array_insert_after_key(
	'datepickerFormat',
	$display_tab_fields,
	array(
		'tribeEventsAdvancedSettingsTitle'   => array(
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'Advanced Template Settings', 'the-events-calendar' ) . '</h3>',
		),
		'tribeEventsBeforeHTML'              => array(
			'type'            => 'wysiwyg',
			'label'           => esc_html__( 'Add HTML before event content', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'If you are familiar with HTML, you can add additional code before the event template. Some themes may require this to help with styling or layout.', 'the-events-calendar' ),
			'validation_type' => 'html',
		),
		'tribeEventsAfterHTML'               => array(
			'type'            => 'wysiwyg',
			'label'           => esc_html__( 'Add HTML after event content', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'If you are familiar with HTML, you can add additional code after the event template. Some themes may require this to help with styling or layout.', 'the-events-calendar' ),
			'validation_type' => 'html',
		),
	)
);
