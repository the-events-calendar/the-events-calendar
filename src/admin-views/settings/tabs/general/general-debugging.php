<?php
/**
 * Debugging settings tab.
 * Subtab of the General Tab.
 *
 * @since 6.7.0
 */

$tec_events_general_debugging = [
	'tec-events-settings-general-debugging-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-general-debugging" class="tec-settings-form__section-header">' . esc_html_x( 'Debugging', 'Title for the debugging section of the general settings.', 'the-events-calendar' ) . '</h3>',
	],
	'debugEvents'                                 => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Debug mode', 'the-events-calendar' ),
		'tooltip'         => sprintf(
			/* Translators: %1$s - wordpress.org link to the Debug Bar Plugin */
			esc_html__(
				'Enable this option to log debug information. By default this will log to your server PHP error log. If you\'d like to see the log messages in your browser, then we recommend that you install the %1$s and look for the "Tribe" tab in the debug output.',
				'the-events-calendar'
			),
			'<a target="_blank" rel="noopener noreferrer" href="https://wordpress.org/extend/plugins/debug-bar/">' . esc_html__( 'Debug Bar Plugin', 'the-events-calendar' ) . '</a>'
		),
		'default'         => false,
		'validation_type' => 'boolean',
		'conditional'     => is_super_admin(),
	],
];

$tec_events_general_debugging += [
	'tec-troubleshooting-infobox-start'   => [
		'type' => 'html',
		'html' => '<div class="tec-settings-infobox">',
	],
	'tec-troubleshooting-infobox-logo'    => [
		'type' => 'html',
		'html' => '<img class="tec-settings-infobox-logo" src="' . tribe_resource_url( 'images/settings-icons/icon-image-high-five.svg', false, null, Tribe__Events__Main::instance() ) . '" alt="Events troubleshooting Logo">',
	],
	'tec-troubleshooting-infobox-title'   => [
		'type' => 'html',
		'html' => '<h3 class="tec-settings-infobox-title">' . __( 'There is a solution for every problem', 'the-events-calendar' ) . '</h3>',
	],
	'tec-troubleshooting-infobox-content' => [
		'type' => 'html',
		'html' => sprintf(
		/* Translators: %1$s - opening paragraph tag, %2$s - opening anchor tag, %3$s - closing anchor tag, %4$s - closing paragraph tag */
			__( '%1$sSometimes things just don’t work as expected. The %2$stroubleshooting page%3$s has a wealth of resources to get you back on track.%4$s', 'the-events-calendar' ),
			'<p>',
			'<a href="' . esc_url( 'edit.php?post_type=tribe_events&page=tec-troubleshooting' ) . '">',
			'</a>',
			'</p>',
		),
	],
	'tec-troubleshooting-infobox-end'     => [
		'type' => 'html',
		'html' => '</div>',
	],
];

$general_debugging = new Tribe__Settings_Tab(
	'general-debugging-tab',
	esc_html__( 'Debugging', 'the-events-calendar' ),
	[
		'priority' => 0.15,
		'fields'   => apply_filters(
			'tribe_general_settings_debugging_section',
			$tec_events_general_debugging
		),
	]
);

/**
 * Fires after the general debugging settings tab has been created.
 *
 * @since 6.7.0
 *
 * @param Tribe__Settings_Tab $general_debugging The general debugging settings tab.
 */
do_action( 'tec_events_settings_tab_general_debugging', $general_debugging );

return $general_debugging;
