// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import * as React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {afterEach, beforeEach, describe, expect, it, jest} from '@jest/globals';

// Mock the `@wordpress/data` package to intercept the `useDispatch` and `useSelect` hooks.
jest.mock( '@wordpress/data', () => ( {
	...( jest.requireActual( '@wordpress/data' ) as Object ),
	select: jest.fn(),
	useSelect: jest.fn(),
	useDispatch: jest.fn(),
} ) );
import TestProvider from "../_support/TestProvider";

import EventCost from '../../../src/resources/packages/classy/fields/EventCost/EventCost';

// Get the @wordpress/data module mocked functions.
const mockSelect = jest.mocked( require( '@wordpress/data' ).select );
const mockUseSelect = jest.mocked( require( '@wordpress/data' ).useSelect );
const mockUseDispatch = jest.mocked( require( '@wordpress/data' ).useDispatch );

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
		position: 'prefix',
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
]

describe( 'EventCost', () => {
	beforeEach( () => {
		jest.resetModules();
		jest.clearAllMocks();
	} );

	afterEach( () => {
		jest.resetAllMocks();
		jest.restoreAllMocks();
		jest.resetModules();
	} );

	it( 'should render the event cost component with default values', () => {
		mockSelect.mockImplementation((store: string): any => {
			if (store === 'core/editor') {
				return {
					getEditedPostAttribute: (attribute: string): any => {
						return attribute === 'meta' ? {} : null;
					}
				}
			}

			if (store === 'tec/classy') {
				return {
					getCurrencyOptions: () => currencyOptions,
					getDefaultCurrency: () => currencyOptions[0],
				}
			}
		});
		mockUseSelect.mockImplementation((selector: Function): any => {
			return selector(mockSelect);
		});
		mockUseDispatch.mockImplementation((store: string): Function[] => {
			if (store === 'core/editor') {
				return {
					editPost: jest.fn()
				}
			}
		});

		render(<TestProvider><EventCost/></TestProvider>);

		// Check that the main elements are rendered.
		expect( screen.getByText( 'Event cost' ) ).toBeInTheDocument();
		expect( screen.getByLabelText( 'Event is free' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Enter a single price or a price range (e.g. 10-20)' ) ).toBeInTheDocument();
	} );
} );
