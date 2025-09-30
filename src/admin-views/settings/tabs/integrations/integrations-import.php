<?php
/**
 *  Import settings tab.
 *  Subtab of the Integrations Tab.
 *
 * @since 6.15.6
 *
 * @version 6.15.6
 */

use TEC\Common\Admin\Entities\Div;
use TEC\Common\Admin\Entities\Heading;
use TEC\Common\Admin\Entities\Paragraph;
use TEC\Common\Admin\Entities\Plain_Text;
use Tribe\Utils\Element_Classes as Classes;

$internal    = [];
$current_url = tribe( 'tec.main' )->settings()->get_url( [ 'tab' => 'integrations-import-tab' ] );

$tec_events_integrations_import = [
	'tec-settings-form__header-block' => ( new Div( new Classes( [ 'tec-settings-form__header-block', 'tec-settings-form__header-block--horizontal' ] ) ) )->add_children(
		[
			new Heading(
				_x( 'Import', 'Calendar display settings header', 'the-events-calendar' ),
				2,
				new Classes( [ 'tec-settings-form__section-header' ] )
			),
			( new Paragraph( new Classes( [ 'tec-settings-form__section-description' ] ) ) )->add_child(
				new Plain_Text(
					__(
						'Below you can set up the authentication for different calendar services, in order to be able to import events from them.',
						'the-events-calendar'
					)
				)
			),
		]
	),
];

$internal = array_merge( $internal, $tec_events_integrations_import );

/**
 * If there's an Event Aggregator license key, add the Meetup.com API fields
 */
if ( get_option( 'pue_install_key_event_aggregator' ) ) {

	$missing_meetup_credentials = ! tribe( 'events-aggregator.settings' )->is_ea_authorized_for_meetup();

	$meetup  = '<fieldset id="tribe-field-meetup_token" class="tribe-field tribe-field-text tribe-size-medium">';
	$meetup .= '<legend class="tribe-field-label">' . esc_html__( 'Meetup Authentication', 'the-events-calendar' ) . '</legend>';
	$meetup .= '<div class="tribe-field-wrap">';

	if ( $missing_meetup_credentials ) {
		$meetup             .= '<p>' . esc_html__( 'You need to connect to Meetup for Event Aggregator to work properly', 'the-events-calendar' ) . '</p>';
		$meetup_button_label = __( 'Connect to Meetup', 'the-events-calendar' );
	} else {
		$meetup_button_label     = __( 'Refresh your connection to Meetup', 'the-events-calendar' );
		$meetup_disconnect_label = __( 'Disconnect', 'the-events-calendar' );
		$meetup_disconnect_url   = tribe( 'events-aggregator.settings' )->build_disconnect_meetup_url( $current_url );
	}

	$meetup .= '<a target="_blank" class="tribe-ea-meetup-button" href="' . esc_url( Tribe__Events__Aggregator__Record__Meetup::get_auth_url( [ 'back' => 'settings' ] ) ) . '">' .
		esc_html( $meetup_button_label ) . '</a>';

	if ( ! $missing_meetup_credentials ) {
		$meetup .= '<a href="' . esc_url( $meetup_disconnect_url ) . '" class="tribe-ea-meetup-disconnect">' . esc_html( $meetup_disconnect_label ) . '</a>';
	}

	$meetup .= '</div>';
	$meetup .= '</fieldset>';

	$internal_meetup = [
		'meetup-start'        => [
			'type' => 'html',
			'html' => '<h3 class="tec-settings-form__section-header tec-settings-form__section-header--sub">' . esc_html__( 'Meetup', 'the-events-calendar' ) . '</h3>',
		],
		'meetup_token_button' => [
			'type' => 'html',
			'html' => $meetup,
		],
	];

	$internal_meetup = tribe( 'settings' )->wrap_section_content( 'tec-events-settings-meetup', $internal_meetup );

	$internal = array_merge( $internal, $internal_meetup );
}

/**
 * Show Eventbrite API Connection only if Eventbrite Plugin is active or an Event Aggregator license key is present.
 */
if ( class_exists( 'Tribe__Events__Tickets__Eventbrite__Main', false ) || get_option( 'pue_install_key_event_aggregator' ) ) {

	$missing_eb_credentials = ! tribe( 'events-aggregator.settings' )->is_ea_authorized_for_eb();

	$eventbrite  = '<fieldset id="tribe-field-eventbrite_token" class="tribe-field tribe-field-text tribe-size-medium">';
	$eventbrite .= '<legend class="tribe-field-label">' . esc_html__( 'Eventbrite Authentication', 'the-events-calendar' ) . '</legend>';
	$eventbrite .= '<div class="tribe-field-wrap">';

	if ( $missing_eb_credentials ) {
		$eventbrite             .= '<p>' . esc_html__( 'You need to connect to Eventbrite for Event Aggregator to work properly', 'the-events-calendar' ) . '</p>';
		$eventbrite_button_label = __( 'Connect to Eventbrite', 'the-events-calendar' );
	} else {
		$eventbrite_button_label     = __( 'Refresh your connection to Eventbrite', 'the-events-calendar' );
		$eventbrite_disconnect_label = __( 'Disconnect', 'the-events-calendar' );
		$eventbrite_disconnect_url   = tribe( 'events-aggregator.settings' )->build_disconnect_eventbrite_url( $current_url );
	}

	$eventbrite .= '<a target="_blank" class="tribe-ea-eventbrite-button" href="' . esc_url( Tribe__Events__Aggregator__Record__Eventbrite::get_auth_url( [ 'back' => 'settings' ] ) ) . '">' . esc_html( $eventbrite_button_label ) . '</a>';

	if ( ! $missing_eb_credentials ) {
		$eventbrite .= '<a href="' . esc_url( $eventbrite_disconnect_url ) . '" class="tribe-ea-eventbrite-disconnect">' . esc_html( $eventbrite_disconnect_label ) . '</a>';
	}

	$eventbrite .= '</div>';
	$eventbrite .= '</fieldset>';

	$internal2 = [
		'eb-start'        => [
			'type' => 'html',
			'html' => '<h3 class="tec-settings-form__section-header tec-settings-form__section-header--sub">' . esc_html__( 'Eventbrite', 'the-events-calendar' ) . '</h3>',
		],
		'eb_token_button' => [
			'type' => 'html',
			'html' => $eventbrite,
		],
	];

	$internal2 = tribe( 'settings' )->wrap_section_content( 'tec-events-settings-eventbrite', $internal2 );

	$internal = array_merge( $internal, $internal2 );
}

return new Tribe__Settings_Tab(
	'integrations-import-tab',
	esc_html__( 'Import', 'the-events-calendar' ),
	[
		'priority' => 10,
		'fields'   => apply_filters(
			'tec_events_settings_integrations_import_section',
			$internal
		),
	]
);
