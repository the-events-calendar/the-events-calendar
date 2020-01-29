/**
 * External dependencies
 */
import reducer from './reducers';

import { getDefaultState } from './blocks/price/reducer';

import { globals } from '@moderntribe/common/utils';
import { plugins } from '@moderntribe/common/data';
import { store } from '@moderntribe/common/store';
import * as blocks from './blocks';
import initSagas from './sagas';

export const initStore = () => {
	const unsubscribe = wp.data.subscribe( () => {
		if ( ! globals.wpCoreEditor.__unstableIsEditorReady() ) {
			return;
		}

		unsubscribe();

		// getDefaultState();

		const { dispatch, injectReducers } = store;

		initSagas();
		dispatch( plugins.actions.addPlugin( 'events' ) );
		injectReducers( { events: reducer } );
	} );
};

export const getStore = () => store;

export { blocks };
