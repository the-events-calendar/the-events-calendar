<?php
/**
 * Currency settings tab.
 * Subtab of the Display Tab.
 *
 * @since TBD
 */

$tec_events_display_currency = [];

$is_missing_event_tickets_plus = ! defined( 'EVENT_TICKETS_PLUS_FILE' );
$should_hide_upsell            = tec_should_hide_upsell();

// Insert Currency settings.
$tec_events_display_currency = [
	'tribe-events-currency-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-events-settings-display-currency" class="tec_settings__section-header">' . esc_html_x( 'Currency', 'Currency settings section header', 'the-events-calendar' ) . '</h3>',
	],
];

if ( ! $should_hide_upsell && $is_missing_event_tickets_plus ) {
	$tec_events_display_currency = $tec_events_display_currency + [
		'tec-tickets-infobox-start'   => [
			'type'        => 'html',
			'html'        => '<div class="tec-settings-infobox">',
		],
		'tec-tickets-infobox-logo'    => [
			'type'        => 'html',
			'html'        => '<img class="tec-settings-infobox-logo" src="' . plugins_url( 'resources/images/settings-icons/icon-et.svg', __DIR__ ) . '" alt="Events Tickets Logo">',
		],
		'tec-tickets-infobox-title'   => [
			'type'        => 'html',
			'html'        => '<h3 class="tec-settings-infobox-title">' . __( 'Start selling tickets to your events', 'the-events-calendar' ) . '</h3>',
		],
		/* @TODO: This is placeholder text! */
		'tec-tickets-infobox-content' => [
			'type'        => 'html',
			'html'        => '<p>' . __( 'Get Event Tickets to manage attendee registration and ticket sales to your events, for free.', 'the-events-calendar' ) . '</p>',
		],
		'tec-tickets-infobox-link'    => [
			'type'        => 'html',
			'html'        => '<a href="' . esc_url( 'https://evnt.is/1bbx' ) . '" rel="noopener" target="_blank">' . __( 'Learn more.', 'the-events-calendar' ) . '</a>',
		],
		'tec-tickets-infobox-end'     => [
			'type'        => 'html',
			'html'        => '</div>',
		]
	];
}

$tec_events_display_currency = $tec_events_display_currency + [
	'defaultCurrencySymbol'       => [
		'type'            => 'text',
		'label'           => esc_html__( 'Default currency symbol', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Set the default currency symbol for event costs. Note that this only impacts future events, and changes made will not apply retroactively.', 'the-events-calendar' ),
		'validation_type' => 'textarea',
		'size'            => 'small',
		'default'         => '$',
	],
	'defaultCurrencyCode'         => [
		'type'            => 'text',
		'label'           => esc_html__( 'Default currency code', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Set the default currency ISO-4217 code for event costs. This is a three-letter code and is mainly used for data/SEO purposes.', 'the-events-calendar' ),
		'validation_type' => 'textarea',
		'size'            => 'small',
		'default'         => 'USD',
		'attributes'      => [
			'minlength'   => 3,
			'maxlength'   => 3,
			'placeholder' => __( 'USD', 'the-events-calendar' ),
		],
	],
	'reverseCurrencyPosition'     => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Currency symbol follows value', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'The currency symbol normally precedes the value. Enabling this option positions the symbol after the value.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
];


$display_currency = new Tribe__Settings_Tab(
	'display-currency-tab',
	esc_html__( 'Currency', 'the-events-calendar' ),
	[
		'priority' => 5.15,
		'fields'   => apply_filters(
			'tec_events_display_settings_currency_section',
			$tec_events_display_currency
		),
		'parent'   => 'display',
	]
);

do_action( 'tec_events_display_settings_date_time_tab', $display_currency );
