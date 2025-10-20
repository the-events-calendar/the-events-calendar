// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import { describe, expect, it, jest, beforeEach } from '@jest/globals';

import { isAllDayForCutoff, isMultiDayForCutoff } from '@tec/events/classy/functions/date';

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
} );
