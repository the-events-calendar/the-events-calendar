import { select } from '@tec/common/classy/store';
import { EventMeta } from '../types/EventMeta';
import { Settings } from '@tec/common/classy/types/LocalizedData';
import { METADATA_EVENT_ORGANIZER_ID, METADATA_EVENT_VENUE_ID } from '../constants';
import { StoreState } from '../types/Store';
import { TECSettings } from '../types/Settings';
import { EventDateTimeDetails } from '../types/EventDateTimeDetails';
import { areDatesOnSameDay } from '@tec/common/classy/functions';
import { createSelector } from '@wordpress/data';

/**
 * Retrieves the post meta from the editor.
 *
 * @since TBD
 *
 * @returns {EventMeta} The event meta or an empty object if not available.
 */
export function getPostMeta(): EventMeta {
	return select( 'core/editor' )?.getEditedPostAttribute( 'meta' ) ?? {};
}

/**
 * Retrieves the settings from the Classy store.
 *
 * @since TBD
 *
 * @returns {Settings} The settings or an empty object if not available.
 */
export function getSettings(): Settings {
	return select( 'tec/classy' ).getSettings() ?? {};
}

/**
 * Retrieves comprehensive event date and time details from post meta and settings.
 *
 * This is a memoized selector that combines event date/time information from the WordPress
 * editor meta and Classy store settings. It processes raw date strings into Date objects,
 * determines multiday and all-day status, and returns a complete EventDateTimeDetails object.
 *
 * When event start or end dates are not set (i.e., for new events), the selector provides
 * sensible defaults: start time defaults to 8:00 AM and end time defaults to 5:00 PM on the
 * current date. The timezone is retrieved from meta or falls back to the settings timezone.
 *
 * The selector is memoized using createSelector from @wordpress/data and only recalculates
 * when the relevant meta fields (_EventStartDate, _EventEndDate, _EventTimezone, _EventAllDay)
 * or settings (timezoneString) change.
 *
 * @since TBD
 *
 * @return {EventDateTimeDetails} Object containing eventStart and eventEnd as ISO strings,
 *                                isMultiday flag, isAllDay flag, eventTimezone string, and
 *                                all settings from the Classy store.
 */
export const getEventDateTimeDetails = createSelector(
	(): EventDateTimeDetails => {
		const meta = getPostMeta();
		const settings = getSettings();

		const eventStartDateString = meta?._EventStartDate ?? '';
		const eventEndDateString = meta?._EventEndDate ?? '';
		const eventTimezone = meta?._EventTimezone || settings.timezoneString;

		let eventStart: Date;
		if ( eventStartDateString ) {
			eventStart = new Date( eventStartDateString );
		} else {
			eventStart = new Date();
			eventStart.setHours( 8, 0, 0, 0 );
		}

		let eventEnd: Date;
		if ( eventEndDateString ) {
			eventEnd = new Date( eventEndDateString );
		} else {
			eventEnd = new Date();
			eventEnd.setHours( 17, 0, 0, 0 );
		}
		const isMultiday = ! areDatesOnSameDay( eventStart, eventEnd );
		const isAllDay = meta?._EventAllDay ?? false;

		return {
			eventStart: eventStart.toISOString(),
			eventEnd: eventEnd.toISOString(),
			isMultiday,
			isAllDay,
			eventTimezone,
			...settings,
		} as EventDateTimeDetails;
	},
	() => {
		const meta = getPostMeta();
		const settings = getSettings();

		return [
			meta?._EventStartDate,
			meta?._EventEndDate,
			meta?._EventTimezone,
			meta?._EventAllDay,
			settings.timezoneString,
			settings,
		];
	}
);

/**
 * Returns the current Event post Organizer IDs.
 *
 * @since TBD
 *
 * @return {number[]} Array of Organizer IDs.
 */
export function getEditedPostOrganizerIds(): number[] {
	const meta = getPostMeta();

	return ( meta?.[ METADATA_EVENT_ORGANIZER_ID ] ?? [] ).map( ( id: string | number ) =>
		typeof id === 'string' ? parseInt( id ) : id
	);
}

/**
 * Returns the current Event post Venue IDs.
 *
 * @since TBD
 *
 * @return {number[]} Array of Venue IDs.
 */
export function getEditedPostVenueIds(): number[] {
	const meta = getPostMeta();

	return ( meta?.[ METADATA_EVENT_VENUE_ID ] ?? [] ).map( ( id: string | number ) =>
		typeof id === 'string' ? parseInt( id ) : id
	);
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
 * Returns whether the event is using tickets.
 *
 * @param {StoreState} state The store state.
 *
 * @return {boolean} Whether the event is using tickets.
 */
export function isUsingTickets( state: StoreState ): boolean {
	return ( state?.areTicketsSupported || false ) && ( state?.isUsingTickets || false );
}

/**
 * Returns whether an event is new (no start or end date meta) or not.
 *
 * @since TBD
 *
 * @return {boolean} Whether the current event post is a new one or not.
 */
export function isNewEvent(): boolean {
	const { _EventStartDate, _EventEndDate }: EventMeta = getPostMeta();

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
	const { venuesLimit = 1 } = getSettings() as TECSettings;
	return Math.max( 0, venuesLimit );
}

/**
 * Returns whether the content is disabled from the settings.
 *
 * @since TBD
 *
 * @return {boolean} Whether the content is disabled.
 */
export function isContentDisabled(): boolean {
	const { disableContent = false } = getSettings() as TECSettings;
	return disableContent;
}

/**
 * Returns the reason for content being disabled from the settings.
 *
 * @since TBD
 *
 * @return {string|null} The reason for content being disabled, or null if not set.
 */
export function getContentDisabledReason(): string | null {
	const { disableContentReason = null } = getSettings() as TECSettings;
	return disableContentReason;
}
