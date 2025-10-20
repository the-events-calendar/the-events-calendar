import { EndOfDayCutoff } from '@tec/events/classy/types/Settings';

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
	// Create normalized dates at the cutoff time for comparison
	const startCutoffDate = new Date( startDate );
	startCutoffDate.setHours( endOfDayCutoff.hours, endOfDayCutoff.minutes, 0, 0 );

	const endCutoffDate = new Date( endDate );
	endCutoffDate.setHours( endOfDayCutoff.hours, endOfDayCutoff.minutes, 0, 0 );

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

	// Compare the effective days
	return effectiveStartDay.getTime() !== effectiveEndDay.getTime();
}
