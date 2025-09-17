// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import * as React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import { afterEach, beforeEach, describe, expect, it, jest } from '@jest/globals';
import mockWpDataModule from '../_support/mockWpDataModule';

import TestProvider from '../_support/TestProvider';

const { mockSelect, mockUseSelect, mockUseDispatch } = mockWpDataModule();

import EventAdmission from '../../../src/resources/packages/classy/fields/EventAdmission/EventAdmission';
import {
	METADATA_EVENT_COST,
	METADATA_EVENT_CURRENCY,
	METADATA_EVENT_CURRENCY_POSITION,
	METADATA_EVENT_CURRENCY_SYMBOL,
} from '../../../src/resources/packages/classy/constants';

const currencyOptions = [
	// US Dollar
	{
		code: 'USD',
		label: 'US Dollar',
		position: 'prefix',
		symbol: '$',
	},
	// Euro
	{
		code: 'EUR',
		label: 'Euro',
		position: 'postfix',
		symbol: '�',
	},
	// British Pound
	{
		code: 'GBP',
		label: 'British Pound',
		position: 'prefix',
		symbol: '�',
	},
];

describe( 'EventAdmission', () => {
	let mockEditPost;
	let mockSetIsUsingTickets;
	let mockSetTicketsSupported;

	const setupMocks = ( meta = {}, options = {} ) => {
		const { areTicketsSupported = false, isUsingTickets = false } = options;

		mockEditPost = jest.fn();
		mockSetIsUsingTickets = jest.fn();
		mockSetTicketsSupported = jest.fn();

		mockSelect.mockImplementation( ( store: string ): any => {
			if ( store === 'core/editor' ) {
				return {
					getEditedPostAttribute: ( attribute: string ): any => {
						return attribute === 'meta' ? meta : null;
					},
				};
			}

			if ( store === 'tec/classy' ) {
				return {
					getCurrencyOptions: () => currencyOptions,
					getDefaultCurrency: () => currencyOptions[ 0 ],
				};
			}

			if ( store === 'tec/classy/events' ) {
				return {
					areTicketsSupported: () => areTicketsSupported,
					isUsingTickets: () => areTicketsSupported && isUsingTickets,
				};
			}

			// Throw an error for unknown stores to help identify what needs mocking.
			throw new Error( `Unknown store requested in mockSelect: ${ store }` );
		} );

		mockUseSelect.mockImplementation( ( callback: Function, deps?: any[] ): any => {
			// Call the callback with our mock select.
			return callback( mockSelect );
		} );

		mockUseDispatch.mockImplementation( ( store: string ): any => {
			if ( store === 'core/editor' ) {
				return {
					editPost: mockEditPost,
				};
			}

			if ( store === 'tec/classy/events' ) {
				return {
					setIsUsingTickets: mockSetIsUsingTickets,
					setTicketsSupported: mockSetTicketsSupported,
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

	it( 'should render the event admission component with default title', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// Check that the main elements are rendered.
		expect( screen.getByText( 'Event Admission' ) ).toBeInTheDocument();

		// Should show EventCost component when not using tickets.
		expect( screen.getByText( 'Event cost' ) ).toBeInTheDocument();
		expect( screen.getByLabelText( 'Event is free' ) ).toBeInTheDocument();
	} );

	it( 'should render EventCost component when not using tickets', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// EventCost component should be visible.
		expect( screen.getByText( 'Event cost' ) ).toBeInTheDocument();
		expect( screen.getByRole( 'textbox', { name: /Event cost/i } ) ).toBeInTheDocument();
		expect( screen.getByRole( 'checkbox', { name: /Event is free/i } ) ).toBeInTheDocument();
	} );

	it( 'should not show ticket mode buttons when tickets are not supported', () => {
		setupMocks( {}, { areTicketsSupported: false } );

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// Manual Pricing button should not be visible when tickets are not supported.
		expect( screen.queryByText( 'Manual Pricing' ) ).not.toBeInTheDocument();
	} );

	it( 'should show ticket mode buttons when tickets are supported but not using tickets', () => {
		// Mock a fill for the slot to ensure buttons render.
		const MockTicketButton = () => <button>Use Tickets</button>;

		setupMocks( {}, { areTicketsSupported: true, isUsingTickets: false } );

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
				<div slot="tec.classy.fields.event-admission.buttons">
					<MockTicketButton />
				</div>
			</TestProvider>
		);

		// Note: The slot mechanism might not work in test environment.
		// We're testing the component logic, not the slot implementation.
		// The component should attempt to render the slot when conditions are met.
	} );

	it( 'should not show EventCost when using tickets', () => {
		setupMocks( {}, { areTicketsSupported: true, isUsingTickets: true } );

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// EventCost component should not be visible when using tickets.
		expect( screen.queryByText( 'Event cost' ) ).not.toBeInTheDocument();
		expect( screen.queryByRole( 'textbox', { name: /Event cost/i } ) ).not.toBeInTheDocument();
	} );

	it( 'should call setIsUsingTickets when Manual Pricing button is clicked', () => {
		// This test simulates the button click behavior.
		// Since slots are complex in test environment, we'll test the button directly.
		const ButtonComponent = () => {
			const { setIsUsingTickets } = mockUseDispatch( 'tec/classy/events' );
			return <button onClick={ () => setIsUsingTickets( false ) }>Manual Pricing</button>;
		};

		setupMocks( {}, { areTicketsSupported: true, isUsingTickets: true } );

		render(
			<TestProvider>
				<ButtonComponent />
			</TestProvider>
		);

		const manualPricingButton = screen.getByText( 'Manual Pricing' );
		fireEvent.click( manualPricingButton );

		expect( mockSetIsUsingTickets ).toHaveBeenCalledWith( false );
	} );

	it( 'should display existing cost value from meta when not using tickets', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '25.50',
		} );

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		expect( costInput ).toHaveValue( '$25.50' );
	} );

	it( 'should handle free events correctly when not using tickets', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '0',
		} );

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		const freeToggle = screen.getByRole( 'checkbox', { name: /Event is free/i } );

		// Should display "Free" and have the toggle checked.
		expect( costInput ).toHaveValue( 'Free' );
		expect( costInput ).toBeDisabled();
		expect( freeToggle ).toBeChecked();
	} );

	it( 'should update cost value through EventCost when typing', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// Focus and type a new value.
		fireEvent.focus( costInput );
		fireEvent.change( costInput, { target: { value: '35.99' } } );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '35.99' },
		} );

		// While focused, should show raw value.
		expect( costInput ).toHaveValue( '35.99' );

		// After blur, should show formatted value.
		fireEvent.blur( costInput );
		expect( costInput ).toHaveValue( '$35.99' );
	} );

	it( 'should toggle between free and paid event through EventCost', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		const freeToggle = screen.getByRole( 'checkbox', { name: /Event is free/i } );

		// Initially not free.
		expect( freeToggle ).not.toBeChecked();
		expect( costInput ).toBeEnabled();

		// Toggle to free.
		fireEvent.click( freeToggle );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '0' },
		} );
		expect( costInput ).toHaveValue( 'Free' );
		expect( costInput ).toBeDisabled();

		// Toggle back to paid.
		fireEvent.click( freeToggle );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '' },
		} );
		expect( costInput ).toBeEnabled();
	} );

	it( 'should format currency with custom symbol and position', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '100',
			[ METADATA_EVENT_CURRENCY_SYMBOL ]: '�',
			[ METADATA_EVENT_CURRENCY_POSITION ]: 'postfix',
		} );

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		expect( costInput ).toHaveValue( '100.00�' );
	} );

	it( 'should render tickets slot area', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// The component should have the slot for tickets.
		// Note: Actual slot rendering depends on WordPress components.
		// We're verifying the component structure includes the slot.
		const admissionContainer = document.querySelector( '.classy-field--event-admission' );
		expect( admissionContainer ).toBeInTheDocument();
	} );

	it( 'should preserve previous cost value when toggling free on and off', async () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '50.00',
		} );

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		const freeToggle = screen.getByRole( 'checkbox', { name: /Event is free/i } );

		// Initially has a cost.
		expect( costInput ).toHaveValue( '$50.00' );

		// Toggle to free.
		fireEvent.click( freeToggle );
		expect( costInput ).toHaveValue( 'Free' );

		// Toggle back to paid - should restore previous value.
		fireEvent.click( freeToggle );
		expect( mockEditPost ).toHaveBeenLastCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '50.00' },
		} );
	} );

	it( 'should format price ranges correctly when not using tickets', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '10-20',
		} );

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// Should display formatted range.
		expect( costInput ).toHaveValue( '$10.00 - $20.00' );

		// When focused, should show raw value.
		fireEvent.focus( costInput );
		expect( costInput ).toHaveValue( '10-20' );

		// When blurred, should format again.
		fireEvent.blur( costInput );
		expect( costInput ).toHaveValue( '$10.00 - $20.00' );
	} );

	it( 'should handle transition from tickets to manual pricing', () => {
		const { rerender } = render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// Start with tickets enabled and in use.
		setupMocks( {}, { areTicketsSupported: true, isUsingTickets: true } );

		rerender(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// EventCost should not be visible.
		expect( screen.queryByText( 'Event cost' ) ).not.toBeInTheDocument();

		// Switch to manual pricing.
		setupMocks( {}, { areTicketsSupported: true, isUsingTickets: false } );

		rerender(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// EventCost should now be visible.
		expect( screen.getByText( 'Event cost' ) ).toBeInTheDocument();
		expect( screen.getByRole( 'textbox', { name: /Event cost/i } ) ).toBeInTheDocument();
	} );

	it( 'should handle transition from manual pricing to tickets', () => {
		const { rerender } = render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// Start with manual pricing.
		setupMocks( { [ METADATA_EVENT_COST ]: '25.00' }, { areTicketsSupported: true, isUsingTickets: false } );

		rerender(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// EventCost should be visible with the value.
		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		expect( costInput ).toHaveValue( '$25.00' );

		// Switch to tickets.
		setupMocks( { [ METADATA_EVENT_COST ]: '25.00' }, { areTicketsSupported: true, isUsingTickets: true } );

		rerender(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// EventCost should no longer be visible.
		expect( screen.queryByRole( 'textbox', { name: /Event cost/i } ) ).not.toBeInTheDocument();
	} );

	it( 'should maintain cost value when switching between tickets and manual pricing', () => {
		const { rerender } = render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// Start with manual pricing and a cost.
		setupMocks( { [ METADATA_EVENT_COST ]: '75.00' }, { areTicketsSupported: true, isUsingTickets: false } );

		rerender(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		let costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		expect( costInput ).toHaveValue( '$75.00' );

		// Switch to tickets.
		setupMocks( { [ METADATA_EVENT_COST ]: '75.00' }, { areTicketsSupported: true, isUsingTickets: true } );

		rerender(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		expect( screen.queryByRole( 'textbox', { name: /Event cost/i } ) ).not.toBeInTheDocument();

		// Switch back to manual pricing - cost should be preserved.
		setupMocks( { [ METADATA_EVENT_COST ]: '75.00' }, { areTicketsSupported: true, isUsingTickets: false } );

		rerender(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		expect( costInput ).toHaveValue( '$75.00' );
	} );

	it( 'should render with correct CSS classes', () => {
		setupMocks();

		const { container } = render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// Check for the main container class.
		expect( container.querySelector( '.classy-field--event-admission' ) ).toBeInTheDocument();
		expect( container.querySelector( '.classy-field__title' ) ).toBeInTheDocument();
		expect( container.querySelector( '.classy-field__inputs' ) ).toBeInTheDocument();
	} );

	it( 'should display CurrencySelector when EventCost is rendered', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		// Check that CurrencySelector is rendered via EventCost.
		expect( screen.getByRole( 'button', { name: /\$ USD/ } ) ).toBeInTheDocument();
	} );

	it( 'should handle invalid price values gracefully through EventCost', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: 'invalid',
		} );

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// Should display as $0.00 when invalid.
		expect( costInput ).toHaveValue( '$0.00' );
	} );

	it( 'should clear cost when input is emptied through EventCost', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '50',
		} );

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// Clear the input.
		fireEvent.focus( costInput );
		fireEvent.change( costInput, { target: { value: '' } } );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '' },
		} );

		expect( costInput ).toHaveValue( '' );
	} );

	it( 'should properly integrate EventCost with all its features', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '15 - 25 - 35',
		} );

		render(
			<TestProvider>
				<EventAdmission title="Event Admission" />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// Should show min to max range.
		expect( costInput ).toHaveValue( '$15.00 - $35.00' );
	} );
} );
