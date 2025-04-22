import {
	RefObject,
	useCallback,
	useMemo,
	useRef,
	useState,
} from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { EventDateTimeDetails } from '../../../types/EventDateTimeDetails';
import { ToggleControl } from '@wordpress/components';
import { _x } from '@wordpress/i18n';
import {
	METADATA_EVENT_ALLDAY,
	METADATA_EVENT_END_DATE,
	METADATA_EVENT_START_DATE,
} from '../../../constants';
import { format, getDate } from '@wordpress/date';
import { usePostEdits } from '../../../hooks';
import { UsePostEditsReturn } from '../../../types/UsePostEditsReturn';
import './style.pcss';
import { Hours } from '../../../types/Hours';
import { Minutes } from '../../../types/Minutes';
import StartSelector from './StartSelector';
import EndSelector from './EndSelector';

type EventDateTimeProps = {
	title: string;
};

type EventDateTimeRefs = {
	endTimeHours: number;
	endTimeMinutes: number;
	multiDayDuration: number;
	singleDayDuration: number;
	startTimeHours: number;
	startTimeMinutes: number;
};

const phpDateMysqlFormat = 'Y-m-d H:i:s';

function getNewStartEndDates(
	endDate: Date,
	startDate: Date,
	updated: 'start' | 'end',
	newDate: string
) {
	const previousDurationInMs = Math.abs(
		endDate.getTime() - startDate.getTime()
	);
	let newStartDate: Date;
	let newEndDate: Date;

	if ( updated === 'start' ) {
		// The start date has been updated by the user.
		newStartDate = getDate( newDate );
		newEndDate = endDate;

		if ( newStartDate.getTime() >= endDate.getTime() ) {
			// The start date is after the current end date: push the end date forward.
			newEndDate = new Date(
				newStartDate.getTime() + previousDurationInMs
			);
		}
	} else {
		// The end date has been updated by the user.
		newStartDate = startDate;
		newEndDate = getDate( newDate );

		if ( newEndDate.getTime() < startDate.getTime() ) {
			// The end date is before the current start date: push the start date back.
			newStartDate = new Date(
				newEndDate.getTime() - previousDurationInMs
			);
		}
	}
	return { newStartDate, newEndDate };
}

function getMultiDayEndDate(
	refs: RefObject< EventDateTimeRefs >,
	newValue: boolean,
	startDate: Date
) {
	let newEndDate: Date;
	const { singleDayDuration, multiDayDuration } = refs.current;
	let duration;

	if ( newValue ) {
		// Move the end date forward by 24 hours plus the single day duration.
		duration = multiDayDuration + singleDayDuration;
	} else {
		duration = singleDayDuration;
	}

	return new Date( startDate.getTime() + duration );
}

function getAllDayNewDates(
	newValue: boolean,
	startDate: Date,
	endOfDayCutoff: {
		hours: Hours;
		minutes: Minutes;
	},
	endDate: Date,
	refs: RefObject< EventDateTimeRefs >
) {
	let newStartDate: Date;
	let newEndDate: Date;

	if ( newValue ) {
		// Move the start date to the current day end-of-day cutoff time.
		newStartDate = new Date( startDate );
		newStartDate.setHours( endOfDayCutoff.hours );
		newStartDate.setMinutes( endOfDayCutoff.minutes );
		// Round the current duration to the nearest day and remove one second.
		const days = Math.ceil(
			( endDate.getTime() - startDate.getTime() ) /
				( 1000 * 60 * 60 * 24 )
		);
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
		newStartDate.setHours( refs.current.startTimeHours );
		newStartDate.setMinutes( refs.current.startTimeMinutes );
		newEndDate = new Date( endDate );
		newEndDate.setHours( refs.current.endTimeHours );
		newEndDate.setMinutes( refs.current.endTimeMinutes );
	}

	return { newStartDate, newEndDate };
}

