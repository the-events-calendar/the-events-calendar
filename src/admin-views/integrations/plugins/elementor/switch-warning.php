<?php
/**
 * Template for the Elementor editor notice.
 *
 * This template is used to inject a helper message to the post editor to help users understand that they are must save before switching to Elementor.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */

?>
<div id="tec-events-integration-elementor-warn-about-switch-button">
	<?php esc_html_e( 'This event needs to be saved before switching to Elementor.', 'the-events-calendar' ); ?>
</div>

<script>
	document.getElementById( 'elementor-switch-mode-button' ).setAttribute( 'disabled', 'disabled' );
	document.getElementById( 'elementor-switch-mode' ).appendChild( document.getElementById( 'tec-events-integration-elementor-warn-about-switch-button' ) );
</script>
