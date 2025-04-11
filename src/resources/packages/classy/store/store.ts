import { reducer } from './reducer';
import * as actions from './actions';
import * as selectors from './selectors';
export { createRegistry } from './registry';

export const STORE_NAME = 'tec/classy';

export const storeConfig = {
	reducer,
	actions,
	selectors,
};
