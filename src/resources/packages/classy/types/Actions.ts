import { Action } from 'redux';

export const SET_IS_USING_TICKETS = 'SET_IS_USING_TICKETS';
export const SET_TICKETS_SUPPORTED = 'SET_TICKETS_SUPPORTED';

export type SetIsUsingTicketsAction = {
	type: typeof SET_IS_USING_TICKETS;
	isUsing: boolean;
} & Action< typeof SET_IS_USING_TICKETS >;

export type SetTicketsSupportedAction = {
	type: typeof SET_TICKETS_SUPPORTED;
	supported: boolean;
} & Action< typeof SET_TICKETS_SUPPORTED >;
