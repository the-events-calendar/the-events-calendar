import { EndOfDayCutoff } from '@tec/events/classy/types/Settings';
import { Hours } from '@tec/common/classy/types/Hours';
import { Minutes } from '@tec/common/classy/types/Minutes';

/**
 * Determines if an event should be considered "all-day" based on the end-of-day cutoff settings.
 *
 * This function checks if the event's start time matches the configured end-of-day cutoff
 * and if the end time is one minute before the cutoff on the same or next day.
 *
 * @since TBD
 *
 * @param {EndOfDayCutoff} endOfDayCutoff The end-of-day cutoff configuration with hours and minutes.
 * @param {Date} startDate The event start date.
 * @param {Date} endDate The event end date.
 *
 * @return {boolean} Whether the event is all-day based on the end-of-day cutoff.
 */
export function isAllDayForCutoff( endOfDayCutoff: EndOfDayCutoff, startDate: Date, endDate: Date ): boolean {
	const { hours, minutes } = endOfDayCutoff;

	if ( ! ( startDate.getHours() === hours && startDate.getMinutes() === minutes ) ) {
		// If the start date does not match, it's not all-day following the end-of-day cutoff.
		return false;
	}

	// The start date has the same hours and minutes as the end-of-day cutoff, we can build a cut-off date.
	const cutoffEndDate = new Date( startDate.getTime() );
	cutoffEndDate.setMinutes( cutoffEndDate.getMinutes() - 1 );

	return cutoffEndDate.getHours() === endDate.getHours() && cutoffEndDate.getMinutes() === endDate.getMinutes();
}

/**
 * Calculates the duration of an event in effective days based on the end-of-day cutoff settings.
 *
 * This function determines how many effective days an event spans, where an "effective day"
 * is defined by the end-of-day cutoff time rather than midnight. Events within the same
 * effective day return 0, events spanning two effective days return 1, and so on.
 *
 * @since TBD
 *
 * @param {EndOfDayCutoff} endOfDayCutoff The end-of-day cutoff configuration with hours and minutes.
 * @param {Date} startDate The event start date.
 * @param {Date} endDate The event end date.
 *
 * @return {number} The number of effective day boundaries crossed (0 for same day, 1 for next day, etc.).
 */
export function getDurationInDaysForCutoff(
	endOfDayCutoff: {
		hours: Hours;
		minutes: Minutes;
	},
	startDate: Date,
	endDate: Date
): number {
	// If start time is before the cutoff, the "day" starts at the cutoff of the previous day
	const effectiveStartDay = new Date( startDate );
	if (
		startDate.getHours() < endOfDayCutoff.hours ||
		( startDate.getHours() === endOfDayCutoff.hours && startDate.getMinutes() < endOfDayCutoff.minutes )
	) {
		effectiveStartDay.setDate( effectiveStartDay.getDate() - 1 );
	}
	effectiveStartDay.setHours( 0, 0, 0, 0 );

	// If end time is before the cutoff, the "day" starts at the cutoff of the previous day
	const effectiveEndDay = new Date( endDate );
	if (
		endDate.getHours() < endOfDayCutoff.hours ||
		( endDate.getHours() === endOfDayCutoff.hours && endDate.getMinutes() < endOfDayCutoff.minutes )
	) {
		effectiveEndDay.setDate( effectiveEndDay.getDate() - 1 );
	}
	effectiveEndDay.setHours( 0, 0, 0, 0 );

	// Calculate the difference in milliseconds and convert to days
	const millisecondsDiff = effectiveEndDay.getTime() - effectiveStartDay.getTime();
	const daysDiff = millisecondsDiff / ( 1000 * 60 * 60 * 24 );

	// Round to handle any floating point precision issues
	return Math.round( daysDiff );
}

/**
 * Determines if an event spans multiple days based on the end-of-day cutoff settings.
 *
 * This function checks if the event's start and end dates are on different calendar days,
 * accounting for the configured end-of-day cutoff time.
 *
 * @since TBD
 *
 * @param {EndOfDayCutoff} endOfDayCutoff The end-of-day cutoff configuration with hours and minutes.
 * @param {Date} startDate The event start date.
 * @param {Date} endDate The event end date.
 *
 * @return {boolean} Whether the event spans multiple days based on the end-of-day cutoff.
 */
export function isMultiDayForCutoff( endOfDayCutoff: EndOfDayCutoff, startDate: Date, endDate: Date ): boolean {
	return getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate ) > 0;
}
