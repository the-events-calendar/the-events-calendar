<?php
/**
 * Maintgenance settings tab.
 * Subtab of the General Tab.
 *
 * @since 6.7.0
 */

/**
 * @var Tribe__Events__Event_Cleaner $event_cleaner
 */
$event_cleaner = tribe( 'tec.event-cleaner' );

// Our default tooltip.
$trash_tooltip = esc_html__( 'This option allows you to automatically move past events to trash.', 'the-events-calendar' );
// Some adjusted functionality with CT1 activated.
if ( tribe()->getVar( 'ct1_fully_activated' ) ) {
	$trash_tooltip = sprintf(
		/* Translators: %1$d - number of days, %2$s - `EMPTY_TRASH_DAYS` constant (code), %3$s - link to the documentation */
		__( 'Trashed events will permanently be deleted in %1$d days, you can change that value using <code>%2$s</code>. <a href="%3$s" rel="noopener noreferrer" target="_blank">Read more.</a>', 'the-events-calendar' ),
		(int) EMPTY_TRASH_DAYS,
		'EMPTY_TRASH_DAYS',
		'https://evnt.is/1bcs'
	);
}

// Add the "Maintenance" section.
$tec_events_general_maintenance = [
	'tec-events-settings-general-maintenance-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-general-maintenance" class="tec-settings-form__section-header">' . esc_html_x( 'Maintenance', 'Title for the maintenance section of the general settings.', 'the-events-calendar' ) . '</h3>',
	],
	$event_cleaner->key_trash_events                => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Move to trash events older than', 'the-events-calendar' ),
		'tooltip'         => $trash_tooltip,
		'validation_type' => 'options',
		'size'            => 'small',
		'default'         => null,
		'options'         => [
			null => esc_html__( 'Disabled', 'the-events-calendar' ),
			1    => esc_html__( '1 month', 'the-events-calendar' ),
			3    => esc_html__( '3 months', 'the-events-calendar' ),
			6    => esc_html__( '6 months', 'the-events-calendar' ),
			9    => esc_html__( '9 months', 'the-events-calendar' ),
			12   => esc_html__( '1 year', 'the-events-calendar' ),
			24   => esc_html__( '2 years', 'the-events-calendar' ),
			36   => esc_html__( '3 years', 'the-events-calendar' ),
		],
	],
	$event_cleaner->key_delete_events               => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Permanently delete events older than', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'This option allows you to bulk delete past events. Be careful and backup your database before removing your events as there is no way to reverse the changes.', 'the-events-calendar' ),
		'validation_type' => 'options',
		'size'            => 'small',
		'default'         => null,
		'options'         => [
			null => esc_html__( 'Disabled', 'the-events-calendar' ),
			1    => esc_html__( '1 month', 'the-events-calendar' ),
			3    => esc_html__( '3 months', 'the-events-calendar' ),
			6    => esc_html__( '6 months', 'the-events-calendar' ),
			9    => esc_html__( '9 months', 'the-events-calendar' ),
			12   => esc_html__( '1 year', 'the-events-calendar' ),
			24   => esc_html__( '2 years', 'the-events-calendar' ),
			36   => esc_html__( '3 years', 'the-events-calendar' ),
		],
	],
	'amalgamate-duplicates'                         => [
		'type' => 'html',
		'html' => '<fieldset class="tribe-field tribe-field-html"><legend class="tribe-field-label">' . esc_html__( 'Merge duplicate Venues &amp; Organizers', 'the-events-calendar' ) . '</legend><div class="tribe-field-wrap">' . Tribe__Events__Amalgamator::migration_button( esc_html__( 'Merge Duplicates', 'the-events-calendar' ) ) . '<p class="description">' . esc_html__( 'Click this button to automatically merge identical venues and organizers.', 'the-events-calendar' ) . '</p></div></fieldset>',
	],
];

$general_maintenance = new Tribe__Settings_Tab(
	'general-maintenance-tab',
	esc_html__( 'Maintenance', 'the-events-calendar' ),
	[
		'priority' => 0.10,
		'fields'   => apply_filters( 'tribe_general_settings_maintenance_section', $tec_events_general_maintenance ),
	]
);

/**
 * Fires after the general maintenance settings tab has been created.
 *
 * @since 6.7.0
 *
 * @param Tribe__Settings_Tab $general_maintenance The general maintenance settings tab.
 */
do_action( 'tec_events_settings_tab_general_maintenance', $general_maintenance );

return $general_maintenance;
