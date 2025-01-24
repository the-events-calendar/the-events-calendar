<?php
/**
 * Calendar settings tab.
 * Subtab of the Display Tab.
 *
 * @since 6.7.0
 * @since 6.9.1 Added logic to include page templates from theme to template options.
 *
 * @version 6.9.1
 */

use TEC\Common\Admin\Entities\Div;
use TEC\Common\Admin\Entities\Heading;
use TEC\Common\Admin\Entities\Paragraph;
use TEC\Common\Admin\Entities\Plain_Text;
use Tribe\Events\Views\V2\Manager;
use Tribe\Utils\Element_Classes as Classes;

/**
 * Filter to determine if the Events Templates should be displayed in the settings.
 *
 * @since 6.4.0
 *
 * @param bool $should_display Whether the Events Templates should be displayed.
 */
$tec_events_should_display_events_templates = apply_filters(
	'tec_events_should_display_events_template_setting',
	! tec_is_full_site_editor()
);

$template_options = [
	''        => esc_html__( 'Default Events Template', 'the-events-calendar' ),
	'default' => esc_html__( 'Default Page Template', 'the-events-calendar' ),
];

$templates = get_page_templates();
ksort( $templates );

$template_options += array_flip( $templates );

$posts_per_page_tooltip = apply_filters(
	'tec_events_display_calendar_settings_posts_per_page_tooltip',
	esc_html__( 'The number of events per page on the List View. Does not affect other views.', 'the-events-calendar' )
);

$tribe_enable_views_tooltip = apply_filters(
	'tec_events_settings_display_calendar_enable_views_tooltip',
	esc_html__( 'You must select at least one view.', 'the-events-calendar' )
);

$section_header_classes = new Classes( [ 'tec-settings-form__section-header', 'tec-settings-form__section-header--sub' ] );

$tec_events_display_calendar = [
	'tec-settings-form__header-block' => ( new Div( new Classes( [ 'tec-settings-form__header-block', 'tec-settings-form__header-block--horizontal' ] ) ) )->add_children(
		[
			new Heading(
				_x( 'Calendar', 'Calendar display settings header', 'the-events-calendar' ),
				2,
				new Classes( [ 'tec-settings-form__section-header' ] )
			),
			( new Paragraph( new Classes( [ 'tec-settings-form__section-description' ] ) ) )->add_child(
				new Plain_Text(
					__(
						"The settings below control the display of your calendar. If things don't look right, try switching between the two style sheet options or pick a page template from your theme (not available on block themes). Check out our customization guide for instructions on template modifications.",
						'the-events-calendar'
					)
				)
			),
		]
	),
];

$calendar_template_section = [
	'calendar_template_header' => [
		'type' => 'html',
		'html' => '<h3 class="tec-settings-form__section-header tec-settings-form__section-header--sub">'
			. esc_html_x( 'Calendar Template', 'Calendar template display settings header', 'the-events-calendar' )
			. '</h3>',
	],
	'stylesheet_mode'          => [
		'type'            => 'radio',
		'label'           => __( 'Default stylesheet used for events templates', 'the-events-calendar' ),
		'default'         => 'tribe',
		'validation_type' => 'options',
		'options'         => [
			'skeleton' => __( 'Skeleton Styles', 'the-events-calendar' )
				. '<p class="description tribe-style-selection">'
				. __(
					'Only includes enough css to achieve complex layouts like calendar and week view.',
					'the-events-calendar'
				)
				. '</p>',
			'tribe'    => __( 'Default Styles', 'the-events-calendar' )
				. '<p class="description tribe-style-selection">'
				. __(
					'A fully designed and styled theme for your events pages.',
					'the-events-calendar'
				)
				. '</p>',
		],
	],
	'tribeEventsTemplate'      => [
		'type'            => 'dropdown',
		'label'           => __( 'Events template', 'the-events-calendar' ),
		'tooltip'         => __( 'Page template to control the appearance of your calendar and event content.', 'the-events-calendar' ),
		'tooltip_first'   => true,
		'validation_type' => 'options',
		'size'            => 'small',
		'default'         => 'default',
		'options'         => $template_options,
		'conditional'     => $tec_events_should_display_events_templates,
	],
];

$calendar_template_section = tribe( 'settings' )->wrap_section_content( 'tec-events-settings-calendar-template', $calendar_template_section );

$tec_events_display_calendar += $calendar_template_section;

$calendar_display_section = [
	'calendar_display_header' => [
		'type' => 'html',
		'html' => '<h3 class="tec-settings-form__section-header tec-settings-form__section-header--sub">'
			. esc_html_x( 'Calendar Display', 'Calendar display display settings header', 'the-events-calendar' )
			. '</h3>',
	],
	'tribeEnableViews'        => [
		'type'            => 'checkbox_list',
		'label'           => __( 'Enable event views', 'the-events-calendar' ),
		'tooltip'         => $tribe_enable_views_tooltip,
		'default'         => array_keys( tribe( Manager::class )->get_publicly_visible_views() ),
		'validation_type' => 'options_multi',
		'options'         => array_map(
			static function ( $view ) {
				return tribe( Manager::class )->get_view_label_by_class( $view );
			},
			tribe( Manager::class )->get_publicly_visible_views( false )
		),
	],
	'viewOption'              => [
		'type'            => 'dropdown',
		'label'           => __( 'Default view', 'the-events-calendar' ),
		'validation_type' => 'not_empty',
		'size'            => 'small',
		'default'         => 'month',
		'options'         => array_map(
			static function ( $view ) {
				return tribe( Manager::class )->get_view_label_by_class( $view );
			},
			tribe( Manager::class )->get_publicly_visible_views()
		),
	],
	'monthEventAmount'        => [
		'type'            => 'text',
		'label'           => __( 'Month view events per day', 'the-events-calendar' ),
		'validation_type' => 'int',
		'size'            => 'small',
		'default'         => '3',
		'tooltip'         => sprintf(
			/* Translators: %s: URL to knowledgebase. */
			__( 'Default is 3. To impose no limit, specify -1. Note that there may be performance issues if you allow too many events per day. <a href="%s" rel="noopener" target="_blank">Read more</a>.', 'the-events-calendar' ),
			'https://evnt.is/rh'
		),
	],
	'postsPerPage'            => [
		'type'            => 'text',
		'label'           => esc_html__( 'Number of events to show per page', 'the-events-calendar' ),
		'tooltip'         => $posts_per_page_tooltip,
		'tooltip_first'   => true,
		'size'            => 'small',
		'default'         => tribe_events_views_v2_is_enabled() ? 12 : get_option( 'posts_per_page' ),
		'validation_type' => 'positive_int',
	],
	'showComments'            => [
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

$calendar_display_section = apply_filters( 'tec_events_settings_display_calendar_display_section', $calendar_display_section );

$calendar_display_section = tribe( 'settings' )->wrap_section_content( 'tec-events-settings-calendar-display', $calendar_display_section );

$tec_events_display_calendar += $calendar_display_section;

$display_calendar = new Tribe__Settings_Tab(
	'display-calendar-tab',
	esc_html__( 'Calendar', 'the-events-calendar' ),
	[
		'priority' => 5.01,
		'fields'   => apply_filters(
			'tec_events_settings_display_calendar_section',
			$tec_events_display_calendar
		),
	]
);

/**
 * Fires after the display settings calendar tab has been created.
 *
 * @since 6.7.0
 *
 * @param Tribe__Settings_Tab $display_calendar The display settings calendar tab.
 */
do_action( 'tec_events_settings_tab_display_calendar', $display_calendar );

return $display_calendar;
