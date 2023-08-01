/**
 * Internal dependencies
 */
import { selectors } from '@moderntribe/events/data/blocks/venue';
import { wpData } from '@moderntribe/common/utils/globals';

/**
 * Synchronizes venues in the state with the meta of the post.
 *
 * @since TBD
 */
export const syncVenuesWithPost = () => {
	let currentPost = wpData.select( 'core/editor' ).getCurrentPost();
	let modifiedPost = {
		...currentPost,
		meta: {
			...currentPost.meta,
			_EventVenueID: selectors.getVenuesInBlock( getState() ),
		}
	};

	wpData.dispatch( 'core/editor' ).editPost( modifiedPost );
};