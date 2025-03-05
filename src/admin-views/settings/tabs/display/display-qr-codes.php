<?php
/**
 * QR Codes settings tab.
 * Subtab of the Display Tab.
 *
 * @since TBD
 */

use TEC\Events\QR\Settings as QR_Settings;

$tec_events_display_qr_codes = [
	QR_Settings::get_enabled_option_slug() => [
		'type'            => 'toggle',
		'label'           => esc_html__( 'Use QR Codes', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enable QR Codes for Events', 'the-events-calendar' ),
		'default'         => true,
		'validation_type' => 'boolean',
		'classes'         => 'tec-events-qr-codes',
	],
	QR_Settings::get_enable_shortcode_option_slug() => [
		'type'            => 'toggle',
		'label'           => esc_html__( 'Enable QR shortcode', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Ability to use [tec_event_qr] shortcode', 'the-events-calendar' ),
		'default'         => true,
		'validation_type' => 'boolean',
	],
];

$display_qr_codes = new Tribe__Settings_Tab(
	QR_Settings::get_enabled_option_slug(),
	esc_html__( 'QR Codes', 'the-events-calendar' ),
	[
		'priority' => 5.25,
		'fields'   => apply_filters(
			'tec_events_settings_display_qr_codes_section',
			$tec_events_display_qr_codes
		),
	]
);

/**
 * Fires after the QR Codes settings tab has been created.
 *
 * @since TBD
 *
 * @param Tribe__Settings_Tab $display_qr_codes The QR Codes settings tab.
 */
do_action( 'tec_events_settings_tab_display_qr_codes', $display_qr_codes );

return $display_qr_codes;
