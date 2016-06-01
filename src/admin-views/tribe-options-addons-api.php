<?php
/**
 * Create a easy way to hook to the Add-ons Tab Fields
 * @var array
 */
$internal = apply_filters( 'tribe_addons_tab_fields', array() );
$fields = array_merge(
	array(
		'addons-box-start' => array(
			'type' => 'html',
			'html' => '<div id="modern-tribe-info">',
		),
		'addons-box-title' => array(
			'type' => 'html',
			'html' => '<h1>' . esc_html__( 'APIs', 'the-events-calendar' ) . '</h1>',
		),
		'addons-box-description' => array(
			'type' => 'html',
			'html' => __( '<p>Certain features and add-ons require an API key in order for The Events Calendar to work with outside sources. Please follow the instructions below to configure your settings.</p>', 'the-events-calendar' ),
		),
		'addons-box-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
		'addons-form-content-start' => array(
			'type' => 'html',
			'html' => '<div class="tribe-settings-form-wrap">',
		),
	),
	$internal,
	array(
		'addons-form-content-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
	)
);

/**
 * Allow developer to fully filter the Addons Tab contents
 * Following the structure of the arguments for a Tribe__Settings_Tab instance
 *
 * @var array
 */
$addons = apply_filters(
	'tribe_addons_tab',
	array(
		'priority' => 50,
		'fields'   => $fields,
	)
);

// Only create the Add-ons Tab if there is any
if ( ! empty( $internal ) ) {
	new Tribe__Settings_Tab( 'addons', esc_html__( 'APIs', 'the-events-calendar' ), $addons );
}
