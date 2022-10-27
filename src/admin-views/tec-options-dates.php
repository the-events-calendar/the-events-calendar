<?php
$sample_date = strtotime( 'January 15 ' . date( 'Y' ) );

// Date Format Settings array.
$tec_events_date_fields     = [
	'tribe-form-content-start'           => [
		'type' => 'html',
		'html' => '<div class="tribe-settings-form-wrap">',
	],
	'tribeEventsDateFormatSettingsTitle' => [
		'type' => 'html',
		'html' => '<h3>' . esc_html__( 'Date Format Settings', 'tribe-common' ) . '</h3>',
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
	'datepickerFormat'                   => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Compact Date Format', 'tribe-common' ),
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
	'dateWithYearFormat'                 => [
		'type'            => 'text',
		'label'           => esc_html__( 'Date with year', 'the-events-calendar' ),
		'tooltip'         => sprintf(
			esc_html__( 'Enter the format to use for displaying dates with the year. Used when showing an event from a future year. Example: %1$s', 'the-events-calendar' ),
			date( get_option( 'dateWithYearFormat', get_option( 'date_format' ) ), $sample_date )
		),
		'default'         => get_option( 'date_format' ),
		'size'            => 'medium',
		'validation_type' => 'not_empty',
	],
	'dateWithoutYearFormat'              => [
		'type'            => 'text',
		'label'           => esc_html__( 'Date without year', 'the-events-calendar' ),
		'tooltip'         => sprintf(
			esc_html__( 'Enter the format to use for displaying dates without a year. Used when showing an event from the current year. Example: %1$s', 'the-events-calendar' ),
			date( get_option( 'dateWithoutYearFormat', 'F j' ), $sample_date )
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
			date( get_option( 'dateWithoutYearFormat', 'F Y' ), $sample_date )
		),
		'default'         => 'F Y',
		'size'            => 'medium',
		'validation_type' => 'not_empty',
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
	'multi-day-cutoff-helper'            => [
		'type'        => 'html',
		'html'        => '<p class="tribe-field-indent tribe-field-description description">' . sprintf( esc_html__( "Have an event that runs past midnight? Select a time after that event's end to avoid showing the event on the next day's calendar.", 'the-events-calendar' ) ) . '</p>',
		'conditional' => ( '' != get_option( 'permalink_structure' ) ),
	],
	'tribe-form-content-end'             => [
		'type' => 'html',
		'html' => '</div>',
	],
];

$tec_events_date_fields  = apply_filters( 'tribe_dates_settings_tab_fields', $tec_events_date_fields );

$tec_events_display_date = [
	'priority' => 20,
	'fields'   => $tec_events_date_fields,
];
