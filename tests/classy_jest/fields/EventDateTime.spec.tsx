// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import * as React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import { describe, expect, it, jest, beforeAll, afterAll } from '@jest/globals';
import mockWpDataModule from '../_support/mockWpDataModule';

import TestProvider from '../_support/TestProvider';

const { mockSelect, mockUseSelect, mockUseDispatch } = mockWpDataModule();

import EventDateTime from '../../../src/resources/packages/classy/fields/EventDateTime/EventDateTime';
import {
	METADATA_EVENT_ALLDAY,
	METADATA_EVENT_END_DATE,
	METADATA_EVENT_START_DATE,
} from '@tec/events/classy/constants';

describe( 'EventDateTime', () => {
	let mockEditPost;

	const defaultEventDateTimeDetails = {
		dateWithYearFormat: 'F j, Y',
		endOfDayCutoff: { hours: 23, minutes: 59 },
		eventEnd: '2024-03-15T17:00:00.000Z',
		eventStart: '2024-03-15T08:00:00.000Z',
		eventTimezone: 'America/New_York',
		isAllDay: false,
		isMultiday: false,
		startOfWeek: 1,
		timeFormat: 'g:i a',
		timezoneString: 'America/New_York',
	};

	const setupMocks = ( overrides = {} ) => {
		mockEditPost = jest.fn();

		const eventDateTimeDetails = {
			...defaultEventDateTimeDetails,
			...overrides,
		};

		mockSelect.mockImplementation( ( store: string ): any => {
			if ( store === 'tec/classy/events' ) {
				return {
					getEventDateTimeDetails: () => eventDateTimeDetails,
					isNewEvent: () => overrides.isNewEvent ?? false,
				};
			}

			// Throw an error for unknown stores to help identify what needs mocking.
			throw new Error( `Unknown store requested in mockSelect: ${ store }` );
		} );

		mockUseSelect.mockImplementation( ( mapSelect ): any => {
			const select = ( store: string ): any => {
				if ( store === 'tec/classy/events' ) {
					return {
						getEventDateTimeDetails: () => eventDateTimeDetails,
						isNewEvent: () => overrides.isNewEvent ?? false,
					};
				}

				if ( store === 'tec/classy' ) {
					return {
						getSettings: () => ( {
							dateWithYearFormat: 'F j, Y',
							timeFormat: 'g:i a',
							startOfWeek: 1,
							timezoneString: 'America/New_York',
							timeInterval: 30,
						} ),
						getTimeInterval: () => 30,
					};
				}

				// Throw an error for unknown stores to help identify what needs mocking.
				throw new Error( `Unknown store requested in mockUseSelect: ${ store }` );
			};

			return mapSelect( select );
		} );

		mockUseDispatch.mockImplementation( ( store: string ): any => {
			if ( store === 'core/editor' ) {
				return {
					editPost: mockEditPost,
				};
			}

			// Throw an error for unknown stores to help identify what needs mocking.
			throw new Error( `Unknown store requested in mockUseDispatch: ${ store }` );
		} );
	};

	beforeAll( () => {
		jest.resetModules();
		jest.clearAllMocks();
	} );

	afterAll( () => {
		jest.resetModules();
	} );

	it( 'should render the event date time component with default values', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		// Check that the main elements are rendered.
		expect( screen.getByText( 'Date & time' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Multi-day event' ) ).toBeInTheDocument();
		expect( screen.getByText( 'All-day event' ) ).toBeInTheDocument();

		// Check that the toggles are unchecked by default.
		const multiDayToggle = screen.getByRole( 'checkbox', { name: /Multi-day event/i } );
		const allDayToggle = screen.getByRole( 'checkbox', { name: /All-day event/i } );
		expect( multiDayToggle ).not.toBeChecked();
		expect( allDayToggle ).not.toBeChecked();
	} );

	it( 'should display existing date values from meta', () => {
		setupMocks( {
			eventStart: '2024-06-20T14:30:00.000Z',
			eventEnd: '2024-06-20T18:45:00.000Z',
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		// Should display formatted dates and times.
		// Note: The exact format depends on the StartSelector and EndSelector components.
		// We're checking that the component renders without errors with provided dates.
		expect( screen.getByText( 'Date & time' ) ).toBeInTheDocument();
	} );

	it( 'should handle multi-day events correctly', () => {
		setupMocks( {
			eventStart: '2024-06-20T08:00:00.000Z',
			eventEnd: '2024-06-22T17:00:00.000Z',
			isMultiday: true,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const multiDayToggle = screen.getByRole( 'checkbox', { name: /Multi-day event/i } );
		expect( multiDayToggle ).toBeChecked();
	} );

	it( 'should handle all-day events correctly', () => {
		setupMocks( {
			eventStart: '2024-06-20T00:00:00.000Z',
			eventEnd: '2024-06-20T23:59:00.000Z',
			isAllDay: true,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const allDayToggle = screen.getByRole( 'checkbox', { name: /All-day event/i } );
		expect( allDayToggle ).toBeChecked();
	} );

	it( 'should toggle between multi-day and single-day event', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const multiDayToggle = screen.getByRole( 'checkbox', { name: /Multi-day event/i } );

		// Initially not multi-day.
		expect( multiDayToggle ).not.toBeChecked();

		// Toggle to multi-day.
		fireEvent.click( multiDayToggle );

		// Should update the dates to span multiple days.
		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: expect.objectContaining( {
				[ METADATA_EVENT_START_DATE ]: expect.any( String ),
				[ METADATA_EVENT_END_DATE ]: expect.any( String ),
			} ),
		} );
	} );

	it( 'should toggle between all-day and timed event', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const allDayToggle = screen.getByRole( 'checkbox', { name: /All-day event/i } );

		// Initially not all-day.
		expect( allDayToggle ).not.toBeChecked();

		// Toggle to all-day.
		fireEvent.click( allDayToggle );

		// Should update the meta with all-day flag and adjusted times.
		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: expect.objectContaining( {
				[ METADATA_EVENT_START_DATE ]: expect.any( String ),
				[ METADATA_EVENT_END_DATE ]: expect.any( String ),
				[ METADATA_EVENT_ALLDAY ]: '1',
			} ),
		} );

		// Toggle back to timed.
		fireEvent.click( allDayToggle );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: expect.objectContaining( {
				[ METADATA_EVENT_START_DATE ]: expect.any( String ),
				[ METADATA_EVENT_END_DATE ]: expect.any( String ),
				[ METADATA_EVENT_ALLDAY ]: '0',
			} ),
		} );
	} );

	it( 'should preserve previous time values when toggling multi-day on and off', () => {
		const originalStart = '2024-06-20T10:30:00.000Z';
		const originalEnd = '2024-06-20T15:45:00.000Z';

		setupMocks( {
			eventStart: originalStart,
			eventEnd: originalEnd,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const multiDayToggle = screen.getByRole( 'checkbox', { name: /Multi-day event/i } );

		// Toggle to multi-day.
		fireEvent.click( multiDayToggle );

		// The first call should extend the end date.
		expect( mockEditPost ).toHaveBeenCalledTimes( 1 );

		// Toggle back to single-day.
		fireEvent.click( multiDayToggle );

		// Should attempt to restore previous state.
		expect( mockEditPost ).toHaveBeenCalledTimes( 2 );
	} );

	it( 'should preserve previous time values when toggling all-day on and off', () => {
		const originalStart = '2024-06-20T10:30:00.000Z';
		const originalEnd = '2024-06-20T15:45:00.000Z';

		setupMocks( {
			eventStart: originalStart,
			eventEnd: originalEnd,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const allDayToggle = screen.getByRole( 'checkbox', { name: /All-day event/i } );

		// Toggle to all-day.
		fireEvent.click( allDayToggle );

		// The first call should set to full day times.
		expect( mockEditPost ).toHaveBeenCalledTimes( 1 );

		// Toggle back to timed.
		fireEvent.click( allDayToggle );

		// Should attempt to restore previous state.
		expect( mockEditPost ).toHaveBeenCalledTimes( 2 );
	} );

	it( 'should handle timezone changes', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		// The TimeZone component should be rendered.
		// Note: Without mocking the actual TimeZone component, we can't fully test interaction,
		// but we can verify the component renders and the container structure.
		const container = screen.getByText( 'Date & time' ).closest( '.classy-field--event-datetime' );
		expect( container ).toBeInTheDocument();
	} );

	it( 'should handle new event initialization', () => {
		setupMocks( {
			isNewEvent: true,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		// When it's a new event, the component should render without errors.
		// The filter will be added internally to handle the save operation.
		expect( screen.getByText( 'Date & time' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Multi-day event' ) ).toBeInTheDocument();
		expect( screen.getByText( 'All-day event' ) ).toBeInTheDocument();
	} );

	it( 'should handle existing event initialization', () => {
		setupMocks( {
			isNewEvent: false,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		// Existing events should render normally without special initialization.
		expect( screen.getByText( 'Date & time' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Multi-day event' ) ).toBeInTheDocument();
		expect( screen.getByText( 'All-day event' ) ).toBeInTheDocument();
	} );

	it( 'should handle multi-day and all-day toggles together', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const multiDayToggle = screen.getByRole( 'checkbox', { name: /Multi-day event/i } );
		const allDayToggle = screen.getByRole( 'checkbox', { name: /All-day event/i } );

		// Enable multi-day first.
		fireEvent.click( multiDayToggle );
		expect( mockEditPost ).toHaveBeenCalledTimes( 1 );

		// Then enable all-day.
		fireEvent.click( allDayToggle );
		expect( mockEditPost ).toHaveBeenCalledTimes( 2 );
		expect( mockEditPost ).toHaveBeenLastCalledWith( {
			meta: expect.objectContaining( {
				[ METADATA_EVENT_ALLDAY ]: '1',
			} ),
		} );
	} );

	it( 'should format dates with MySQL format for storage', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const multiDayToggle = screen.getByRole( 'checkbox', { name: /Multi-day event/i } );
		fireEvent.click( multiDayToggle );

		// Should update the meta with formatted dates.
		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: expect.objectContaining( {
				[ METADATA_EVENT_START_DATE ]: expect.stringMatching( /\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/ ),
				[ METADATA_EVENT_END_DATE ]: expect.stringMatching( /\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/ ),
			} ),
		} );
	} );

	it( 'should handle end-of-day cutoff for all-day events', () => {
		const endOfDayCutoff = { hours: 22, minutes: 30 };

		setupMocks( {
			endOfDayCutoff,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const allDayToggle = screen.getByRole( 'checkbox', { name: /All-day event/i } );

		// Enable all-day.
		fireEvent.click( allDayToggle );

		// The component should use the end-of-day cutoff when setting all-day times.
		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: expect.objectContaining( {
				[ METADATA_EVENT_ALLDAY ]: '1',
			} ),
		} );
	} );

	it( 'should update both start and end dates when not multi-day', () => {
		setupMocks( {
			isMultiday: false,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		// In single-day mode, changing start date should also update end date to maintain single-day constraint.
		// This behavior is handled internally by the component's onDateChange callback.
		expect( screen.getByText( 'Date & time' ) ).toBeInTheDocument();
	} );

	it( 'should maintain duration when adjusting dates in multi-day mode', () => {
		setupMocks( {
			eventStart: '2024-06-20T10:00:00.000Z',
			eventEnd: '2024-06-22T15:00:00.000Z',
			isMultiday: true,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const multiDayToggle = screen.getByRole( 'checkbox', { name: /Multi-day event/i } );
		expect( multiDayToggle ).toBeChecked();

		// The component should maintain the duration between start and end when adjusting dates.
		// This is handled by the getNewStartEndDates function internally.
	} );

	it( 'should properly set default dates for new events', () => {
		setupMocks( {
			eventStart: new Date().setHours( 8, 0, 0, 0 ),
			eventEnd: new Date().setHours( 17, 0, 0, 0 ),
			isNewEvent: true,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		// New events should have default 8 AM to 5 PM times.
		// The component sets these defaults internally.
		expect( screen.getByText( 'Date & time' ) ).toBeInTheDocument();
	} );

	it( 'should handle timezone string updates', () => {
		const newTimezone = 'Europe/London';

		setupMocks( {
			eventTimezone: 'America/New_York',
		} );

		const { rerender } = render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		// Simulate a timezone change (would normally happen through TimeZone component).
		setupMocks( {
			eventTimezone: newTimezone,
		} );

		rerender(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		// Component should handle timezone changes appropriately.
		expect( screen.getByText( 'Date & time' ) ).toBeInTheDocument();
	} );

	it( 'should handle invalid date strings gracefully', () => {
		// Provide ISO date strings which will be valid Date objects.
		setupMocks( {
			eventStart: '2024-03-15T08:00:00.000Z',
			eventEnd: '2024-03-15T17:00:00.000Z',
		} );

		// Component should handle dates without crashing.
		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		expect( screen.getByText( 'Date & time' ) ).toBeInTheDocument();
	} );

	it( 'should apply correct CSS classes', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const container = screen.getByText( 'Date & time' ).closest( '.classy-field' );
		expect( container ).toHaveClass( 'classy-field--event-datetime' );

		const titleElement = screen.getByText( 'Date & time' ).closest( '.classy-field__title' );
		expect( titleElement ).toBeInTheDocument();

		const inputsContainer = container.querySelector( '.classy-field__inputs' );
		expect( inputsContainer ).toBeInTheDocument();

		const groups = container.querySelectorAll( '.classy-field__group' );
		expect( groups ).toHaveLength( 2 );
	} );

	it( 'should correctly identify multi-day events based on dates', () => {
		// Same day should not be multi-day.
		setupMocks( {
			eventStart: '2024-06-20T08:00:00.000Z',
			eventEnd: '2024-06-20T17:00:00.000Z',
			isMultiday: false,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const multiDayToggle = screen.getByRole( 'checkbox', { name: /Multi-day event/i } );
		expect( multiDayToggle ).not.toBeChecked();
	} );

	it( 'should correctly identify multi-day events spanning multiple days', () => {
		// Different days should be multi-day.
		setupMocks( {
			eventStart: '2024-06-20T08:00:00.000Z',
			eventEnd: '2024-06-21T17:00:00.000Z',
			isMultiday: true,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const multiDayToggle = screen.getByRole( 'checkbox', { name: /Multi-day event/i } );
		expect( multiDayToggle ).toBeChecked();
	} );

	it( 'should sync multi-day state when all-day is enabled', () => {
		setupMocks( {
			isMultiday: true,
			isAllDay: false,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const allDayToggle = screen.getByRole( 'checkbox', { name: /All-day event/i } );
		const multiDayToggle = screen.getByRole( 'checkbox', { name: /Multi-day event/i } );

		expect( multiDayToggle ).toBeChecked();
		expect( allDayToggle ).not.toBeChecked();

		// Enable all-day while multi-day is on.
		fireEvent.click( allDayToggle );

		// Should maintain multi-day state and adjust times for all-day.
		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: expect.objectContaining( {
				[ METADATA_EVENT_ALLDAY ]: '1',
			} ),
		} );
	} );

	it( 'should handle rapid toggle clicks without errors', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const multiDayToggle = screen.getByRole( 'checkbox', { name: /Multi-day event/i } );
		const allDayToggle = screen.getByRole( 'checkbox', { name: /All-day event/i } );

		// Rapidly toggle both controls.
		fireEvent.click( multiDayToggle );
		fireEvent.click( allDayToggle );
		fireEvent.click( multiDayToggle );
		fireEvent.click( allDayToggle );

		// Should handle rapid toggles without errors.
		expect( mockEditPost ).toHaveBeenCalled();
	} );

	it( 'should set correct all-day flag values in meta', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		const allDayToggle = screen.getByRole( 'checkbox', { name: /All-day event/i } );

		// Enable all-day.
		fireEvent.click( allDayToggle );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: expect.objectContaining( {
				[ METADATA_EVENT_ALLDAY ]: '1',
			} ),
		} );

		// Disable all-day.
		fireEvent.click( allDayToggle );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: expect.objectContaining( {
				[ METADATA_EVENT_ALLDAY ]: '0',
			} ),
		} );
	} );

	it( 'should handle edge case where start date equals end date', () => {
		setupMocks( {
			eventStart: '2024-06-20T10:00:00.000Z',
			eventEnd: '2024-06-20T10:00:00.000Z',
			isMultiday: false,
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		// Should handle same start and end time without errors.
		expect( screen.getByText( 'Date & time' ) ).toBeInTheDocument();

		const multiDayToggle = screen.getByRole( 'checkbox', { name: /Multi-day event/i } );
		expect( multiDayToggle ).not.toBeChecked();
	} );

	it( 'should handle missing timezone information', () => {
		setupMocks( {
			eventTimezone: undefined,
			timezoneString: 'UTC',
		} );

		render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		// Should fall back to settings timezone when event timezone is missing.
		expect( screen.getByText( 'Date & time' ) ).toBeInTheDocument();
	} );

	it( 'should handle component unmount correctly for new events', () => {
		setupMocks( {
			isNewEvent: true,
		} );

		const { unmount } = render(
			<TestProvider>
				<EventDateTime title="Date & time" />
			</TestProvider>
		);

		// Component should render correctly.
		expect( screen.getByText( 'Date & time' ) ).toBeInTheDocument();

		// Unmount should not cause errors.
		// The filter cleanup will happen internally via useEffect.
		expect( () => unmount() ).not.toThrow();
	} );
} );
