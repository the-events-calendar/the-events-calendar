<?php
/**
 * Create a easy way to hook to the Add-ons Tab Fields
 * @var array
 */
$internal = array();

$current_url = Tribe__Settings::instance()->get_url( array( 'tab' => 'addons' ) );

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
			<p>
				<?php
				if ( $missing_fb_credentials ) {
					esc_html_e( 'You need to connect to Facebook for Event Aggregator to work properly' );
					$facebook_button_label = __( 'Connect to Facebook', 'the-events-calendar' );
				} else {
					if ( $passed > 0 ) {
						echo sprintf( __( 'Your Event Aggregator Facebook connection has expired %s.', 'the-events-calendar' ), $time );
					} else {
						echo sprintf( __( 'Your Event Aggregator Facebook connection will expire %s.', 'the-events-calendar' ), $time );
					}
					$facebook_button_label = __( 'Refresh your connection to Facebook', 'the-events-calendar' );
					$facebook_disconnect_label = __( 'Disconnect', 'the-events-calendar' );
					$facebook_disconnect_url = Tribe__Events__Aggregator__Settings::instance()->build_disconnect_facebook_url( $current_url );
				}
				?>
			</p>
			<a target="_blank" class="tribe-ea-facebook-button" href="<?php echo esc_url( Tribe__Events__Aggregator__Record__Facebook::get_auth_url( array( 'back' => 'settings' ) ) ); ?>"><?php esc_html_e( $facebook_button_label ); ?></a>
			<?php if ( ! $missing_fb_credentials ) : ?>
				<a href="<?php echo esc_url( $facebook_disconnect_url ); ?>" class="tribe-ea-facebook-disconnect"><?php echo esc_html( $facebook_disconnect_label ); ?></a>
			<?php endif; ?>
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
			'html' => '<p>' . esc_html__( 'You need to connect Event Aggregator to Facebook to import your events from Facebook.', 'the-events-calendar' ) . '</p>',
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
			'html' => '<p>' . esc_html__( 'You need a Meetup API Key to import your events from Meetup.', 'the-events-calendar' ) . '</p>',
		),
		'meetup_api_key' => array(
			'type' => 'text',
			'label' => esc_html__( 'Meetup API Key', 'the-events-calendar' ),
			'tooltip' => sprintf( __( '%s to view your Meetup API Key', 'the-events-calendar' ), '<a href="https://secure.meetup.com/meetup_api/key/" target="_blank">' . __( 'Click here', 'the-events-calendar' ) . '</a>' ),
			'size' => 'medium',
			'validation_type' => 'alpha_numeric',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
		),
	);

	if ( ! Tribe__Events__Aggregator::instance()->api( 'origins' )->is_oauth_enabled( 'facebook' ) ) {
		unset( $internal['fb-start'], $internal['fb-info-box'], $internal['fb_token_button'] );
	}
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
			'html' => '<p>' . __( 'Some features and add-ons require you to enter an API key or log into a third-party website so that The Events Calendar can communicate with an outside source.', 'the-events-calendar' ) . '</p>',
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
