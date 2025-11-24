import * as React from 'react';
import { useEffect } from 'react';
import { Slot, ToggleControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { format } from '@wordpress/date';
import { useCallback, useMemo, useRef, useState } from '@wordpress/element';
import { addFilter, removeFilter } from '@wordpress/hooks';
import { _x } from '@wordpress/i18n';
import { EndSelector, StartSelector, TimeZone } from '@tec/common/classy/components';
import { areDatesOnSameDay } from '@tec/common/classy/functions';
import { DateTimeUpdateType, DateUpdateType, FieldProps } from '@tec/common/classy/types/FieldProps';
import {
	METADATA_EVENT_ALLDAY,
	METADATA_EVENT_END_DATE,
	METADATA_EVENT_START_DATE,
	METADATA_EVENT_TIMEZONE,
} from '../../constants';
import { getAllDayNewDates, getMultiDayDates, getNewStartEndDates } from '../../functions/events';
import { StoreSelect } from '../../types/Store';

const phpDateMysqlFormat = 'Y-m-d H:i:s';

/**
 * React component for managing event date and time.
 *
 * @since TBD
 *
 * @param {FieldProps} props Component properties including title.
 *
 * @return {React.JSX.Element} The rendered EventDateTime component.
 */
export default function EventDateTime( props: FieldProps ): React.JSX.Element {
	const {
		dateWithYearFormat,
		endOfDayCutoff,
		eventEnd,
		eventStart,
		eventTimezone,
		isAllDay,
		isMultiday,
		startOfWeek,
		timeFormat,
		isNewEvent,
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
				removeFilter( 'editor.preSavePost', 'tec.classy.events' );

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
	const [ previousDates, setPreviousDates ] = useState( dates );

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
			const { newStartDate, newEndDate, notify } = getNewStartEndDates(
				endDate,
				startDate,
				updated,
				newDate,
				isMultidayValue
			);

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

			setPreviousDates( dates );
		},
		[ endDateIsoString, startDateIsoString, editPost, isMultidayValue, isAllDayValue ]
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
	}, [ dateWithYearFormat, isAllDayValue, isMultidayValue, isSelectingDate, startOfWeek, timeFormat, dates ] );

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
	}, [ dateWithYearFormat, isAllDayValue, isMultidayValue, isSelectingDate, startOfWeek, timeFormat, dates ] );

	const onMultiDayToggleChange = useCallback(
		( newValue: boolean ) => {
			// Save current state when turning ON
			if ( newValue && ! isAllDayValue ) {
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

			if ( isMultidayValue ) {
				const syncedDates = getMultiDayDates(
					isMultidayValue,
					newStartDate,
					newEndDate,
					defaultDates.current.start,
					defaultDates.current.end,
					previousDates
				);

				setDates( { start: syncedDates.newStartDate, end: syncedDates.newEndDate } );
			} else {
				setDates( { start: newStartDate, end: newEndDate } );
			}

			editPost( {
				meta: {
					[ METADATA_EVENT_START_DATE ]: format( phpDateMysqlFormat, newStartDate ),
					[ METADATA_EVENT_END_DATE ]: format( phpDateMysqlFormat, newEndDate ),
					[ METADATA_EVENT_ALLDAY ]: newValue ? '1' : '0',
				},
			} );

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
				{ /* Render additional fields before the default ones. */ }
				<Slot name="tec.classy.fields.event-date-time.before" />

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

				{ /* Render additional fields after the default ones. */ }
				<Slot name="tec.classy.fields.event-date-time.after" />
			</div>
		</div>
	);
}
