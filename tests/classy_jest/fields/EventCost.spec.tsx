// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import * as React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import { describe, expect, it, jest } from '@jest/globals';
import mockWpDataModule from '../_support/mockWpDataModule';

import TestProvider from '../_support/TestProvider';

const { mockSelect, mockUseSelect, mockUseDispatch } = mockWpDataModule();

import EventCost from '../../../src/resources/packages/classy/fields/EventCost/EventCost';
import {
	METADATA_EVENT_COST,
	METADATA_EVENT_CURRENCY_POSITION,
	METADATA_EVENT_CURRENCY_SYMBOL,
} from '@tec/events/classy/constants';

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
		symbol: '€',
	},
	// British Pound
	{
		code: 'GBP',
		label: 'British Pound',
		position: 'prefix',
		symbol: '£',
	},
	// Japanese Yen
	{
		code: 'JPY',
		label: 'Japanese Yen',
		position: 'prefix',
		symbol: '¥',
	},
	// Chinese Yuan
	{
		code: 'CNY',
		label: 'Chinese Yuan',
		position: 'prefix',
		symbol: '¥',
	},
];

describe( 'EventCost', () => {
	let mockEditPost;

	const setupMocks = ( meta = {} ) => {
		mockEditPost = jest.fn();

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

			// Throw an error for unknown stores to help identify what needs mocking
			throw new Error( `Unknown store requested in mockSelect: ${ store }` );
		} );

		mockUseDispatch.mockImplementation( ( store: string ): any => {
			if ( store === 'core/editor' ) {
				return {
					editPost: mockEditPost,
				};
			}

			// Throw an error for unknown stores to help identify what needs mocking
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

	it( 'should render the event cost component with default values', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		// Check that the main elements are rendered
		expect( screen.getByText( 'Event cost' ) ).toBeInTheDocument();
		expect( screen.getByLabelText( 'Event is free' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Enter a single price or a price range (e.g. 10-20)' ) ).toBeInTheDocument();

		// Check the input field is enabled and empty by default
		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		expect( costInput ).toBeEnabled();
		expect( costInput ).toHaveValue( '' );

		// Check that the free toggle is unchecked by default
		const freeToggle = screen.getByRole( 'checkbox', { name: /Event is free/i } );
		expect( freeToggle ).not.toBeChecked();
	} );

	it( 'should display existing cost value from meta', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '25.50',
		} );

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		expect( costInput ).toHaveValue( '$25.50' );
	} );

	it( 'should format currency with custom symbol and position', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '100',
			[ METADATA_EVENT_CURRENCY_SYMBOL ]: '€',
			[ METADATA_EVENT_CURRENCY_POSITION ]: 'postfix',
		} );

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		expect( costInput ).toHaveValue( '100.00€' );
	} );

	it( 'should handle free events correctly', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '0',
		} );

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		const freeToggle = screen.getByRole( 'checkbox', { name: /Event is free/i } );

		// Should display "Free" and have the toggle checked
		expect( costInput ).toHaveValue( 'Free' );
		expect( costInput ).toBeDisabled();
		expect( freeToggle ).toBeChecked();
	} );

	it( 'should toggle between free and paid event', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		const freeToggle = screen.getByRole( 'checkbox', { name: /Event is free/i } );

		// Initially not free
		expect( freeToggle ).not.toBeChecked();
		expect( costInput ).toBeEnabled();

		// Toggle to free
		fireEvent.click( freeToggle );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '0' },
		} );
		expect( costInput ).toHaveValue( 'Free' );
		expect( costInput ).toBeDisabled();

		// Toggle back to paid
		fireEvent.click( freeToggle );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '' },
		} );
		expect( costInput ).toBeEnabled();
	} );

	it( 'should preserve previous cost value when toggling free on and off', async () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '50.00',
		} );

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		const freeToggle = screen.getByRole( 'checkbox', { name: /Event is free/i } );

		// Initially has a cost
		expect( costInput ).toHaveValue( '$50.00' );

		// Toggle to free
		fireEvent.click( freeToggle );
		expect( costInput ).toHaveValue( 'Free' );

		// Toggle back to paid - should restore previous value
		fireEvent.click( freeToggle );
		expect( mockEditPost ).toHaveBeenLastCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '50.00' },
		} );
	} );

	it( 'should update cost value when typing', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// Focus and type a new value
		fireEvent.focus( costInput );
		fireEvent.change( costInput, { target: { value: '35.99' } } );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '35.99' },
		} );

		// While focused, should show raw value
		expect( costInput ).toHaveValue( '35.99' );

		// After blur, should show formatted value
		fireEvent.blur( costInput );
		expect( costInput ).toHaveValue( '$35.99' );
	} );

	it( 'should strip prefix currency symbol from user input', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// User types with the "$" prefix symbol.
		fireEvent.focus( costInput );
		fireEvent.change( costInput, { target: { value: '$35.99' } } );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '35.99' },
		} );

		// trigger focus again.
		fireEvent.blur( costInput );
		fireEvent.focus( costInput );
		// While focused, input shows raw value.
		expect( costInput ).toHaveValue( '35.99' );

		// On blur, value is formatted with the symbol.
		fireEvent.blur( costInput );
		expect( costInput ).toHaveValue( '$35.99' );
	} );

	it( 'should strip postfix currency symbol from user input', async () => {
		setupMocks( {
			[ METADATA_EVENT_CURRENCY_SYMBOL ]: '€',
			[ METADATA_EVENT_CURRENCY_POSITION ]: 'postfix',
		} );

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// User types with the "€" postfix symbol.
		fireEvent.focus( costInput );
		fireEvent.change( costInput, { target: { value: '35.99€' } } );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '35.99' },
		} );

		// trigger focus again.
		fireEvent.blur( costInput );
		fireEvent.focus( costInput );

		// While focused, input shows cleaned value without symbol.
		expect( costInput ).toHaveValue( '35.99' );

		// On blur, value is formatted with the symbol postfix.
		fireEvent.blur( costInput );
		expect( costInput ).toHaveValue( '35.99€' );
	} );

	it( 'should strip currency symbols in typed ranges', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// User types a range with symbols on both numbers.
		fireEvent.focus( costInput );
		fireEvent.change( costInput, { target: { value: '$10 - $20' } } );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '10 - 20' },
		} );

		// trigger focus again.
		fireEvent.blur( costInput );
		fireEvent.focus( costInput );
		// While focused, cleaned range is shown without symbols.
		expect( costInput ).toHaveValue( '10 - 20' );

		// On blur, formatted range restores symbols.
		fireEvent.blur( costInput );
		expect( costInput ).toHaveValue( '$10.00 - $20.00' );
	} );

	it( 'should format price ranges correctly', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '10-20',
		} );

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// Should display formatted range
		expect( costInput ).toHaveValue( '$10.00 - $20.00' );

		// When focused, should show raw value
		fireEvent.focus( costInput );
		expect( costInput ).toHaveValue( '10-20' );

		// When blurred, should format again
		fireEvent.blur( costInput );
		expect( costInput ).toHaveValue( '$10.00 - $20.00' );
	} );

	it( 'should handle multiple prices with formatting', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '15 - 25 - 35',
		} );

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// Should show min to max range
		expect( costInput ).toHaveValue( '$15.00 - $35.00' );
	} );

	it( 'should format single price correctly', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '99.9',
		} );

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// Should format to 2 decimal places
		expect( costInput ).toHaveValue( '$99.90' );
	} );

	it( 'should handle invalid price values gracefully', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: 'invalid',
		} );

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// Should display as $0.00 when invalid
		expect( costInput ).toHaveValue( '$0.00' );
	} );

	it( 'should clear cost when input is emptied', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '50',
		} );

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// Clear the input
		fireEvent.focus( costInput );
		fireEvent.change( costInput, { target: { value: '' } } );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '' },
		} );

		expect( costInput ).toHaveValue( '' );
	} );

	it( 'should work with postfix currency position', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '45.50',
			[ METADATA_EVENT_CURRENCY_SYMBOL ]: '€',
			[ METADATA_EVENT_CURRENCY_POSITION ]: 'postfix',
		} );

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		expect( costInput ).toHaveValue( '45.50€' );
	} );

	it( 'should format price range with postfix currency', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '20-50',
			[ METADATA_EVENT_CURRENCY_SYMBOL ]: '€',
			[ METADATA_EVENT_CURRENCY_POSITION ]: 'postfix',
		} );

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		expect( costInput ).toHaveValue( '20.00€ - 50.00€' );
	} );

	it( 'should not format value when input has focus', () => {
		setupMocks( {
			[ METADATA_EVENT_COST ]: '123.456',
		} );

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );

		// Initially formatted
		expect( costInput ).toHaveValue( '$123.46' );

		// Focus shows raw value
		fireEvent.focus( costInput );
		expect( costInput ).toHaveValue( '123.456' );

		// Blur formats again
		fireEvent.blur( costInput );
		expect( costInput ).toHaveValue( '$123.46' );
	} );

	it( 'should handle edge cases in price ranges', () => {
		// Test with spaces and extra dashes
		setupMocks( {
			[ METADATA_EVENT_COST ]: '  10  -  -  20  ',
		} );

		const { rerender } = render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		expect( costInput ).toHaveValue( '$10.00 - $20.00' );

		// Test with same min and max
		setupMocks( {
			[ METADATA_EVENT_COST ]: '30-30',
		} );

		rerender(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		expect( costInput ).toHaveValue( '$30.00' );
	} );

	it( 'should integrate with CurrencySelector component', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		// Check that CurrencySelector is rendered (by looking for the currency button)
		// The button text format depends on the currency position
		expect( screen.getByRole( 'button', { name: /\$ USD/ } ) ).toBeInTheDocument();
	} );

	it( 'should disabled cost input when free is checked', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		const freeToggle = screen.getByRole( 'checkbox', { name: /Event is free/i } );

		// Initially enabled
		expect( costInput ).toBeEnabled();

		// Check free toggle
		fireEvent.click( freeToggle );

		// Should be disabled
		expect( costInput ).toBeDisabled();
		expect( costInput ).toHaveValue( 'Free' );
	} );

	it( 'should remember cost value after toggling free multiple times', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventCost />
			</TestProvider>
		);

		const costInput = screen.getByRole( 'textbox', { name: /Event cost/i } );
		const freeToggle = screen.getByRole( 'checkbox', { name: /Event is free/i } );

		// Enter a cost
		fireEvent.focus( costInput );
		fireEvent.change( costInput, { target: { value: '75' } } );
		fireEvent.blur( costInput );

		// Toggle to free
		fireEvent.click( freeToggle );
		expect( costInput ).toHaveValue( 'Free' );

		// Toggle back
		fireEvent.click( freeToggle );
		expect( mockEditPost ).toHaveBeenLastCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '75' },
		} );

		// Toggle to free again
		fireEvent.click( freeToggle );

		// Toggle back again - should still remember
		fireEvent.click( freeToggle );
		expect( mockEditPost ).toHaveBeenLastCalledWith( {
			meta: { [ METADATA_EVENT_COST ]: '75' },
		} );
	} );
} );
