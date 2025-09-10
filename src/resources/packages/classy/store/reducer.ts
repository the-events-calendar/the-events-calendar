import { Action } from '@tec/common/classy/types/Actions';
import { Reducer } from "redux";
import { StoreState } from '../types/Store';
import {
	SET_IS_USING_TICKETS,
	SET_TICKETS_SUPPORTED,
	SetIsUsingTicketsAction,
	SetTicketsSupportedAction,
} from '../types/Actions';

const initialState: StoreState = {
	areTicketsSupported: false,
	isUsingTickets: false,
};

export const reducer: Reducer< StoreState > = ( state: StoreState = initialState, action: Action ): StoreState => {
	switch ( action.type ) {
		case SET_IS_USING_TICKETS:
			return {
				...state,
				isUsingTickets: ( action as SetIsUsingTicketsAction ).isUsing,
			};

		case SET_TICKETS_SUPPORTED:
			return {
				...state,
				areTicketsSupported: ( action as SetTicketsSupportedAction ).supported,
			};

		default:
			return state;
	}
};
