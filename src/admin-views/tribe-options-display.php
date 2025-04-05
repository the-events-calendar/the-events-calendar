<?php
/**
 * Handles the display settings for The Events Calendar.
 */

// Begin Settings content - header section.
$tec_events_display_fields = [
	'info-start'           => [
		'type' => 'html',
		'html' => '<div class="tec-settings-header">',
	],
	'info-box-title'       => [
		'type' => 'html',
		'html' => '<h2>' . _x( 'Display Settings', 'Display settings tab header', 'the-events-calendar' ) . '</h2>',
	],
	'info-box-description' => [
		'type' => 'html',
		'html' => '<p>'
			. __( 'The settings below control the display of your calendar. If things don\'t look right, try switching between the two style sheet options or pick a page template from your theme (not available on block themes). ', 'the-events-calendar' )
			. sprintf(
				/* Translators: %s: URL to knowledgebase. Please continue to use &#37; for % to avoid PHP warnings. */
				__( ' Check out our <a href="%s" rel="noopener" target="_blank">customization guide</a> for instructions on template modifications.', 'the-events-calendar' ),
				esc_url( 'https://evnt.is/1bbs' )
			)
			. '</p>',
	],
	'info-end'             => [
		'type' => 'html',
		'html' => '</div>',
	],
];

$display_tab = new Tribe__Settings_Tab(
	'display',
	esc_html__( 'Display', 'the-events-calendar' ),
	[
		'priority' => 5,
		'fields'   => [],
	]
);

$calendar_tab = require_once __DIR__ . '/settings/tabs/display/display-calendar.php';
$display_tab->add_child( $calendar_tab );

$date_time = require_once __DIR__ . '/settings/tabs/display/display-date-time.php';
$display_tab->add_child( $date_time );

$currency_tab = require_once __DIR__ . '/settings/tabs/display/display-currency.php';
$display_tab->add_child( $currency_tab );

$maps_tab = require_once __DIR__ . '/settings/tabs/display/display-maps.php';
$display_tab->add_child( $maps_tab );

$additional_content_tab = require_once __DIR__ . '/settings/tabs/display/display-additional-content.php';
$display_tab->add_child( $additional_content_tab );

/**
 * Fires after the display settings tab has been created.
 *
 * @since 6.7.0
 *
 * @param Tribe__Settings_Tab $display_tab The display settings tab.
 */
do_action( 'tec_events_settings_tab_display', $display_tab );

return $display_tab;
