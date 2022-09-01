/**
 * Internal dependencies
 */
import * as reducer from './reducer';
import * as types from './types';
import * as actions from './actions';
import * as selectors from './selectors';
import sagas from './sagas';

export default reducer.default;
export {
	reducer,
	types,
	actions,
	selectors,
	sagas,
};
