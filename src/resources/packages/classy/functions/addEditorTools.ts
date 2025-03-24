import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

/**
 * Adds the Editor tools inserting them among the Block Editor tools.
 *
 * This function is weird-looking to work around the lack of an official API to add/remove Editor tools.
 * The function registers a plugin to be called when the toolbar is rendered; then calls a function that looks like
 * a React Component (EditorTools) that will not actually render a component, but will use the call to insert a button
 * in the Editor tools.
 *
 * @since TBD
 *
 * @param {(this: GlobalEventHandlers, ev: MouseEvent) => void} onClick The click event to handle on press of the added
 *     button.
 * @param {Document|null} document The document to work on, or `null` to work on `window.document`.
 *
 * @return {void} The Editor tools are added to the tools bar.
 */
export function addEditorTools(
	onClick: ( this: GlobalEventHandlers, ev: MouseEvent ) => void,
	document: Document | null = null
): void {
	document = document || window.document;

	// 2. The function _should_ render a React component, but won't. It will insert a button.
	function EditorTools() {
		const editorDocumentTools = document.querySelector(
			'.editor-document-tools .editor-document-tools__left'
		);
		const previewButton = document.querySelector(
			'.tec-editor-tool--preview'
		);

		if ( editorDocumentTools && previewButton === null ) {
			const previewButton = document.createElement( 'button' );
			previewButton.classList.add(
				'tec-editor-tool',
				'tec-editor-tool--preview',
				'button'
			);
			previewButton.type = 'button';
			previewButton.dataset.toolbarItem = 'true';
			previewButton.innerHTML = `<span class="dashicons dashicons-visibility"></span>${ __(
				'Visual',
				'the-events-calendar'
			) }`;
			editorDocumentTools.append( previewButton );
			previewButton.onclick = onClick;
		}

		// 3. Do not actually render any component.
		return null;
	}

	// 1. When the time comes for plugin registration, it's the moment to insert the button.
	registerPlugin( 'tec-editor-tools', {
		render: EditorTools,
	} );
}
