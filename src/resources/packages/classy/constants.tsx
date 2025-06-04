// Post types.
export const POST_TYPE_EVENT = 'tribe_events';

/**
 * Metadata keys for the Classy editor.
 *
 * These keys are used to store event-related metadata in the WordPress editor.
 *
 * Note that for these keys to be saved correctly, they must be ALSO be registered
 * in the \TEC\Events\Classy\Meta::META array.
 */
export const METADATA_EVENT_ALLDAY = '_EventAllDay';
export const METADATA_EVENT_COST = '_EventCost';
export const METADATA_EVENT_CURRENCY = '_EventCurrency';
export const METADATA_EVENT_CURRENCY_POSITION = '_EventCurrencyPosition';
export const METADATA_EVENT_CURRENCY_SYMBOL = '_EventCurrencySymbol';
export const METADATA_EVENT_END_DATE = '_EventEndDate';
export const METADATA_EVENT_IS_FREE = '_EventIsFree';
export const METADATA_EVENT_START_DATE = '_EventStartDate';
export const METADATA_EVENT_TIMEZONE = '_EventTimezone';
export const METADATA_EVENT_ORGANIZER_ID = '_EventOrganizerID';
export const METADATA_EVENT_VENUE_ID = '_EventVenueID';
export const METADATA_EVENT_URL = '_EventURL';
