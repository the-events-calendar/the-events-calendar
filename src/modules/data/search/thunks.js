/**
 * Internal dependencies
 */
import * as actions from './actions';
import * as selectors from './selectors';
import {
	middlewares
} from '@moderntribe/common/store';

const { request: {
	actions:requestActions,
	utils:requestUtils,
} } = middlewares;

// TODO: There is a lot of logic in this thunk that should be moved into
// each specific call instead. Given the function name and location,
// "search" should only search given params and handle success/error.
export const search = ( id, params ) => ( dispatch, getState ) => {
	const {
		term = '',
		exclude = [],
		perPage = 50,
		populated = false,
		page = 1,
	} = params;

	const total = selectors.getTotal( getState(), { name: id } );

	if ( total !== 0 && page > total ) {
		return;
	}

	// This logic should probably not be in here. Instead, this should be called
	// before the search call and determine whether search is called or not.
	if ( populated && term.trim() === '' ) {
		dispatch( actions.clearBlock( id ) );
		return;
	}

	const query = requestUtils.toWPQuery( {
		per_page: perPage,
		search: term,
		page,
		exclude,
	} );

	const postType = selectors.getSearchPostType( getState(), { name: id } );
	const options = {
		path: `${ postType }?${ query }`,
		actions: {
			start: () => dispatch( actions.enableSearchIsLoading( id ) ),
			success: ( { body, headers } ) => {
				if ( term !== selectors.getSearchTerm( getState(), { name: id } ) ) {
					return;
				}
				dispatch( actions.disableSearchIsLoading( id ) );
				if ( page === 1 ) {
					dispatch( actions.setResults( id, body ) );
				} else {
					dispatch( actions.addResults( id, body ) );
				}
				dispatch( actions.setPage( id, page ) );
				dispatch( actions.setTotalPages( id, requestUtils.getTotalPages( headers ) ) );
			},
			error: () => dispatch( actions.disableSearchIsLoading( id ) ),
		},
	};

	dispatch( requestActions.wpRequest( options ) );
};
