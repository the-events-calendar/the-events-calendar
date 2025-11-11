<?php
/**
 * Handles the Integration (a.k.a. Add-ons) settings for The Events Calendar.
 *
 * @since 4.0.0
 * @since 6.15.6 Restructured to split settings into tabs.
 *
 * @version 6.15.6
 */
$internal = [];

$current_url = tribe( 'tec.main' )->settings()->get_url( [ 'tab' => 'addons' ] );

/**
 * Filters the integration settings fields for The Events Calendar.
 *
 * @since 4.0.0
 *
 * @param array $internal Array of integration settings fields.
 */
$internal = apply_filters( 'tribe_addons_tab_fields', $internal );

$info_box = [
	'tec-settings-addons-title' => [
		'type' => 'html',
		'html' => '<div class="tec-settings-form__header-block tec-settings-form__header-block--horizontal">'
				. '<h3 id="tec-settings-addons-title" class="tec-settings-form__section-header">'
				. _x( 'Integrations', 'Integrations section header', 'the-events-calendar' )
				. '</h3>'
				. '<p class="tec-settings-form__section-description">'
				. esc_html__( 'The Events Calendar, Event Tickets and their add-ons integrate with other online tools and services to bring you additional features. Use the settings below to connect to third-party APIs and manage your integrations.', 'the-events-calendar' )
				. '</p>'
				. '</div>',
	],
];

$fields = array_merge(
	$info_box,
	$internal,
);

/**
 * Allows filtering the Integrations tab contents.
 * Following the structure of the arguments for a Tribe__Settings_Tab instance.
 *
 * @param array
 */
$addons = apply_filters(
	'tribe_addons_tab',
	[
		'priority' => 50,
		'fields'   => [],
	]
);

// Only create the Integrations tab if there are any fields.
// Note, Google Maps API will always be there.
if ( empty( $internal ) ) {
	return;
}

// Create the Integrations tab.
$addons_tab = new Tribe__Settings_Tab(
	'addons',
	esc_html__( 'Integrations', 'the-events-calendar' ),
	$addons
);

// Create the Google Maps subtab, which is the starting tab.
$gmaps_tab = new Tribe__Settings_Tab(
	'google-maps',
	esc_html__( 'Google Maps', 'the-events-calendar' ),
	[
		'priority' => 10,
		'fields'   => $fields,
	],
);
$addons_tab->add_child( $gmaps_tab );

/**
 * Create the Imports subtab if any of the below are true:
 * - An Event Aggregator license key is present.
 * - The Eventbrite plugin is active.
 */
if ( get_option( 'pue_install_key_event_aggregator' ) || class_exists( 'Tribe__Events__Tickets__Eventbrite__Main' ) ) {
	$imports_tab = require_once __DIR__ . '/settings/tabs/integrations/integrations-import.php';
	$addons_tab->add_child( $imports_tab );
}


/**
 * Fires after the Integrations settings tab has been created.
 *
 * Similar to the 'tec_events_settings_tab_display' action, this hook allows you to modify
 * or extend the Integrations settings tab after it has been created.
 *
 * @since 6.7.0
 *
 * @param Tribe__Settings_Tab $addons_tab The Integrations settings tab object.
 */
do_action( 'tec_settings_tab_addons', $addons_tab );
