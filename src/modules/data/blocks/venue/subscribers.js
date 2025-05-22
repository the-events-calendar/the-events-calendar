/**
 * External dependencies
 */
import { differenceBy } from 'lodash';

/**
 * Internal dependencies
 */
import { globals } from '@moderntribe/common/utils';
import { store } from '@moderntribe/common/store';
import { actions as formActions } from '@moderntribe/common/data/forms';
import { actions as venueActions, selectors as venueSelectors } from '@moderntribe/events/data/blocks/venue';
import { syncVenuesWithPost } from '@moderntribe/events/blocks/event-venue/data/meta-sync';
import { actions as requestActions } from '@moderntribe/common/store/middlewares/request';

const { getState, dispatch } = store;

/**
 * Returns criteria for comparing blocks.
 *
 * @exports
 * @param {Object} block Object with block attributes and data.
 * @return {string} Client ID of the block.
 */
export const compareBlocks = ( block ) => block.clientId;

/**
 * Checks whether the block is the venue block.
 *
 * @exports
 * @param {Object} block Object with block attributes and data.
 * @return {boolean} Whether the block is the venue block or not.
 */
export const isVenueBlock = ( block ) => block.name === 'tribe/event-venue';

/**
 * Moves the venue to the trash if appropriate (if it is a draft and was removed).
 *
 * @since 6.2.0
 * @param {number} venue
 */
globals.wpHooks.addAction(
	'tec.events.blocks.venue.maybeRemoveVenue',
	'tec.events.blocks.venue.subscribers',
	( venue ) => {
		const path = `tribe_venue/${ venue }`;
		const options = {
			path,
			actions: {
				success: formActions.deleteEntry( dispatch )( path ),
			},
		};
		dispatch( requestActions.wpRequest( options ) );
	}
);

/**
 * Handles the block that was added.
 *
 * @exports
 * @param {Object} block Object with block attributes and data.
 */
export const handleBlockAdded = ( block ) => {
	// only handle event venue block addition
	if ( ! isVenueBlock( block ) ) {
		return;
	}

	// return early if venue attribute is empty
	if ( ! block.attributes.venue ) {
		return;
	}

	dispatch( venueActions.addVenueInBlock( block.clientId, block.attributes.venue ) );
};

/**
 * Handles the block that was removed.
 *
 * @exports
 * @return {Function} Function that handles the block that was removed.
 */
export const handleBlockRemoved = () => ( block ) => {
	// only handle event venue block removal
	if ( ! isVenueBlock( block ) ) {
		return;
	}

	const venue = venueSelectors.getVenueByClientId( getState(), block );

	// remove venue from block state
	if ( venue ) {
		dispatch( venueActions.removeVenueInBlock( block.clientId, venue ) );

		/**
		 * Moves the venue to the trash if appropriate (if it is a draft and was removed).
		 *
		 * @since 6.2.0
		 * @param {number} venue
		 */
		globals.wpHooks.doAction( 'tec.events.blocks.venue.maybeRemoveVenue', venue );
	}

	syncVenuesWithPost();
};

/**
 * Handles changes in the blocks in the editor.
 *
 * @exports
 * @param {Array} currBlocks Array of current blocks in the editor.
 * @param {Array} prevBlocks Array of previous blocks in the editor.
 */
export const onBlocksChangeHandler = ( currBlocks, prevBlocks ) => {
	const blocksAdded = differenceBy( currBlocks, prevBlocks, compareBlocks );
	const blocksRemoved = differenceBy( prevBlocks, currBlocks, compareBlocks );

	if ( blocksAdded.length ) {
		blocksAdded.forEach( handleBlockAdded );
	}

	if ( blocksRemoved.length ) {
		blocksRemoved.forEach( handleBlockRemoved( currBlocks ) );
	}
};

/**
 * Listener for blocks change in the editor.
 *
 * @exports
 * @param {Function} selector Selector function to get current blocks.
 * @return {Function} Listener that subscribes to WP store.
 */
export const onBlocksChangeListener = ( selector ) => {
	let holdBlocks = selector();
	return () => {
		const prevBlocks = holdBlocks;
		const currBlocks = selector();
		holdBlocks = currBlocks;

		if ( prevBlocks.length !== currBlocks.length || differenceBy( currBlocks, prevBlocks, compareBlocks ).length ) {
			onBlocksChangeHandler( currBlocks, prevBlocks );
		}
	};
};

/**
 * @function subscribe
 * @description This subscribes to any changes in the wp data store.
 *              Since state and attribute changes should not be called
 *              in `componentDidMount()` and `componentWillUnmount()`,
 *              global state changes (tribe common store) and meta changes
 *              are handled in a global subscriber.
 */
const subscribe = () => {
	globals.wpData.subscribe( onBlocksChangeListener( globals.wpDataSelectCoreEditor().getBlocks ) );
};

export default subscribe;
