<div id="tec-events-integration-elementor-warn-about-switch-button">
	<?php esc_html_e( 'This event needs to be saved before switching to Elementor.', 'the-events-calendar' ); ?>
</div>

<script>
	document.getElementById( 'elementor-switch-mode-button' ).setAttribute( 'disabled', 'disabled' );
	document.getElementById( 'elementor-switch-mode' ).appendChild( document.getElementById( 'tec-events-integration-elementor-warn-about-switch-button' ) );
</script>
