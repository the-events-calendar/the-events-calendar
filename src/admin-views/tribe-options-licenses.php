<?php
$link = add_query_arg(
	array(
		'utm_campaign' => 'in-app',
		'utm_medium'   => 'plugin-tec',
		'utm_source'   => 'notice',
	), self::$tecUrl . 'license-keys/'
);

$link = esc_url( $link );

// Explanatory text about license settings for the tab information box
$html = __( '<p>The license key you received when completing your purchase from %1$s will grant you access to support and updates until it expires. You do not need to enter the key below for the plugins to work, but you will need to enter it to get automatic updates. <strong>Find your license keys at <a href="%2$s" target="_blank">%3$s</a></strong>.</p> <p>Each paid add-on has its own unique license key. Simply paste the key into its appropriate field on below, and give it a moment to validate. You know you\'re set when a green expiration date appears alongside a "valid" message.</p> <p>If you\'re seeing a red message telling you that your key isn\'t valid or is out of installs, visit <a href="%4$s" target="_blank">%5$s</a> to manage your installs or renew / upgrade your license.</p><p>Not seeing an update but expecting one? In WordPress, go to <a href="%6$s">Dashboard > Updates</a> and click "Check Again".</p>', 'the-events-calendar' );

// Expand with extra information for mu network users
if ( is_multisite() ) {
	$network_all_sites_text = __( '<strong> Using our plugins in a multisite network? </strong>
		Please note that your license key will be applied to the entire network, not just this site.', 'the-events-calendar' );

	$network_admin_only = is_network_admin() ? __( 'Only license fields for <strong>network activated</strong> plugins will be
		listed on this screen. ', 'the-events-calendar' ) : '';

	$html .= "<p> $network_all_sites_text $network_admin_only </p>";
}

$licenses_tab = array(
	'info-start' => array(
		'type' => 'html',
		'html' => '<div id="modern-tribe-info">',
	),
	'info-box-title' => array(
		'type' => 'html',
		'html' => '<h2>' . __( 'Licenses', 'the-events-calendar' ) . '</h2>',
	),
	'info-box-description' => array(
		'type' => 'html',
		'html' => sprintf(
			$html,
			self::$tecUrl,
			$link,
			self::$tecUrl . 'license-keys/',
			$link,
			self::$tecUrl . 'license-keys/',
			admin_url( '/update-core.php' )
		),
	),
	'info-end' => array(
		'type' => 'html',
		'html' => '</div>',
	),
	'tribe-form-content-start' => array(
		'type' => 'html',
		'html' => '<div class="tribe-settings-form-wrap">',
	),
	// TODO: Figure out how properly close this wrapper after the license content
	'tribe-form-content-end'   => array(
		'type' => 'html',
		'html' => '</div>',
	),
);