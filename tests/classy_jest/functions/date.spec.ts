// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import { describe, expect, it, jest, beforeEach } from '@jest/globals';

import { isAllDayForCutoff, isMultiDayForCutoff, getDurationInDaysForCutoff } from '@tec/events/classy/functions/date';

describe( 'Date Functions', () => {
	beforeEach( () => {
		jest.clearAllMocks();
	} );

	describe( 'isAllDayForCutoff', () => {
		it( 'should return true when start date matches cutoff and end date is one minute before', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };
			const startDate = new Date( '2024-01-15 23:59:00' );
			const endDate = new Date( '2024-01-15 23:58:00' );

			const result = isAllDayForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( true );
		} );

		it( 'should return false when start date does not match cutoff hours', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };
			const startDate = new Date( '2024-01-15 10:00:00' );
			const endDate = new Date( '2024-01-15 23:58:00' );

			const result = isAllDayForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( false );
		} );

		it( 'should return false when start date does not match cutoff minutes', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };
			const startDate = new Date( '2024-01-15 23:30:00' );
			const endDate = new Date( '2024-01-15 23:58:00' );

			const result = isAllDayForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( false );
		} );

		it( 'should return false when end date does not match expected cutoff end time', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };
			const startDate = new Date( '2024-01-15 23:59:00' );
			const endDate = new Date( '2024-01-15 22:00:00' );

			const result = isAllDayForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( false );
		} );
	} );

	describe( 'isMultiDayForCutoff', () => {
		it( 'should return false for single-day event within same calendar day', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };
			const startDate = new Date( '2024-01-15 10:00:00' );
			const endDate = new Date( '2024-01-15 18:00:00' );

			const result = isMultiDayForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( false );
		} );

		it( 'should return true for event spanning different calendar days', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };
			const startDate = new Date( '2024-01-15 10:00:00' );
			const endDate = new Date( '2024-01-16 18:00:00' );

			const result = isMultiDayForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( true );
		} );

		it( 'should return false for event crossing midnight but within same cutoff day', () => {
			const endOfDayCutoff = { hours: 3, minutes: 0 }; // 3 AM cutoff
			const startDate = new Date( '2024-01-15 22:00:00' ); // 10 PM
			const endDate = new Date( '2024-01-16 02:00:00' ); // 2 AM next day (before cutoff)

			const result = isMultiDayForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( false );
		} );

		it( 'should return true for event crossing midnight and beyond cutoff', () => {
			const endOfDayCutoff = { hours: 3, minutes: 0 }; // 3 AM cutoff
			const startDate = new Date( '2024-01-15 22:00:00' ); // 10 PM
			const endDate = new Date( '2024-01-16 04:00:00' ); // 4 AM next day (after cutoff)

			const result = isMultiDayForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( true );
		} );

		it( 'should return false when both times are before cutoff on same calendar day', () => {
			const endOfDayCutoff = { hours: 6, minutes: 0 }; // 6 AM cutoff
			const startDate = new Date( '2024-01-15 02:00:00' ); // 2 AM on day 15 (before cutoff - part of day 14)
			const endDate = new Date( '2024-01-15 03:00:00' ); // 3 AM on day 15 (before cutoff - part of day 14)

			const result = isMultiDayForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( false );
		} );

		it( 'should return true for multi-day event with different cutoff days', () => {
			const endOfDayCutoff = { hours: 6, minutes: 0 }; // 6 AM cutoff
			const startDate = new Date( '2024-01-15 10:00:00' ); // 10 AM
			const endDate = new Date( '2024-01-16 10:00:00' ); // 10 AM next day

			const result = isMultiDayForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( true );
		} );

		it( 'should return false for event starting and ending at exactly the cutoff time on same day', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };
			const startDate = new Date( '2024-01-15 23:59:00' );
			const endDate = new Date( '2024-01-15 23:59:00' );

			const result = isMultiDayForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( false );
		} );

		it( 'should handle events spanning multiple calendar days (3+ days)', () => {
			const endOfDayCutoff = { hours: 23, minutes: 59 };
			const startDate = new Date( '2024-01-15 10:00:00' );
			const endDate = new Date( '2024-01-18 18:00:00' ); // 3 days later

			const result = isMultiDayForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( true );
		} );

		it( 'should return true for event starting before cutoff and ending after cutoff next day', () => {
			const endOfDayCutoff = { hours: 4, minutes: 0 }; // 4 AM cutoff
			const startDate = new Date( '2024-01-15 02:00:00' ); // 2 AM (before cutoff - part of day 14)
			const endDate = new Date( '2024-01-15 10:00:00' ); // 10 AM (after cutoff - part of day 15)

			const result = isMultiDayForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( true );
		} );
	} );

	describe( 'getDurationInDaysForCutoff', () => {
		it( 'should return 0 for event within same effective day', () => {
			const endOfDayCutoff = { hours: 3, minutes: 0 }; // 3 AM cutoff
			const startDate = new Date( '2024-01-15 10:00:00' );
			const endDate = new Date( '2024-01-15 18:00:00' );

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 0 );
		} );

		it( 'should return 1 for event spanning two effective days', () => {
			const endOfDayCutoff = { hours: 3, minutes: 0 }; // 3 AM cutoff
			const startDate = new Date( '2024-01-15 10:00:00' );
			const endDate = new Date( '2024-01-16 10:00:00' );

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 1 );
		} );

		it( 'should return 2 for event spanning three effective days', () => {
			const endOfDayCutoff = { hours: 3, minutes: 0 }; // 3 AM cutoff
			const startDate = new Date( '2024-01-15 10:00:00' );
			const endDate = new Date( '2024-01-17 10:00:00' );

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 2 );
		} );

		it( 'should return 3 for event spanning four effective days', () => {
			const endOfDayCutoff = { hours: 3, minutes: 0 }; // 3 AM cutoff
			const startDate = new Date( '2024-01-15 10:00:00' );
			const endDate = new Date( '2024-01-18 10:00:00' );

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 3 );
		} );

		it( 'should return 0 for event crossing midnight but within same cutoff day', () => {
			const endOfDayCutoff = { hours: 3, minutes: 0 }; // 3 AM cutoff
			const startDate = new Date( '2024-01-15 22:00:00' ); // 10 PM
			const endDate = new Date( '2024-01-16 02:00:00' ); // 2 AM next day (before cutoff)

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 0 );
		} );

		it( 'should return 1 for event crossing midnight and beyond cutoff', () => {
			const endOfDayCutoff = { hours: 3, minutes: 0 }; // 3 AM cutoff
			const startDate = new Date( '2024-01-15 22:00:00' ); // 10 PM
			const endDate = new Date( '2024-01-16 04:00:00' ); // 4 AM next day (after cutoff)

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 1 );
		} );

		it( 'should return 0 when both times are before cutoff on same calendar day', () => {
			const endOfDayCutoff = { hours: 6, minutes: 0 }; // 6 AM cutoff
			const startDate = new Date( '2024-01-15 02:00:00' ); // 2 AM on day 15 (before cutoff - part of day 14)
			const endDate = new Date( '2024-01-15 03:00:00' ); // 3 AM on day 15 (before cutoff - part of day 14)

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 0 );
		} );

		it( 'should return 1 for event starting before cutoff and ending after cutoff same calendar day', () => {
			const endOfDayCutoff = { hours: 4, minutes: 0 }; // 4 AM cutoff
			const startDate = new Date( '2024-01-15 02:00:00' ); // 2 AM (before cutoff - part of day 14)
			const endDate = new Date( '2024-01-15 10:00:00' ); // 10 AM (after cutoff - part of day 15)

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 1 );
		} );

		it( 'should return 0 for event starting and ending at exactly the cutoff time', () => {
			const endOfDayCutoff = { hours: 3, minutes: 0 }; // 3 AM cutoff
			const startDate = new Date( '2024-01-15 03:00:00' );
			const endDate = new Date( '2024-01-15 03:00:00' );

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 0 );
		} );

		it( 'should return 1 for event starting at cutoff and ending at cutoff next day', () => {
			const endOfDayCutoff = { hours: 3, minutes: 0 }; // 3 AM cutoff
			const startDate = new Date( '2024-01-15 03:00:00' );
			const endDate = new Date( '2024-01-16 03:00:00' );

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 1 );
		} );

		it( 'should handle early morning cutoff correctly', () => {
			const endOfDayCutoff = { hours: 6, minutes: 0 }; // 6 AM cutoff
			const startDate = new Date( '2024-01-15 10:00:00' ); // 10 AM
			const endDate = new Date( '2024-01-16 10:00:00' ); // 10 AM next day

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 1 );
		} );

		it( 'should handle non-standard cutoff times (e.g., 1:30 AM)', () => {
			const endOfDayCutoff = { hours: 1, minutes: 30 }; // 1:30 AM cutoff
			const startDate = new Date( '2024-01-15 22:00:00' ); // 10 PM
			const endDate = new Date( '2024-01-16 01:00:00' ); // 1 AM next day (before cutoff)

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 0 );
		} );

		it( 'should handle event starting before cutoff on one day and ending before cutoff on next calendar day', () => {
			const endOfDayCutoff = { hours: 5, minutes: 0 }; // 5 AM cutoff
			const startDate = new Date( '2024-01-15 02:00:00' ); // 2 AM on day 15 (part of effective day 14)
			const endDate = new Date( '2024-01-16 03:00:00' ); // 3 AM on day 16 (part of effective day 15)

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 1 );
		} );

		it( 'should return 0 for very short events within same effective day', () => {
			const endOfDayCutoff = { hours: 3, minutes: 0 }; // 3 AM cutoff
			const startDate = new Date( '2024-01-15 14:00:00' );
			const endDate = new Date( '2024-01-15 14:30:00' ); // 30 minutes later

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 0 );
		} );

		it( 'should handle events spanning a week correctly', () => {
			const endOfDayCutoff = { hours: 3, minutes: 0 }; // 3 AM cutoff
			const startDate = new Date( '2024-01-15 10:00:00' );
			const endDate = new Date( '2024-01-22 10:00:00' ); // 7 days later

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 7 );
		} );

		it( 'should handle midnight cutoff (00:00) correctly', () => {
			const endOfDayCutoff = { hours: 0, minutes: 0 }; // Midnight cutoff
			const startDate = new Date( '2024-01-15 10:00:00' );
			const endDate = new Date( '2024-01-16 10:00:00' );

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 1 );
		} );

		it( 'should handle maximum cutoff (10:00) correctly', () => {
			const endOfDayCutoff = { hours: 10, minutes: 0 }; // 10 AM cutoff
			const startDate = new Date( '2024-01-15 11:00:00' ); // After cutoff
			const endDate = new Date( '2024-01-16 11:00:00' ); // After cutoff next day

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 1 );
		} );

		it( 'should handle event crossing cutoff at maximum allowed time (10:00)', () => {
			const endOfDayCutoff = { hours: 10, minutes: 0 }; // 10 AM cutoff
			const startDate = new Date( '2024-01-15 08:00:00' ); // Before cutoff (part of day 14)
			const endDate = new Date( '2024-01-15 12:00:00' ); // After cutoff (part of day 15)

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 1 );
		} );

		it( 'should handle 30-minute interval cutoff (06:30)', () => {
			const endOfDayCutoff = { hours: 6, minutes: 30 }; // 6:30 AM cutoff
			const startDate = new Date( '2024-01-15 10:00:00' );
			const endDate = new Date( '2024-01-16 10:00:00' );

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 1 );
		} );

		it( 'should return 0 for event before midnight cutoff on same calendar day', () => {
			const endOfDayCutoff = { hours: 0, minutes: 0 }; // Midnight cutoff
			const startDate = new Date( '2024-01-15 10:00:00' );
			const endDate = new Date( '2024-01-15 18:00:00' );

			const result = getDurationInDaysForCutoff( endOfDayCutoff, startDate, endDate );

			expect( result ).toBe( 0 );
		} );
	} );
} );
