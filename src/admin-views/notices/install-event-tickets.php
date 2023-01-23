<?php
/**
 * View: Install `Event Tickets` notice.
 *
 * @since TBD
 *
 * @var string $plugin_slug      The plugin slug for the install/activation notice.
 * @var string $action           The notice action (`install` or `activate`).
 * @var string $title            The notice title.
 * @var string $description      The notice description.
 * @var string $button_label     The notice button label.
 * @var string $tickets_logo     The `Event Tickets` button for the notice.
 * @var string $ajax_nonce       The AJAX nonce.
 * @var string $redirect_url     The redirect_url for the action after install.
 * @var string $installing_label The `Installing` label.
 * @var string $installed_label  The `Installed` label.
 * @var string $activating_label The `Activating` label.
 * @var string $activated_label  The `Activated` label.
 */
$button_classes = [
	'components-button',
	'is-primary',
	'tec-admin__notice-install-content-button',
];
?>
<div class="tec-admin__notice-install-aside">
	<img
		src="<?php echo esc_url( $tickets_logo ); ?>"
		alt="<?php esc_attr_e( 'Event Tickets', 'the-events-calendar' ); ?>"
	/>
</div>
<div class="tec-admin__notice-install-content">

	<h3 class="tec-admin__notice-install-content-title"><?php echo esc_html( $title ); ?></h3>

	<div class="tec-admin__notice-install-content-description">
		<?php echo wpautop( esc_html( $description ) ); ?>
	</div>

	<button
		id="tribe-tickets-install-plugin"
		<?php tribe_classes( $button_classes ); ?>
		data-plugin-slug="<?php echo esc_attr( $plugin_slug ); ?>"
		data-nonce="<?php echo esc_attr( $ajax_nonce ); ?>"
		data-action="<?php echo esc_attr( $action ); ?>"
		data-redirect-url="<?php echo esc_attr( $redirect_url ); ?>"
		data-installing-label="<?php echo esc_attr( $installing_label ); ?>"
		data-installed-label="<?php echo esc_attr( $installed_label ); ?>"
		data-activating-label="<?php echo esc_attr( $activating_label ); ?>"
		data-activated-label="<?php echo esc_attr( $activated_label ); ?>"
	><?php echo esc_html( $button_label ); ?></button>

</div>
