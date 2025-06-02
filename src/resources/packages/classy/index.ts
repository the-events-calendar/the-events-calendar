import { addFilter, addAction, didAction } from '@wordpress/hooks';
import renderFields from './functions/renderFields';
import { STORE_NAME, storeConfig } from './store';
import { WPDataRegistry } from '@wordpress/data/build-types/registry';
import { getRegistry } from '@tec/common/classy/store';

// Hook on the Classy application initialization to add TEC store to the Classy registry.
// @todo move this to a better place.
const registerTecStore = () => {
	( getRegistry() as WPDataRegistry ).registerStore( STORE_NAME, storeConfig );
};
if ( didAction( 'tec.classy.initialized' ) ) {
	registerTecStore();
} else {
	addAction( 'tec.classy.initialized', 'tec.classy.events', registerTecStore );
}

// Hook on the Classy fields rendering logic to render the fields.
addFilter( 'tec.classy.render', 'tec.classy.events', renderFields );
