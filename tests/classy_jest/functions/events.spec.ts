// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import { describe, expect, it, jest, beforeEach } from '@jest/globals';

import { getMultiDayDates, getAllDayNewDates, getNewStartEndDates } from '@tec/events/classy/functions/events';

describe( 'Event Functions', () => {
	beforeEach( () => {
		jest.clearAllMocks();
	} );

	describe( 'getMultiDayDates', () => {
		const startDate = new Date( '2024-01-15 10:00:00' );
		const endDate = new Date( '2024-01-15 18:00:00' ); // 8 hours duration
		const defaultStartDate = new Date( '2024-01-15 08:00:00' );
		const defaultEndDate = new Date( '2024-01-15 17:00:00' );

		it( 'should extend end date by 24 hours plus duration when enabling multiday', () => {
			const result = getMultiDayDates( true, startDate, endDate, defaultStartDate, defaultEndDate, null );

			// Duration is 8 hours (28800000 ms), should add 24 hours (86400000 ms) + 8 hours
			const expectedEndTime = startDate.getTime() + 24 * 60 * 60 * 1000 + 8 * 60 * 60 * 1000;

			expect( result.newStartDate ).toEqual( startDate );
			expect( result.newEndDate.getTime() ).toBe( expectedEndTime );
		} );

		it( 'should preserve start date when enabling multiday', () => {
			const result = getMultiDayDates( true, startDate, endDate, defaultStartDate, defaultEndDate, null );

			expect( result.newStartDate ).toEqual( startDate );
		} );

		it( 'should revert to previous dates when disabling multiday with previous dates', () => {
			const previousDates = {
				start: new Date( '2024-01-15 09:00:00' ),
				end: new Date( '2024-01-15 17:00:00' ),
			};

			const result = getMultiDayDates(
				false,
				startDate,
				endDate,
				defaultStartDate,
				defaultEndDate,
				previousDates
			);

			expect( result.newStartDate ).toEqual( previousDates.start );
			expect( result.newEndDate ).toEqual( previousDates.end );
		} );

		it( 'should use default dates when disabling multiday without previous dates', () => {
			const result = getMultiDayDates( false, startDate, endDate, defaultStartDate, defaultEndDate, null );

			expect( result.newStartDate ).toEqual( defaultStartDate );
			expect( result.newEndDate ).toEqual( defaultEndDate );
		} );

		it( 'should handle zero duration events when enabling multiday', () => {
			const sameDates = new Date( '2024-01-15 10:00:00' );

			const result = getMultiDayDates( true, sameDates, sameDates, defaultStartDate, defaultEndDate, null );

			// Should add exactly 24 hours when duration is 0
			const expectedEndTime = sameDates.getTime() + 24 * 60 * 60 * 1000;

			expect( result.newStartDate ).toEqual( sameDates );
			expect( result.newEndDate.getTime() ).toBe( expectedEndTime );
		} );

		it( 'should handle very short duration events when enabling multiday', () => {
			const start = new Date( '2024-01-15 10:00:00' );
			const end = new Date( '2024-01-15 10:30:00' ); // 30 minutes

			const result = getMultiDayDates( true, start, end, defaultStartDate, defaultEndDate, null );

			// 24 hours + 30 minutes
			const expectedEndTime = start.getTime() + 24 * 60 * 60 * 1000 + 30 * 60 * 1000;

			expect( result.newEndDate.getTime() ).toBe( expectedEndTime );
		} );

		it( 'should handle long duration events when enabling multiday', () => {
			const start = new Date( '2024-01-15 08:00:00' );
			const end = new Date( '2024-01-15 23:00:00' ); // 15 hours

			const result = getMultiDayDates( true, start, end, defaultStartDate, defaultEndDate, null );

			// 24 hours + 15 hours
			const expectedEndTime = start.getTime() + 24 * 60 * 60 * 1000 + 15 * 60 * 60 * 1000;

			expect( result.newEndDate.getTime() ).toBe( expectedEndTime );
		} );
	} );

	describe( 'getAllDayNewDates', () => {
		const startDate = new Date( '2024-01-15 10:00:00' );
		const endDate = new Date( '2024-01-15 18:00:00' );
		const defaultStartDate = new Date( '2024-01-15 08:00:00' );
		const defaultEndDate = new Date( '2024-01-15 17:00:00' );

		it( 'should set start date to cutoff time when enabling all-day for single day event', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };

			const result = getAllDayNewDates(
				true,
				startDate,
				endOfDayCutoff,
				endDate,
				defaultStartDate,
				defaultEndDate,
				null
			);

			expect( result.newStartDate.getHours() ).toBe( 23 );
			expect( result.newStartDate.getMinutes() ).toBe( 59 );
			expect( result.newStartDate.getSeconds() ).toBe( 0 );
			expect( result.newStartDate.getMilliseconds() ).toBe( 0 );
		} );

		it( 'should set end date to cutoff minus 1 second for single day all-day event', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };

			const result = getAllDayNewDates(
				true,
				startDate,
				endOfDayCutoff,
				endDate,
				defaultStartDate,
				defaultEndDate,
				null
			);

			// For single day (duration 0), should be (0 + 1) * 24 hours - 1 second = 23:59:59 same day
			const expectedDuration = 1 * 24 * 60 * 60 * 1000 - 1;
			const expectedEndTime = result.newStartDate.getTime() + expectedDuration;

			expect( result.newEndDate.getTime() ).toBe( expectedEndTime );
		} );

		it( 'should handle multi-day all-day events correctly', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };
			const multiDayStart = new Date( '2024-01-15 10:00:00' );
			const multiDayEnd = new Date( '2024-01-17 18:00:00' ); // 2+ days

			const result = getAllDayNewDates(
				true,
				multiDayStart,
				endOfDayCutoff,
				multiDayEnd,
				defaultStartDate,
				defaultEndDate,
				null
			);

			// Should span multiple days based on getDurationInDaysForCutoff result
			expect( result.newEndDate.getTime() ).toBeGreaterThan( result.newStartDate.getTime() );
		} );

		it( 'should revert to previous dates when disabling all-day with previous dates', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };
			const previousDates = {
				start: new Date( '2024-01-15 09:00:00' ),
				end: new Date( '2024-01-15 17:00:00' ),
			};

			const result = getAllDayNewDates(
				false,
				startDate,
				endOfDayCutoff,
				endDate,
				defaultStartDate,
				defaultEndDate,
				previousDates
			);

			expect( result.newStartDate ).toEqual( previousDates.start );
			expect( result.newEndDate ).toEqual( previousDates.end );
		} );

		it( 'should use default dates when disabling all-day without previous dates', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };

			const result = getAllDayNewDates(
				false,
				startDate,
				endOfDayCutoff,
				endDate,
				defaultStartDate,
				defaultEndDate,
				null
			);

			expect( result.newStartDate ).toEqual( defaultStartDate );
			expect( result.newEndDate ).toEqual( defaultEndDate );
		} );

		it( 'should handle early morning cutoff (3 AM) correctly', () => {
			const endOfDayCutoff = { hours: 3, minutes: 0 };

			const result = getAllDayNewDates(
				true,
				startDate,
				endOfDayCutoff,
				endDate,
				defaultStartDate,
				defaultEndDate,
				null
			);

			expect( result.newStartDate.getHours() ).toBe( 3 );
			expect( result.newStartDate.getMinutes() ).toBe( 0 );
		} );

		it( 'should handle late morning cutoff (10 AM) correctly', () => {
			const endOfDayCutoff = { hours: 10, minutes: 0 };

			const result = getAllDayNewDates(
				true,
				startDate,
				endOfDayCutoff,
				endDate,
				defaultStartDate,
				defaultEndDate,
				null
			);

			expect( result.newStartDate.getHours() ).toBe( 10 );
			expect( result.newStartDate.getMinutes() ).toBe( 0 );
		} );

		it( 'should handle non-standard cutoff times (e.g., 6:30 AM)', () => {
			const endOfDayCutoff = { hours: 6, minutes: 30 };

			const result = getAllDayNewDates(
				true,
				startDate,
				endOfDayCutoff,
				endDate,
				defaultStartDate,
				defaultEndDate,
				null
			);

			expect( result.newStartDate.getHours() ).toBe( 6 );
			expect( result.newStartDate.getMinutes() ).toBe( 30 );
		} );

		it( 'should preserve the date portion of start date when enabling all-day', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };

			const result = getAllDayNewDates(
				true,
				startDate,
				endOfDayCutoff,
				endDate,
				defaultStartDate,
				defaultEndDate,
				null
			);

			expect( result.newStartDate.getFullYear() ).toBe( startDate.getFullYear() );
			expect( result.newStartDate.getMonth() ).toBe( startDate.getMonth() );
			expect( result.newStartDate.getDate() ).toBe( startDate.getDate() );
		} );
	} );

	describe( 'getNewStartEndDates', () => {
		const startDate = new Date( '2024-01-15 10:00:00' );
		const endDate = new Date( '2024-01-15 18:00:00' );

		describe( 'startDate updates', () => {
			it( 'should update start date and keep end time in single-day mode', () => {
				const newDate = '2024-01-20T10:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'startDate', newDate, false );

				expect( result.newStartDate.toISOString() ).toBe( new Date( newDate ).toISOString() );
				expect( result.newEndDate.getHours() ).toBe( endDate.getHours() );
				expect( result.newEndDate.getMinutes() ).toBe( endDate.getMinutes() );
				expect( result.newEndDate.getDate() ).toBe( 20 ); // Same as new start date
			} );

			it( 'should update start date without changing end date in multi-day mode', () => {
				const newDate = '2024-01-20T10:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'startDate', newDate, true );

				expect( result.newStartDate.toISOString() ).toBe( new Date( newDate ).toISOString() );
				expect( result.newEndDate ).toEqual( endDate );
			} );

			it( 'should not notify when updating start date directly', () => {
				const newDate = '2024-01-20T10:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'startDate', newDate, false );

				expect( result.notify.startDate ).toBe( false );
			} );

			it( 'should notify end date when it changes implicitly in single-day mode', () => {
				const newDate = '2024-01-20T10:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'startDate', newDate, false );

				expect( result.notify.endDate ).toBe( true );
			} );
		} );

		describe( 'startTime updates', () => {
			it( 'should update start time', () => {
				const newDate = '2024-01-15T14:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'startTime', newDate, false );

				expect( result.newStartDate.getHours() ).toBe( 14 );
			} );

			it( 'should push end time forward when start time exceeds end time', () => {
				const newDate = '2024-01-15T19:00:00'; // After current end time

				const result = getNewStartEndDates( endDate, startDate, 'startTime', newDate, false );

				expect( result.newEndDate.getTime() ).toBeGreaterThanOrEqual( result.newStartDate.getTime() );
			} );

			it( 'should not notify when updating start time directly', () => {
				const newDate = '2024-01-15T14:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'startTime', newDate, false );

				expect( result.notify.startTime ).toBe( false );
			} );

			it( 'should notify end time when it changes implicitly', () => {
				const newDate = '2024-01-15T19:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'startTime', newDate, false );

				expect( result.notify.endTime ).toBe( true );
			} );
		} );

		describe( 'endDate updates', () => {
			it( 'should update end date when after start date', () => {
				const newDate = '2024-01-20T18:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'endDate', newDate, false );

				expect( result.newEndDate.toISOString() ).toBe( new Date( newDate ).toISOString() );
			} );

			it( 'should adjust start date when end date is before start date', () => {
				const newDate = '2024-01-10T18:00:00'; // Before start date

				const result = getNewStartEndDates( endDate, startDate, 'endDate', newDate, false );

				expect( result.newStartDate.getTime() ).toBeLessThan( result.newEndDate.getTime() );
			} );

			it( 'should maintain duration when adjusting start date', () => {
				const duration = endDate.getTime() - startDate.getTime();
				const newDate = '2024-01-10T18:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'endDate', newDate, false );

				const newDuration = result.newEndDate.getTime() - result.newStartDate.getTime();
				expect( newDuration ).toBe( duration );
			} );

			it( 'should not notify when updating end date directly', () => {
				const newDate = '2024-01-20T18:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'endDate', newDate, false );

				expect( result.notify.endDate ).toBe( false );
			} );

			it( 'should notify start date when it changes implicitly', () => {
				const newDate = '2024-01-10T18:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'endDate', newDate, false );

				expect( result.notify.startDate ).toBe( true );
			} );
		} );

		describe( 'endTime updates', () => {
			it( 'should update end time when after start time', () => {
				const newDate = '2024-01-15T20:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'endTime', newDate, false );

				expect( result.newEndDate.getHours() ).toBe( 20 );
			} );

			it( 'should pull start time back when end time is before start time', () => {
				const newDate = '2024-01-15T08:00:00'; // Before start time

				const result = getNewStartEndDates( endDate, startDate, 'endTime', newDate, false );

				expect( result.newStartDate.getTime() ).toBeLessThanOrEqual( result.newEndDate.getTime() );
			} );

			it( 'should not notify when updating end time directly', () => {
				const newDate = '2024-01-15T20:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'endTime', newDate, false );

				expect( result.notify.endTime ).toBe( false );
			} );

			it( 'should notify start time when it changes implicitly', () => {
				const newDate = '2024-01-15T08:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'endTime', newDate, false );

				expect( result.notify.startTime ).toBe( true );
			} );
		} );

		describe( 'error handling', () => {
			it( 'should handle invalid date strings by creating invalid Date objects', () => {
				const result = getNewStartEndDates( endDate, startDate, 'startDate', 'invalid-date', false );

				// JavaScript Date constructor doesn't throw on invalid strings, it creates invalid Date objects
				expect( isNaN( result.newStartDate.getTime() ) ).toBe( true );
				expect( isNaN( result.newEndDate.getTime() ) ).toBe( true );
			} );

			it( 'should handle empty date string', () => {
				const result = getNewStartEndDates( endDate, startDate, 'startDate', '', false );

				// Empty string creates invalid Date
				expect( isNaN( result.newStartDate.getTime() ) ).toBe( true );
			} );
		} );

		describe( 'notification logic', () => {
			it( 'should not notify for dates/times that did not change', () => {
				const newDate = '2024-01-15T11:00:00'; // Only time changed

				const result = getNewStartEndDates( endDate, startDate, 'startTime', newDate, false );

				expect( result.notify.startDate ).toBe( false ); // Date didn't change
				expect( result.notify.endDate ).toBe( false ); // End date didn't change
			} );

			it( 'should notify when implicit date change occurs', () => {
				const newDate = '2024-01-20T10:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'startDate', newDate, false );

				expect( result.notify.endDate ).toBe( true );
			} );

			it( 'should notify when implicit time change occurs', () => {
				const newDate = '2024-01-15T19:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'startTime', newDate, false );

				expect( result.notify.endTime ).toBe( true );
			} );
		} );

		describe( 'multi-day vs single-day behavior', () => {
			it( 'should not update end date when changing start date in multi-day mode', () => {
				const newDate = '2024-01-20T10:00:00';

				const result = getNewStartEndDates(
					endDate,
					startDate,
					'startDate',
					newDate,
					true // Multi-day enabled
				);

				expect( result.newEndDate ).toEqual( endDate );
			} );

			it( 'should update end date when changing start date in single-day mode', () => {
				const newDate = '2024-01-20T10:00:00';

				const result = getNewStartEndDates(
					endDate,
					startDate,
					'startDate',
					newDate,
					false // Multi-day disabled
				);

				expect( result.newEndDate.getDate() ).toBe( 20 ); // Same day as new start
			} );
		} );

		describe( 'edge cases', () => {
			it( 'should handle same start and end times', () => {
				const sameDate = new Date( '2024-01-15 10:00:00' );
				const newDate = '2024-01-20T10:00:00';

				const result = getNewStartEndDates( sameDate, sameDate, 'startDate', newDate, false );

				expect( result.newStartDate ).toBeDefined();
				expect( result.newEndDate ).toBeDefined();
			} );

			it( 'should handle end time exactly at start time boundary', () => {
				const newDate = '2024-01-15T10:00:00'; // Exactly at start time

				const result = getNewStartEndDates( endDate, startDate, 'endTime', newDate, false );

				expect( result.newStartDate.getTime() ).toBeLessThanOrEqual( result.newEndDate.getTime() );
			} );

			it( 'should handle midnight times correctly', () => {
				const midnightStart = new Date( '2024-01-15 00:00:00' );
				const midnightEnd = new Date( '2024-01-15 23:59:00' );
				const newDate = '2024-01-20T00:00:00';

				const result = getNewStartEndDates( midnightEnd, midnightStart, 'startDate', newDate, false );

				expect( result.newStartDate.getHours() ).toBe( 0 );
				expect( result.newStartDate.getMinutes() ).toBe( 0 );
			} );

			it( 'should handle leap to very distant future date', () => {
				const newDate = '2025-12-31T10:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'startDate', newDate, false );

				expect( result.newStartDate.getFullYear() ).toBe( 2025 );
				expect( result.newStartDate.getMonth() ).toBe( 11 ); // December
			} );

			it( 'should handle leap to past date', () => {
				const newDate = '2023-01-01T10:00:00';

				const result = getNewStartEndDates( endDate, startDate, 'startDate', newDate, false );

				expect( result.newStartDate.getFullYear() ).toBe( 2023 );
			} );
		} );
	} );
} );
