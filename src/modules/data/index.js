/**
 * External dependencies
 */
import reducer from './reducers';

import { plugins } from '@moderntribe/common/data';
import { store } from '@moderntribe/common/store';

export const initStore = () => {
	const { dispatch, injectReducers } = store;

	dispatch( plugins.actions.addPlugin( 'events' ) );
	injectReducers( { events: reducer } );
};

export const getStore = () => store;
