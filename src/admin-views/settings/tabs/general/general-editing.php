<?php
/**
 * Editing settings tab.
 * Subtab of the General Tab.
 *
 * @since 6.7.0
 */

$is_missing_aggregator_license_key = empty( get_option( 'pue_install_key_event_aggregator', false ) );
$should_hide_upsell                = tec_should_hide_upsell();

// Add the "Editing" section.
$tec_events_general_editing = [
	'tec-events-settings-general-editing-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-general-editing" class="tec-settings-form__section-header">' . esc_html_x( 'Editing', 'Title for the editing section of the general settings.', 'the-events-calendar' ) . '</h3>',
	],
	'tec-aggregator-infobox-start'              => [
		'type'        => 'html',
		'html'        => '<div class="tec-settings-infobox">',
		'conditional' => $is_missing_aggregator_license_key && ! $should_hide_upsell,
	],
	'tec-aggregator-infobox-logo'               => [
		'type'        => 'html',
		'html'        => '<img class="tec-settings-infobox-logo" src="' . plugins_url( 'src/resources/images/settings-icons/icon-event-aggregator.svg', TRIBE_EVENTS_FILE ) . '" alt="Events Aggregator Logo">',
		'conditional' => $is_missing_aggregator_license_key && ! $should_hide_upsell,
	],
	'tec-aggregator-infobox-title'              => [
		'type'        => 'html',
		'html'        => '<h3 class="tec-settings-infobox-title">' . __( 'Import events with Event Aggregator', 'the-events-calendar' ) . '</h3>',
		'conditional' => $is_missing_aggregator_license_key && ! $should_hide_upsell,
	],
	'tec-aggregator-infobox-content'            => [
		'type'        => 'html',
		'html'        => '<p>' . __( 'Effortlessly fill your calendar with events from Meetup, Eventbrite, iCal, Google Calendar, and more.', 'the-events-calendar' ) . '</p>',
		'conditional' => $is_missing_aggregator_license_key && ! $should_hide_upsell,
	],
	'tec-aggregator-infobox-link'               => [
		'type'        => 'html',
		'html'        => '<a href="' . esc_url( 'https://evnt.is/1bby' ) . '" rel="noopener" target="_blank">' . __( 'Learn more.', 'the-events-calendar' ) . '</a>',
		'conditional' => $is_missing_aggregator_license_key && ! $should_hide_upsell,
	],
	'tec-aggregator-infobox-end'                => [
		'type'        => 'html',
		'html'        => '</div>',
		'conditional' => $is_missing_aggregator_license_key && ! $should_hide_upsell,
	],
	'disable_metabox_custom_fields'             => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Show Custom Fields metabox', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enable WordPress Custom Fields on events in the classic editor.', 'the-events-calendar' ),
		'default'         => true,
		'validation_type' => 'boolean',
	],
];


$general_editing = new Tribe__Settings_Tab(
	'general-editing-tab',
	esc_html__( 'Editing', 'the-events-calendar' ),
	[
		'priority' => 0.05,
		'fields'   => apply_filters(
			'tribe_general_settings_editing_section',
			$tec_events_general_editing
		),
	]
);

/**
 * Fires after the general editing settings tab has been created.
 *
 * @since 6.7.0
 *
 * @param Tribe__Settings_Tab $general_editing The general editing settings tab.
 */
do_action( 'tec_events_settings_tab_general_editing', $general_editing );

return $general_editing;
