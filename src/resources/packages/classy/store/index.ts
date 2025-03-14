import { createReduxStore, register, combineReducers } from '@wordpress/data';
import * as reducers from './reducers';
import * as actions from './actions';
import * as selectors from './selectors';

/**
 * Initializes the Classy Redux store.
 *
 * @since TBD
 *
 * @return {void} The store is initialized and registered under the 'tec/classy' namespace.
 */
export function init(): void {
	const store = createReduxStore( 'tec/classy', {
		reducer: combineReducers( reducers ),
		actions,
		selectors,
	} );
	register( store );
}
