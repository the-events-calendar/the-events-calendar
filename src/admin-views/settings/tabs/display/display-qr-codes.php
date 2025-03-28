<?php
/**
 * QR Codes settings tab.
 * Subtab of the Display Tab.
 *
 * @since TBD
 */

use TEC\Events\QR\Settings as QR_Settings;
use TEC\Common\Admin\Entities\H3;
use Tribe\Utils\Element_Classes;
use Tribe\Utils\Element_Attributes;

$slug = QR_Settings::get_option_slugs();

$tec_events_display_qr_codes = [
	$slug['title']       => [
		'type' => 'html',
		'html' => new H3(
			esc_html_x( 'QR Codes', 'QR Codes settings section header', 'the-events-calendar' ),
			new Element_Classes( [ 'tec-settings-form__section-header' ] ),
			new Element_Attributes( [ 'id' => 'tec-settings-events-settings-display-additional' ] )
		),
	],
	$slug['enabled']     => [
		'type'            => 'toggle',
		'label'           => esc_html__( 'Use QR Codes', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enable QR Codes for Events', 'the-events-calendar' ),
		'default'         => true,
		'validation_type' => 'boolean',
	],
	$slug['prefix']      => [
		'type'            => 'text',
		'label'           => esc_html__( 'QR Code Prefix', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'The prefix to be used for the permalinks.', 'the-events-calendar' ),
		'default'         => 'qr',
		'validation_type' => 'string',
	],
	$slug['size']        => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'QR Code Size', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Select the default dimensions of the generated QR image.', 'the-events-calendar' ),
		'default'         => '4',
		'options'         => [
			'4'  => esc_html__( '125x125', 'the-events-calendar' ),
			'8'  => esc_html__( '250x250', 'the-events-calendar' ),
			'21' => esc_html__( '650x650', 'the-events-calendar' ),
			'32' => esc_html__( '1000x1000', 'the-events-calendar' ),
		],
		'validation_type' => 'string',
	],
	$slug['redirection'] => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Default Redirection Behavior', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Set the default behavior for QR code redirection.', 'the-events-calendar' ),
		'default'         => 'current_event',
		'options'         => [
			'current'  => esc_html__( 'Redirect to the current event', 'the-events-calendar' ),
			'upcoming' => esc_html__( 'Redirect to the first upcoming event', 'the-events-calendar' ),
			'specific' => esc_html__( 'Redirect to a specific event ID', 'the-events-calendar' ),
			'next'     => esc_html__( 'Redirect to the next event in a series', 'the-events-calendar' ),
		],
		'validation_type' => 'string',
	],
	$slug['event_id']    => [
		'type'                => 'text',
		'label'               => esc_html__( 'Event ID', 'the-events-calendar' ),
		'tooltip'             => esc_html__( 'Event ID for when "Specific Event" is selected above.', 'the-events-calendar' ),
		'default'             => '',
		'validation_type'     => 'integer',
		'class'               => 'tribe-dependent',
		'fieldset_attributes' => [
			'data-depends'   => '#' . $slug['redirection'] . '-select',
			'data-condition' => 'specific',
		],
	],
	$slug['series_id']   => [
		'type'                => 'text',
		'label'               => esc_html__( 'Series ID', 'the-events-calendar' ),
		'tooltip'             => esc_html__( 'Series ID for when "Next Event in Series" is selected above.', 'the-events-calendar' ),
		'default'             => '',
		'validation_type'     => 'integer',
		'class'               => 'tribe-dependent',
		'fieldset_attributes' => [
			'data-depends'   => '#' . $slug['redirection'] . '-select',
			'data-condition' => 'next',
		],
	],
	$slug['fallback']    => [
		'type'            => 'text',
		'label'           => esc_html__( 'Error Handling Page', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Redirect to this URL if QR code is invalid or the event is not found.', 'the-events-calendar' ),
		'default'         => '',
		'validation_type' => 'url',
	],
];

/**
 * Filters the QR codes settings section fields.
 *
 * @since TBD
 *
 * @param array $tec_events_display_qr_codes Array of settings fields for the QR codes section.
 */
$fields = apply_filters( 'tec_events_settings_display_qr_codes_section', $tec_events_display_qr_codes );

$display_qr_codes = new Tribe__Settings_Tab(
	$slug['title'],
	esc_html__( 'QR Codes', 'the-events-calendar' ),
	[
		'priority' => 5.25,
		'fields'   => $fields,
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
