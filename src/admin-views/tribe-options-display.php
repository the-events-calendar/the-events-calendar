<?php
use \Tribe\Events\Views\V2\Manager;

$tec = Tribe__Events__Main::instance();
$tcmn = Tribe__Main::instance();
$ecp = defined( 'EVENTS_CALENDAR_PRO_FILE' );

$template_options = [
	''        => esc_html__( 'Default Events Template', 'the-events-calendar' ),
	'default' => esc_html__( 'Default Page Template', 'the-events-calendar' ),
];

$templates = get_page_templates();
ksort( $templates );
foreach ( array_keys( $templates ) as $template ) {
	$template_options[ $templates[ $template ] ] = $template;
}

$tribe_enable_views_tooltip = esc_html__( 'You must select at least one view.', 'the-events-calendar' );

if ( $ecp && tribe_is_using_basic_gmaps_api() ) {
	$tribe_enable_views_tooltip .= ' ' . sprintf(
		__( 'Please note that you are using The Events Calendar\'s default Google Maps API key, which will limit the Map View\'s functionality. Visit <a href="edit.php?page=tribe-common&tab=addons&post_type=%s">the Integrations Settings page</a> to learn more and add your own Google Maps API key.', 'the-events-calendar' ),
		Tribe__Events__Main::POSTTYPE
	);
}

$posts_per_page_tooltip = ! class_exists( 'Tribe__Events__Pro__Main', false )
	? esc_html__( 'The number of events per page on the List View. Does not affect other views.', 'the-events-calendar' )
	: esc_html__( 'The number of events per page on the List, Photo, and Map Views. Does not affect other views.', 'the-events-calendar' );

// Begin Settings content - header section.
$tec_events_display_fields = [
	'info-start'           => [
		'type' => 'html',
		'html' => '<div class="tec-settings-header">',
	],
	'info-box-title'       => [
		'type' => 'html',
		'html' => '<h2>' . __( 'Display Settings', 'Display settings tab header', 'the-events-calendar' ) . '</h2>',
	],
	'info-box-description' => [
		'type' => 'html',
		'html' => '<p>'
				  . __( 'The settings below control the display of your calendar. If things don\'t look right, try switching between the two style sheet options or pick a page template from your theme (not available on block themes). ', 'the-events-calendar' )
				  . sprintf(
						  /* Translators: %s: URL to knowledgebase. Please continue to use &#37; for % to avoid PHP warnings. */
					  __( ' Check out our <a href="%s" rel="noopener" target="_blank">customization guide</a> for instructions on template modifications.', 'the-events-calendar' ),
					  esc_url( 'https://evnt.is/1bbs' )
				  )
				  . '</p>',
	],
	'info-end'             => [
		'type' => 'html',
		'html' => '</div>',
	],
];


// Add the TOC
$tec_events_general_toc = [
	'tec-events-settings-display-toc-nav-start'             => [
		'type' => 'html',
		'html' => '<div class="tec-settings-header">',
	],
	'tec-events-settings-display-toc-start'           => [
		'type' => 'html',
		'html' => '<ul id="tec-events-events-settings-display-toc" class="tec-events-settings__toc">',
	],
	'tec-events-settings-display-toc-label'           => [
		'type' => 'html',
		'html' => '<li>' . _x( 'Jump to:', 'Text introducing the table of contents links.', 'the-events-calendar' ) . '</li>',
	],
	'tec-events-settings-display-toc-calendar'         => [
		'type' => 'html',
		'html' => '<li><a href="#tec-settings-events-settings-display-calendar">' . _x( 'Calendar Display', 'Calendar Display table of contents link.', 'the-events-calendar' ) . '</a>',
	],
	'tec-events-settings-display-toc-date'         => [
		'type' => 'html',
		'html' => '<li><a href="#tec-settings-events-settings-display-date">' . _x( 'Date & Time', 'Date & Time table of contents link.', 'the-events-calendar' ) . '</a>',
	],
	'tec-events-settings-display-toc-currency' => [
		'type' => 'html',
		'html' => '<li><a href="#tec-settings-events-settings-display-currency">' . _x( 'Currency', 'Currency table of contents link.', 'the-events-calendar' ) . '</a>',
	],
	'tec-events-settings-display-toc-maps' => [
		'type' => 'html',
		'html' => '<li><a href="#tec-settings-events-settings-display-maps">' . _x( 'Maps', 'Maps table of contents link.', 'the-events-calendar' ) . '</a>',
	],
	'tec-events-settings-display-toc-additional' => [
		'type' => 'html',
		'html' => '<li><a href="#tec-settings-events-settings-display-additional">' . _x( 'Additional Content', 'Additional Content table of contents link.', 'the-events-calendar' ) . '</a>',
	],
	'tec-events-settings-display-toc-end'             => [
		'type' => 'html',
		'html' => '</ul>',
	],
	'tec-events-settings-display-toc-nav-end'                 => [
		'type' => 'html',
		'html' => '</div>',
	],
];