export default function EventDateTime( props: EventDateTimeProps ) {
	const {
		eventStart,
		eventEnd,
		isMultiday,
		isAllDay,
		eventTimezone,
		startOfWeek,
		endOfDayCutoff,
		dateWithYearFormat,
		timeFormat,
	} = useSelect( ( select ) => {
		const {
			getEventDateTimeDetails,
		}: { getEventDateTimeDetails: () => EventDateTimeDetails } =
			select( 'tec/classy' );
		return getEventDateTimeDetails();
	}, [] );
	const { editPost } = usePostEdits() as UsePostEditsReturn;

	const [ isSelectingDate, setIsSelectingDate ] = useState<
		'start' | 'end' | false
	>( false );
	const [ dates, setDates ] = useState( {
		start: eventStart,
		end: eventEnd,
	} );
	const [ isMultidayValue, setIsMultidayValue ] = useState( isMultiday );
	const [ isAllDayValue, setIsAllDayValue ] = useState( isAllDay );
	const { start: startDate, end: endDate } = dates;

	// Store a reference to some ground values to allow the toggle of multi-day and all-day correctly.
	const refs = useRef( {
		startTimeHours: isAllDay ? 8 : startDate.getHours(),
		startTimeMinutes: isAllDay ? 0 : startDate.getMinutes(),
		endTimeHours: isAllDay ? 17 : endDate.getHours(),
		endTimeMinutes: isAllDay ? 0 : endDate.getMinutes(),
		// Default single day duration is 9 hours.
		singleDayDuration: isMultiday
			? 9 * 60 * 60 * 1000
			: dates.end.getTime() - dates.start.getTime(),
		// Default multi day duration is 24 hours.
		multiDayDuration: isMultiday
			? dates.end.getTime() - dates.start.getTime()
			: 24 * 60 * 60 * 1000,
	} );

	// Used in dependencies.
	const startDateIsoString = startDate.toISOString();
	const endDateIsoString = endDate.toISOString();

	const onDateChange = useCallback(
		( updated: 'start' | 'end', newDate: string ): void => {
			const { newStartDate, newEndDate } = getNewStartEndDates(
				endDate,
				startDate,
				updated,
				newDate
			);

			editPost( {
				meta: {
					[ METADATA_EVENT_START_DATE ]: format(
						phpDateMysqlFormat,
						newStartDate
					),
					[ METADATA_EVENT_END_DATE ]: format(
						phpDateMysqlFormat,
						newEndDate
					),
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
		},
		[
			endDateIsoString,
			startDateIsoString,
			editPost,
			setDates,
			setIsSelectingDate,
		]
	);

	const onDateInputClick = useCallback(
		( selecting: 'start' | 'end' ) => {
			if ( selecting === isSelectingDate ) {
				// Do nothing.
				return;
			}

			return setIsSelectingDate( selecting );
		},
		[ isSelectingDate, setIsSelectingDate ]
	);

	const onStartTimeChange = useCallback(
		( newDate: string ) => onDateChange( 'start', newDate ),
		[ onDateChange ]
	);

	const onEndTimeChange = useCallback(
		( newDate: string ) => onDateChange( 'end', newDate ),
		[ onDateChange ]
	);

	const startSelector = useMemo(
		() => (
			<StartSelector
				dateWithYearFormat={ dateWithYearFormat }
				endDate={ endDate }
				isAllDay={ isAllDayValue }
				isMultiday={ isMultidayValue }
				isSelectingDate={ isSelectingDate }
				onClick={ () => onDateInputClick( 'start' ) }
				onClose={ () => setIsSelectingDate( false ) }
				onChange={ onDateChange }
				onFocusOutside={ () => setIsSelectingDate( false ) }
				startDate={ startDate }
				startOfWeek={ startOfWeek }
				timeFormat={ timeFormat }
			/>
		),
		[
			dateWithYearFormat,
			startDateIsoString,
			endDateIsoString,
			setIsSelectingDate,
			isSelectingDate,
			isAllDayValue,
			isMultidayValue,
			startOfWeek,
			timeFormat,
		]
	);

	const endSelector = useMemo(
		() => (
			<EndSelector
				dateWithYearFormat={ dateWithYearFormat }
				endDate={ endDate }
				isAllDay={ isAllDayValue }
				isMultiday={ isMultidayValue }
				isSelectingDate={ isSelectingDate }
				onClick={ () => onDateInputClick( 'end' ) }
				onClose={ () => setIsSelectingDate( false ) }
				onChange={ onDateChange }
				onFocusOutside={ () => setIsSelectingDate( false ) }
				startDate={ startDate }
				startOfWeek={ startOfWeek }
				timeFormat={ timeFormat }
			/>
		),
		[
			dateWithYearFormat,
			startDateIsoString,
			endDateIsoString,
			setIsSelectingDate,
			isSelectingDate,
			isAllDayValue,
			isMultidayValue,
			startOfWeek,
			timeFormat,
		]
	);

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
			let { newStartDate, newEndDate } = getAllDayNewDates(
				newValue,
				startDate,
				endOfDayCutoff,
				endDate,
				refs
			);

			editPost( {
				meta: {
					[ METADATA_EVENT_START_DATE ]: format(
						phpDateMysqlFormat,
						newStartDate
					),
					[ METADATA_EVENT_END_DATE ]: format(
						phpDateMysqlFormat,
						newEndDate
					),
					[ METADATA_EVENT_ALLDAY ]: newValue ? '1' : '0',
				},
			} );

			setDates( { start: newStartDate, end: newEndDate } );
			setIsAllDayValue( newValue );
		},
		[
			startDateIsoString,
			endDateIsoString,
			endOfDayCutoff,
			editPost,
			setDates,
			setIsAllDayValue,
		]
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
					<ToggleControl
						__nextHasNoMarginBottom
						label={ _x(
							'Multi-day event',
							'Multi-day toggle label',
							'the-events-calendar'
						) }
						checked={ isMultidayValue }
						onChange={ onMultiDayToggleChange }
					></ToggleControl>

					<ToggleControl
						__nextHasNoMarginBottom
						label={ _x(
							'All-day event',
							'All-day toggle label',
							'the-events-calendar'
						) }
						checked={ isAllDayValue }
						onChange={ onAllDayToggleChange }
					/>
				</div>
			</div>
		</div>
	);
}
