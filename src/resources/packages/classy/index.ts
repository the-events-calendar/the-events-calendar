import { addAction, addFilter, didAction, doAction } from '@wordpress/hooks';
import renderFields from './functions/renderFields';
import { storeConfig } from './store';
import { STORE_NAME } from './constants';
import { getRegistry } from '@tec/common/classy/store';
import * as constants from './constants';
import { getNewStartEndDates, getAllDayNewDates, getMultiDayDates } from './functions/events';

/**
 * Hook on the Classy application initialization to add TEC store to the Classy registry.
 *
 * @since TBD
 *
 * @return {void} The ECP store is registered.
 */
const registerStore = (): void => {
	getRegistry().registerStore( STORE_NAME, storeConfig );

	/**
	 * Fires after the TEC store is registered and the TEC Classy application is initialized.
	 *
	 * @since TBD
	 *
	 * @return {void} The TEC store is registered.
	 */
	doAction( 'tec.classy.events.initialized' );
};

if ( didAction( 'tec.classy.initialized' ) ) {
	registerStore();
} else {
	addAction( 'tec.classy.initialized', 'tec.classy.events', registerStore );
}

// Hook on the Classy fields rendering logic to render the fields.
addFilter( 'tec.classy.render', 'tec.classy.events', renderFields );

// Export constants and functions to allow their reference in other packages.
export {
	constants,
	getNewStartEndDates,
	getAllDayNewDates,
	getMultiDayDates,
};
