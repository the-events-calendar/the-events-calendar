import { ACTION_CLASSY_EDIT_POST } from './constants';
import { StoreState } from '../types/StoreState';
import { Action, EditPostAction } from '../types/Actions';

/**
 * Updates the store when receiving a post update action.
 *
 * @param {StoreState} state The current store state.
 * @param {Action}    action The dispatched action.
 *
 * @return {StoreState} The updated store state.
 */
export const reducer = (
	state: StoreState = {},
	action: Action
): StoreState => {
	if ( action.type === ACTION_CLASSY_EDIT_POST ) {
		return {
			...state,
			...( action as EditPostAction ).updates,
		};
	}

	return state;
};
