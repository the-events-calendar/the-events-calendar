import { areDatesOnSameDay, areDatesOnSameTime } from '@tec/common/classy/functions';
import { DateTimeUpdateType } from '@tec/common/classy/types/FieldProps';
import { Hours } from '@tec/common/classy/types/Hours';
import { Minutes } from '@tec/common/classy/types/Minutes';
import { NewDatesReturn } from '../types/EventDateTimeDetails';
import { getDurationInDaysForCutoff } from '../functions/date';

/**
 * Calculates new dates for multiday toggle.
 *
 * @since TBD
 *
 * @param {boolean} newValue Whether multiday is being enabled.
 * @param {Date} startDate The current start date.
 * @param {Date} endDate The current end date.
 * @param {Date} defaultStartDate The default start date (8:00 AM).
 * @param {Date} defaultEndDate The default end date (5:00 PM).
 * @param {{start: Date, end: Date} | null} previousDates The previous dates before toggle, if available.
 * @return {{newStartDate: Date, newEndDate: Date}} An object containing the new start and end dates.
 */
export function getMultiDayDates(
	newValue: boolean,
	startDate: Date,
	endDate: Date,
	defaultStartDate: Date,
	defaultEndDate: Date,
	previousDates: { start: Date; end: Date } | null
): { newStartDate: Date; newEndDate: Date } {
	if ( newValue ) {
		// Enable multiday: start date + 24 hours + duration difference
		const duration = endDate.getTime() - startDate.getTime();
		const newEndDate = new Date( startDate.getTime() + 24 * 60 * 60 * 1000 + duration );
		return { newStartDate: startDate, newEndDate };
	} else {
		// Disable multiday: revert to previous state if available, otherwise default
		const revertStart = previousDates ? previousDates.start : defaultStartDate;
		const revertEnd = previousDates ? previousDates.end : defaultEndDate;
		return { newStartDate: revertStart, newEndDate: revertEnd };
	}
}

/**
 * Calculates new start and end dates when toggling all-day events.
 *
 * @since TBD
 *
 * @param {boolean} newValue Indicates whether the event is now an all-day event.
 * @param {Date} startDate The current start date of the event.
 * @param {{hours: Hours, minutes: Minutes}} endOfDayCutoff The time at which the day ends.
 * @param {Date} endDate The current end date of the event.
 * @param {Date} defaultStartDate The default start date (8:00 AM).
 * @param {Date} defaultEndDate The default end date (5:00 PM).
 * @param {{start: Date, end: Date} | null} previousDates The previous dates before toggle, if available.
 * @return {{newStartDate: Date, newEndDate: Date}} An object containing the new start and end dates.
 */
export function getAllDayNewDates(
	newValue: boolean,
	startDate: Date,
	endOfDayCutoff: {
		hours: Hours;
		minutes: Minutes;
	},
	endDate: Date,
	defaultStartDate: Date,
	defaultEndDate: Date,
	previousDates: { start: Date; end: Date } | null
): { newStartDate: Date; newEndDate: Date } {
	if ( newValue ) {
		// Enable all-day: set to full day.
		const newStartDate = new Date( startDate );
		newStartDate.setHours( endOfDayCutoff.hours, endOfDayCutoff.minutes, 0, 0 );
		const durationInDays = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );
		// The event will last at least one day and the cutoff minus one second.
		const duration = ( durationInDays + 1 ) * 24 * 60 * 60 * 1000 - 1;
		const newEndDate = new Date( newStartDate.getTime() + duration );

		return { newStartDate, newEndDate };
	} else {
		// Disable all-day: revert to previous state if available, otherwise default.
		const revertStart = previousDates ? previousDates.start : defaultStartDate;
		const revertEnd = previousDates ? previousDates.end : defaultEndDate;
		return { newStartDate: revertStart, newEndDate: revertEnd };
	}
}

/**
 * Calculates new start and end dates based on user updates.
 *
 * @since TBD
 *
 * @param {Date} endDate The current end date.
 * @param {Date} startDate The current start date.
 * @param {'start' | 'end'} updated Indicates whether the start or end date was updated.
 * @param {string} newDate The new date string provided by the user.
 * @param {boolean} isMultiDayEnabled Whether multiday is enabled.
 *
 * @return {NewDatesReturn} An object defining the new start and end dates, and whether the user needs to be notified
 *     of the implicit change of either.
 */
export function getNewStartEndDates(
	endDate: Date,
	startDate: Date,
	updated: DateTimeUpdateType,
	newDate: string,
	isMultiDayEnabled: boolean
): NewDatesReturn {
	// Milliseconds.
	const duration = endDate.getTime() - startDate.getTime();

	// By default, do not move the start date but keep it to the previous value.
	let newStartDate = startDate;

	// By default, do not move the end date but keep it to the previous value.
	let newEndDate = endDate;
	let notify = { startDate: false, startTime: false, endDate: false, endTime: false };

	try {
		switch ( updated ) {
			case 'startDate':
				newStartDate = new Date( newDate );

				// If not multiday update end date with original duration.
				if ( ! isMultiDayEnabled ) {
					newEndDate = new Date( newDate );
					newEndDate.setHours( endDate.getHours(), endDate.getMinutes() );
				}

				break;
			case 'startTime':
				// The user has updated the start date.
				newStartDate = new Date( newDate );

				if ( newStartDate.getTime() >= endDate.getTime() ) {
					// For time updates, push end time to next interval
					newEndDate = new Date( newStartDate );
				}

				break;
			case 'endDate':
				// The user has updated the end date.
				newEndDate = new Date( newDate );
				if ( newEndDate.getTime() <= startDate.getTime() ) {
					// For date updates, maintain duration
					newStartDate = new Date( newEndDate.getTime() - duration );
				}

				break;
			case 'endTime':
				// The user has updated the end date.
				newEndDate = new Date( newDate );

				if ( newEndDate.getTime() < startDate.getTime() ) {
					// For time updates, pull start time to previous interval.
					newStartDate = new Date( newEndDate.getTime() - duration );
				}
				break;
		}

		// Highlight the start date if it actually changed as a consequence of the update.
		notify.startDate = updated !== 'startDate' && ! areDatesOnSameDay( startDate, newStartDate );

		// Highlight the start time if it actually changed as a consequence of the update.
		notify.startTime = updated !== 'startTime' && ! areDatesOnSameTime( startDate, newStartDate );

		// Highlight the end date if it actually changed as a consequence of the update.
		notify.endDate = updated !== 'endDate' && ! areDatesOnSameDay( endDate, newEndDate );

		// Highlight the end time if it actually changed as a consequence of the update.
		notify.endTime = updated !== 'endTime' && ! areDatesOnSameTime( endDate, newEndDate );
	} catch ( e ) {
		// Something went wrong while processing the dates, return the values unchanged and notify no field.
		newStartDate = startDate;
		newEndDate = endDate;
		// Nothing to notify since nothing changed.
		notify = { startDate: false, startTime: false, endDate: false, endTime: false };
	}

	return { newStartDate, newEndDate, notify };
}
