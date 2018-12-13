/**
 * Internal dependencies
 */
import {
	setStartDateTime,
	setEndDateTime,
	setStartTimeInput,
	setEndTimeInput,
	setAllDay as setAllDayAction,
	setMultiDay as setMultiDayAction,
	setSeparatorDate,
	setSeparatorTime,
	setTimeZone,
	setTimeZoneLabel,
	setTimeZoneVisibility,
	setNaturalLanguageLabel,
} from './actions';
import { DEFAULT_STATE } from './reducer';
import { maybeBulkDispatch } from '@moderntribe/events/data/utils';
import { date, moment } from '@moderntribe/common/utils';

const {
	isSameDay,
	parseFormats,
	toDateTime,
	toTime,
} = moment;

export const setInitialState = ( { get, attributes } ) => ( dispatch ) => {
	const timeZone = get( 'timeZone', DEFAULT_STATE.timeZone );
	const timeZoneLabel = get( 'timeZoneLabel', timeZone );

	/**
	 * @todo: remove maybeBuildDispatch, dispatch declaratively instead
	 */
	maybeBulkDispatch( attributes, dispatch )( [
		[ setStartDateTime, 'start', DEFAULT_STATE.start ],
		[ setEndDateTime, 'end', DEFAULT_STATE.end ],
		[ setAllDayAction, 'allDay', DEFAULT_STATE.allDay ],
		[ setSeparatorDate, 'separatorDate', DEFAULT_STATE.dateTimeSeparator ],
		[ setSeparatorTime, 'separatorTime', DEFAULT_STATE.timeRangeSeparator ],
		[ setTimeZone, 'timeZone', timeZoneLabel ],
		[ setTimeZoneLabel, 'timeZoneLabel', timeZoneLabel ],
		[ setTimeZoneVisibility, 'showTimeZone', DEFAULT_STATE.showTimeZone ],
	] );

	const values = {
		start: DEFAULT_STATE.start,
		end: DEFAULT_STATE.end,
	};

	if ( attributes.start ) {
		const startMoment = parseFormats( attributes.start );
		values.start = toDateTime( startMoment );
		const startTimeInput = toTime( startMoment );
		dispatch( setStartDateTime( values.start ) );
		dispatch( setStartTimeInput( startTimeInput ) );
	}
	if ( attributes.end ) {
		const endMoment = parseFormats( attributes.end );
		values.end = toDateTime( endMoment );
		const endTimeInput = toTime( endMoment );
		dispatch( setEndDateTime( values.end ) );
		dispatch( setEndTimeInput( endTimeInput ) );
	}

	dispatch( setNaturalLanguageLabel( date.rangeToNaturalLanguage( values.start, values.end ) ) );
	dispatch( setMultiDayAction( ! isSameDay( values.start, values.end ) ) );
};
