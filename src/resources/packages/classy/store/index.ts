import { reducer } from './reducer';
import * as actions from './actions';
import * as selectors from './selectors';

export const STORE_NAME = 'tec/classy/events';

export const storeConfig = {
	reducer,
	actions,
	selectors,
};
