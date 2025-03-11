import {createRoot} from '@wordpress/element';
import {Classy} from "../elements";

/**
 * Cached instance of the classy element.
 * Acts as a singleton to avoid creating multiple instances of the same element.
 *
 * @since TBD
 *
 * @type {HTMLElement|null}
 */
let classyElement: HTMLElement | null = null;

/**
 * Returns an existing Classy element or creates a new one if it doesn't exist.
 *
 * This function ensures that only one Classy element exists throughout the application
 * by caching the reference in the `classyElement` variable.
 *
 * @since TBD
 *
 * @param {Document|null} document - The document to use for element creation.
 *     If null, the global document will be used.
 * @returns {HTMLElement} The existing or newly created Classy element.
 */
export function getOrCreateElement(document: Document | null = null): HTMLElement {
	document = document || window.document;

	if (classyElement !== null) {
		return classyElement;
	}

	const element = document.createElement('div');
	element.id = 'tec-classy';
	element.classList.add('classy-root', 'classy-root--admin');
	classyElement = element;

	return element;
}

/**
 * Inserts the Classy element into the DOM after the visual editor.
 *
 * This function will get or create the Classy element and insert it into the DOM
 * immediately after the visual editor element. If the visual editor cannot be found,
 * the function will return false indicating failure.
 *
 * @since TBD
 *
 * @param {Document|null} document - The document to operate within.
 *     If null, the global document will be used.
 *
 * @returns {boolean} True if insertion was successful, false otherwise.
 */
export function insertElement(document: Document | null = null): boolean {
	document = document || window.document;
	const element = getOrCreateElement(document);
	const visualEditor = document.querySelector('.editor-visual-editor');

	if (!visualEditor) {
		return false;
	}

	visualEditor.parentNode.insertBefore(element, visualEditor.nextSibling);

	return true;
}

/**
 * Toggles the visibility of the Classy element.
 *
 * This function adds or removes the 'classy-root--hidden' class from the Classy element,
 * which should be styled with CSS to show or hide the element accordingly.
 * The element will be created if it doesn't already exist.
 *
 * @since TBD
 *
 * @returns {void}
 */
export function toggleElementVisibility(): void {
	getOrCreateElement().classList.toggle('classy-root--hidden');
}

/**
 * Initializes the application by creating and rendering the Classy root element.
 *
 * This function ensures that the Classy element is either retrieved or created
 * and then initializes the React application by rendering the `Classy` component
 * into that element using React's `createRoot` API.
 *
 * @since TBD
 *
 * @param {Document|null} document - The document to use for element creation.
 *	 If null, the global document will be used.
 *
 * @returns {void}
 */
export function initApp(document: Document | null = null): void {
	document = document || window.document;
	const classyRoot = createRoot(getOrCreateElement(document));
	classyRoot.render(Classy());
}
