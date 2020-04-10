/**
 * Internal dependencies
 */
import reducer, { setInitialState, defaultStateToMetaMap } from './reducer';

import * as selectors from './selectors';
import * as actions from './actions';
import * as types from './types';

export default reducer;
export { selectors, actions, types, setInitialState, defaultStateToMetaMap };
