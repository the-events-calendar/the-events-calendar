import * as React from 'react';
import { useEffect } from 'react';
import { useCallback, useMemo, useRef, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { Hours } from '@tec/common/classy/types/Hours';
import { Minutes } from '@tec/common/classy/types/Minutes';
import { DateTimeUpdateType, DateUpdateType, FieldProps } from '@tec/common/classy/types/FieldProps';
import { ToggleControl } from '@wordpress/components';
import { _x } from '@wordpress/i18n';
import {
	METADATA_EVENT_ALLDAY,
	METADATA_EVENT_END_DATE,
	METADATA_EVENT_START_DATE,
	METADATA_EVENT_TIMEZONE,
} from '../../constants';
import { format } from '@wordpress/date';
import { EndSelector, StartSelector, TimeZone } from '@tec/common/classy/components';
import { StoreSelect } from '../../types/Store';
import { addFilter, removeFilter } from '@wordpress/hooks';
import { areDatesOnSameDay, areDatesOnSameTime } from '@tec/common/classy/functions';

const phpDateMysqlFormat = 'Y-m-d H:i:s';

type NewDatesReturn = {
	newStartDate: Date;
	newEndDate: Date;
	notify: {
		startDate: boolean;
		startTime: boolean;
		endDate: boolean;
		endTime: boolean;
	};
};

/**
 * Calculates new start and end dates based on user updates.
 *
 * @since TBD
 *
 * @param {Date} endDate The current end date.
 * @param {Date} startDate The current start date.
 * @param {'start' | 'end'} updated Indicates whether the start or end date was updated.
 * @param {string} newDate The new date string provided by the user.
 * @return {NewDatesReturn} An object defining the new start and end dates, and whether the user needs to be notified
 *     of the implicit change of either.
 */
function getNewStartEndDates(
	endDate: Date,
	startDate: Date,
	updated: DateTimeUpdateType,
	newDate: string
): NewDatesReturn {
	// Milliseconds.
	const duration = endDate.getTime() - startDate.getTime();
	const isMultiday = ! areDatesOnSameDay( startDate, endDate );
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
				if ( ! isMultiday ) {
					newEndDate = new Date( newStartDate.getTime() + duration );
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

				if ( newEndDate.getTime() <= startDate.getTime() ) {
					// For time updates, pull start time to previous interval.
					newStartDate = new Date( newEndDate );
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
function getMultiDayDates(
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
function getAllDayNewDates(
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
		// Enable all-day: set to full day
		const newStartDate = new Date( startDate );
		newStartDate.setHours( 0, 0, 0, 0 );

		const newEndDate = new Date( endDate );
		newEndDate.setHours( endOfDayCutoff.hours, endOfDayCutoff.minutes );

		return { newStartDate, newEndDate };
	} else {
		// Disable all-day: revert to previous state if available, otherwise default
		const revertStart = previousDates ? previousDates.start : defaultStartDate;
		const revertEnd = previousDates ? previousDates.end : defaultEndDate;
		return { newStartDate: revertStart, newEndDate: revertEnd };
	}
}

/**
 * React component for managing event date and time.
 *
 * @since TBD
 *
 * @param {FieldProps} props Component properties including title.
 *
 * @return {JSX.Element} The rendered EventDateTime component.
 */
export default function EventDateTime( props: FieldProps ): JSX.Element {
	const {
		dateWithYearFormat,
		endOfDayCutoff,
		eventEnd,
		eventStart,
		eventTimezone,
		isAllDay,
		isMultiday,
		isNewEvent,
		startOfWeek,
		timeFormat,
	} = useSelect( ( select ) => {
		const tecStore: StoreSelect = select( 'tec/classy/events' );

		return { ...tecStore.getEventDateTimeDetails(), isNewEvent: tecStore.isNewEvent() };
	}, [] );

	useEffect( (): void => {
		// The `isNewEvent` flag will always be `false` on existing events.
		// The `isNewEvent` flag will be first `undefined` (while the selector is resolved), then `true` on new events.
		// This is done with a filter and not by dispatching a change to the edited post to avoid
		// the "You have unsaved changes ..." alert for a user that changed nothing.
		if ( isNewEvent === true ) {
			const filterPreSavePost = ( edits: { meta?: Object } ): { meta?: Object } => {
				if ( ! ( eventStart && eventEnd && eventTimezone ) ) {
					// We're missing the information required.
					return edits;
				}

				// Remove the function for this event, it will not be required anymore.
				removeFilter( 'editor.preSavePost', 'tec.classy.events', filterPreSavePost );

				// Add the start date, end date and timezone information to the payload sent to the backend.
				edits.meta = edits?.meta || {};
				if ( ! edits.meta[ METADATA_EVENT_START_DATE ] ) {
					edits.meta[ METADATA_EVENT_START_DATE ] = format( phpDateMysqlFormat, eventStart );
				}
				if ( ! edits.meta[ METADATA_EVENT_END_DATE ] ) {
					edits.meta[ METADATA_EVENT_END_DATE ] = format( phpDateMysqlFormat, eventEnd );
				}
				if ( ! edits.meta[ METADATA_EVENT_TIMEZONE ] ) {
					edits.meta[ METADATA_EVENT_TIMEZONE ] = eventTimezone;
				}

				return edits;
			};

			addFilter( 'editor.preSavePost', 'tec.classy.events', filterPreSavePost );
		}
	}, [ isNewEvent ] );

	const { editPost } = useDispatch( 'core/editor' );

	const [ isSelectingDate, setIsSelectingDate ] = useState< DateUpdateType | false >( false );
	const [ dates, setDates ] = useState( {
		start: new Date( eventStart ),
		end: new Date( eventEnd ),
	} );
	const [ isMultidayValue, setIsMultidayValue ] = useState( isMultiday );
	const [ isAllDayValue, setIsAllDayValue ] = useState( isAllDay );
	const [ previousDates, setPreviousDates ] = useState< { start: Date; end: Date } | null >( null );

	// Default values: current day with 8:00 AM start and 5:00 PM end.
	const defaultDates = useRef( {
		start: new Date( new Date().setHours( 8, 0, 0, 0 ) ),
		end: new Date( new Date().setHours( 17, 0, 0, 0 ) ),
	} );
	const { start: startDate, end: endDate } = dates;
	const [ timezoneString, setTimezoneString ] = useState( eventTimezone );
	const [ highlightStartTime, setHighlightStartTime ] = useState( false );
	const [ highlightEndTime, setHighlightEndTime ] = useState( false );

	// Used in dependencies.
	const startDateIsoString = startDate.toISOString();
	const endDateIsoString = endDate.toISOString();

	const onDateChange = useCallback(
		( updated: DateTimeUpdateType, newDate: string ): void => {
			const { newStartDate, newEndDate, notify } = getNewStartEndDates( endDate, startDate, updated, newDate );

			editPost( {
				meta: {
					[ METADATA_EVENT_START_DATE ]: format( phpDateMysqlFormat, newStartDate ),
					[ METADATA_EVENT_END_DATE ]: format( phpDateMysqlFormat, newEndDate ),
				},
			} );

			// If the start date and end date are on the same year, month, day, then it's not multiday.
			setIsMultidayValue( ! areDatesOnSameDay( newStartDate, newEndDate ) );

			setDates( { start: newStartDate, end: newEndDate } );
			setIsSelectingDate( false );
			setHighlightStartTime( notify.startTime );
			setHighlightEndTime( notify.endTime );
		},
		[ endDateIsoString, startDateIsoString, editPost ]
	);

	const onDateInputClick = useCallback(
		( selecting: DateUpdateType ) => {
			if ( selecting === isSelectingDate ) {
				// Do nothing.
				return;
			}

			return setIsSelectingDate( selecting );
		},
		[ isSelectingDate ]
	);

	const startSelector = useMemo( () => {
		return (
			<StartSelector
				dateWithYearFormat={ dateWithYearFormat }
				endDate={ endDate }
				highlightTime={ highlightStartTime }
				isAllDay={ isAllDayValue }
				isMultiday={ isMultidayValue }
				isSelectingDate={ isSelectingDate }
				onChange={ onDateChange }
				onClick={ () => onDateInputClick( 'startDate' ) }
				onClose={ () => setIsSelectingDate( false ) }
				startDate={ startDate }
				startOfWeek={ startOfWeek }
				timeFormat={ timeFormat }
			/>
		);
	}, [
		dateWithYearFormat,
		endDateIsoString,
		isAllDayValue,
		isMultidayValue,
		isSelectingDate,
		startDateIsoString,
		startOfWeek,
		timeFormat,
		endDate,
	] );

	const endSelector = useMemo( () => {
		return (
			<EndSelector
				dateWithYearFormat={ dateWithYearFormat }
				endDate={ endDate }
				highlightTime={ highlightEndTime }
				isAllDay={ isAllDayValue }
				isMultiday={ isMultidayValue }
				isSelectingDate={ isSelectingDate }
				onChange={ onDateChange }
				onClick={ () => onDateInputClick( 'endDate' ) }
				onClose={ () => setIsSelectingDate( false ) }
				startDate={ startDate }
				startOfWeek={ startOfWeek }
				timeFormat={ timeFormat }
			/>
		);
	}, [
		dateWithYearFormat,
		endDateIsoString,
		isAllDayValue,
		isMultidayValue,
		isSelectingDate,
		startDateIsoString,
		startOfWeek,
		timeFormat,
		startDate,
	] );

	const onMultiDayToggleChange = useCallback(
		( newValue: boolean ) => {
			// Save current state when turning ON
			if ( newValue ) {
				setPreviousDates( { start: startDate, end: endDate } );
			}

			const { newStartDate, newEndDate } = getMultiDayDates(
				newValue,
				startDate,
				endDate,
				defaultDates.current.start,
				defaultDates.current.end,
				previousDates
			);

			editPost( {
				meta: {
					[ METADATA_EVENT_START_DATE ]: format( phpDateMysqlFormat, newStartDate ),
					[ METADATA_EVENT_END_DATE ]: format( phpDateMysqlFormat, newEndDate ),
				},
			} );

			setDates( { start: newStartDate, end: newEndDate } );
			setIsMultidayValue( newValue );
		},
		[ startDateIsoString, endDateIsoString, editPost, previousDates ]
	);

	const onAllDayToggleChange = useCallback(
		( newValue: boolean ) => {
			// Save current state when turning ON
			if ( newValue && ! isMultidayValue ) {
				setPreviousDates( { start: startDate, end: endDate } );
			}

			const { newStartDate, newEndDate } = getAllDayNewDates(
				newValue,
				startDate,
				endOfDayCutoff,
				endDate,
				defaultDates.current.start,
				defaultDates.current.end,
				previousDates
			);

			editPost( {
				meta: {
					[ METADATA_EVENT_START_DATE ]: format( phpDateMysqlFormat, newStartDate ),
					[ METADATA_EVENT_END_DATE ]: format( phpDateMysqlFormat, newEndDate ),
					[ METADATA_EVENT_ALLDAY ]: newValue ? '1' : '0',
				},
			} );

			setDates( { start: newStartDate, end: newEndDate } );
			setIsAllDayValue( newValue );
		},
		[ startDateIsoString, endDateIsoString, endOfDayCutoff, editPost, isMultidayValue, previousDates ]
	);

	const onTimezoneChange = useCallback(
		( timezone: string ): void => {
			editPost( {
				meta: {
					[ METADATA_EVENT_START_DATE ]: format( phpDateMysqlFormat, startDate ),
					[ METADATA_EVENT_END_DATE ]: format( phpDateMysqlFormat, endDate ),
					[ METADATA_EVENT_TIMEZONE ]: timezone,
				},
			} );
			setTimezoneString( timezone );
		},
		[ startDateIsoString, endDateIsoString ]
	);

	return (
		<div className="classy-field classy-field--event-datetime">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<div className="classy-field__inputs">
				<div className="classy-field__group">
					{ startSelector }
					{ endSelector }
				</div>

				<div className="classy-field__group">
					<div className="classy-field__subgroup classy-field__subgroup--left">
						<ToggleControl
							__nextHasNoMarginBottom
							label={ _x( 'Multi-day event', 'Multi-day toggle label', 'the-events-calendar' ) }
							checked={ isMultidayValue }
							onChange={ onMultiDayToggleChange }
						/>

						<ToggleControl
							__nextHasNoMarginBottom
							label={ _x( 'All-day event', 'All-day toggle label', 'the-events-calendar' ) }
							checked={ isAllDayValue }
							onChange={ onAllDayToggleChange }
						/>
					</div>

					<div className="classy-field__subgroup classy-field__subgroup--right">
						<TimeZone timezone={ timezoneString } onTimezoneChange={ onTimezoneChange } />
					</div>
				</div>
			</div>
		</div>
	);
}
