import { useDispatch, useSelect } from '@wordpress/data';
import { UsePostEditsReturn } from '../types/UsePostEditsReturn';
import { STORE_NAME } from '../store';

/**
 * Custom hook to get and set the post attributes.
 *
 * @since TBD
 *
 * This hook exposes an API that mimics the `core/editor` one and acts as a middleware
 * that will store and retrieve information from both the `core/editor` and the `tec/classy` stores.
 * The `core/editor` store, though, might not be available (e.g. in Community Events); in this case
 * the hook will store and retrieve information from the `tec/classy` store.
 *
 * @returns {UsePostEditsReturn} The hook API.
 */
export function usePostEdits(): UsePostEditsReturn {
	const { postTitle, postContent, meta } = useSelect( ( select ) => {
		const classySelect = select( 'tec/classy' );

		return {
			// @ts-ignore
			postTitle: classySelect.getEditedPostAttribute( 'title' ) || '',
			// @ts-ignore
			postContent: classySelect.getEditedPostContent() || '',
			// @ts-ignore
			meta: classySelect.getEditedPostAttribute( 'meta' ) || {},
		};
	}, [] );

	const { editPost } = useDispatch( STORE_NAME );

	return {
		postTitle,
		postContent,
		meta,
		editPost,
	};
}
