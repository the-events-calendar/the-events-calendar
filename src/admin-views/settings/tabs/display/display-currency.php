<?php
/**
 * Currency settings tab.
 * Subtab of the Display Tab.
 *
 * @since 6.7.0
 * @since 6.15.6 Hide Event Tickets upsell when Event Tickets is enabled. [TEC-5585]
 */

$tec_events_display_currency = [];

// Insert Currency settings.
$tec_events_display_currency = [
	'tribe-events-currency-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-events-settings-display-currency" class="tec-settings-form__section-header">' . esc_html_x( 'Currency', 'Currency settings section header', 'the-events-calendar' ) . '</h3>',
	],
];

$tec_events_display_currency = $tec_events_display_currency + [
	'defaultCurrencySymbol'   => [
		'type'            => 'text',
		'label'           => esc_html__( 'Default currency symbol', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Set the default currency symbol for event costs. Note that this only impacts future events, and changes made will not apply retroactively.', 'the-events-calendar' ),
		'validation_type' => 'textarea',
		'size'            => 'small',
		'default'         => '$',
	],
	'defaultCurrencyCode'     => [
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
	'reverseCurrencyPosition' => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Currency symbol follows value', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'The currency symbol normally precedes the value. Enabling this option positions the symbol after the value.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
];

// Add the Tickets Plus upsell if the user doesn't have it.
if ( ! tec_should_hide_upsell() && ! did_action( 'tec_tickets_fully_loaded' ) ) {
	$tec_events_display_currency = $tec_events_display_currency + [
		'tec-tickets-infobox-start'   => [
			'type' => 'html',
			'html' => '<div class="tec-settings-infobox">',
		],
		'tec-tickets-infobox-logo'    => [
			'type' => 'html',
			'html' => '<img class="tec-settings-infobox-logo" src="' . plugins_url( 'src/resources/images/settings-icons/icon-et.svg', TRIBE_EVENTS_FILE ) . '" alt="Events Tickets Logo">',
		],
		'tec-tickets-infobox-title'   => [
			'type' => 'html',
			'html' => '<h3 class="tec-settings-infobox-title">' . __( 'Start selling tickets to your events', 'the-events-calendar' ) . '</h3>',
		],
		'tec-tickets-infobox-content' => [
			'type' => 'html',
			'html' => '<p>' . __( 'Get Event Tickets to manage attendee registration and ticket sales to your events, for free.', 'the-events-calendar' ) . '</p>', /* @TODO: This is placeholder text! */
		],
		'tec-tickets-infobox-link'    => [
			'type' => 'html',
			'html' => '<a href="' . esc_url( 'https://evnt.is/1bbx' ) . '" rel="noopener" target="_blank">' . __( 'Learn more.', 'the-events-calendar' ) . '</a>',
		],
		'tec-tickets-infobox-end'     => [
			'type' => 'html',
			'html' => '</div>',
		],
	];
}


$display_currency = new Tribe__Settings_Tab(
	'display-currency-tab',
	esc_html__( 'Currency', 'the-events-calendar' ),
	[
		'priority' => 5.15,
		'fields'   => apply_filters(
			'tec_events_settings_display_currency_section',
			$tec_events_display_currency
		),
	]
);

/**
 * Fires after the display settings currency tab has been created.
 *
 * @since 6.7.0
 *
 * @param Tribe__Settings_Tab $display_currency The display settings currency tab.
 */
do_action( 'tec_events_settings_tab_display_date_time', $display_currency );

return $display_currency;
