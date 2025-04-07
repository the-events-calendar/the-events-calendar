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
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use Tribe__Events__Main as TEC;

$slug = QR_Settings::get_option_slugs();

$options = [
	'current'  => esc_html__( 'Redirect to Current Event', 'the-events-calendar' ),
	'upcoming' => esc_html__( 'Redirect to First Upcoming Event', 'the-events-calendar' ),
	'specific' => esc_html__( 'Redirect to Specific Event', 'the-events-calendar' ),
];


if ( has_action( 'tribe_common_loaded', 'tribe_register_pro' ) ) {
	$options['next'] = esc_html__( 'Redirect to Next Event in Series', 'the-events-calendar' );

	$args = [
		'posts_per_page' => -1,
		'post_type'      => Series::POSTTYPE,
		'post_status'    => 'publish',
		'orderby'        => 'ID',
		'order'          => 'DESC',
	];

	$series = get_posts( $args );
	if ( ! empty( $series ) ) {
		$series_options = [];
		foreach ( $series as $series ) {
			$series_options[ $series->ID ] = $series->ID . ' - ' . $series->post_title;
		}
	} else {
		$series_options[0] = _x( 'There are no Series created yet.', 'No series created yet', 'the-events-calendar' );
	}
}

$args = [
	'posts_per_page' => -1,
	'post_type'      => TEC::POSTTYPE,
	'post_status'    => 'publish',
	'orderby'        => 'ID',
	'order'          => 'DESC',
];

$events = get_posts( $args );
if ( ! empty( $events ) ) {
	$event_options = [];
	foreach ( $events as $event ) {
		$event_options[ $event->ID ] = $event->ID . ' - ' . $event->post_title;
	}
} else {
	$event_options[0] = _x( 'There are no Events created yet.', 'No events created yet', 'the-events-calendar' );
}


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
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Use QR Codes', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enable QR Codes for Events', 'the-events-calendar' ),
		'default'         => true,
		'validation_type' => 'boolean',
	],
	$slug['size']        => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Fallback Image Dimensions', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Set the default dimensions for the generated QR Code when they are not provided.', 'the-events-calendar' ),
		'default'         => '4',
		'options'         => [
			'4'  => esc_html__( '140 x 140 px', 'the-events-calendar' ),
			'8'  => esc_html__( '280 x 280 px', 'the-events-calendar' ),
			'12' => esc_html__( '420 x 420 px', 'the-events-calendar' ),
			'16' => esc_html__( '560 x 560 px', 'the-events-calendar' ),
			'20' => esc_html__( '700 x 700 px', 'the-events-calendar' ),
			'24' => esc_html__( '840 x 840 px', 'the-events-calendar' ),
			'28' => esc_html__( '980 x 980 px', 'the-events-calendar' ),
		],
		'validation_type' => 'options',
	],
	$slug['redirection'] => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Fallback Redirection Behavior', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Set the default redirection behavior for the generated QR Code when it is not provided.', 'the-events-calendar' ),
		'default'         => 'current',
		'options'         => $options,
		'validation_type' => 'options',
	],
	$slug['event_id']    => [
		'type'                => 'dropdown',
		'label'               => esc_html__( 'Event ID', 'the-events-calendar' ),
		'tooltip'             => esc_html__( 'Event ID for when "Specific Event" is selected above.', 'the-events-calendar' ),
		'default'             => '',
		'options'             => $event_options,
		'validation_type'     => 'int',
		'can_be_empty'        => true,
		'class'               => 'tribe-dependent',
		'fieldset_attributes' => [
			'data-depends'   => '#' . $slug['redirection'] . '-select',
			'data-condition' => 'specific',
		],
	],
	$slug['series_id']   => [
		'type'                => 'dropdown',
		'label'               => esc_html__( 'Series ID', 'the-events-calendar' ),
		'tooltip'             => esc_html__( 'Series ID for when "Next Event in Series" is selected above.', 'the-events-calendar' ),
		'default'             => '',
		'options'             => $series_options,
		'validation_type'     => 'int',
		'can_be_empty'        => true,
		'class'               => 'tribe-dependent',
		'fieldset_attributes' => [
			'data-depends'   => '#' . $slug['redirection'] . '-select',
			'data-condition' => 'next',
		],
	],
	$slug['fallback']    => [
		'type'            => 'text',
		'label'           => esc_html__( 'Fallback URL', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Redirect to this URL if QR code is invalid or the event is not found.', 'the-events-calendar' ),
		'default'         => '',
		'validation_type' => 'url',
		'can_be_empty'    => true,
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
