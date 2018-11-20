/**
 * Internal dependencies
 */
import {
	setStartDateTime,
	setEndDateTime,
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
} = moment;

export const setInitialState = ( { get, attributes } ) => ( dispatch ) => {
	const timeZone = get( 'timeZone', DEFAULT_STATE.timeZone );
	const defaultTimeZone = get( 'timeZoneLabel', timeZone );

	maybeBulkDispatch( attributes, dispatch )( [
		[ setStartDateTime, 'start', DEFAULT_STATE.start ],
		[ setEndDateTime, 'end', DEFAULT_STATE.end ],
		[ setAllDayAction, 'allDay', DEFAULT_STATE.allDay ],
		[ setSeparatorDate, 'separatorDate', DEFAULT_STATE.dateTimeSeparator ],
		[ setSeparatorTime, 'separatorTime', DEFAULT_STATE.timeRangeSeparator ],
		[ setTimeZone, 'timeZone', DEFAULT_STATE.timeZone ],
		[ setTimeZoneLabel, 'timeZoneLabel', defaultTimeZone ],
		[ setTimeZoneVisibility, 'showTimeZone', DEFAULT_STATE.showTimeZone ],
	] );

	const values = {
		start: DEFAULT_STATE.start,
		end: DEFAULT_STATE.end,
	};

	if ( attributes.start ) {
		values.start = toDateTime( parseFormats( attributes.start ) );
		dispatch( setStartDateTime( values.start ) );
	}

	if ( attributes.end ) {
		values.end = toDateTime( parseFormats( attributes.end ) );
		dispatch( setEndDateTime( values.end ) );
	}

	dispatch( setNaturalLanguageLabel( date.rangeToNaturalLanguage( values.start, values.end ) ) );

	dispatch( setMultiDayAction( ! isSameDay( values.start, values.end ) ) );
};
