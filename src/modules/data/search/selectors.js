/**
 * External dependencies
 */
import { createSelector } from 'reselect';
import { DEFAULT_STATE } from './reducers/search';

export const blockSelector = ( state, props ) => state.events.search[ props.name ];

export const getSearchPostType = createSelector(
	[ blockSelector ],
	( block ) => block ? block.postType : DEFAULT_STATE.postType
);

export const getSearchTerm = createSelector(
	[ blockSelector ],
	( block ) => block ? block.term : DEFAULT_STATE.term
);

export const getIsLoading = createSelector(
	[ blockSelector ],
	( block ) => block ? block.isLoading : DEFAULT_STATE.isLoading,
);

export const getResults = createSelector(
	[ blockSelector ],
	( block ) => block ? block.results : DEFAULT_STATE.results,
);

export const getPage = createSelector(
	[ blockSelector ],
	( block ) => block ? block.page : DEFAULT_STATE.page,
);

export const getTotal = createSelector(
	[ blockSelector ],
	( block ) => block ? block.totalPages : DEFAULT_STATE.totalPages,
);
