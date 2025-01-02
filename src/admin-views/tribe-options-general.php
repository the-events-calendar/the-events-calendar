<?php
/**
 * General settings tab.
 * This tab sets up the main structure and the "sidebar" for the sub-tabs under it.
 *
 * @since 6.7.0
 */

$general_tab_fields = [
	'info-start'                                     => [
		'type' => 'html',
		'html' => '<div class="tec-settings-header">',
	],
	'upsell-heading'                                 => [
		'type'  => 'heading',
		'label' => tec_should_hide_upsell()
			? esc_html__( 'Finding your calendar.', 'the-events-calendar' )
			: esc_html__( 'Finding & extending your calendar.', 'the-events-calendar' ),
	],
	'tec-setup-guide'                                => [
		'type' => 'html',
		'html' => '<p>' . esc_html__( 'Looking for additional functionality including recurring events, custom meta, community events, ticket sales and more?', 'the-events-calendar' ),
	],
	'tec-add-ons-link'                               => [
		'type' => 'html',
		'html' => sprintf(
			'<br><a href="%1$s">%2$s</a></p>',
			esc_url( 'edit.php?post_type=tribe_events&page=tribe-app-shop' ),
			esc_html_x( 'Check out the available add-ons.', 'Text for link to the add-ons page.', 'the-events-calendar' )
		),
	],
	'tec-links-section-start'                        => [
		'type' => 'html',
		'html' => '<div class="tec-settings-header-links-section">',
	],
	'tec-links-documentation-section-start'          => [
		'type' => 'html',
		'html' => '<ul class="tec-settings-header-links-section__documentation">'
			. '<li>' . esc_html__( 'Documentation', 'the-events-calendar' ) . '</li>',
	],
	'tec-documentation-section-getting-started-link' => [
		'type' => 'html',
		'html' => '<li><a href="'
			. esc_url( 'https://evnt.is/1bbv' ) . '" rel="noopener" target="_blank">'
			. esc_html__( 'Getting started guide', 'the-events-calendar' )
			. '</a></li>',
	],
	'tec-documentation-section-knowledgebase-link'   => [
		'type' => 'html',
		'html' => '<li><a href="'
			. esc_url( 'https://evnt.is/1bbw' ) . '" rel="noopener" target="_blank">'
			. esc_html__( 'Knowledgebase', 'the-events-calendar' )
			. '</a></li>',
	],
	'tec-links-documentation-section-end'            => [
		'type' => 'html',
		'html' => '</ul>',
	],
	'tec-links-help-section-start'                   => [
		'type' => 'html',
		'html' => '<div class="tec-settings-header-links-section__help">',
	],
	'tec-view-calendar-link'                         => [
		'type' => 'html',
		'html' => '<ul>'
			. '<li>' . esc_html__( 'Where\'s my calendar?', 'the-events-calendar' ) . '</li>'
			. '<li><a href="'
			. esc_url( tribe( 'tec.main' )->getLink() ) . '">'
			. esc_html__( 'Right here', 'the-events-calendar' )
			. '</a></li>'

			. '</ul>',
	],
	'tec-having-trouble-links'                       => [
		'type' => 'html',
		'html' => '<ul>'
			. '<li>' . esc_html__( 'Having trouble?', 'the-events-calendar' ) . '</li>'
			. '<li><a href="'
			. esc_url( 'edit.php?post_type=tribe_events&page=tec-events-help-hub' ) . '">'
			. esc_html__( 'Help', 'the-events-calendar' )
			. '</a></li>'
			. '<li><a href="'
			. esc_url( 'edit.php?post_type=tribe_events&page=tec-troubleshooting' ) . '">'
			. esc_html__( 'Troubleshoot', 'the-events-calendar' )
			. '</a></li>'
			. '</ul>',
	],
	'tec-links-help-section-end'                     => [
		'type' => 'html',
		'html' => '</div>',
	],
	'tec-links-section-end'                          => [
		'type' => 'html',
		'html' => '</div>',
	],
	'ical-info'                                      => [
		'type'             => 'html',
		'display_callback' => '<p id="ical-link" class="tribe-field-indent tribe-field-description description">' . esc_html__( 'Here is the iCal feed URL for your events:', 'the-events-calendar' ) . ' <code>' . tribe_get_ical_link() . '</code></p>',
		'conditional'      => function_exists( 'tribe_get_ical_link' ), // @TODO: this never loads.
	],
	'info-end'                                       => [
		'type' => 'html',
		'html' => '</div>',
	],
];

$general_tab = new Tribe__Settings_Tab(
	'general',
	esc_html__( 'General', 'the-events-calendar' ),
	[
		'priority' => 0,
		'fields'   => [], // Parent tabs don't have content of their own!
	]
);

// Add each of the sub-tabs.
$viewing_tab = require_once __DIR__ . '/settings/tabs/general/general-viewing.php';
$general_tab->add_child( $viewing_tab );

$editing_tab = require_once __DIR__ . '/settings/tabs/general/general-editing.php';
$general_tab->add_child( $editing_tab );

$maintenance_tab = require_once __DIR__ . '/settings/tabs/general/general-maintenance.php';
$general_tab->add_child( $maintenance_tab );

$debugging_tab = require_once __DIR__ . '/settings/tabs/general/general-debugging.php';
$general_tab->add_child( $debugging_tab );

/**
 * Fires after the general tab has been set up.
 *
 * @param Tribe__Settings_Tab $general_tab The general tab.
 */
do_action( 'tec_events_settings_tab_general', $general_tab );

return $general_tab;
