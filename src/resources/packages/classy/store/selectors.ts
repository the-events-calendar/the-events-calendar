import { select } from '@wordpress/data';
import { StoreState } from '../types/StoreState';

/**
 * Returns an attribute of the currently edited post.
 *
 * @since TBD
 *
 * @param {StoreState} state The current store state.
 * @param {string}     attribute The attribute to fetch from the store.
 *
 * @return {any} The attribute value fetched from the `core/editor` store if available, else the `tec/classy` store.
 */
export function getEditedPostAttribute(
	state: StoreState,
	attribute: string
): string {
	const coreEditor = select( 'core/editor' );

	if ( coreEditor ) {
		// @ts-ignore
		return coreEditor.getEditedPostAttribute( attribute ) ?? null;
	}

	return state?.[ attribute ] ?? '';
}
