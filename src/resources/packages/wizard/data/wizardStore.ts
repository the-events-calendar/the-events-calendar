import { registerStore } from '@wordpress/data';

// bringing in the api pieces from other files in the app
import * as selectors from './selectors';
import * as actions from './actions';
import * as resolvers from './resolvers';
import reducer from './reducer';
import controls from './controls';

const STORE_NAME = 'TECWizardStore/data';

registerStore(
	STORE_NAME,
	{
		selectors,
		actions,
		resolvers,
		controls
	}
);

export { STORE_NAME };
