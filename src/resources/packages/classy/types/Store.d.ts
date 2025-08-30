import { TECSettings } from './Settings';
import { EventMeta } from './EventMeta';
import { EventDateTimeDetails } from './EventDateTimeDetails';

export type StoreState = {
	areTicketsSupported?: boolean;
	isUsingTickets?: boolean;
};

/**
 * The type that should be assigned to the return value of the `select('tec/classy/events')` call.
 *
 * @example
 * ```
 * const tecStore: StoreSelect = select('tec/classy/events');
 * ```
 */
export type StoreSelect = {
	areTicketsSupported: () => boolean;
	getEditedPostOrganizerIds: () => number[];
	getEditedPostVenueIds: () => number[];
	getEventDateTimeDetails: () => EventDateTimeDetails;
	getPostMeta: () => EventMeta;
	getSettings: () => TECSettings;
	getVenuesLimit: () => number;
	isNewEvent: () => boolean;
	isUsingTickets: () => boolean;
};

/**
 * The type that should be assigned to the return value of the `dispatch('tec/classy/events')` call.
 *
 * @example
 * ```
 * const tecStore: StoreDispatch = dispatch('tec/classy/events');
 * ```
 */
export type StoreDispatch = {
	setIsUsingTickets: ( isUsing: boolean ) => void;
	setTicketsSupported: ( areSupported: boolean ) => void;
};
