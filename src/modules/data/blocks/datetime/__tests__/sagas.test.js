/**
 * External dependencies
 */
import { put, call, select, take, all } from 'redux-saga/effects';
import { applyFilters } from '@wordpress/hooks';

/**
 * Internal Dependencies
 */
import { types, actions, selectors } from '@moderntribe/events/data/blocks/datetime';
import watchers, * as sagas from '@moderntribe/events/data/blocks/datetime/sagas';
import moment from 'moment';
import { toDateTime } from '@moderntribe/common/utils/moment';
import {
	date as dateUtil,
	moment as momentUtil,
	time as timeUtil,
} from '@moderntribe/common/utils';

describe( 'Event Date time Block sagas', () => {
	describe( 'watchers', () => {
		test( 'watcher actions', () => {
			const gen = watchers();
			expect( gen.next().value ).toEqual(
				take( [
					types.SET_DATE_RANGE,
					types.SET_START_DATE_TIME,
					types.SET_END_DATE_TIME,
					types.SET_START_TIME,
					types.SET_END_TIME,
					types.SET_MULTI_DAY,
					types.SET_TIME_ZONE,
					types.SET_NATURAL_LANGUAGE_LABEL,
				] )
			);
			expect( gen.next( {} ).value ).toEqual(
				call( sagas.handler, {} )
			);
			expect( gen.next().done ).toEqual( false );
		} );
	} );

	describe( 'deriveMomentsFromDates', () => {
		it( 'should create moments out of dates', () => {
			const gen = sagas.deriveMomentsFromDates();

			expect( gen.next().value ).toEqual(
				all( {
					start: select( selectors.getStart ),
					end: select( selectors.getEnd ),
				} )
			);
			expect( gen.next( { start: 1, end: 2 } ).value ).toEqual(
				all( {
					start: call( momentUtil.toMoment, 1 ),
					end: call( momentUtil.toMoment, 2 ),
				} )
			);
		} );
	} );
	describe( 'deriveSecondsFromDates', () => {
		it( 'should create seconds out of dates', () => {
			const gen = sagas.deriveSecondsFromDates();

			expect( gen.next().value ).toEqual(
				call( sagas.deriveMomentsFromDates )
			);

			const moments = {
				start: 1,
				end: 2,
			};

			expect( gen.next( moments ).value ).toEqual(
				all( {
					start: call( momentUtil.toDatabaseTime, moments.start ),
					end: call( momentUtil.toDatabaseTime, moments.end ),
				} )
			);

			expect( gen.next( { start: '01:00:00', end: '02:00:00' } ).value ).toEqual(
				all( {
					start: call( timeUtil.toSeconds, '01:00:00' ),
					end: call( timeUtil.toSeconds, '02:00:00' ),
				} )
			);
		} );
	} );

	describe( 'setHumanReadableLabel', () => {
		test( 'No date provided', () => {
			const gen = sagas.setHumanReadableLabel();

			expect( gen.next().value ).toEqual(
				select( selectors.getNaturalLanguageLabel ),
			);

			expect( gen.next().value ).toEqual(
				call( dateUtil.rangeToNaturalLanguage, undefined, undefined ),
			);

			expect( gen.next().done ).toEqual( true );
		} );

		test( 'Custom data is provided', () => {
			const dates =  {
				start: moment( '12-25-2017', 'MM-DD-YYYY' ),
				end: moment( '12-25-2018', 'MM-DD-YYYY' ),
			};
			const gen = sagas.setHumanReadableLabel( dates );

			expect( gen.next().value ).toEqual(
				select( selectors.getNaturalLanguageLabel ),
			);

			expect( gen.next().value ).toEqual(
				call( dateUtil.rangeToNaturalLanguage, dates.start, dates.end ),
			);

			const expected = 'December 25 2017 at 12:00 am - December 25 2018 at 12:00 am';
			expect( gen.next( expected ).value ).toEqual(
				put( actions.setNaturalLanguageLabel( expected ) ),
			);

			expect( gen.next().done ).toEqual( true );
		} );
	} );

	describe( 'setHumanReadableFromDate', () => {
		test( 'When called from start date', () => {
			const formated = toDateTime( moment( '12-25-2018', 'MM-DD-YYYY' ) );
			const gen = sagas.setHumanReadableFromDate( actions.setStartDateTime( formated ) );
			expect( gen.next().value ).toEqual(
				select( selectors.getStart ),
			);

			expect( gen.next().value ).toEqual(
				select( selectors.getEnd ),
			);

			expect( gen.next().value ).toEqual(
				call( sagas.setHumanReadableLabel, { end: undefined, start: '2018-12-25 00:00:00' } )
			);

			expect( gen.next().done ).toBe( true );
		} );

		test( 'When called from end date', () => {
			const formated = toDateTime( moment( '12-25-2018', 'MM-DD-YYYY' ) );
			const gen = sagas.setHumanReadableFromDate( actions.setEndDateTime( formated ) );
			expect( gen.next().value ).toEqual(
				select( selectors.getStart )
			);
			expect( gen.next().value ).toEqual(
				select( selectors.getEnd ),
			);
			expect( gen.next().value ).toEqual(
				call( sagas.setHumanReadableLabel, { start: undefined, end: '2018-12-25 00:00:00' } )
			);
			expect( gen.next().done ).toBe( true );
		} );
	} );

	describe( 'resetNaturalLanguageLabel', () => {
		test( 'Select values from state', () => {
			const gen = sagas.resetNaturalLanguageLabel();
			const start = toDateTime( moment( '12-25-2018 12:30', 'MM-DD-YYYY HH:mm' ) );
			const end = toDateTime( moment( '12-25-2018 14:40', 'MM-DD-YYYY HH:mm' ) );

			expect( gen.next().value ).toEqual( all( {
				start: select( selectors.getStart ),
				end: select( selectors.getEnd ),
			} ) );

			expect( gen.next( { start, end } ).value ).toEqual(
				call( sagas.setHumanReadableLabel, { start, end } ),
			);

			expect( gen.next().done ).toEqual( true );
		} );

		describe( 'onHumanReadableChange', () => {
			test( 'On change handler when dates are null', () => {
				const gen = sagas.onHumanReadableChange();
				const label = { start: null, end: null };

				expect( gen.next().value ).toEqual(
					select( selectors.getNaturalLanguageLabel )
				);

				expect( gen.next( label ).value ).toEqual(
					call( dateUtil.labelToDate, label )
				);

				expect( gen.next( label ).value ).toEqual(
					call( sagas.resetNaturalLanguageLabel )
				);

				expect( gen.next().done ).toEqual( true );
			} );

			test( 'On change handler when dates are set', () => {
				const gen = sagas.onHumanReadableChange();

				const label = { start: '12-25-1995', end: '12-25-1995' };

				const dates = {
					start: moment( '12-25-1995', 'MM-DD-YYYY' ),
					end: moment( '12-25-1995', 'MM-DD-YYYY' ),
				};

				expect( gen.next().value ).toEqual(
					select( selectors.getNaturalLanguageLabel )
				);

				expect( gen.next( label ).value ).toEqual(
					call( dateUtil.labelToDate, label )
				);

				expect( gen.next( label ).value ).toEqual(
					all( {
						start: call( momentUtil.toMoment, label.start ),
						end: call( momentUtil.toMoment, label.end || label.start ),
					} )
				);

				expect( gen.next( dates ).value ).toEqual(
					call( momentUtil.adjustStart, dates.start, dates.end )
				);

				expect( gen.next( dates ).value ).toEqual(
					call( momentUtil.isSameDay, dates.start, dates.end )
				);

				expect( gen.next( true ).value ).toEqual(
					all( {
						start: call( momentUtil.toDateTime, dates.start ),
						end: call( momentUtil.toDateTime, dates.end ),
					} )
				);

				expect( gen.next( dates ).value ).toEqual(
					all( [
						put( actions.setStartDateTime( dates.start ) ),
						put( actions.setEndDateTime( dates.end ) ),
						put( actions.setMultiDay( false ) ),
					] )
				);

				expect( gen.next().done ).toEqual( true );
			} );
		} );
	} );

	describe( 'onTimeZoneChange', () => {
		test( 'Set timezone label on timezone change', () => {
			const action = {
				payload: {
					timeZone: 'America/Los_Angeles',
				},
			};

			const gen = sagas.onTimeZoneChange( action );

			expect( gen.next().value ).toEqual(
				put( actions.setTimeZoneLabel( action.payload.timeZone ) )
			);

			expect( gen.next().done ).toEqual( true );
		} );
	} );

	describe( 'handleDateRangeChange', () => {
		it( 'should update date range', () => {
			const payload = { from: '2015-01-01', to: '2015-01-10' };
			const action = { payload };
			const moments = {
				from: moment( payload.from, 'MM-DD-YYYY' ),
				to: moment( payload.to, 'MM-DD-YYYY' ),
			};
			const gen = sagas.handleDateRangeChange( action );

			expect( gen.next().value ).toEqual(
				call( sagas.deriveMomentsFromDates )
			);

			expect( gen.next( moments ).value ).toEqual(
				all( {
					from: call( momentUtil.toMoment, payload.from ),
					to: call( momentUtil.toMoment, payload.to || payload.from ),
				} )
			);

			expect( gen.next( moments ).value ).toMatchSnapshot();

			expect( gen.next().value ).toEqual(
				call( momentUtil.adjustStart, moments.start, moments.end )
			);

			expect( gen.next( { start: 1, end: 2 } ).value ).toEqual(
				all( {
					start: call( momentUtil.toDateTime, 1 ),
					end: call( momentUtil.toDateTime, 2 ),
				} )
			);

			expect( gen.next( { start: 1, end: 2 } ).value ).toEqual(
				all( [
					put( actions.setStartDateTime( 1 ) ),
					put( actions.setEndDateTime( 2 ) ),
				] )
			);

			expect( gen.next().done ).toEqual( true );
		} );
	} );

	describe( 'preventEndTimeBeforeStartTime', () => {
		let action, seconds;

		beforeEach( () => {
			action = {
				type: types.SET_START_TIME,
				payload: {
					start: 55000,
				},
			};
			seconds = {
				start: action.payload.start,
				end: 2000,
			};
		} );

		it( 'should do nothing when multiday', () => {
			const gen = sagas.preventEndTimeBeforeStartTime( action );
			expect( gen.next().value ).toEqual(
				select( selectors.getMultiDay )
			);
			expect( gen.next( true ).done ).toEqual( true );
		} );
		it( 'should adjust time when start and end time are the same', () => {
			seconds.end = action.payload.start;

			const gen = sagas.preventEndTimeBeforeStartTime( action );
			expect( gen.next().value ).toEqual(
				select( selectors.getMultiDay )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.deriveSecondsFromDates )
			);
			expect( gen.next( seconds ).value ).toEqual(
				call( [ Object, 'assign' ], seconds, action.payload )
			);
			expect( gen.next( seconds ).value ).toEqual(
				call( sagas.deriveMomentsFromDates )
			);

			const moments = { start: 1, end: 2 };
			expect( gen.next( moments ).value ).toEqual(
				all( {
					start: call( momentUtil.setTimeInSeconds, moments.start, seconds.start ),
					end: call( momentUtil.setTimeInSeconds, moments.end, seconds.end ),
				} )
			);
			expect( gen.next( moments ).value ).toEqual(
				all( {
					start: call( momentUtil.toDateTime, moments.start ),
					end: call( momentUtil.toDateTime, moments.end ),
				} )
			);
			expect( gen.next( moments ).value ).toEqual(
				all( [
					put( actions.setStartDateTime( moments.start ) ),
					put( actions.setEndDateTime( moments.end ) ),
				] )
			);

			expect( gen.next().done ).toEqual( true );
		} );
		it( 'should roll back start time when exceeds day time', () => {
			action.payload.start = seconds.start = seconds.end = 86400;

			const gen = sagas.preventEndTimeBeforeStartTime( action );
			expect( gen.next().value ).toEqual(
				select( selectors.getMultiDay )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.deriveSecondsFromDates )
			);
			expect( gen.next( seconds ).value ).toEqual(
				call( [ Object, 'assign' ], seconds, action.payload )
			);
			expect( gen.next( seconds ).value ).toEqual(
				call( sagas.deriveMomentsFromDates )
			);

			const moments = { start: 1, end: 2 };
			expect( gen.next( moments ).value ).toEqual(
				all( {
					start: call( momentUtil.setTimeInSeconds, moments.start, seconds.start ),
					end: call( momentUtil.setTimeInSeconds, moments.end, seconds.end ),
				} )
			);
			expect( gen.next( moments ).value ).toEqual(
				all( {
					start: call( momentUtil.toDateTime, moments.start ),
					end: call( momentUtil.toDateTime, moments.end ),
				} )
			);
			expect( gen.next( moments ).value ).toEqual(
				all( [
					put( actions.setStartDateTime( moments.start ) ),
					put( actions.setEndDateTime( moments.end ) ),
				] )
			);

			expect( gen.next().done ).toEqual( true );
		} );
	} );
	describe( 'preventStartTimeAfterEndTime', () => {
		let action, seconds;

		beforeEach( () => {
			action = {
				type: types.SET_END_TIME,
				payload: {
					end: 55000,
				},
			};
			seconds = {
				start: 60000,
				end: action.payload.end,
			};
		} );

		it( 'should do nothing when multiday', () => {
			const gen = sagas.preventStartTimeAfterEndTime( action );
			expect( gen.next().value ).toEqual(
				select( selectors.getMultiDay )
			);
			expect( gen.next( true ).done ).toEqual( true );
		} );
		it( 'should adjust time when start and end time are the same', () => {
			const gen = sagas.preventStartTimeAfterEndTime( action );
			expect( gen.next().value ).toEqual(
				select( selectors.getMultiDay )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.deriveSecondsFromDates )
			);
			expect( gen.next( seconds ).value ).toEqual(
				call( [ Object, 'assign' ], seconds, action.payload )
			);
			expect( gen.next( seconds ).value ).toEqual(
				call( sagas.deriveMomentsFromDates )
			);

			const moments = { start: 1, end: 2 };
			expect( gen.next( moments ).value ).toEqual(
				all( {
					start: call( momentUtil.setTimeInSeconds, moments.start, seconds.start ),
					end: call( momentUtil.setTimeInSeconds, moments.end, seconds.end ),
				} )
			);
			expect( gen.next( moments ).value ).toEqual(
				all( {
					start: call( momentUtil.toDateTime, moments.start ),
					end: call( momentUtil.toDateTime, moments.end ),
				} )
			);
			expect( gen.next( moments ).value ).toEqual(
				all( [
					put( actions.setStartDateTime( moments.start ) ),
					put( actions.setEndDateTime( moments.end ) ),
				] )
			);

			expect( gen.next().done ).toEqual( true );
		} );
		it( 'should handle when start time would be on previous day', () => {
			const gen = sagas.preventStartTimeAfterEndTime( action );
			expect( gen.next().value ).toEqual(
				select( selectors.getMultiDay )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.deriveSecondsFromDates )
			);
			expect( gen.next( seconds ).value ).toEqual(
				call( [ Object, 'assign' ], seconds, action.payload )
			);
			expect( gen.next( seconds ).value ).toEqual(
				call( sagas.deriveMomentsFromDates )
			);

			const moments = { start: 1, end: 2 };
			expect( gen.next( moments ).value ).toEqual(
				all( {
					start: call( momentUtil.setTimeInSeconds, moments.start, seconds.start ),
					end: call( momentUtil.setTimeInSeconds, moments.end, seconds.end ),
				} )
			);
			expect( gen.next( moments ).value ).toEqual(
				all( {
					start: call( momentUtil.toDateTime, moments.start ),
					end: call( momentUtil.toDateTime, moments.end ),
				} )
			);
			expect( gen.next( moments ).value ).toEqual(
				all( [
					put( actions.setStartDateTime( moments.start ) ),
					put( actions.setEndDateTime( moments.end ) ),
				] )
			);

			expect( gen.next().done ).toEqual( true );
		} );
	} );

	describe( 'setAllDay', () => {
		it( 'it should set all day', () => {
			const gen = sagas.setAllDay();
			const moments = { start: 1, end: 2 };

			expect( gen.next().value ).toEqual(
				call( sagas.deriveMomentsFromDates )
			);

			expect( gen.next( moments ).value ).toEqual(
				all( {
					start: call( momentUtil.setTimeInSeconds, moments.start, 0 ),
					end: call( momentUtil.setTimeInSeconds, moments.end, 86399 ),
				} )
			);

			expect( gen.next().value ).toEqual(
				all( {
					start: call( momentUtil.toDateTime, moments.start ),
					end: call( momentUtil.toDateTime, moments.end ),
				} )
			);

			expect( gen.next( moments ).value ).toEqual(
				all( [
					put( actions.setStartDateTime( moments.start ) ),
					put( actions.setEndDateTime( moments.end ) ),
					put( actions.setAllDay( true ) ),
				] )
			);
		} );
	} );

	describe( 'handleMultiDay', () => {
		let action;

		beforeEach( () => {
			action = {
				payload: {
					multiDay: true,
				},
			};
		} );

		it( 'it should handle multi day', () => {
			const moments = { start: 1, end: { add: jest.fn() } };
			const gen = sagas.handleMultiDay( action );

			expect( gen.next().value ).toEqual(
				call( sagas.deriveMomentsFromDates )
			);
			expect( gen.next( moments ).value ).toEqual(
				call( applyFilters, 'tec.datetime.defaultRange', 3 )
			);
			expect( gen.next( 3 ).value ).toEqual(
				call( [ moments.end, 'add' ], 3, 'days' )
			);
			expect( gen.next().value ).toEqual(
				call( momentUtil.toDateTime, moments.end )
			);
			expect( gen.next( 5 ).value ).toEqual(
				put( actions.setEndDateTime( 5 ) )
			);
		} );
		it( 'it should handle when not multi day', () => {
			action.payload.multiDay = false;
			const moments = { start: 1, end: 2 };
			const gen = sagas.handleMultiDay( action );

			expect( gen.next().value ).toEqual(
				call( sagas.deriveMomentsFromDates )
			);
			expect( gen.next( moments ).value ).toEqual(
				call( momentUtil.replaceDate, moments.end, moments.start )
			);
			expect( gen.next( 3 ).value ).toEqual(
				call( momentUtil.adjustStart, moments.start, 3 )
			);
			expect( gen.next( moments ).value ).toEqual(
				all( {
					start: call( momentUtil.toDateTime, moments.start ),
					end: call( momentUtil.toDateTime, moments.end ),
				} )
			);
			expect( gen.next( moments ).value ).toEqual(
				all( [
					put( actions.setStartDateTime( moments.start ) ),
					put( actions.setEndDateTime( moments.end ) ),
				] )
			);
		} );
	} );

	describe( 'handleStartTimeChange', () => {
		it( 'should handle all day', () => {
			const gen = sagas.handleStartTimeChange( {
				payload: {
					start: 'all-day',
				},
			} );

			expect( gen.next().value ).toEqual(
				call( sagas.setAllDay )
			);
			expect( gen.next().done ).toEqual( true );
		} );
		it( 'should handle start time change', () => {
			const action = {
				payload: {
					start: 50000,
				},
			};
			const gen = sagas.handleStartTimeChange( action );

			expect( gen.next().value ).toEqual(
				call( sagas.deriveMomentsFromDates )
			);
			expect( gen.next( { start: 55000 } ).value ).toEqual(
				call( momentUtil.setTimeInSeconds, 55000, action.payload.start )
			);
			expect( gen.next().value ).toEqual(
				call( momentUtil.toDateTime, 55000 )
			);
			expect( gen.next( '2017-01-01' ).value ).toEqual(
				put( actions.setStartDateTime( '2017-01-01' ) )
			);
			expect( gen.next().done ).toEqual( true );
		} );
	} );

	describe( 'handleEndTimeChange', () => {
		it( 'should handle all day', () => {
			const gen = sagas.handleEndTimeChange( {
				payload: {
					end: 'all-day',
				},
			} );

			expect( gen.next().value ).toEqual(
				call( sagas.setAllDay )
			);
			expect( gen.next().done ).toEqual( true );
		} );
		it( 'should handle end time change', () => {
			const action = {
				payload: {
					end: 50000,
				},
			};
			const gen = sagas.handleEndTimeChange( action );

			expect( gen.next().value ).toEqual(
				call( sagas.deriveMomentsFromDates )
			);
			expect( gen.next( { end: 55000 } ).value ).toEqual(
				call( momentUtil.setTimeInSeconds, 55000, action.payload.end )
			);
			expect( gen.next().value ).toEqual(
				call( momentUtil.toDateTime, 55000 )
			);
			expect( gen.next( '2017-01-01' ).value ).toEqual(
				put( actions.setEndDateTime( '2017-01-01' ) )
			);
			expect( gen.next().done ).toEqual( true );
		} );
	} );

	describe( 'setStartTimeInput', () => {
		it( 'should set start time input', () => {
			const start = 'January 1, 2018';
			const gen = sagas.setStartTimeInput();

			expect( gen.next().value ).toEqual(
				call( sagas.deriveMomentsFromDates )
			);
			expect( gen.next( { start } ).value ).toEqual(
				call( momentUtil.toTime, start )
			);
			expect( gen.next( start ).value ).toEqual(
				put( actions.setStartTimeInput( start ) )
			);
			expect( gen.next().done ).toEqual( true );
		} );
	} );

	describe( 'setEndTimeInput', () => {
		it( 'should set end time input', () => {
			const end = 'January 1, 2018';
			const gen = sagas.setEndTimeInput();

			expect( gen.next().value ).toEqual(
				call( sagas.deriveMomentsFromDates )
			);
			expect( gen.next( { end } ).value ).toEqual(
				call( momentUtil.toTime, end )
			);
			expect( gen.next( end ).value ).toEqual(
				put( actions.setEndTimeInput( end ) )
			);
			expect( gen.next().done ).toEqual( true );
		} );
	} );

	describe( 'handler', () => {
		let action;

		beforeEach( () => {
			action = { type: null };
		} );

		it( 'should handle time zone changes', () => {
			action.type = types.SET_TIME_ZONE;
			const gen = sagas.handler( action );
			expect( gen.next().value ).toEqual(
				call( sagas.onTimeZoneChange, action )
			);
			expect( gen.next().done ).toEqual( true );
		} );

		it( 'should handle date range changes', () => {
			action.type = types.SET_DATE_RANGE;
			const gen = sagas.handler( action );
			expect( gen.next().value ).toEqual(
				call( sagas.handleDateRangeChange, action )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.resetNaturalLanguageLabel )
			);
			expect( gen.next().done ).toEqual( true );
		} );

		it( 'should handle start date changes', () => {
			action.type = types.SET_START_DATE_TIME;
			const gen = sagas.handler( action );
			expect( gen.next().value ).toEqual(
				call( sagas.preventEndTimeBeforeStartTime, action )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.setHumanReadableFromDate, action )
			);
			expect( gen.next().done ).toEqual( true );
		} );

		it( 'should handle end date changes', () => {
			action.type = types.SET_END_DATE_TIME;
			const gen = sagas.handler( action );
			expect( gen.next().value ).toEqual(
				call( sagas.preventStartTimeAfterEndTime, action )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.setHumanReadableFromDate, action )
			);
			expect( gen.next().done ).toEqual( true );
		} );

		it( 'should handle start time changes', () => {
			action.type = types.SET_START_TIME;
			const gen = sagas.handler( action );
			expect( gen.next().value ).toEqual(
				call( sagas.handleStartTimeChange, action )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.preventEndTimeBeforeStartTime, action )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.setStartTimeInput )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.resetNaturalLanguageLabel )
			);
			expect( gen.next().done ).toEqual( true );
		} );

		it( 'should handle end time changes', () => {
			action.type = types.SET_END_TIME;
			const gen = sagas.handler( action );
			expect( gen.next().value ).toEqual(
				call( sagas.handleEndTimeChange, action )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.preventStartTimeAfterEndTime, action )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.setEndTimeInput )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.resetNaturalLanguageLabel )
			);
			expect( gen.next().done ).toEqual( true );
		} );

		it( 'should handle multi-day changes', () => {
			action.type = types.SET_MULTI_DAY;
			const gen = sagas.handler( action );
			expect( gen.next().value ).toEqual(
				call( sagas.handleMultiDay, action )
			);
			expect( gen.next().value ).toEqual(
				call( sagas.resetNaturalLanguageLabel )
			);
			expect( gen.next().done ).toEqual( true );
		} );

		it( 'should handle natural label changes', () => {
			action.type = types.SET_NATURAL_LANGUAGE_LABEL;
			const gen = sagas.handler( action );
			expect( gen.next().value ).toEqual(
				call( sagas.onHumanReadableChange, action )
			);
			expect( gen.next().done ).toEqual( true );
		} );
	} );
} );
