/**
 * External dependencies
 */
import reducer from './reducers';

import { globals } from '@moderntribe/common/utils';
import { plugins } from '@moderntribe/common/data';
import { store } from '@moderntribe/common/store';
import * as blocks from './blocks';
import initSagas from './sagas';

const setInitialState = ( entityRecord ) => {
	blocks.setInitialState( entityRecord );
};

export const initStore = () => {
	const unsubscribe = globals.wpData.subscribe( () => {
		if ( ! globals.wpCoreEditor.__unstableIsEditorReady() ) {
			return;
		}

		unsubscribe();

		if ( ! globals.wpCoreEditor.isCleanNewPost() ) {
			const postId = globals.wpCoreEditor.getCurrentPostId();
			const entityRecord = globals.wpCore.getEntityRecord( 'postType', 'tribe_events', postId );

			setInitialState( entityRecord );
		}

		const { dispatch, injectReducers } = store;

		initSagas();
		dispatch( plugins.actions.addPlugin( 'events' ) );
		injectReducers( { events: reducer } );
	} );
};

export const getStore = () => store;

export { blocks };
