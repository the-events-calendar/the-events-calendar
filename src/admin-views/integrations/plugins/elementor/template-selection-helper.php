<?php
/**
 * Template for the Elementor template selection helper.
 *
 * This template is used to inject a helper message to the Elementor editor to help users understand that they are editing,
 * the JavaScript and CSS are inline to avoid having the loading complexity for Elementor Edit area.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */

?>
<style>
	.tec-events-elementor-template-selection-helper {
		width: 100%;
		margin-bottom: 10px;
	}
</style>
<script>
	( () => {
		const singleEventTemplateWrapper = document.getElementById( 'tribe-events-pg-template' );

		// Only continue if we have this element.
		if ( 0 === singleEventTemplateWrapper.length ) {
			return;
		}
		let injected = false;

		const helperContainer = document.createElement( 'div' );
		const text = document.createTextNode( '<?php esc_html_e( 'Looking for the Event template? Click the folder and navigate to My Templates.', 'the-events-calendar' ); ?>' );
		helperContainer.appendChild( text );
		helperContainer.classList.add( 'tec-events-elementor-template-selection-helper' );

		const mutationObserverCallback = ( mutationsList, observer ) => {
			// Already injected, no need to do it again.
			if ( injected ) {
				return;
			}

			const includedElementorEditArea = mutationsList.some( ( mutation ) => {
				return mutation.target.classList.contains( 'elementor-edit-area' );
			} );

			if ( false === includedElementorEditArea ) {
				return;
			}

			injected = true;

			document.querySelector( '.elementor-add-new-section' ).prepend( helperContainer );
		};
		const observer = new MutationObserver( mutationObserverCallback );

		// Actually do the observing of the DOM.
		observer.observe( singleEventTemplateWrapper, { childList: true, characterData: false, subtree: true, attributes: false } );
	} )();
</script>
