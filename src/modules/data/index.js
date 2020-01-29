/**
 * External dependencies
 */
import reducer from './reducers';

import { globals } from '@moderntribe/common/utils';
import { plugins } from '@moderntribe/common/data';
import { store } from '@moderntribe/common/store';
import * as blocks from './blocks';
import initSagas from './sagas';

const setInitialState = () => {
	blocks.setInitialState();
};

export const initStore = () => {
	const unsubscribe = wp.data.subscribe( () => {
		if ( ! globals.wpCoreEditor.__unstableIsEditorReady() ) {
			return;
		}

		unsubscribe();

		setInitialState();

		const { dispatch, injectReducers } = store;

		initSagas();
		dispatch( plugins.actions.addPlugin( 'events' ) );
		injectReducers( { events: reducer } );
	} );
};

export const getStore = () => store;

export { blocks };
