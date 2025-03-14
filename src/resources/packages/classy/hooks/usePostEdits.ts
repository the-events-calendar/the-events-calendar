import { useDispatch, useSelect } from '@wordpress/data';
import { UsePostEditsReturn } from '../types/UsePostEditsReturn';

/**
 * Custom hook to get and set the post attributes.
 *
 * @since TBD
 *
 * This hook exposes an API that mimice the `core/editor` one and acts as a middleware
 * that will store and retrieve information from both the `core/editor` and the `tec/classy` stores.
 * The `core/editor` store, though, might not be available (e.g. in Community Events); in this case
 * the hook will store and retrieve information from the `tec/classy` store.
 *
 * @returns {UsePostEditsReturn} The hook API.
 */
export function usePostEdits(): UsePostEditsReturn {
	const postTitle: string = useSelect(
		// @ts-ignore - no types available.
		( select ): string => {
			// @ts-ignore
			return select( 'tec/classy' ).getEditedPostAttribute( 'title' );
		},
		[]
	);

	const { editPost } = useDispatch( 'tec/classy' );

	return {
		postTitle,
		editPost,
	};
}
