<?php
/**
 * Date & Time settings tab.
 * Subtab of the Display Tab.
 *
 * @since 6.7.0
 */

use TEC\Common\Admin\Entities\Div;
use TEC\Common\Admin\Entities\Field_Wrapper;
use TEC\Common\Admin\Entities\Heading;
use Tribe\Utils\Element_Classes as Classes;

$sample_date = strtotime( 'January 15 ' . gmdate( 'Y' ) );

$site_time_format = get_option( 'time_format' );

$end_time_options = [
	'single-event' => esc_html__( 'Single event page', 'the-events-calendar' ),
	'day'          => esc_html__( 'Day view', 'the-events-calendar' ),
	'list'         => esc_html__( 'List view', 'the-events-calendar' ),
	'month'        => esc_html__( 'Month view tooltip', 'the-events-calendar' ),
];
/**
 * Allow other plugins to add their views to the control.
 *
 * @since 6.4.1
 *
 * @param array $end_time_options The list of views where the end time can be removed.
 */
$end_time_options = apply_filters( 'tec_events_display_remove_event_end_time_options', $end_time_options );

$tec_events_display_date = [
	'tec-settings-date-header' => ( new Div( new Classes( [ 'tec-settings-form__header-block' ] ) ) )->add_children(
		[
			new Heading(
				_x( 'Date & Time', 'Date and Time settings section header', 'the-events-calendar' ),
				2,
				new Classes( [ 'tec-settings-form__section-header' ] )
			),
			// @todo: Need to create a <code> element.
			( new Field_Wrapper(
				new Tribe__Field(
					'tribeEventsDateFormatExplanation',
					[
						'type' => 'html',
						'html' => '<p class="tec-settings-form__section-description">'
							. sprintf(
								/* Translators: %1$s: PHP date function, %2$s: URL to WP knowledgebase. */
								__( 'The following three fields accept the date format options available to the PHP %1$s function. <a href="%2$s" target="_blank">Learn how to make your own date format here</a>.', 'the-events-calendar' ),
								'<code>date()</code>',
								'https://wordpress.org/support/article/formatting-date-and-time/'
							)
							. '</p>',
					]
				)
			) ),
		]
	),
	'dateWithYearFormat'       => [
		'type'            => 'text',
		'label'           => esc_html__( 'Date with year format', 'the-events-calendar' ),
		'default'         => get_option( 'date_format' ),
		'size'            => 'medium',
		'validation_type' => 'not_empty',
		'tooltip'         => sprintf(
			/* Translators: %1$s: Example date with year format. */
			esc_html__( 'Enter the format to use for displaying dates with the year. Used when showing an event from a future year. Example: %1$s', 'the-events-calendar' ),
			gmdate(
				tribe_get_option(
					'dateWithYearFormat',
					get_option( 'date_format', 'F j, Y' )
				),
				$sample_date
			)
		),
	],
	'dateWithoutYearFormat'    => [
		'type'            => 'text',
		'label'           => esc_html__( 'Date without year format', 'the-events-calendar' ),
		'default'         => 'F j',
		'size'            => 'medium',
		'validation_type' => 'not_empty',
		'tooltip'         => sprintf(
			/* Translators: %1$s: Example date without year format. */
			esc_html__( 'Enter the format to use for displaying dates without a year. Used when showing an event from the current year. Example: %1$s', 'the-events-calendar' ),
			gmdate( tribe_get_option( 'dateWithoutYearFormat', 'F j' ), $sample_date )
		),
	],
	'monthAndYearFormat'       => [
		'type'            => 'text',
		'label'           => esc_html__( 'Month and year format', 'the-events-calendar' ),
		'default'         => 'F Y',
		'size'            => 'medium',
		'validation_type' => 'not_empty',
		'tooltip'         => sprintf(
			/* Translators: %1$s: Example month and year format. */
			esc_html__( 'Enter the format to use for dates that show a month and year only. Used on month view. Example: %1$s', 'the-events-calendar' ),
			gmdate( tribe_get_option( 'monthAndYearFormat', 'F Y' ), $sample_date )
		),
	],
	'datepickerFormat'         => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Compact date format', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Select the date format used for elements with minimal space, such as in datepickers.', 'the-events-calendar' ),
		'default'         => 1,
		'validation_type' => 'options',
		'options'         => [
			'0'  => gmdate( 'Y-m-d', $sample_date ),
			'1'  => gmdate( 'n/j/Y', $sample_date ),
			'2'  => gmdate( 'm/d/Y', $sample_date ),
			'3'  => gmdate( 'j/n/Y', $sample_date ),
			'4'  => gmdate( 'd/m/Y', $sample_date ),
			'5'  => gmdate( 'n-j-Y', $sample_date ),
			'6'  => gmdate( 'm-d-Y', $sample_date ),
			'7'  => gmdate( 'j-n-Y', $sample_date ),
			'8'  => gmdate( 'd-m-Y', $sample_date ),
			'9'  => gmdate( 'Y.m.d', $sample_date ),
			'10' => gmdate( 'm.d.Y', $sample_date ),
			'11' => gmdate( 'd.m.Y', $sample_date ),
		],
	],
	'dateTimeSeparator'        => [
		'type'            => 'text',
		'label'           => esc_html__( 'Date time separator', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enter the separator that will be placed between the date and time, when both are shown.', 'the-events-calendar' ),
		'default'         => ' @ ',
		'size'            => 'small',
		'validation_type' => 'html',
	],
	'timeRangeSeparator'       => [
		'type'            => 'text',
		'label'           => esc_html__( 'Time range separator', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enter the separator that will be used between the start and end time of an event.', 'the-events-calendar' ),
		'default'         => ' - ',
		'size'            => 'small',
		'validation_type' => 'html',
	],
	'multiDayCutoff'           => [
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
	'remove_event_end_time'    => [
		'type'            => 'checkbox_list',
		'label'           => esc_html__( 'Remove event end time', 'the-events-calendar' ),
		'options'         => $end_time_options,
		'validation_type' => 'options_multi',
		'can_be_empty'    => true,
		'tooltip'         => sprintf(
			// Dev note: This string is multi-line to remove the need for a line break tag.
			/* Translators: %1$s - opening italics tag, %2$s - opening anchor tag, %3$s - closing anchor tag, %4$s - closing italics tag */
			__(
				'When one of these boxes is checked, the end time will no longer display for events that end on the same day when viewing the specified view.
				%1$s Source: %2$s Remove the Event End Time in Views %3$s%4$s',
				'the-events-calendar',
			),
			'<i>',
			'<a href="' . esc_url( 'https://theeventscalendar.com/knowledgebase/k/remove-the-event-end-time-in-views/' ) . '" target="_blank">',
			'</a>',
			'</i>'
		),
	],
];

$display_date_time = new Tribe__Settings_Tab(
	'display-date-time-tab',
	esc_html__( 'Date & Time', 'the-events-calendar' ),
	[
		'priority' => 5.10,
		'fields'   => apply_filters(
			'tec_events_settings_display_date_time_section',
			$tec_events_display_date
		),
	]
);

/**
 * Fires after the display settings date & time tab has been created.
 *
 * @since 6.7.0
 *
 * @param Tribe__Settings_Tab $display_date_time The display settings date & time tab.
 */
do_action( 'tec_events_settings_tab_display_date_time', $display_date_time );

return $display_date_time;
