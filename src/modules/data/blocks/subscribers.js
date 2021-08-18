/**
 * Internal dependencies
 */
import { wpData, postObjects } from '@moderntribe/common/utils/globals';
import { editor } from '@moderntribe/common/data';
import { store } from '@moderntribe/common/store';
import {
	defaultStateToMetaMap as datetimeMap,
	selectors as datetimeSelectors,
} from './datetime';
import {
	defaultStateToMetaMap as priceMap,
	selectors as priceSelectors,
} from './price';
import {
	defaultStateToMetaMap as venueMap,
	selectors as venueSelectors,
} from './venue';
import {
	defaultStateToMetaMap as websiteMap,
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
 * @param {object}   map      Map of state key to meta key.
 * @param {string}   mapKey   State key for map.
 * @param {Function} selector Selector to get block state.
 * @returns {object} Object of post meta to be saved.
 */
export const setMeta = ( map, mapKey, selector ) => {
	const metaKey = map[ mapKey ];
	const blockState = selector( store.getState() );
	return { [ metaKey ]: blockState[ mapKey ] };
};

/**
 * Set meta for given block.
 *
 * @param {object} blockToMapAndSelectorMap Map of block to state and meta map and selector.
 * @param {string} blockKey                 Block key for map.
 * @returns {object} Object of post meta to be saved for given block.
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
			datetime: [ datetimeMap, datetimeSelectors.datetimeSelector ],
			price: [ priceMap, priceSelectors.getPriceBlock ],
			venue: [ venueMap, venueSelectors.venueBlockSelector ],
			website: [ websiteMap, websiteSelectors.getWebsiteBlock ],
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
