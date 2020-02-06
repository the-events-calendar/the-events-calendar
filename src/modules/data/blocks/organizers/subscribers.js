/**
 * External dependencies
 */
import { differenceBy } from 'lodash';

 /**
 * Internal dependencies
 */
import { globals } from "@moderntribe/common/utils";

const compareBlocks = block => block.clientId;

const onBlocksChangeHandler = ( currBlocks, prevBlocks ) => {
	const blocksAdded = differenceBy( currBlocks, prevBlocks, compareBlocks );
	const blocksRemoved = differenceBy( prevBlocks, currBlocks, compareBlocks );
	// do stuff here
};

const onBlocksChangeListener = ( selector ) => {
	let prevBlocks = selector();
	return () => {
		const currBlocks = selector();

		if ( ! prevBlocks.length ) {
			// return if prevBlocks is empty as this is initial load of blocks or
			// all blocks are removed and default paragraph block was added
			return;
		}

		if (
			prevBlocks.length !== currBlocks.length ||
			differenceBy( currBlocks, prevBlocks, compareBlocks ).length
		) {
			// deal with stuff
			onBlocksChangeHandler( currBlocks, prevBlocks )
			console.log('prevBlocks:', prevBlocks);
			console.log('currBlocks:', currBlocks);
			prevBlocks = currBlocks;
		}

	};
}

const subscribe = () => {
	globals.wpData.subscribe(
		onBlocksChangeListener(
			globals.wpData.select( 'core/editor' ).getBlocks
		)
	);
};

export default subscribe;
