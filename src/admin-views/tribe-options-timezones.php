<?php
defined( 'ABSPATH' ) or exit();

$enable_button_label  = __( 'Enable timezone support', 'the-events-calendar' );
$enable_button_url    = esc_url( wp_nonce_url( add_query_arg( 'timezone-update', '1', Tribe__Events__Settings::instance()->get_url() ), 'timezone-settings' ) );
$enable_button_text   = __( 'Update Timezone Data', 'the-events-calendar' );
$enable_button_help   = sprintf( __( 'Click this button to update your database and take advantage of additional timezone capabilities. Please <a href="%s" target="_blank">configure WordPress</a> to use the correct timezone before clicking this button!', 'the-events-calendar' ),
	esc_url( get_admin_url( null, 'options-general.php' ) )
);

$enable_button_html = <<<HTML
	<fieldset class="tribe-field tribe-field-html">
		<legend> $enable_button_label </legend>
		<div class="tribe-field-wrap">
			<a href="$enable_button_url" class="button"> $enable_button_text </a>
			<p class="tribe-field-indent description">
				$enable_button_help
			</p>
		</div>
	</fieldset>
	<div class="clear"></div>
HTML;

return array(
	'tribe_events_timezones_title' => array(
		'type' => 'html',
		'html' => '<h2>' . esc_html__( 'Timezone Settings', 'the-events-calendar' ) . '</h2>',
	),
	'tribe_events_enable_timezones' => array(
		'type' => 'html',
		'html' => $enable_button_html
	),
	'tribe_events_timezone_mode' => array(
		'type'            => 'dropdown',
		'label'           => __( 'Timezone mode', 'the-events-calendar' ),
		'validation_type' => 'options',
		'size'            => 'large',
		'options'         => array(
			'event' => __( 'Use the local timezones for each event', 'the-events-calendar' ),
			'site'  => __( 'Use the sitewide timezone everywhere', 'the-events-calendar' ),
		),
	),
	'tribe_events_timezones_show_zone' => array(
		'type'            => 'checkbox_bool',
		'label'           => __( 'Show timezone', 'the-events-calendar' ),
		'tooltip'         => __( 'Appends the timezone to the end of event scheduling information &ndash; this can be useful when you have events in numerous different timezones.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	),
);
