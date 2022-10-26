<?php
use \Tribe\Events\Views\V2\Manager;

$tec = Tribe__Events__Main::instance();

$template_options = [
	''        => esc_html__( 'Default Events Template', 'the-events-calendar' ),
	'default' => esc_html__( 'Default Page Template', 'the-events-calendar' ),
];

$templates        = get_page_templates();
ksort( $templates );
foreach ( array_keys( $templates ) as $template ) {
	$template_options[ $templates[ $template ] ] = $template;
}

$stylesheet_option = [ 'type' => 'html'];

$tribe_enable_views_tooltip = esc_html__( 'You must select at least one view.', 'the-events-calendar' );

if ( tribe_is_using_basic_gmaps_api() && class_exists( 'Tribe__Events__Pro__Main' ) ) {
	$tribe_enable_views_tooltip .= ' ' . sprintf(
		__( 'Please note that you are using The Events Calendar\'s default Google Maps API key, which will limit the Map View\'s functionality. Visit %sthe API Settings page%s to learn more and add your own Google Maps API key.', 'the-events-calendar' ),
		sprintf( '<a href="edit.php?page=tribe-common&tab=addons&post_type=%s">', Tribe__Events__Main::POSTTYPE ),
		'</a>'
	);
}

$views = tribe( Manager::class )->get_publicly_visible_views( false );
$enabled_views = tribe( Manager::class )->get_publicly_visible_views();

