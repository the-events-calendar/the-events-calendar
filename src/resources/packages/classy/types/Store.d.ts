import {TECSettings} from './Settings';
import {EventMeta} from "./EventMeta";
import {EventDateTimeDetails} from "./EventDateTimeDetails";

export type StoreState = {
	areTicketsSupported?: boolean;
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
	getSettings: () => TECSettings;
	getPostMeta: () => EventMeta;
	getEventDateTimeDetails: () => EventDateTimeDetails;
	getEditedPostOrganizerIds: () => number[];
	getEditedPostVenueIds: () => number[];
	areTicketsSupported: (state: StoreState) => boolean;
	isNewEvent: () => boolean;
	getVenuesLimit: () => number
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
};
