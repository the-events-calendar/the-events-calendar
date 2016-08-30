<?php
/**
 * Create a easy way to hook to the Add-ons Tab Fields
 * @var array
 */
$internal = array();

// if there's an Event Aggregator license key, add the Facebook API fields
if ( get_option( 'pue_install_key_event_aggregator' ) ) {
	$fb_token = tribe_get_option( 'fb_token' );
	$fb_token_expires = tribe_get_option( 'fb_token_expires' );
	$fb_token_scopes = tribe_get_option( 'fb_token_scopes' );

	$missing_fb_credentials = ! $fb_token || ! $fb_token_scopes || ! $fb_token_expires || $fb_token_expires <= time();

	if ( ! $missing_fb_credentials ) {
		/**
		 * Allow developers to filter how many seconds they want to be warned about FB token expiring
		 * @param int
		 */
		$boundary = apply_filters( 'tribe_aggregator_facebook_token_expire_notice_boundary', 4 * DAY_IN_SECONDS );

		// Creates a Boundary for expire warning to appear, before the actual expiring of the token
		$boundary = $fb_token_expires - $boundary;

		$diff = human_time_diff( time(), $boundary );
		$passed = ( time() - $fb_token_expires );
		$original = date( 'Y-m-d H:i:s', $fb_token_expires );

		$time[] = '<span title="' . esc_attr( $original ) . '">';
		if ( $passed > 0 ) {
			$time[] = sprintf( esc_html_x( 'about %s ago', 'human readable time ago', 'the-events-calendar' ), $diff );
		} else {
			$time[] = sprintf( esc_html_x( 'in about %s', 'in human readable time', 'the-events-calendar' ), $diff );
		}
		$time[] = '</span>';
		$time = implode( '', $time );
	}

	ob_start();
	?>

	<fieldset id="tribe-field-facebook_token" class="tribe-field tribe-field-text tribe-size-medium">
		<legend class="tribe-field-label"><?php esc_html_e( 'Facebook Token', 'the-events-calendar' ) ?></legend>
		<div class="tribe-field-wrap">
			<?php wp_nonce_field( 'tribe-save-facebook-credentials' ); ?>
			<p>
			<?php
			if ( $missing_fb_credentials ) {
				esc_html_e( 'You need to enter a Facebook Token for Event Aggregator to work properly' );
			} else {
				if ( $passed > 0 ) {
					echo sprintf( __( 'Your Event Aggregator Facebook token has expired %s.', 'the-events-calendar' ), $time );
				} else {
					echo sprintf( __( 'Your Event Aggregator Facebook token will expire %s.', 'the-events-calendar' ), $time );
				}
			}
			?>
			</p>
			<div class="tribe-ea-facebook-login">
				<iframe id="facebook-login" src="<?php echo esc_url( Tribe__Events__Aggregator__Record__Facebook::get_iframe_url() ); ?>" width="80" height="30"></iframe>
				<div class="tribe-ea-status" data-error-message="<?php esc_attr_e( '@todo:error-fb-message', 'the-events-calendar' ); ?>"></div>
			</div>
		</div>
	</fieldset>

	<?php
	$facebook_token_html = ob_get_clean();

	$internal = array(
		'fb-start' => array(
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'Facebook', 'the-events-calendar' ) . '</h3>',
		),
		'fb-info-box' => array(
			'type' => 'html',
			'html' => '<p>' . esc_html__( 'You need a Facebook Token to access data via the Facebook Graph API to import your events from Facebook.', 'the-events-calendar' ) . '</p>',
		),
		'fb_token_button' => array(
			'type' => 'html',
			'html' => $facebook_token_html,
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
			'html' => '<h2>' . esc_html__( 'APIs', 'the-events-calendar' ) . '</h2>',
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
