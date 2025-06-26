import * as React from 'react';
import { RefObject, useCallback, useMemo, useRef, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { Hours } from '@tec/common/classy/types/Hours';
import { Minutes } from '@tec/common/classy/types/Minutes';
import { FieldProps } from '@tec/common/classy/types/FieldProps.ts';
import { ToggleControl } from '@wordpress/components';
import { _x } from '@wordpress/i18n';
import {
	METADATA_EVENT_ALLDAY,
	METADATA_EVENT_END_DATE,
	METADATA_EVENT_START_DATE,
	METADATA_EVENT_TIMEZONE,
} from '../../constants';
import { format, getDate } from '@wordpress/date';
import StartSelector from './StartSelector';
import EndSelector from './EndSelector';
import { TimeZone } from '@tec/common/classy/components';
import { EventDateTimeDetails } from '../../types/EventDateTimeDetails';
import { addFilter, removeFilter } from '@wordpress/hooks';
import { useEffect } from 'react';

type DateTimeRefs = {
	endTimeHours: number;
	endTimeMinutes: number;
	multiDayDuration: number;
	singleDayDuration: number;
	startTimeHours: number;
	startTimeMinutes: number;
};

const phpDateMysqlFormat = 'Y-m-d H:i:s';

type NewDatesReturn = {
	newStartDate: Date;
	newEndDate: Date;
	notify: {
		start: boolean;
		end: boolean;
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
	updated: 'start' | 'end',
	newDate: string
): NewDatesReturn {
	let newStartDate: Date;
	let newEndDate: Date;
	let notify = { start: false, end: false };

	if ( updated === 'start' ) {
		// The user has updated the start date.
		newStartDate = getDate( newDate );
		newEndDate = endDate;

		if ( newStartDate.getTime() >= endDate.getTime() ) {
			// The start date is after the current end date: set the end date to the start date.
			newEndDate = new Date( newStartDate.getTime() );
			notify.end = true;
		}
	} else {
		// The user has updated the end date.
		newStartDate = startDate;
		newEndDate = getDate( newDate );

		if ( newEndDate.getTime() <= startDate.getTime() ) {
			// The end date is before the current start date: set the start date to the end date.
			newStartDate = new Date( newEndDate.getTime() );
			notify.start = true;
		}
	}

	return { newStartDate, newEndDate, notify };
}

/**
 * Calculates a new end date for multi-day events.
 *
 * @since TBD
 *
 * @param {RefObject<DateTimeRefs>} refs A reference object containing duration information.
 * @param {boolean} newValue Indicates whether the event is now multi-day.
 * @param {Date} startDate The current start date of the event.
 * @return {Date} The new end date for the event.
 */
function getMultiDayEndDate( refs: RefObject< DateTimeRefs >, newValue: boolean, startDate: Date ) {
	const { singleDayDuration, multiDayDuration } = refs.current as DateTimeRefs;
	let duration;

	if ( newValue ) {
		// Move the end date forward by 24 hours plus the single day ouration.
		duration = multiDayDuration + singleDayDuration;
	} else {
		duration = singleDayDuration;
	}

	return new Date( startDate.getTime() + duration );
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
 * @param {RefObject<DateTimeRefs>} refs A reference object containing saved time information.
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
	refs: RefObject< DateTimeRefs >
) {
	if ( refs.current === null ) {
		return { newStartDate: startDate, newEndDate: endDate };
	}

	let newStartDate: Date;
	let newEndDate: Date;

	if ( newValue ) {
		// Move the start date to the current day end-of-day cutoff time.
		newStartDate = new Date( startDate );
		newStartDate.setHours( endOfDayCutoff.hours );
		newStartDate.setMinutes( endOfDayCutoff.minutes );
		// Round the current duration to the nearest day and remove one second.
		const days = Math.ceil( ( endDate.getTime() - startDate.getTime() ) / ( 1000 * 60 * 60 * 24 ) );
		const duration = days * 1000 * 60 * 60 * 24 - 1; // Subtract one second to avoid the next day.
		// Move the end date to the next day's end-of-day cutoff time minus on second; e.g. 23:59:59
		newEndDate = new Date( newStartDate.getTime() + duration );

		// Save the current start date and end times.
		refs.current.startTimeHours = startDate.getHours();
		refs.current.startTimeMinutes = startDate.getMinutes();
		refs.current.endTimeHours = endDate.getHours();
		refs.current.endTimeMinutes = endDate.getMinutes();
	} else {
		// Restore the saved start and end times, but respect the days.
		newStartDate = new Date( startDate );
		newStartDate.setHours( refs.current.startTimeHours, refs.current.startTimeMinutes, 0 );
		newEndDate = new Date( endDate );
		newEndDate.setHours( refs.current.endTimeHours, refs.current.endTimeMinutes, 0 );
	}

	return { newStartDate, newEndDate };
}

/**
 * React component for managing event date and time.
 *
 * @since TBD
 *
 * @param {FieldProps} props Component properties including title.
 * @return {JSX.Element} The rendered EventDateTime component.
 */
export default function EventDateTime( props: FieldProps ) {
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
		const { getEventDateTimeDetails }: { getEventDateTimeDetails: () => EventDateTimeDetails } =
			select( 'tec/classy/events' );
		const { isNewEvent }: { isNewEvent: () => boolean } = select( 'tec/classy/events' );

		return { ...getEventDateTimeDetails(), isNewEvent: isNewEvent() };
	}, [] );

	useEffect( (): void => {
		// The `isNewEvent` flag will always be `false` on existing events.
		// The `isNewEvent` flag will be first `undefined` (while the selector is resolved), then `true` on new events.
		// This is done with a filter and not by dispatching a change to the edited post to avoid the
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
				edits.meta[ METADATA_EVENT_START_DATE ] = edits[METADATA_EVENT_START_DATE] || format( phpDateMysqlFormat, eventStart );
				edits.meta[ METADATA_EVENT_END_DATE ] = edits[METADATA_EVENT_END_DATE] || format( phpDateMysqlFormat, eventEnd );
				edits.meta[ METADATA_EVENT_TIMEZONE ] = edits[METADATA_EVENT_TIMEZONE] || eventTimezone;

				return edits;
			};

			addFilter( 'editor.preSavePost', 'tec.classy.events', filterPreSavePost );
		}
	}, [ isNewEvent ] );

	const { editPost } = useDispatch( 'core/editor' );

	const [ isSelectingDate, setIsSelectingDate ] = useState< 'start' | 'end' | false >( false );
	const [ dates, setDates ] = useState( {
		start: getDate( eventStart ),
		end: getDate( eventEnd ),
	} );
	const [ isMultidayValue, setIsMultidayValue ] = useState( isMultiday );
	const [ isAllDayValue, setIsAllDayValue ] = useState( isAllDay );
	const { start: startDate, end: endDate } = dates;
	const [ timezoneString, setTimezoneString ] = useState( eventTimezone );
	const [ higlightStartTime, setHighlightStartTime ] = useState( false );
	const [ highlightEndTime, setHighlightEndTime ] = useState( false );

	// Store a reference to some ground values to allow the toggle of multi-day and all-day correctly.
	const refs = useRef( {
		startTimeHours: isAllDay ? 8 : startDate.getHours(),
		startTimeMinutes: isAllDay ? 0 : startDate.getMinutes(),
		endTimeHours: isAllDay ? 17 : endDate.getHours(),
		endTimeMinutes: isAllDay ? 0 : endDate.getMinutes(),
		// The default single-day duration is 9 hours.
		singleDayDuration: isMultiday ? 9 * 60 * 60 * 1000 : dates.end.getTime() - dates.start.getTime(),
		// The default multi-day duration is 24 hours.
		multiDayDuration: isMultiday ? dates.end.getTime() - dates.start.getTime() : 24 * 60 * 60 * 1000,
	} );

	// Used in dependencies.
	const startDateIsoString = startDate.toISOString();
	const endDateIsoString = endDate.toISOString();

	const onDateChange = useCallback(
		( updated: 'start' | 'end', newDate: string ): void => {
			const { newStartDate, newEndDate, notify } = getNewStartEndDates( endDate, startDate, updated, newDate );

			editPost( {
				meta: {
					[ METADATA_EVENT_START_DATE ]: format( phpDateMysqlFormat, newStartDate ),
					[ METADATA_EVENT_END_DATE ]: format( phpDateMysqlFormat, newEndDate ),
				},
			} );

			// If the start date and end date are on the same year, month, day, then it's not multiday.
			if (
				newStartDate.getFullYear() === newEndDate.getFullYear() &&
				newStartDate.getMonth() === newEndDate.getMonth() &&
				newStartDate.getDate() === newEndDate.getDate()
			) {
				setIsMultidayValue( false );
			}

			setDates( { start: newStartDate, end: newEndDate } );
			setIsSelectingDate( false );
			setHighlightStartTime( notify.start );
			setHighlightEndTime( notify.end );
		},
		[ endDateIsoString, startDateIsoString, editPost ]
	);

	const onDateInputClick = useCallback(
		( selecting: 'start' | 'end' ) => {
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
				highightTime={ higlightStartTime }
				isAllDay={ isAllDayValue }
				isMultiday={ isMultidayValue }
				isSelectingDate={ isSelectingDate }
				onChange={ onDateChange }
				onClick={ () => onDateInputClick( 'start' ) }
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
				onClick={ () => onDateInputClick( 'end' ) }
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
	] );

	const onMultiDayToggleChange = useCallback(
		( newValue: boolean ) => {
			let newEndDate = getMultiDayEndDate( refs, newValue, startDate );
			onDateChange( 'end', format( phpDateMysqlFormat, newEndDate ) );
			setIsMultidayValue( newValue );
		},
		[ startDateIsoString ]
	);

	const onAllDayToggleChange = useCallback(
		( newValue: boolean ) => {
			let { newStartDate, newEndDate } = getAllDayNewDates( newValue, startDate, endOfDayCutoff, endDate, refs );

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
		[ startDateIsoString, endDateIsoString, endOfDayCutoff, editPost ]
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
