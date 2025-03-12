/**
 * The cached value of the original Visual Editor height.
 *
 * @since TBD
 *
 * @type {number|null|string} The original Visual Editor height.
 */
let originalVisualEditorHeight: string | null = null;

/**
 * Returns the visual editor element from a given document.
 *
 * @param {Document|null} document - The document to operate within.
 *     If null, the global document will be used.
 *
 * @return {HTMLElement|null} The visual editor element or null if it could not be found.
 */
export function getElement(
	document: Document | null = null
): HTMLElement | null {
	document = document ?? window.document;
	return document.querySelector(
		'.editor-visual-editor.edit-post-visual-editor'
	) as HTMLElement | null;
}

/**
 * Toggles the visibility of the visual editor.
 *
 * This function will toggle the visibility of the visual editor by setting its height
 * to 0 or the original value depending on whether it is currently visible or not.
 *
 * @since TBD
 *
 * @param {Document|null} document - The document to operate within.
 *     If null, the global document will be used.
 *
 * @return {boolean} True if the Visual Editor visibility was toggled, false if the Visual Editor element was not found.
 */
export function toggleVisibility( document: Document | null = null ): boolean {
	const visualEditor = getElement( document );

	if ( visualEditor === null ) {
		return false;
	}

	originalVisualEditorHeight =
		originalVisualEditorHeight ?? visualEditor.style.height;

	if ( visualEditor.style.height === originalVisualEditorHeight ) {
		visualEditor.style.height = '0';
		visualEditor.setAttribute( 'aria-hidden', 'true' );
	} else {
		visualEditor.style.height = originalVisualEditorHeight;
		visualEditor.removeAttribute( 'aria-hidden' );
	}

	return true;
}
