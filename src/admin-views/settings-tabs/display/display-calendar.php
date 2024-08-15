<?php
/**
 * Calendar settings tab.
 * Subtab of the Display Tab.
 *
 * @since TBD
 */

use Tribe\Events\Views\V2\Manager;

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

$posts_per_page_tooltip = apply_filters(
	'tec_events_display_calendar_settings_posts_per_page_tooltip',
	esc_html__( 'The number of events per page on the List View. Does not affect other views.', 'the-events-calendar' )
);

$tribe_enable_views_tooltip = apply_filters(
	'tec_events_display_calendar_settings_enable_views_tooltip',
	esc_html__( 'You must select at least one view.', 'the-events-calendar' )
);

$tec_events_display_calendar = [
	'tec-events-calendar-display-title'              => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-events-settings-display-calendar" class="tec_settings__section-header tec_settings__section-header--wide">' . _x( 'Calendar', 'Calendar display settings header', 'the-events-calendar' ) . '</h3>',
	],
	'tec-events-display-calendar-template-separator' => [
		'type' => 'html',
		'html' => '<hr class="tec_settings__section-separator">',
	],
	'tec-events-calendar-display-template-title'     => [
		'type' => 'html',
		'html' => '<h2 id="tec-settings-events-settings-display-template-calendar" class="tec_settings__section-header">' . _x( 'Template', 'Calendar template display settings header', 'the-events-calendar' ) . '</h2>',
	],
	'stylesheetOption'                               => [ 'type' => 'html' ],
	'stylesheet_mode'                                => [
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
				. '</p>',
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
	'tribeEventsTemplate'                            => [
		'type'            => 'dropdown',
		'label'           => __( 'Events template', 'the-events-calendar' ),
		'tooltip'         => __( 'Choose a page template to control the appearance of your calendar and event content.', 'the-events-calendar' ),
		'validation_type' => 'options',
		'size'            => 'small',
		'default'         => 'default',
		'options'         => $template_options,
		'conditional'     => $tec_events_should_display_events_templates,
	],
	'tec-events-display-calendar-views-separator'    => [
		'type' => 'html',
		'html' => '<hr class="tec_settings__section-separator">',
	],
	'tec-events-calendar-display-views-title'        => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-events-settings-display-views-calendar" class="tec_settings__section-header">' . _x( 'Views', 'Calendar views display settings header', 'the-events-calendar' ) . '</h3>',
	],
	'tribeEnableViews'                               => [
		'type'            => 'checkbox_list',
		'label'           => __( 'Enable event views', 'the-events-calendar' ),
		'tooltip'         => $tribe_enable_views_tooltip,
		'default'         => array_keys( tribe( Manager::class )->get_publicly_visible_views() ),
		'options'         => array_map(
			static function ( $view ) {
				return tribe( Manager::class )->get_view_label_by_class( $view );
			},
			tribe( Manager::class )->get_publicly_visible_views( false )
		),
		'validation_type' => 'options_multi',
	],
	'viewOption'                                     => [
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
	'monthEventAmount'                               => [
		'type'            => 'text',
		'label'           => __( 'Month view events per day', 'the-events-calendar' ),
		'tooltip'         => sprintf(
			/* Translators: %s: URL to knowledgebase. */
			__( 'Change the default 3 events per day in month view. To impose no limit, you may specify -1. Please note there may be performance issues if you allow too many events per day. <a href="%s" rel="noopener" target="_blank">Read more</a>.', 'the-events-calendar' ),
			'https://evnt.is/rh'
		),
		'validation_type' => 'int',
		'size'            => 'small',
		'default'         => '3',
	],
	'postsPerPage'                                   => [
		'type'            => 'text',
		'label'           => esc_html__( 'Number of events to show per page', 'the-events-calendar' ),
		'tooltip'         => $posts_per_page_tooltip,
		'size'            => 'small',
		'default'         => tribe_events_views_v2_is_enabled() ? 12 : get_option( 'posts_per_page' ),
		'validation_type' => 'positive_int',
	],
	'showComments'                                   => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Show comments', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enable comments on event pages.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
	'tribeDisableTribeBar'                           => [
		'type'            => 'checkbox_bool',
		'label'           => __( 'Disable the event search bar', 'the-events-calendar' ),
		'tooltip'         => __( 'Hide the search field on all views.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
];

$display_calendar = new Tribe__Settings_Tab(
	'display-calendar-tab',
	esc_html__( 'Calendar', 'the-events-calendar' ),
	[
		'priority' => 5.01,
		'fields'   => apply_filters(
			'tec_events_display_settings_calendar_section',
			$tec_events_display_calendar
		),
		'parent'   => 'display',
	]
);
do_action( 'tec_events_display_settings_calendar_tab', $display_calendar );
