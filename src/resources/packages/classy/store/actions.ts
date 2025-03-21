import { PostUpdates } from '../types/PostUpdates';
import { dispatch } from '@wordpress/data';
import { ACTION_CLASSY_EDIT_POST } from './constants';
import { EditPostAction } from '../types/Actions';

/**
 * Builds the action required to update the Classy store and dispatches an update
 * to the `core/editor` store (if available) while doing it.
 *
 * @since TBD
 *
 * @return {EditPostAction} The action to dispatch.
 */
export function editPost( updates: PostUpdates ): EditPostAction {
	// @ts-ignore
	dispatch( 'core/editor' )?.editPost( updates );

	return {
		type: ACTION_CLASSY_EDIT_POST,
		updates,
	};
}
