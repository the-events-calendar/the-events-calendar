import { act } from '@testing-library/react';

/**
 * Fires a keydown event for the Escape key on the given element.
 *
 * WordPress listeners on the Escape key press event are checking an event property, `keyCode`, which is deprecated.
 * The `@testing-library/user-event` library does not set `keyCode`, so we need to manually create and dispatch the
 * event.
 *
 * In place of this code:
 * ```
 * const user = userEvent.setup();
 * await user.type(element, '{Escape}');
 * ```
 *
 * Use:
 * ```
 * await keyDownEscape(element);
 * ```
 *
 * This function creates a new KeyboardEvent with the 'keydown' type and sets its properties accordingly.
 *
 * @param element {Element} The element on which to dispatch the Escape event.
 *
 * @return Promise<boolean> A promise that resolves to the result of the `dispatchEvent` call.
 */
export async function keyDownEscape( element: Element ): Promise< boolean > {
	const keyDownEscapeEvent = new KeyboardEvent( 'keydown', {
		key: 'Escape',
		code: 'Escape',
		keyCode: 27, // While deprecated, this is the property the dialog WordPress logic is actually using.
		bubbles: true,
		cancelable: true,
	} );

	// Wrap the call in `act` to allow React to process all the interaction units in the queue.
	return await act( () => element.dispatchEvent( keyDownEscapeEvent ) );
}
