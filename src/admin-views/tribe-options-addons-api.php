<?php
/**
 * Create a easy way to hook to the Add-ons Tab Fields
 * @var array
 */
$internal = array();

// if there's an Event Aggregator license key, add the Facebook API fields
if ( get_option( 'pue_install_key_event_aggregator' ) ) {
	$internal = array(
		'fb-start' => array(
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'Facebook', 'the-events-calendar' ) . '</h3>',
		),
		'fb-info-box' => array(
			'type' => 'html',
			'html' => '<p>' . esc_html__( 'You need a Facebook App ID and App Secret to access data via the Facebook Graph API to import your events from Facebook.', 'the-events-calendar' ) . '</p>',
		),
		'fb_api_key' => array(
			'type' => 'text',
			'label' => esc_html__( 'Facebook App ID', 'the-events-calendar' ),
			'tooltip' => sprintf( __( '<p>%s to view or create your Facebook Apps', 'the-events-calendar' ), '<a href="https://developers.facebook.com/apps" target="_blank"></p>' . __( 'Click here', 'the-events-calendar' ) . '</a>' ),
			'size' => 'medium',
			'validation_type' => 'alpha_numeric',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
		),
		'fb_api_secret' => array(
			'type' => 'text',
			'label' => esc_html__( 'Facebook App secret', 'the-events-calendar' ),
			'tooltip' => sprintf( __( '<p>%s to view or create your App Secret', 'the-events-calendar' ), '<a href="https://developers.facebook.com/apps" target="_blank"></p>' . __( 'Click here', 'the-events-calendar' ) . '</a>' ),
			'size' => 'medium',
			'validation_type' => 'alpha_numeric',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
		),
		'meetup-start' => array(
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'Meetup', 'the-events-calendar' ) . '</h3>',
		),
		'meetup-info-box' => array(
			'type' => 'html',
			'html' => '<p>' . esc_html__( 'You need a Meetup API Key to access data via the Meetup API to import your events from Meetup.', 'the-events-calendar' ) . '</p>',
		),
		'meetup_api_key' => array(
			'type' => 'text',
			'label' => esc_html__( 'Meetup API Key', 'the-events-calendar' ),
			'tooltip' => sprintf( __( '<p>%s to view your Meetup API Key', 'the-events-calendar' ), '<a href="https://secure.meetup.com/meetup_api/key/" target="_blank"></p>' . __( 'Click here', 'the-events-calendar' ) . '</a>' ),
			'size' => 'medium',
			'validation_type' => 'alpha_numeric',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
		),
	);
}

$internal = apply_filters( 'tribe_addons_tab_fields', $internal );

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
