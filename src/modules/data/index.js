/**
 * External dependencies
 */
import reducer from './reducers';

import { actions } from '@moderntribe/common/data/plugins';
import { store } from '@moderntribe/common/store';

export const initStore = () => {
	const { dispatch, injectReducers } = store;

	dispatch( actions.addPlugin( 'events' ) );
	injectReducers( { events: reducer } );
};

export const getStore = () => store;
