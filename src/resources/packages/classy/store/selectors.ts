import { select } from '@tec/common/classy/store';
import { EventDateTimeDetails } from '../types/EventDateTimeDetails';
import { EventMeta } from '../types/EventMeta';
import { Settings } from '@tec/common/classy/types/LocalizedData';
import { getDate } from '@wordpress/date';
import { METADATA_EVENT_ORGANIZER_ID, METADATA_EVENT_VENUE_ID } from '../constants';
import { StoreState } from '../types/StoreState';
import { TECSettings } from '../types/Settings';

/**
 * Returns the event date and time details, read from its meta. If the meta is not set,
 * it will return default values.
 *
 * @since TBD
 *
 * @returns {EventDateTimeDetails} The event date and time details.
 */
export function getEventDateTimeDetails(): EventDateTimeDetails {
	// @ts-ignore
	const meta: EventMeta = select( 'core/editor' )?.getEditedPostAttribute( 'meta' ) ?? {};
	// @ts-ignore
	const settings: Settings = select( 'tec/classy' ).getSettings();

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
	const isMultiday =
		eventStart.getDate() !== eventEnd.getDate() ||
		eventStart.getMonth() !== eventEnd.getMonth() ||
		eventStart.getFullYear() !== eventEnd.getFullYear();
	const isAllDayStringValue = meta?._EventAllDay ?? '0';
	const isAllDay = isAllDayStringValue === '1';
	const eventTimezone = meta?._EventTimezone || settings.timezoneString;

	return {
		eventStart: eventStart.toISOString(),
		eventEnd: eventEnd.toISOString(),
		isMultiday,
		isAllDay,
		eventTimezone,
		...settings,
	} as EventDateTimeDetails;
}

/**
 * Returns the current Event post Organizer IDs.
 *
 * @since TBD
 *
 * @return {number[]} Array of Organizer IDs.
 */
export function getEditedPostOrganizerIds(): number[] {
	// @ts-ignore
	const meta: EventMeta = select( 'core/editor' )?.getEditedPostAttribute( 'meta' ) ?? {};

	const ids = ( meta?.[ METADATA_EVENT_ORGANIZER_ID ] ?? [] ).map( ( id: string | number ) =>
		typeof id === 'string' ? parseInt( id ) : id
	);

	return ids;
}

/**
 * Returns the current Event post Venue IDs.
 *
 * @since TBD
 *
 * @return {number[]} Array of Venue IDs.
 */
export function getEditedPostVenueIds(): number[] {
	// @ts-ignore
	const meta: EventMeta = select( 'core/editor' )?.getEditedPostAttribute( 'meta' ) ?? {};

	const ids = ( meta?.[ METADATA_EVENT_VENUE_ID ] ?? [] ).map( ( id: string | number ) =>
		typeof id === 'string' ? parseInt( id ) : id
	);

	return ids;
}

/**
 * Returns whether tickets are supported.
 *
 * The initial value is read from the localized setting.
 *
 * @param {StoreState} state The store state.
 *
 * @return {boolean} Whether tickets are supported.
 */
export function areTicketsSupported( state: StoreState ): boolean {
	return state?.areTicketsSupported || false;
}

/**
 * Returns whether an event is new (no start or end date meta) or not.
 *
 * @since TBD
 *
 * @return {boolean} Whether the current event post is a new one or not.
 */
export function isNewEvent(): boolean {
	const { _EventStartDate, _EventEndDate }: EventMeta =
		// @ts-ignore
		select( 'core/editor' )?.getEditedPostAttribute( 'meta' ) ?? {};

	return ! _EventStartDate || ! _EventEndDate;
}

/**
 * Returns the venue limit from the settings.
 *
 * @since TBD
 *
 * @return {number} The venue limit.
 */
export function getVenuesLimit(): number {
	// @ts-ignore
	const { venuesLimit = 1 } = select( 'tec/classy' ).getSettings() as TECSettings;
	return Math.max( 0, venuesLimit );
}
