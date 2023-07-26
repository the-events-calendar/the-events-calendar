/**
 * Internal dependencies
 */
import { wpData, postObjects } from '@moderntribe/common/utils/globals';
import { editor } from '@moderntribe/common/data';
import { store } from '@moderntribe/common/store';
import {
	reducer as datetimeReducer,
	selectors as datetimeSelectors,
} from './datetime';
import {
	reducer as priceReducer,
	selectors as priceSelectors,
} from './price';
import {
	reducer as venueReducer,
	selectors as venueSelectors,
} from './venue';
import {
	reducer as websiteReducer,
	selectors as websiteSelectors,
} from './website';

const {
	select: wpSelect,
	dispatch: wpDispatch,
	subscribe: wpSubscribe,
} = wpData;

/**
 * Set meta for given key.
 *
 * @exports
 * @param {Object}   map      Map of state key to meta key.
 * @param {string}   mapKey   State key for map.
 * @param {Function} selector Selector to get block state.
 * @returns {Object} Object of post meta to be saved.
 */
export const setMeta = ( map, mapKey, selector ) => {
	const metaKey = map[ mapKey ];
	const blockState = selector( store.getState() );
	return { [ metaKey ]: blockState[ mapKey ] };
};

/**
 * Set meta for given block.
 *
 * @param {Object} blockToMapAndSelectorMap Map of block to state and meta map and selector.
 * @param {string} blockKey                 Block key for map.
 * @returns {Object} Object of post meta to be saved for given block.
 */
export const setBlockMeta = ( blockToMapAndSelectorMap, blockKey ) => {
	const [ map, selector ] = blockToMapAndSelectorMap[ blockKey ];
	const mapKeys = Object.keys( map );

	return mapKeys.reduce( ( prevValue, mapKey ) => ( {
		...prevValue,
		...setMeta( map, mapKey, selector ),
	} ), {} );
};

/**
 * Subscribe to WP store.
 */
const subscribe = () => {
	if ( ! postObjects().tribe_events.is_new_post ) {
		return;
	}

	const unsubscribe = wpSubscribe( () => {
		if ( ! wpSelect( 'core/editor' ).isEditedPostDirty() ) {
			return;
		}

		unsubscribe();

		const blockToMapAndSelectorMap = {
			datetime: [ datetimeReducer.defaultStateToMetaMap, datetimeSelectors.datetimeSelector ],
			price: [ priceReducer.defaultStateToMetaMap, priceSelectors.getPriceBlock ],
			venue: [ venueReducer.defaultStateToMetaMap, venueSelectors.venueBlockSelector ],
			website: [ websiteReducer.defaultStateToMetaMap, websiteSelectors.getWebsiteBlock ],
		};
		const blockKeys = Object.keys( blockToMapAndSelectorMap );
		const postId = wpSelect( 'core/editor' ).getCurrentPostId();

		const meta = blockKeys.reduce( ( prevValue, blockKey ) => ( {
			...prevValue,
			...setBlockMeta( blockToMapAndSelectorMap, blockKey ),
		} ), {} );

		wpDispatch( 'core' ).editEntityRecord( 'postType', editor.EVENT, postId, { meta } );
	} );
};

export default subscribe;