$tec_events_display_fields += $tec_events_general_toc;

// Start the form content wrapper.
$tec_events_general_form_end = [

	'tribe-form-content-start' => [
		'type' => 'html',
		'html' => '<div class="tribe-settings-form-wrap tec-settings-display">',
	],
];

$tec_events_display_fields += $tec_events_general_form_end;

// Insert Basic Template settings.
$tec_events_display_template = [
	'tribe-events-calendar-display-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-events-settings-display-calendar">' . __( 'Calendar Display', 'Calendar display settings section header', 'the-events-calendar' ) . '</h3>',
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
			'tribe'    => __( 'Default Styles', 'the-events-calendar' )
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
		'default'         => array_keys(  tribe( Manager::class )->get_publicly_visible_views() ),
		'options'         => array_map(
			static function( $view ) {
				return tribe( Manager::class )->get_view_label_by_class( $view );
			},
			tribe( Manager::class )->get_publicly_visible_views( false )
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
			tribe( Manager::class )->get_publicly_visible_views()
		),
	],
	'monthEventAmount'        => [
		'type'            => 'text',
		'label'           => __( 'Month view events per day', 'the-events-calendar' ),
		'tooltip'         => sprintf(
			__( 'Change the default 3 events per day in month view. To impose no limit, you may specify -1. Please note there may be performance issues if you allow too many events per day. <a href="%s" rel="noopener" target="_blank">Read more</a>.', 'the-events-calendar' ),
			'https://evnt.is/rh'
		),
		'validation_type' => 'int',
		'size'            => 'small',
		'default'         => '3',
	],
	'postsPerPage'                     => [
		'type'            => 'text',
		'label'           => esc_html__( 'Number of events to show per page', 'the-events-calendar' ),
		'tooltip'         => $posts_per_page_tooltip,
		'size'            => 'small',
		'default'         => tribe_events_views_v2_is_enabled() ? 12 : get_option( 'posts_per_page' ),
		'validation_type' => 'positive_int',
	],
	'showComments'                     => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Show comments', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enable comments on event pages.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
	'tribeDisableTribeBar'    => [
		'type'            => 'checkbox_bool',
		'label'           => __( 'Disable the event search bar', 'the-events-calendar' ),
		'tooltip'         => __( 'Hide the search field on all views.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
];

$tec_events_display_fields += $tec_events_display_template;

$sample_date = strtotime( 'January 15 ' . date( 'Y' ) );

// Date Format Settings.
$tec_events_date_fields     = [
	'tribeEventsDateFormatSettingsTitle' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-events-settings-display-date">' . esc_html__( 'Date & Time', 'Date and Time settings section header', 'tribe-common' ) . '</h3>',
	],
	'tribeEventsDateFormatExplanation'   => [
		'type' => 'html',
		'html' => '<p>'
					. sprintf(
						__( 'The following three fields accept the date format options available to the PHP %1$s function. <a href="%2$s" target="_blank">Learn how to make your own date format here</a>.', 'tribe-common' ),
						'<code>date()</code>',
						'https://wordpress.org/support/article/formatting-date-and-time/'
					)
					. '</p>',
	],
	'dateWithYearFormat'                 => [
		'type'            => 'text',
		'label'           => esc_html__( 'Date with year format', 'the-events-calendar' ),
		'tooltip'         => sprintf(
			esc_html__( 'Enter the format to use for displaying dates with the year. Used when showing an event from a future year. Example: %1$s', 'the-events-calendar' ),
			date(
				tribe_get_option(
					'dateWithYearFormat',
					get_option( 'date_format', 'F j, Y' )
				),
				$sample_date
			)
		),
		'default'         => get_option( 'date_format' ),
		'size'            => 'medium',
		'validation_type' => 'not_empty',
	],
	'dateWithoutYearFormat'              => [
		'type'            => 'text',
		'label'           => esc_html__( 'Date without year format', 'the-events-calendar' ),
		'tooltip'         => sprintf(
			esc_html__( 'Enter the format to use for displaying dates without a year. Used when showing an event from the current year. Example: %1$s', 'the-events-calendar' ),
			date( tribe_get_option( 'dateWithoutYearFormat', 'F j' ), $sample_date )
		),
		'default'         => 'F j',
		'size'            => 'medium',
		'validation_type' => 'not_empty',
	],
	'monthAndYearFormat'                 => [
		'type'            => 'text',
		'label'           => esc_html__( 'Month and year format', 'the-events-calendar' ),
		'tooltip'         => sprintf(
			esc_html__( 'Enter the format to use for dates that show a month and year only. Used on month view. Example: %1$s', 'the-events-calendar' ),
			date( tribe_get_option( 'monthAndYearFormat', 'F Y' ), $sample_date )
		),
		'default'         => 'F Y',
		'size'            => 'medium',
		'validation_type' => 'not_empty',
	],
	'datepickerFormat'                   => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Compact date format', 'tribe-common' ),
		'tooltip'         => esc_html__( 'Select the date format used for elements with minimal space, such as in datepickers.', 'tribe-common' ),
		'default'         => 1,
		'options'         => [
			'0'  => date( 'Y-m-d', $sample_date ),
			'1'  => date( 'n/j/Y', $sample_date ),
			'2'  => date( 'm/d/Y', $sample_date ),
			'3'  => date( 'j/n/Y', $sample_date ),
			'4'  => date( 'd/m/Y', $sample_date ),
			'5'  => date( 'n-j-Y', $sample_date ),
			'6'  => date( 'm-d-Y', $sample_date ),
			'7'  => date( 'j-n-Y', $sample_date ),
			'8'  => date( 'd-m-Y', $sample_date ),
			'9'  => date( 'Y.m.d', $sample_date ),
			'10' => date( 'm.d.Y', $sample_date ),
			'11' => date( 'd.m.Y', $sample_date ),
		],
		'validation_type' => 'options',
	],
	'dateTimeSeparator'                  => [
		'type'            => 'text',
		'label'           => esc_html__( 'Date time separator', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enter the separator that will be placed between the date and time, when both are shown.', 'the-events-calendar' ),
		'default'         => ' @ ',
		'size'            => 'small',
		'validation_type' => 'html',
	],
	'timeRangeSeparator'                 => [
		'type'            => 'text',
		'label'           => esc_html__( 'Time range separator', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enter the separator that will be used between the start and end time of an event.', 'the-events-calendar' ),
		'default'         => ' - ',
		'size'            => 'small',
		'validation_type' => 'html',
	],
	'multiDayCutoff'                   => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'End of day cutoff', 'the-events-calendar' ),
		'tooltip'         => __( "Have an event that runs past midnight? Select a time after that event's end to avoid showing the event on the next day's calendar.", 'the-events-calendar' ),
		'validation_type' => 'options',
		'size'            => 'small',
		'default'         => date_i18n( $site_time_format, strtotime( '12:00 am' ) ),
		'options'         => [
			'00:00' => date_i18n( $site_time_format, strtotime( '12:00 am' ) ),
			'01:00' => date_i18n( $site_time_format, strtotime( '01:00 am' ) ),
			'02:00' => date_i18n( $site_time_format, strtotime( '02:00 am' ) ),
			'03:00' => date_i18n( $site_time_format, strtotime( '03:00 am' ) ),
			'04:00' => date_i18n( $site_time_format, strtotime( '04:00 am' ) ),
			'05:00' => date_i18n( $site_time_format, strtotime( '05:00 am' ) ),
			'06:00' => date_i18n( $site_time_format, strtotime( '06:00 am' ) ),
			'07:00' => date_i18n( $site_time_format, strtotime( '07:00 am' ) ),
			'08:00' => date_i18n( $site_time_format, strtotime( '08:00 am' ) ),
			'09:00' => date_i18n( $site_time_format, strtotime( '09:00 am' ) ),
			'10:00' => date_i18n( $site_time_format, strtotime( '10:00 am' ) ),
			'11:00' => date_i18n( $site_time_format, strtotime( '11:00 am' ) ),
		],
	],
];

