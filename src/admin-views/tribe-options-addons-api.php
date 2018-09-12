<?php
/**
 * Create a easy way to hook to the Add-ons Tab Fields
 * @var array
 */
$internal = array();

$current_url = Tribe__Settings::instance()->get_url( array( 'tab' => 'addons' ) );

// if there's an Event Aggregator license key, add the Meetup.com API fields
if ( get_option( 'pue_install_key_event_aggregator' ) ) {

	$internal = array(
		'meetup-start' => array(
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'Meetup', 'the-events-calendar' ) . '</h3>',
		),
		'meetup-info-box' => array(
			'type' => 'html',
			'html' => '<p>' . esc_html__( 'You need a Meetup API Key to import your events from Meetup.', 'the-events-calendar' ) . '</p>',
		),
		'meetup_api_key' => array(
			'type'            => 'text',
			'label'           => esc_html__( 'Meetup API Key', 'the-events-calendar' ),
			'tooltip'         => sprintf( __( '%s to view your Meetup API Key', 'the-events-calendar' ), '<a href="https://secure.meetup.com/meetup_api/key/" target="_blank">' . __( 'Click here', 'the-events-calendar' ) . '</a>' ),
			'size'            => 'medium',
			'validation_type' => 'alpha_numeric',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
		),
	);
}

/**
 * Show Eventbrite API Connection only if Eventbrite Plugin is Active or Event Aggregator license key has a license key
 */
if ( class_exists( 'Tribe__Events__Tickets__Eventbrite__Main' ) || get_option( 'pue_install_key_event_aggregator' ) ) {

	$missing_eb_credentials = ! tribe( 'events-aggregator.settings' )->is_ea_authorized_for_eb();

	ob_start();
	?>

	<fieldset id="tribe-field-eventbrite_token" class="tribe-field tribe-field-text tribe-size-medium">
		<legend class="tribe-field-label"><?php esc_html_e( 'Eventbrite Token', 'the-events-calendar' ) ?></legend>
		<div class="tribe-field-wrap">
			<?php
			if ( $missing_eb_credentials ) {
				echo '<p>' . esc_html__( 'You need to connect to Eventbrite for Event Aggregator to work properly' ) . '</p>';
				$eventbrite_button_label = __( 'Connect to Eventbrite', 'the-events-calendar' );
			} else {
				$eventbrite_button_label     = __( 'Refresh your connection to Eventbrite', 'the-events-calendar' );
				$eventbrite_disconnect_label = __( 'Disconnect', 'the-events-calendar' );
				$eventbrite_disconnect_url   = tribe( 'events-aggregator.settings' )->build_disconnect_eventbrite_url( $current_url );
			}
			?>
			<a target="_blank" class="tribe-ea-eventbrite-button" href="<?php echo esc_url( Tribe__Events__Aggregator__Record__Eventbrite::get_auth_url( array( 'back' => 'settings' ) ) ); ?>"><?php esc_html_e( $eventbrite_button_label ); ?></a>
			<?php if ( ! $missing_eb_credentials ) : ?>
				<a href="<?php echo esc_url( $eventbrite_disconnect_url ); ?>" class="tribe-ea-eventbrite-disconnect"><?php echo esc_html( $eventbrite_disconnect_label ); ?></a>
			<?php endif; ?>
		</div>
	</fieldset>

	<?php
	$eventbrite_token_html = ob_get_clean();

	$internal2 = array(
		'eb-start'        => array(
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'Eventbrite', 'the-events-calendar' ) . '</h3>',
		),
		'eb-info-box'     => array(
			'type' => 'html',
			'html' => '<p>' . esc_html__( 'You need to connect Event Aggregator to Eventbrite to import your events from Eventbrite.', 'the-events-calendar' ) . '</p>',
		),
		'eb_token_button' => array(
			'type' => 'html',
			'html' => $eventbrite_token_html,
		),
	);

	$internal = array_merge( $internal, $internal2 );
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
