/**
 * External dependencies
 */
import reducer from './reducers';

import { globals } from '@moderntribe/common/utils';
import { plugins } from '@moderntribe/common/data';
import { store } from '@moderntribe/common/store';
import * as blocks from './blocks';
import initSagas from './sagas';

const { actions, constants } = plugins;

const setInitialState = ( entityRecord ) => {
	blocks.setInitialState( entityRecord );
};

export const initStore = () => {
	const data = globals.postObjects().tribe_events;

	if ( ! data.is_new_post ) {
		setInitialState( data );
	}

	const { dispatch, injectReducers } = store;

	initSagas();
	injectReducers( { [ constants.EVENTS_PLUGIN ]: reducer } );
	dispatch( actions.addPlugin( constants.EVENTS_PLUGIN ) );
};

export const getStore = () => store;

export { blocks };
