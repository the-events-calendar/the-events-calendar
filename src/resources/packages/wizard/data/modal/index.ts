import * as selectors from './selectors';
import * as actions from './actions';
import reducer from './reducer';

export { MODAL_STORE_KEY } from './constants';

export const MODAL_STORE_CONFIG = {
	actions,
	reducer,
	selectors,
};
