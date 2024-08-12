<?php
/**
 * General settings tab.
 * This tab sets up the main structure and the "sidebar" for the sub-tabs under it.
 *
 * @since TBD
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
	'tec-documentation-section-welcome-page-link'    => [
		'type' => 'html',
		'html' => '<li><a href="'
					. esc_url( tribe( 'tec.main' )->settings()->get_url( [ Tribe__Events__Main::instance()->activation_page->welcome_slug => 1 ] ) ) . '">'
					. esc_html__( 'View Welcome Page', 'the-events-calendar' )
					. '</a></li>',
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
					. esc_url( 'edit.php?post_type=tribe_events&page=tec-events-help' ) . '">'
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
	'tec-links-donate-section-start'                 => [
		'type' => 'html',
		'html' => '<div class="tec-settings-header-links-section__donate">',
	],
	'tec-donate-link-info'                           => [
		'type' => 'html',
		'html' => '<p>' . esc_html__( 'If you’re enjoying The Events Calendar, give us kudos by including a link in the footer of calendar views. It really helps us a lot.', 'the-events-calendar' ) . '</p>',
	],
	'donate-link'                                    => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Show The Events Calendar link', 'the-event-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
	'tec-links-donate-section-end'                   => [
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

// Start the form content wrapper.
$general_tab_fields += [
	'tribe-form-content-start' => [
		'type' => 'html',
		'html' => '<div class="tribe-settings-form-wrap tec-settings-general">',
	],
];

// Close the form content wrapper.
$general_tab_fields += [
	'tribe-form-content-end' => [
		'type' => 'html',
		'html' => '</div>',
	],
];

$general_tab = [
	'priority' => 0,
	'fields'   => apply_filters( 'tribe_general_settings_tab_fields', $general_tab_fields ),
];

require_once 'settings-tabs/general/general-viewing.php';
require_once 'settings-tabs/general/general-editing.php';
require_once 'settings-tabs/general/general-maintenance.php';
require_once 'settings-tabs/general/general-debugging.php';
