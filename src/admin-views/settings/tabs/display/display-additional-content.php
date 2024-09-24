<?php
/**
 * Additional Content settings tab.
 * Subtab of the Display Tab.
 *
 * @since 6.7.0
 */

// Insert Advanced Template settings.
$tec_events_display_additional_content = [
	'tribe-events-advanced-settings-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-events-settings-display-additional" class="tec-settings-form__section-header">' . esc_html_x( 'Additional Content', 'Additional content settings section header', 'the-events-calendar' ) . '</h3>',
	],
	'tribeEventsBeforeHTML'                => [
		'type'            => 'wysiwyg',
		'label'           => esc_html__( 'Add HTML before event content', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'If you are familiar with HTML, you can add additional code before the event template. Some themes may require this to help with styling or layout.', 'the-events-calendar' ),
		'validation_type' => 'html',
	],
	'tribeEventsAfterHTML'                 => [
		'type'            => 'wysiwyg',
		'label'           => esc_html__( 'Add HTML after event content', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'If you are familiar with HTML, you can add additional code after the event template. Some themes may require this to help with styling or layout.', 'the-events-calendar' ),
		'validation_type' => 'html',
	],
];

$display_additional_content = new Tribe__Settings_Tab(
	'display-additional-content-tab',
	esc_html__( 'Additional Content', 'the-events-calendar' ),
	[
		'priority' => 5.25,
		'fields'   => apply_filters(
			'tec_events_settings_display_additional_content_section',
			$tec_events_display_additional_content
		),
	]
);

/**
 * Fires after the display additional content settings tab has been created.
 *
 * @since 6.7.0
 *
 * @param Tribe__Settings_Tab $display_additional_content The display additional content settings tab.
 */
do_action( 'tec_events_settings_tab_display_additional_content', $display_additional_content );

return $display_additional_content;
