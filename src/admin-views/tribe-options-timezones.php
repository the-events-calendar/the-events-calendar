<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$enable_button_label  = esc_html__( 'Enable Time Zone support', 'the-events-calendar' );
$enable_button_url    = esc_url( wp_nonce_url( add_query_arg( 'timezone-update', '1', Tribe__Settings::instance()->get_url() ), 'timezone-settings' ) );
$enable_button_text   = esc_html__( 'Update Time Zone Data', 'the-events-calendar' );
$enable_button_help   = sprintf( __( 'Click this button to update your database and take advantage of additional time zone capabilities. Please <a href="%s" target="_blank">configure WordPress</a> to use the correct time zone before clicking this button!', 'the-events-calendar' ),
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
		'html' => '<h3>' . esc_html__( 'Time Zone Settings', 'the-events-calendar' ) . '</h3>',
	),
	'tribe_events_enable_timezones' => array(
		'type' => 'html',
		'html' => $enable_button_html,
	),
	'tribe_events_timezone_mode' => array(
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Time zone mode', 'the-events-calendar' ),
		'validation_type' => 'options',
		'size'            => 'large',
		'options'         => array(
			'event' => esc_html__( 'Use manual time zones for each event', 'the-events-calendar' ),
			'site'  => esc_html__( 'Use the site-wide time zone everywhere', 'the-events-calendar' ),
		),
	),
	'tribe_events_timezones_show_zone' => array(
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Show time zone', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Appends the time zone to the end of event scheduling information &ndash; this can be useful when you have events in numerous different time zones.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	),
);
