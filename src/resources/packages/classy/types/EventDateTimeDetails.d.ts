/**
 * Type definitions for event date and time details in The Events Calendar Classy editor.
 *
 * @since TBD
 */

import { Settings } from '../types/Settings';

/**
 * Event date and time details.
 *
 * Contains comprehensive information about an event's temporal properties including
 * start/end dates, timezone, and special configurations like all-day and multiday events.
 * This type extends the Settings interface from Common's LocalizedData to include
 * additional configuration and localization data.
 *
 * @since TBD
 *
 * @property {string} eventStart The event start date and time in ISO 8601 format (e.g., "2025-10-28T14:30:00").
 * @property {string} eventEnd The event end date and time in ISO 8601 format (e.g., "2025-10-28T16:30:00").
 * @property {boolean} isMultiday Indicates whether the event spans multiple days.
 * @property {boolean} isAllDay Indicates whether the event is configured as an all-day event.
 * @property {string} eventTimezone The timezone identifier for the event (e.g., "America/New_York", "UTC").
 */
export type EventDateTimeDetails = {
	eventStart: string;
	eventEnd: string;
	isMultiday: boolean;
	isAllDay: boolean;
	eventTimezone: string;
} & Settings;

/**
 * Return value for date update operations.
 *
 * Provides the updated date values along with a notification object that tracks
 * which specific date and time fields have changed. This granular change tracking
 * enables precise UI updates, validation triggers, and selective data persistence.
 *
 * The notify object is particularly useful for:
 * - Triggering validation only for changed fields
 * - Updating specific UI components without full re-render
 * - Tracking user modifications for analytics or undo functionality
 * - Optimizing API calls by sending only changed data
 *
 * @since TBD
 *
 * @property {Date} newStartDate The updated event start date as a Date object.
 * @property {Date} newEndDate The updated event end date as a Date object.
 * @property {Object} notify Notification flags indicating which date/time components changed.
 * @property {boolean} notify.startDate Indicates whether the start date component changed.
 * @property {boolean} notify.startTime Indicates whether the start time component changed.
 * @property {boolean} notify.endDate Indicates whether the end date component changed.
 * @property {boolean} notify.endTime Indicates whether the end time component changed.
 */
export type NewDatesReturn = {
	newStartDate: Date;
	newEndDate: Date;
	notify: {
		startDate: boolean;
		startTime: boolean;
		endDate: boolean;
		endTime: boolean;
	};
};
