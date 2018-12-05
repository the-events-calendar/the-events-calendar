/**
 * External dependencies
 */
import reducer from './reducers';

import { plugins } from '@moderntribe/common/data';
import { store } from '@moderntribe/common/store';
import * as blocks from './blocks';
import initSagas from './sagas';

export const initStore = () => {
	const { dispatch, injectReducers } = store;

	initSagas();
	dispatch( plugins.actions.addPlugin( 'events' ) );
	injectReducers( { events: reducer } );
};

export const getStore = () => store;

export { blocks };