$tec_events_display_fields += $tec_events_date_fields;

$is_missing_event_tickets_plus = ! defined( 'EVENT_TICKETS_PLUS_FILE' );
$should_hide_upsell            = tec_should_hide_upsell();

// Insert Currency settings.
$tec_events_display_currency = [
	'tribe-events-currency-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-events-settings-display-currency">' . esc_html__( 'Currency', 'Currency settings section header', 'the-events-calendar' ) . '</h3>',
	],
	'tec-tickets-infobox-start' => [
		'type' => 'html',
		'html' => '<div class="tec-settings-infobox">',
		'conditional' => $is_missing_event_tickets_plus && ! $should_hide_upsell,
	],
	'tec-tickets-infobox-logo' => [
		'type' => 'html',
		'html' => '<img class="tec-settings-infobox-logo" src="' . plugins_url( 'resources/images/settings-icons/icon-et.svg', dirname( __FILE__ ) ) . '" alt="Events Tickets Logo">',
		'conditional' => $is_missing_event_tickets_plus && ! $should_hide_upsell,
	],
	'tec-tickets-infobox-title' => [
		'type' => 'html',
		'html' => '<h3 class="tec-settings-infobox-title">' .  __( 'Start selling tickets to your events', 'the-events-calendar' ) . '</h3>',
		'conditional' => $is_missing_event_tickets_plus && ! $should_hide_upsell,
	],
	/* @TODO: This is placeholder text! */
	'tec-tickets-infobox-content' => [
		'type' => 'html',
		'html' => '<p>' . __( 'Get Event Tickets to manage attendee registration and ticket sales to your events, for free.', 'the-events-calendar' ) . '</p>',
		'conditional' => $is_missing_event_tickets_plus && ! $should_hide_upsell,
	],
	'tec-tickets-infobox-link' => [
		'type' => 'html',
		'html' => '<a href="' . esc_url( 'https://evnt.is/1bbx' ) . '" rel="noopener" target="_blank">' . __( 'Learn more.', 'the-events-calendar' ) . '</a>',
		'conditional' => $is_missing_event_tickets_plus && ! $should_hide_upsell,
	],
	'tec-tickets-infobox-end' => [
		'type' => 'html',
		'html' => '</div>',
		'conditional' => $is_missing_event_tickets_plus && ! $should_hide_upsell,
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

$tec_events_display_fields += $tec_events_display_currency;

// Insert Map settings.
$tec_events_display_maps = [
	'tribe-google-maps-settings-title'     => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-events-settings-display-maps">' . esc_html__( 'Maps', 'Map settings section header', 'the-events-calendar' ) . '</h3>',
	],
	'tec-maps-infobox-start' => [
		'type' => 'html',
		'html' => '<div class="tec-settings-infobox">'
	],
	'tec-maps-infobox-title' => [
		'type' => 'html',
		'html' => '<h3 class="tec-settings-infobox-title">' .  __( 'Advanced Google Maps functionality', 'the-events-calendar' ) . '</h3>',
	],
	/* @TODO: The link i this and the next section should probably be different. */
	'tec-maps-infobox-content' => [
		'type' => 'html',
		'html' => sprintf(
			/* Translators: %1$s - opening paragraph tag, %2$s - opening anchor tag, %3$s - closing anchor tag, %4$s - closing paragraph tag */
			__( '%1$sThe Events Calendar comes with a default API key for basic maps functionality. If you’d like to use more advanced features like custom map pins or dynamic map loads, you’ll need to get your own %2$sGoogle Maps API key%3$s.%4$s', 'the-events-calendar' ),
			'<p>',
			'<a href="' . esc_url( 'https://evnt.is/1bbu' ) . '" rel="noopener" target="_blank">',
			'</a>',
			'</p>'
		),
	],
	'tec-maps-infobox-end' => [
		'type' => 'html',
		'html' => '</div>'
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

$tec_events_display_fields += $tec_events_display_maps;

// Insert Advanced Template settings.
$tec_events_display_advanced = [
	'tribe-events-advanced-settings-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-events-settings-display-additional">' . esc_html__( 'Additional Content', 'Additional content settings section header', 'the-events-calendar' ) . '</h3>',
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

$tec_events_display_fields += $tec_events_display_advanced;

// Close the form content wrapper.
$tec_events_display_fields += [
	'tribe-form-content-end' => [
		'type' => 'html',
		'html' => '</div>',
	]
];

/**
 * Filter the fields available on the display settings tab
 *
 * @param array $fields a nested associative array of fields & field info passed to Tribe__Field
 *
 * @see Tribe__Field
 */
$tec_events_display_fields = apply_filters( 'tec_events_display_settings_tab_fields', $tec_events_display_fields );

$tec_events_display_tab = [
		'priority' => 10,
		'fields'   => $tec_events_display_fields,
];
