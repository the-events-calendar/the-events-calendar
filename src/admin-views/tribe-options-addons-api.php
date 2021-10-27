<?php
/**
 * Create a easy way to hook to the Add-ons Tab Fields
 * @var array
 */
$internal = [];

$current_url = Tribe__Settings::instance()->get_url( [ 'tab' => 'addons' ] );

// if there's an Event Aggregator license key, add the Meetup.com API fields
if ( get_option( 'pue_install_key_event_aggregator' ) ) {

	$missing_meetup_credentials = ! tribe( 'events-aggregator.settings' )->is_ea_authorized_for_meetup();

	ob_start();
	?>

	<fieldset id="tribe-field-meetup_token" class="tribe-field tribe-field-text tribe-size-medium">
		<legend class="tribe-field-label"><?php esc_html_e( 'Meetup Authentication', 'the-events-calendar' ) ?></legend>
		<div class="tribe-field-wrap">
			<?php
			if ( $missing_meetup_credentials ) {
				echo '<p>' . esc_html__( 'You need to connect to Meetup for Event Aggregator to work properly', 'the-events-calendar' ) . '</p>';
				$meetup_button_label = __( 'Connect to Meetup', 'the-events-calendar' );
			} else {
				$meetup_button_label     = __( 'Refresh your connection to Meetup', 'the-events-calendar' );
				$meetup_disconnect_label = __( 'Disconnect', 'the-events-calendar' );
				$meetup_disconnect_url   = tribe( 'events-aggregator.settings' )->build_disconnect_meetup_url( $current_url );
			}
			?>
			<a target="_blank" class="tribe-ea-meetup-button" href="<?php echo esc_url( Tribe__Events__Aggregator__Record__Meetup::get_auth_url( [ 'back' => 'settings' ] ) ); ?>">
				<?php esc_html_e( $meetup_button_label ); ?></a>
			<?php if ( ! $missing_meetup_credentials ) : ?>
				<a href="<?php echo esc_url( $meetup_disconnect_url ); ?>" class="tribe-ea-meetup-disconnect"><?php echo esc_html( $meetup_disconnect_label ); ?></a>
			<?php endif; ?>
		</div>
	</fieldset>

	<?php
	$meetup_token_html = ob_get_clean();

	$internal_meetup = [
		'meetup-start'        => [
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'Meetup', 'the-events-calendar' ) . '</h3>',
		],
		'meetup-info-box'     => [
			'type' => 'html',
			'html' => '<p>' . esc_html__( 'You need to connect Event Aggregator to Meetup to import your events from Meetup.', 'the-events-calendar' ) . '</p>',
		],
		'meetup_token_button' => [
			'type' => 'html',
			'html' => $meetup_token_html,
		],
	];

	$internal = array_merge( $internal, $internal_meetup );

}

/**
 * Show Eventbrite API Connection only if Eventbrite Plugin is Active or Event Aggregator license key has a license key
 */
if ( class_exists( 'Tribe__Events__Tickets__Eventbrite__Main' ) || get_option( 'pue_install_key_event_aggregator' ) ) {

	$missing_eb_credentials = ! tribe( 'events-aggregator.settings' )->is_ea_authorized_for_eb();

	ob_start();
	?>

	<fieldset id="tribe-field-eventbrite_token" class="tribe-field tribe-field-text tribe-size-medium">
		<legend class="tribe-field-label"><?php esc_html_e( 'Eventbrite Authentication', 'the-events-calendar' ) ?></legend>
		<div class="tribe-field-wrap">
			<?php
			if ( $missing_eb_credentials ) {
				echo '<p>' . esc_html__( 'You need to connect to Eventbrite for Event Aggregator to work properly', 'the-events-calendar' ) . '</p>';
				$eventbrite_button_label = __( 'Connect to Eventbrite', 'the-events-calendar' );
			} else {
				$eventbrite_button_label     = __( 'Refresh your connection to Eventbrite', 'the-events-calendar' );
				$eventbrite_disconnect_label = __( 'Disconnect', 'the-events-calendar' );
				$eventbrite_disconnect_url   = tribe( 'events-aggregator.settings' )->build_disconnect_eventbrite_url( $current_url );
			}
			?>
			<a target="_blank" class="tribe-ea-eventbrite-button" href="<?php echo esc_url( Tribe__Events__Aggregator__Record__Eventbrite::get_auth_url( [ 'back' => 'settings' ] ) ); ?>"><?php esc_html_e( $eventbrite_button_label ); ?></a>
			<?php if ( ! $missing_eb_credentials ) : ?>
				<a href="<?php echo esc_url( $eventbrite_disconnect_url ); ?>" class="tribe-ea-eventbrite-disconnect"><?php echo esc_html( $eventbrite_disconnect_label ); ?></a>
			<?php endif; ?>
		</div>
	</fieldset>

	<?php
	$eventbrite_token_html = ob_get_clean();

	$internal2 = [
		'eb-start'        => [
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'Eventbrite', 'the-events-calendar' ) . '</h3>',
		],
		'eb-info-box'     => [
			'type' => 'html',
			'html' => '<p>' . esc_html__( 'You need to connect Event Aggregator to Eventbrite to import your events from Eventbrite.', 'the-events-calendar' ) . '</p>',
		],
		'eb_token_button' => [
			'type' => 'html',
			'html' => $eventbrite_token_html,
		],
	];

	$internal = array_merge( $internal, $internal2 );
}

$internal = apply_filters( 'tribe_addons_tab_fields', $internal );

$fields = array_merge(
	[
		'addons-box-start' => [
			'type' => 'html',
			'html' => '<div id="modern-tribe-info">',
		],
		'addons-box-title' => [
			'type' => 'html',
			'html' => '<h2>' . esc_html__( 'Integrations', 'the-events-calendar' ) . '</h2>',
		],
		'addons-box-description' => [
			'type' => 'html',
			'html' => '<p>' . __( 'The Events Calendar and its add-ons integrate with other online tools and services to bring you additional features. Use the settings below to connect to third-party APIs and manage your integrations.', 'the-events-calendar' ) . '</p>',
		],
		'addons-box-end' => [
			'type' => 'html',
			'html' => '</div>',
		],
		'addons-form-content-start' => [
			'type' => 'html',
			'html' => '<div class="tribe-settings-form-wrap">',
		],
	],
	$internal,
	[
		'addons-form-content-end' => [
			'type' => 'html',
			'html' => '</div>',
		],
	]
);

/**
 * Allow developer to fully filter the Addons Tab contents
 * Following the structure of the arguments for a Tribe__Settings_Tab instance
 *
 * @var array
 */
$addons = apply_filters(
	'tribe_addons_tab',
	[
		'priority' => 50,
		'fields'   => $fields,
	]
);

// Only create the Add-ons Tab if there is any
if ( ! empty( $internal ) ) {
	new Tribe__Settings_Tab( 'addons', esc_html__( 'Integrations', 'the-events-calendar' ), $addons );
}
