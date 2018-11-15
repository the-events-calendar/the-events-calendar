/**
 * External dependencies
 */
import { isEmpty } from 'lodash';

/**
 * Internal dependencies
 */
import * as actions from './actions';
import * as selectors from './selectors';
import { middlewares } from '@moderntribe/common/store';

export const fetchDetails = ( id ) => ( dispatch, getState ) => {
	const state = getState();
	const props = { name: id };
	const isLoading = selectors.getIsLoading( state, props );
	const details = selectors.getDetails( state, props );

	if ( ! isEmpty( details ) || isLoading ) {
		return;
	}

	const postType = selectors.getPostType( state, props );
	const options = {
		path: `${ postType }/${ id }`,
		actions: {
			start: () => dispatch( actions.enableDetailsIsLoading( id ) ),
			success: ( { body } ) => {
				dispatch( actions.setDetails( id, body ) );
				dispatch( actions.disableDetailsIsLoading( id ) );
			},
			error: () => dispatch( actions.disableDetailsIsLoading( id ) ),
		},
	};

	dispatch( middlewares.request.actions.wpRequest( options ) );
};
