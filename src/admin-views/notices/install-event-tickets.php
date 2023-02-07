<?php
/**
 * View: Install `Event Tickets` notice.
 *
 * @since 6.0.9
 *
 * @var string $action           The notice action (`install` or `activate`).
 * @var string $title            The notice title.
 * @var string $description      The notice description.
 * @var string $button_label     The notice button label.
 * @var string $tickets_logo     The `Event Tickets` button for the notice.
 * @var string $redirect_url     The redirect_url for the action after install.
 */
use TEC\Common\StellarWP\Installer\Installer;

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

	<?php	Installer::get()->render_plugin_button( 'event-tickets', $action, $button_label, $redirect_url ); ?>

</div>
