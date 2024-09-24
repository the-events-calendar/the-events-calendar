<?php
/**
 * Maps settings tab.
 * Subtab of the Display Tab.
 *
 * @since 6.7.0
 */

use TEC\Common\Admin\Entities\Div;
use TEC\Common\Admin\Entities\Field_Wrapper;
use TEC\Common\Admin\Entities\Heading;
use Tribe\Utils\Element_Classes as Classes;

$tec_events_display_maps = [];


// Insert Map settings.
$tec_events_display_maps = [
	'tribe-google-maps-settings-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-events-settings-display-maps" class="tec-settings-form__section-header">' . esc_html_x( 'Maps', 'Map settings section header', 'the-events-calendar' ) . '</h3>',
	],
	( new Div( new Classes( [ 'tec-settings-infobox' ] ) ) )->add_children(
		[
			( new Heading(
				__( 'Advanced Google Maps functionality', 'the-events-calendar' ),
				3,
				new Classes( [ 'tec-settings-infobox-title' ] )
			) ),
			new Field_Wrapper(
				new Tribe__Field(
					'tribe-google-maps-settings-infobox',
					[
						'type' => 'html',
						// @TODO: The link in this and the next section should probably be different.
						'html' => sprintf(
							/* Translators: %1$s - opening paragraph tag, %2$s - opening anchor tag, %3$s - closing anchor tag, %4$s - closing paragraph tag */
							__( '%1$sThe Events Calendar comes with a default API key for basic maps functionality. If you’d like to use more advanced features like custom map pins or dynamic map loads, you’ll need to get your own %2$sGoogle Maps API key%3$s.%4$s', 'the-events-calendar' ),
							'<p>',
							'<a href="' . esc_url( 'https://evnt.is/1bbu' ) . '" rel="noopener" target="_blank">',
							'</a>',
							'</p>'
						),
					]
				)
			),
		]
	),
	'embedGoogleMaps'                  => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Enable Maps', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Check to enable maps for events and venues.', 'the-events-calendar' ),
		'default'         => true,
		'class'           => 'google-embed-size',
		'validation_type' => 'boolean',
	],
	'embedGoogleMapsZoom'              => [
		'type'            => 'text',
		'label'           => esc_html__( 'Google Maps default zoom level', 'the-events-calendar' ),
		'tooltip'         => esc_html__( '0 = zoomed out; 21 = zoomed in.', 'the-events-calendar' ),
		'size'            => 'small',
		'default'         => 10,
		'class'           => 'google-embed-field',
		'validation_type' => 'number_or_percent',
	],
];


$display_maps = new Tribe__Settings_Tab(
	'display-maps-tab',
	esc_html__( 'Maps', 'the-events-calendar' ),
	[
		'priority' => 5.20,
		'fields'   => apply_filters(
			'tec_events_settings_display_maps_section',
			$tec_events_display_maps
		),
	]
);

/**
 * Fires after the display settings maps tab has been created.
 *
 * @since 6.7.0
 *
 * @param Tribe__Settings_Tab $display_maps The display settings maps tab.
 */
do_action( 'tec_events_settings_tab_display_maps', $display_maps );

return $display_maps;