$tec_events_display_header = [
	'info-start'           => [
		'type' => 'html',
		'html' => '<div id="modern-tribe-info">',
	],
	'info-box-title'       => [
		'type' => 'html',
		'html' => '<h2>' . __( 'Display Settings', 'the-events-calendar' ) . '</h2>',
	],
	'info-box-description' => [
		'type' => 'html',
		'html' => '<p>'
				  . __( 'The settings below control the display of your calendar. If things don\'t look right, try switching between the three style sheet options or pick a page template from your theme.', 'the-events-calendar' )
				  . '</p> <p>'
				  . sprintf(
						  /* translators: %s: URL to knowledgebase. Please also use &#37; for % to avoid PHP warnings. */
					  __( 'There are going to be situations where no out-of-the-box template is 100&#37; perfect. Check out our <a href="%s">our themer\'s guide</a> for instructions on custom modifications.', 'the-events-calendar' ),
					  Tribe__Main::$tec_url . 'knowledgebase/themers-guide/?utm_medium=plugin-tec&utm_source=generaltab&utm_campaign=in-app'
				  )
				  . '</p>',
	],
	'info-end'             => [
		'type' => 'html',
		'html' => '</div>',
	],
];
$tec_events_display_template = [
	'tribe-events-basic-settings-title' => [
		'type' => 'html',
		'html' => '<h3>' . __( 'Basic Template Settings', 'the-events-calendar' ) . '</h3>',
	],
	'stylesheetOption'              => [ 'type' => 'html'],
	'stylesheet_mode'               => [
		'type'            => 'radio',
		'label'           => __( 'Default stylesheet used for events templates', 'the-events-calendar' ),
		'default'         => 'tribe',
		'options'         => [
			'skeleton' => __( 'Skeleton Styles', 'the-events-calendar' )
						. '<p class=\'description tribe-style-selection\'>'
						. __(
							'Only includes enough css to achieve complex layouts like calendar and week view.',
							'the-events-calendar'
						)
						.'</p>',
			'tribe'    => __( 'Tribe Events Styles', 'the-events-calendar' )
						. '<p class=\'description tribe-style-selection\'>'
						. __(
							'A fully designed and styled theme for your events pages.',
							'the-events-calendar'
						)
						. '</p>',
		],
		'validation_type' => 'options',
	],
	'tribeEventsTemplate'           => [
		'type'            => 'dropdown',
		'label'           => __( 'Events template', 'the-events-calendar' ),
		'tooltip'         => __( 'Choose a page template to control the appearance of your calendar and event content.', 'the-events-calendar' ),
		'validation_type' => 'options',
		'size'            => 'small',
		'default'         => 'default',
		'options'         => $template_options,
		'conditional' => ( ! tec_is_full_site_editor() ),
	],
	'tribeEnableViews'              => [
		'type'            => 'checkbox_list',
		'label'           => __( 'Enable event views', 'the-events-calendar' ),
		'tooltip'         => $tribe_enable_views_tooltip,
		'default'         => array_keys( $enabled_views ),
		'options'         => array_map(
			static function( $view ) {
				return tribe( Manager::class )->get_view_label_by_class( $view );
			},
			$views
		),
		'validation_type' => 'options_multi',
	],
	'viewOption'              => [
		'type'            => 'dropdown',
		'label'           => __( 'Default view', 'the-events-calendar' ),
		'validation_type' => 'not_empty',
		'size'            => 'small',
		'default'         => 'month',
		'options'         => array_map(
			static function( $view ) {
				return tribe( Manager::class )->get_view_label_by_class( $view );
			},
			$enabled_views
		),
	],
	'tribeDisableTribeBar'    => [
		'type'            => 'checkbox_bool',
		'label'           => __( 'Disable the Event Search Bar', 'the-events-calendar' ),
		'tooltip'         => __( 'Check this to use the classic header.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
	'monthEventAmount'        => [
		'type'            => 'text',
		'label'           => __( 'Month view events per day', 'the-events-calendar' ),
		'tooltip'         => sprintf( __( 'Change the default 3 events per day in month view. To impose no limit, you may specify -1. Please note there may be performance issues if you allow too many events per day. <a href="%s">Read more</a>.', 'the-events-calendar' ), 'https://evnt.is/rh' ),
		'validation_type' => 'int',
		'size'            => 'small',
		'default'         => '3',
	],
	'enable_month_view_cache' => [
		'type'            => 'checkbox_bool',
		'label'           => __( 'Enable the Month View Cache', 'the-events-calendar' ),
		'tooltip'         => sprintf( __( 'Check this to cache your month view HTML in transients, which can help improve calendar speed on sites with many events. <a href="%s">Read more</a>.', 'the-events-calendar' ), 'https://evnt.is/18di' ),
		'default'         => true,
		'validation_type' => 'boolean',
	],
	'multi-day-cutoff-helper'             => [
		'type'        => 'html',
		'html'        => '<p class="tribe-field-indent tribe-field-description description">' . sprintf( esc_html__( "Have an event that runs past midnight? Select a time after that event's end to avoid showing the event on the next day's calendar.", 'the-events-calendar' ) ) . '</p>',
		'conditional' => ( '' != get_option( 'permalink_structure' ) ),
	],
	'showComments'                     => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Show comments', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enable comments on event pages.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
];
$tec_events_display_date     = [
	'dateWithYearFormat' => [
		'type'            => 'text',
		'label'           => esc_html__( 'Date with year', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enter the format to use for displaying dates with the year. Used when displaying a date in a future year.', 'the-events-calendar' ),
		'default'         => get_option( 'date_format' ),
		'size'            => 'medium',
		'validation_type' => 'not_empty',
	],
	'dateWithoutYearFormat' => [
		'type'            => 'text',
		'label'           => esc_html__( 'Date without year', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enter the format to use for displaying dates without a year. Used when showing an event from the current year.', 'the-events-calendar' ),
		'default'         => 'F j',
		'size'            => 'medium',
		'validation_type' => 'not_empty',
	],
	'monthAndYearFormat'    => [
		'type'            => 'text',
		'label'           => esc_html__( 'Month and year format', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enter the format to use for dates that show a month and year only. Used on month view.', 'the-events-calendar' ),
		'default'         => 'F Y',
		'size'            => 'medium',
		'validation_type' => 'not_empty',
	],
	'dateTimeSeparator'  => [
		'type'            => 'text',
		'label'           => esc_html__( 'Date time separator', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enter the separator that will be placed between the date and time, when both are shown.', 'the-events-calendar' ),
		'default'         => ' @ ',
		'size'            => 'small',
		'validation_type' => 'html',
	],
	'timeRangeSeparator' => [
		'type'            => 'text',
		'label'           => esc_html__( 'Time range separator', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enter the separator that will be used between the start and end time of an event.', 'the-events-calendar' ),
		'default'         => ' - ',
		'size'            => 'small',
		'validation_type' => 'html',
	],
];
$tec_events_display_advanced = [
	'tribe-events-advanced-settings-title' => [
		'type' => 'html',
		'html' => '<h3>' . esc_html__( 'Advanced Template Settings', 'the-events-calendar' ) . '</h3>',
	],
	'tribeEventsBeforeHTML'            => [
		'type'            => 'wysiwyg',
		'label'           => esc_html__( 'Add HTML before event content', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'If you are familiar with HTML, you can add additional code before the event template. Some themes may require this to help with styling or layout.', 'the-events-calendar' ),
		'validation_type' => 'html',
	],
	'tribeEventsAfterHTML'             => [
		'type'            => 'wysiwyg',
		'label'           => esc_html__( 'Add HTML after event content', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'If you are familiar with HTML, you can add additional code after the event template. Some themes may require this to help with styling or layout.', 'the-events-calendar' ),
		'validation_type' => 'html',
	],
];
$tec_events_display_maps = [
	'tribe-google-maps-settings-title'     => [
		'type' => 'html',
		'html' => '<h3>' . esc_html__( 'Map Settings', 'the-events-calendar' ) . '</h3>',
	],
	'embedGoogleMaps'                  => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Enable Maps', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Check to enable maps for events and venues.', 'the-events-calendar' ),
		'default'         => true,
		'class'           => 'google-embed-size',
		'validation_type' => 'boolean',
	],
	'embedGoogleMapsZoom'              => [
		'type'            => 'text',
		'label'           => esc_html__( 'Google Maps default zoom level', 'the-events-calendar' ),
		'tooltip'         => esc_html__( '0 = zoomed out; 21 = zoomed in.', 'the-events-calendar' ),
		'size'            => 'small',
		'default'         => 10,
		'class'           => 'google-embed-field',
		'validation_type' => 'number_or_percent',
	],
];
$tec_events_display_currency = [
	'tribe-events-currency-title' => [
		'type' => 'html',
		'html' => '<h3>' . esc_html__( 'Currency Settings', 'the-events-calendar' ) . '</h3>',
	],
	'defaultCurrencySymbol'   => [
		'type'            => 'text',
		'label'           => esc_html__( 'Default currency symbol', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Set the default currency symbol for event costs. Note that this only impacts future events, and changes made will not apply retroactively.', 'the-events-calendar' ),
		'validation_type' => 'textarea',
		'size'            => 'small',
		'default'         => '$',
	],
	'defaultCurrencyCode'     => [
		'type'            => 'text',
		'label'           => esc_html__( 'Default currency code', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Set the default currency ISO-4217 code for event costs. This is a three-letter code and is mainly used for data/SEO purposes.', 'the-events-calendar' ),
		'validation_type' => 'textarea',
		'size'            => 'small',
		'default'         => 'USD',
		'attributes'      => [
			'minlength'   => 3,
			'maxlength'   => 3,
			'placeholder' => __( 'USD', 'the-events-calendar' ),
		],
	],
	'reverseCurrencyPosition' => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Currency symbol follows value', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'The currency symbol normally precedes the value. Enabling this option positions the symbol after the value.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
];


// Form start.
$display_tab_fields = Tribe__Main::array_insert_before_key(
	'tribe-form-content-start',
	$display_tab_fields,
	$tec_events_display_header
);

// datepickerFormat
$display_tab_fields = Tribe__Main::array_insert_before_key(
	'datepickerFormat',
	$display_tab_fields,
	$tec_events_display_date
);

// datepickerFormat
$display_tab_fields = Tribe__Main::array_insert_after_key(
	'timeRangeSeparator',
	$display_tab_fields,
	$tec_events_display_currency
);

// datepickerFormat
$display_tab_fields = Tribe__Main::array_insert_after_key(
	'reverseCurrencyPosition',
	$display_tab_fields,
	$tec_events_display_maps
);

$display_tab_fields = Tribe__Main::array_insert_before_key(
	'tribeEventsDateFormatSettingsTitle',
	$display_tab_fields,
	$tec_events_display_template
);

$display_tab_fields = Tribe__Main::array_insert_after_key(
	'datepickerFormat',
	$display_tab_fields,
	$tec_events_display_advanced
);
