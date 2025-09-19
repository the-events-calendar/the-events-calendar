<?php
/**
 * Create an easy way to hook to the Add-ons Tab Fields
 * @var array
 */
$internal = [];

$current_url = tribe( 'tec.main' )->settings()->get_url( [ 'tab' => 'addons' ] );

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
 * Allow developer to fully filter the Addons Tab contents
 * Following the structure of the arguments for a Tribe__Settings_Tab instance
 *
 * @var array
 */
$addons = apply_filters(
	'tribe_addons_tab',
	[
		'priority' => 50,
		'fields'   => [],
	]
);

// Only create the Add-ons Tab if there is any.
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
	'gmaps',
	esc_html__( 'Google Maps', 'the-events-calendar' ),
	[
		'priority' => 10,
		'fields'   => $fields,
	],
);
$addons_tab->add_child( $gmaps_tab );

// Create the Imports subtab.
$imports_tab = require_once __DIR__ . '/settings/tabs/integrations/integrations-import.php';
$addons_tab->add_child( $imports_tab );

do_action( 'tec_settings_tab_addons', $addons_tab );
