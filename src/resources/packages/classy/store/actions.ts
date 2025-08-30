import { StoreDispatch } from '../types/Store';

import {
	SET_IS_USING_TICKETS,
	SET_TICKETS_SUPPORTED,
	SetIsUsingTicketsAction,
	SetTicketsSupportedAction,
} from '../types/Actions';

/**
 * Sets whether tickets are being used in the store.
 *
 * @since TBD
 *
 * @param {boolean} isUsing Whether tickets are being used.
 * @returns {SetIsUsingTicketsAction} An action object containing the type and usage status.
 */
const setIsUsingTickets = ( isUsing: boolean ): SetIsUsingTicketsAction => ( {
	type: SET_IS_USING_TICKETS,
	isUsing,
} );

/**
 * Sets whether tickets are supported in the store.
 *
 * @since TBD
 *
 * @param {boolean} supported Whether tickets are supported.
 * @returns {SetTicketsSupportedAction} An action object containing the type and supported status.
 */
const setTicketsSupported = ( supported: boolean ): SetTicketsSupportedAction => ( {
	type: SET_TICKETS_SUPPORTED,
	supported,
} );

export const actions: StoreDispatch = {
	setIsUsingTickets,
	setTicketsSupported,
};
