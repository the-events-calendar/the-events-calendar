import { select } from '@wordpress/data';
import { StoreState } from '../types/StoreState';
import { EventDateTimeDetails } from '../types/EventDateTimeDetails';
import { EventMeta } from '../types/EventMeta';
import { getDate } from '@wordpress/date';
import { localizedData } from '../localized-data';
import { Settings } from '../types/LocalizedData';

/**
 * Returns an attribute of the currently edited post.
 *
 * @since TBD
 *
 * @param {StoreState} state The current store state.
 * @param {string}     attribute The attribute to fetch from the store.
 *
 * @return {string} The attribute value fetched from the `core/editor` store if available, else the `tec/classy` store.
 */
export function getEditedPostAttribute(
	state: StoreState,
	attribute: string
): string {
	const coreEditor = select( 'core/editor' );

	if ( coreEditor ) {
		// @ts-ignore
		return coreEditor.getEditedPostAttribute( attribute ) ?? '';
	}

	return state?.[ attribute ] ?? '';
}

/**
 * Returns the content of the currently edited post.
 *
 * @since TBD
 *
 * @param {StoreState} state The current store state.
 *
 * @returns {string} The content of the currently edited post.
 */
export function getEditedPostContent( state: StoreState ): string {
	const coreEditor = select( 'core/editor' );

	if ( coreEditor ) {
		// @ts-ignore
		return coreEditor.getEditedPostContent() ?? '';
	} else return state?.content ?? '';
}

/**
 * Returns the ID of the currently edited post.
 *
 * @since TBD
 *
 * @param {StoreState} state The current store state.
 *
 * @returns {number} The ID of the currently edited post.
 */
export function getCurrentPostId( state: StoreState ): number {
	const coreEditor = select( 'core/editor' );

	if ( coreEditor ) {
		// @ts-ignore
		return coreEditor.getCurrentPostId() ?? 0;
	} else return state?.currentPostId ?? 0;
}

/**
 * Returns the event date and time details, read from its meta. If the meta is not set
 * it will return default values
 *
 * @since TBD
 *
 * @param {StoreState} state The current store state.
 *
 * @returns {EventDateTimeDetails} The event date and time details.
 */
export function getEventDateTimeDetails(
	state: StoreState
): EventDateTimeDetails {
	const coreEditor = select( 'core/editor' );
	let meta: EventMeta;

	if ( coreEditor ) {
		// @ts-ignore
		meta = coreEditor.getEditedPostAttribute( 'meta' ) ?? {};
	} else {
		meta = state?.meta || {};
	}

	const eventStartDateString = meta?._EventStartDate ?? '';
	const eventEndDateString = meta?._EventEndDate ?? '';

	let eventStart: Date;
	if ( eventStartDateString ) {
		eventStart = getDate( eventStartDateString );
	} else {
		eventStart = getDate( '' );
		eventStart.setHours( 8, 0, 0 );
	}

	let eventEnd: Date;
	if ( eventEndDateString ) {
		eventEnd = getDate( eventEndDateString );
	} else {
		eventEnd = getDate( '' );
		eventEnd.setHours( 17, 0, 0 );
	}
	const settings: Settings = localizedData.settings;
	const isMultiday =
		eventStart.getDate() !== eventEnd.getDate() ||
		eventStart.getMonth() !== eventEnd.getMonth() ||
		eventStart.getFullYear() !== eventEnd.getFullYear();
	const isAllDay = meta?._EventAllDay ?? false;
	const eventTimezone = meta?._EventTimezone ?? settings.timezoneString;

	return {
		eventStart,
		eventEnd,
		isMultiday,
		isAllDay,
		eventTimezone,
		...settings,
	} as EventDateTimeDetails;
}
