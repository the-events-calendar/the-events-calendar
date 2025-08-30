import { reducer } from './reducer';
import { actions } from './actions';
import * as selectors from './selectors';
import { StoreState } from '../types/Store';

const initialState: StoreState = {
	areTicketsSupported: false,
	isUsingTickets: false,
};

export const storeConfig = {
	reducer,
	actions,
	selectors,
	initialState,
};
