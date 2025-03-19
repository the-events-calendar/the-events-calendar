import { createReduxStore } from '@wordpress/data';
import { reducer } from './reducer';
import * as actions from './actions';
import * as selectors from './selectors';

export const store = createReduxStore( 'tec/classy', {
	reducer,
	actions,
	selectors,
} );
