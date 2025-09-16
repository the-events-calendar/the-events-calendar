
// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import * as React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';
import { afterEach, beforeEach, describe, expect, it, jest } from '@jest/globals';
import EventCost from '../../../src/resources/packages/classy/fields/EventCost/EventCost';
import {
	METADATA_EVENT_COST,
	METADATA_EVENT_CURRENCY,
	METADATA_EVENT_CURRENCY_POSITION,
	METADATA_EVENT_CURRENCY_SYMBOL,
} from '../../../src/resources/packages/classy/constants';
import TestProvider from "../_support/TestProvider";

// Mock the `@wordpress/data` package to intercept the `useDispatch` and `useSelect` hooks.
jest.mock( '@wordpress/data', () => ( {
	...( jest.requireActual( '@wordpress/data' ) as Object ),
	useDispatch: jest.fn(),
	useSelect: jest.fn(),
} ) );

// Get the mocked functions.
const mockUseDispatch = jest.mocked( require( '@wordpress/data' ).useDispatch );
const mockUseSelect = jest.mocked( require( '@wordpress/data' ).useSelect );

describe( 'EventCost', () => {
	let mockEditPost: jest.Mock;
	let mockGetEditedPostAttribute: jest.Mock;
	let mockGetDefaultCurrency: jest.Mock;

	beforeEach( () => {
		jest.resetModules();
		jest.clearAllMocks();

		// Setup default mock functions.
		mockEditPost = jest.fn();
		mockGetEditedPostAttribute = jest.fn();
		mockGetDefaultCurrency = jest.fn();

		// Setup useDispatch mock.
		mockUseDispatch.mockReturnValue( {
			editPost: mockEditPost,
		} );

		// Setup default useSelect mock behavior.
		mockUseSelect.mockImplementation( ( selector ) => {
			const result = selector( ( storeName: string ) => {
				if ( storeName === 'core/editor' ) {
					return {
						getEditedPostAttribute: mockGetEditedPostAttribute,
					};
				}
				if ( storeName === 'tec/classy' ) {
					return {
						getDefaultCurrency: mockGetDefaultCurrency,
					};
				}
				return {};
			} );
			return result;
		} );

		// Default currency configuration.
		mockGetDefaultCurrency.mockReturnValue( {
			symbol: '$',
			position: 'prefix',
			code: 'USD',
		} );
	} );

	afterEach( () => {
		jest.resetAllMocks();
		jest.restoreAllMocks();
		jest.resetModules();
	} );

	it( 'should render the event cost component with default values', () => {
		// Setup empty meta.
		mockGetEditedPostAttribute.mockReturnValue( {} );

		render(<TestProvider>
			<EventCost/>
		</TestProvider>);

		// Check that the main elements are rendered.
		expect( screen.getByText( 'Event cost' ) ).toBeInTheDocument();
		expect( screen.getByLabelText( 'Event is free' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Enter a single price or a price range (e.g. 10-20)' ) ).toBeInTheDocument();
	} );

	// it( 'should display existing event cost from meta', () => {
	// 	// Setup meta with existing cost.
	// 	mockGetEditedPostAttribute.mockReturnValue( {
	// 		[ METADATA_EVENT_COST ]: '25.00',
	// 		[ METADATA_EVENT_CURRENCY_SYMBOL ]: '$',
	// 		[ METADATA_EVENT_CURRENCY_POSITION ]: 'prefix',
	// 	} );
	//
	// 	render( <EventCost /> );
	//
	// 	// Check that the cost is formatted and displayed.
	// 	const input = screen.getByDisplayValue( '$25.00' );
	// 	expect( input ).toBeInTheDocument();
	// } );
	//
	// it( 'should handle free events toggle', () => {
	// 	// Setup meta with a cost.
	// 	mockGetEditedPostAttribute.mockReturnValue( {
	// 		[ METADATA_EVENT_COST ]: '15.00',
	// 	} );
	//
	// 	render( <EventCost /> );
	//
	// 	const freeToggle = screen.getByLabelText( 'Event is free' );
	//
	// 	// Toggle to free.
	// 	fireEvent.click( freeToggle );
	//
	// 	// Check that editPost was called with cost set to 0.
	// 	expect( mockEditPost ).toHaveBeenCalledWith( {
	// 		meta: { [ METADATA_EVENT_COST ]: '0' },
	// 	} );
	//
	// 	// Check that the input shows "Free".
	// 	expect( screen.getByDisplayValue( 'Free' ) ).toBeInTheDocument();
	// } );
	//
	// it( 'should update event cost when user types a new value', async () => {
	// 	// Setup empty meta.
	// 	mockGetEditedPostAttribute.mockReturnValue( {} );
	//
	// 	render( <EventCost /> );
	//
	// 	const input = screen.getByRole( 'textbox', { name: /Event cost/i } );
	//
	// 	// Focus and type a new value.
	// 	fireEvent.focus( input );
	// 	fireEvent.change( input, { target: { value: '50.00' } } );
	//
	// 	// Check that editPost was called with the new value.
	// 	await waitFor( () => {
	// 		expect( mockEditPost ).toHaveBeenCalledWith( {
	// 			meta: { [ METADATA_EVENT_COST ]: '50.00' },
	// 		} );
	// 	} );
	// } );
	//
	// it( 'should format price ranges correctly', () => {
	// 	// Setup meta with a price range.
	// 	mockGetEditedPostAttribute.mockReturnValue( {
	// 		[ METADATA_EVENT_COST ]: '10 - 50',
	// 		[ METADATA_EVENT_CURRENCY_SYMBOL ]: '$',
	// 		[ METADATA_EVENT_CURRENCY_POSITION ]: 'prefix',
	// 	} );
	//
	// 	render( <EventCost /> );
	//
	// 	// Check that the range is formatted correctly.
	// 	const input = screen.getByDisplayValue( '$10.00 - $50.00' );
	// 	expect( input ).toBeInTheDocument();
	// } );
	//
	// it( 'should handle currency position postfix', () => {
	// 	// Setup meta with postfix currency position.
	// 	mockGetEditedPostAttribute.mockReturnValue( {
	// 		[ METADATA_EVENT_COST ]: '25.50',
	// 		[ METADATA_EVENT_CURRENCY_SYMBOL ]: '�',
	// 		[ METADATA_EVENT_CURRENCY_POSITION ]: 'postfix',
	// 	} );
	//
	// 	render( <EventCost /> );
	//
	// 	// Check that the currency is displayed as postfix.
	// 	const input = screen.getByDisplayValue( '25.50�' );
	// 	expect( input ).toBeInTheDocument();
	// } );
	//
	// it( 'should disable cost input when event is free', () => {
	// 	// Setup meta with cost set to 0 (free event).
	// 	mockGetEditedPostAttribute.mockReturnValue( {
	// 		[ METADATA_EVENT_COST ]: '0',
	// 	} );
	//
	// 	render( <EventCost /> );
	//
	// 	const input = screen.getByRole( 'textbox', { name: /Event cost/i } );
	// 	const freeToggle = screen.getByLabelText( 'Event is free' );
	//
	// 	// Check that input is disabled and toggle is checked.
	// 	expect( input ).toBeDisabled();
	// 	expect( freeToggle ).toBeChecked();
	// 	expect( screen.getByDisplayValue( 'Free' ) ).toBeInTheDocument();
	// } );
	//
	// it( 'should preserve previous cost value when toggling free on and off', () => {
	// 	// Setup meta with a cost.
	// 	mockGetEditedPostAttribute.mockReturnValue( {
	// 		[ METADATA_EVENT_COST ]: '30.00',
	// 	} );
	//
	// 	render( <EventCost /> );
	//
	// 	const freeToggle = screen.getByLabelText( 'Event is free' );
	//
	// 	// Toggle to free.
	// 	fireEvent.click( freeToggle );
	// 	expect( mockEditPost ).toHaveBeenCalledWith( {
	// 		meta: { [ METADATA_EVENT_COST ]: '0' },
	// 	} );
	//
	// 	// Clear the mock to check next call.
	// 	mockEditPost.mockClear();
	//
	// 	// Toggle back to paid.
	// 	fireEvent.click( freeToggle );
	// 	expect( mockEditPost ).toHaveBeenCalledWith( {
	// 		meta: { [ METADATA_EVENT_COST ]: '30.00' },
	// 	} );
	// } );
	//
	// it( 'should not format value when input has focus', () => {
	// 	// Setup meta with a simple number.
	// 	mockGetEditedPostAttribute.mockReturnValue( {
	// 		[ METADATA_EVENT_COST ]: '25',
	// 		[ METADATA_EVENT_CURRENCY_SYMBOL ]: '$',
	// 		[ METADATA_EVENT_CURRENCY_POSITION ]: 'prefix',
	// 	} );
	//
	// 	render( <EventCost /> );
	//
	// 	const input = screen.getByRole( 'textbox', { name: /Event cost/i } );
	//
	// 	// Initially should show formatted value.
	// 	expect( input ).toHaveValue( '$25.00' );
	//
	// 	// When focused, should show raw value.
	// 	fireEvent.focus( input );
	// 	expect( input ).toHaveValue( '25' );
	//
	// 	// When blurred, should show formatted value again.
	// 	fireEvent.blur( input );
	// 	expect( input ).toHaveValue( '$25.00' );
	// } );
	//
	// it( 'should handle empty cost value', () => {
	// 	// Setup empty meta.
	// 	mockGetEditedPostAttribute.mockReturnValue( {} );
	//
	// 	render( <EventCost /> );
	//
	// 	const input = screen.getByRole( 'textbox', { name: /Event cost/i } );
	//
	// 	// Clear the input.
	// 	fireEvent.focus( input );
	// 	fireEvent.change( input, { target: { value: '' } } );
	//
	// 	// Check that editPost was called with empty value.
	// 	expect( mockEditPost ).toHaveBeenCalledWith( {
	// 		meta: { [ METADATA_EVENT_COST ]: '' },
	// 	} );
	// } );
	//
	// it( 'should handle multiple price values separated by dashes', () => {
	// 	// Setup meta with multiple prices.
	// 	mockGetEditedPostAttribute.mockReturnValue( {
	// 		[ METADATA_EVENT_COST ]: '15 - 25 - 35',
	// 		[ METADATA_EVENT_CURRENCY_SYMBOL ]: '$',
	// 		[ METADATA_EVENT_CURRENCY_POSITION ]: 'prefix',
	// 	} );
	//
	// 	render( <EventCost /> );
	//
	// 	// Should display min and max values.
	// 	const input = screen.getByDisplayValue( '$15.00 - $35.00' );
	// 	expect( input ).toBeInTheDocument();
	// } );
	//
	// it( 'should handle single price value with trailing dash', () => {
	// 	// Setup meta with trailing dash.
	// 	mockGetEditedPostAttribute.mockReturnValue( {
	// 		[ METADATA_EVENT_COST ]: '20 -',
	// 		[ METADATA_EVENT_CURRENCY_SYMBOL ]: '$',
	// 		[ METADATA_EVENT_CURRENCY_POSITION ]: 'prefix',
	// 	} );
	//
	// 	render( <EventCost /> );
	//
	// 	// Should display single formatted value.
	// 	const input = screen.getByDisplayValue( '$20.00' );
	// 	expect( input ).toBeInTheDocument();
	// } );
	//
	// it( 'should use default currency when no currency is set in meta', () => {
	// 	// Setup meta without currency information.
	// 	mockGetEditedPostAttribute.mockReturnValue( {
	// 		[ METADATA_EVENT_COST ]: '15.00',
	// 	} );
	//
	// 	// Default currency from store.
	// 	mockGetDefaultCurrency.mockReturnValue( {
	// 		symbol: '�',
	// 		position: 'prefix',
	// 		code: 'GBP',
	// 	} );
	//
	// 	render( <EventCost /> );
	//
	// 	// Should use default currency.
	// 	const input = screen.getByDisplayValue( '�15.00' );
	// 	expect( input ).toBeInTheDocument();
	// } );
	//
	// it( 'should update state when meta changes externally', () => {
	// 	// Initial meta.
	// 	const initialMeta = {
	// 		[ METADATA_EVENT_COST ]: '20.00',
	// 		[ METADATA_EVENT_CURRENCY_SYMBOL ]: '$',
	// 		[ METADATA_EVENT_CURRENCY_POSITION ]: 'prefix',
	// 	};
	// 	mockGetEditedPostAttribute.mockReturnValue( initialMeta );
	//
	// 	const { rerender } = render( <EventCost /> );
	//
	// 	// Check initial value.
	// 	expect( screen.getByDisplayValue( '$20.00' ) ).toBeInTheDocument();
	//
	// 	// Update meta externally.
	// 	const updatedMeta = {
	// 		[ METADATA_EVENT_COST ]: '35.00',
	// 		[ METADATA_EVENT_CURRENCY_SYMBOL ]: '�',
	// 		[ METADATA_EVENT_CURRENCY_POSITION ]: 'postfix',
	// 	};
	// 	mockGetEditedPostAttribute.mockReturnValue( updatedMeta );
	//
	// 	// Trigger re-render.
	// 	rerender( <EventCost /> );
	//
	// 	// Check updated value.
	// 	expect( screen.getByDisplayValue( '35.00�' ) ).toBeInTheDocument();
	// } );
	//
	// it( 'should render CurrencySelector component with correct props', () => {
	// 	// Setup meta.
	// 	mockGetEditedPostAttribute.mockReturnValue( {} );
	//
	// 	render( <EventCost /> );
	//
	// 	// Check that CurrencySelector is rendered with correct meta keys.
	// 	const currencySelector = document.querySelector( '.classy-field__input--height-100' );
	// 	expect( currencySelector ).toBeInTheDocument();
	// } );
	//
	// it( 'should handle invalid numeric values gracefully', () => {
	// 	// Setup meta with non-numeric value.
	// 	mockGetEditedPostAttribute.mockReturnValue( {
	// 		[ METADATA_EVENT_COST ]: 'invalid',
	// 		[ METADATA_EVENT_CURRENCY_SYMBOL ]: '$',
	// 		[ METADATA_EVENT_CURRENCY_POSITION ]: 'prefix',
	// 	} );
	//
	// 	render( <EventCost /> );
	//
	// 	// Should display formatted zero for invalid values.
	// 	const input = screen.getByDisplayValue( '$0.00' );
	// 	expect( input ).toBeInTheDocument();
	// } );
	//
	// it( 'should format decimal values to 2 decimal places', () => {
	// 	// Setup meta with many decimal places.
	// 	mockGetEditedPostAttribute.mockReturnValue( {
	// 		[ METADATA_EVENT_COST ]: '19.99999',
	// 		[ METADATA_EVENT_CURRENCY_SYMBOL ]: '$',
	// 		[ METADATA_EVENT_CURRENCY_POSITION ]: 'prefix',
	// 	} );
	//
	// 	render( <EventCost /> );
	//
	// 	// Should round to 2 decimal places.
	// 	const input = screen.getByDisplayValue( '$20.00' );
	// 	expect( input ).toBeInTheDocument();
	// } );
	//
	// it( 'should handle equal min and max values in range', () => {
	// 	// Setup meta with equal values in range.
	// 	mockGetEditedPostAttribute.mockReturnValue( {
	// 		[ METADATA_EVENT_COST ]: '25 - 25',
	// 		[ METADATA_EVENT_CURRENCY_SYMBOL ]: '$',
	// 		[ METADATA_EVENT_CURRENCY_POSITION ]: 'prefix',
	// 	} );
	//
	// 	render( <EventCost /> );
	//
	// 	// Should display single value when min equals max.
	// 	const input = screen.getByDisplayValue( '$25.00' );
	// 	expect( input ).toBeInTheDocument();
	// } );
} );
