import { createReduxStore, register } from '@wordpress/data';
import { reducer } from './reducer';
import * as actions from './actions';
import * as selectors from './selectors';

/**
 * Initializes the Classy Redux store.
 *
 * @since TBD
 *
 * @param {Object} initialState The initial state to be passed to the redux store.
 *
 * @return {void} The store is initialized and registered under the 'tec/classy' namespace.
 */
export function registerStore( initialState: Object = {} ): void {
	const store = createReduxStore( 'tec/classy', {
		reducer,
		actions,
		selectors,
		initialState,
	} );
	register( store );
}
