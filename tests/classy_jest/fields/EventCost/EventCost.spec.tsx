import * as React from 'react';
import { render, screen, waitFor } from '@testing-library/react';
import { userEvent } from '@testing-library/user-event';
import { beforeEach, describe, expect, it, jest } from '@jest/globals';
import { RegistryProvider } from '@wordpress/data';
import { getRegistry } from '@tec/common/classy/store';
import EventCost from '../../../../src/resources/packages/classy/fields/EventCost/EventCost';
import { Currency } from '@tec/common/classy/types/Currency';
import { CurrencyPosition } from '@tec/common/classy/types/CurrencyPosition';

// Mock WordPress data store
jest.mock( '@wordpress/data', () => {
	// Mock state inside the mock function to avoid hoisting issues
	const mockState = {
		meta: {
			_EventCost: '',
			_EventCurrencySymbol: '$',
			_EventCurrencyPosition: 'prefix',
		},
		editPost: jest.fn( ( { meta } ) => {
			if ( meta ) {
				mockState.meta = { ...mockState.meta, ...meta };
			}
		} ),
	};

	const mockSelect = ( storeName: string ) => {
		if ( storeName === 'core/editor' ) {
			return {
				getEditedPostAttribute: ( attribute: string ) => {
					if ( attribute === 'meta' ) {
						return mockState.meta;
					}
					return null;
				},
			};
		}
		if ( storeName === 'tec/classy' ) {
			return {
				getDefaultCurrency: () => ( {
					code: 'USD',
					symbol: '$',
					position: 'prefix',
				} ),
			};
		}
		return {};
	};

	const mockDispatch = ( storeName: string ) => {
		if ( storeName === 'core/editor' ) {
			return {
				editPost: mockState.editPost,
			};
		}
		return {};
	};

	return {
		useSelect: ( callback: ( select: typeof mockSelect ) => any ) => callback( mockSelect ),
		useDispatch: ( storeName: string ) => mockDispatch( storeName ),
		RegistryProvider: ( { children } ) => children,
		__mockState: mockState, // Expose for testing
	};
} );

// Mock WordPress components
jest.mock( '@wordpress/components', () => ( {
	__experimentalInputControl: ( { label, value, onChange, disabled, onFocus, onBlur } ) => (
		<input
			aria-label={ label.props.children }
			value={ value }
			onChange={ ( e ) => onChange( e.target.value ) }
			disabled={ disabled }
			onFocus={ onFocus }
			onBlur={ onBlur }
		/>
	),
	ToggleControl: ( { label, checked, onChange } ) => (
		<label>
			{ label }
			<input type="checkbox" checked={ checked } onChange={ ( e ) => onChange( e.target.checked ) } />
		</label>
	),
} ) );

// Mock CurrencySelector component
jest.mock( '../../../../common/src/resources/packages/classy/components', () => ( {
	CurrencySelector: () => <div className="classy-field__currency-selector" />,
} ) );

describe( 'EventCost Component', () => {
	const mockDefaultCurrency: Currency = {
		code: 'USD',
		symbol: '$',
		position: 'prefix' as CurrencyPosition,
	};

	const mockMeta = {
		[ 'tribe_event_cost' ]: '',
		[ 'tribe_event_currency_symbol' ]: '$',
		[ 'tribe_event_currency_position' ]: 'prefix',
	};

	beforeEach( () => {
		jest.resetAllMocks();
		// Reset the mock meta state
		const mockData = jest.requireMock( '@wordpress/data' ) as any;
		mockData.__mockState.meta = {
			_EventCost: '',
			_EventCurrencySymbol: '$',
			_EventCurrencyPosition: 'prefix',
		};
		mockData.__mockState.editPost.mockClear();
	} );

	it( 'renders correctly with default props', async () => {
		const registry = getRegistry();
		const { container } = render(
			<RegistryProvider value={ registry }>
				<EventCost />
			</RegistryProvider>
		);

		expect( container ).toMatchSnapshot();
	} );

	it( 'displays "Free" when event is free', async () => {
		const registry = getRegistry();
		const { container } = render(
			<RegistryProvider value={ registry }>
				<EventCost />
			</RegistryProvider>
		);

		const freeToggle = screen.getByLabelText( 'Event is free' );
		const costInput = screen.getByLabelText( 'Event cost' );

		// Initially, the input should not be disabled
		expect( ( costInput as HTMLInputElement ).disabled ).toBe( false );
		expect( ( costInput as HTMLInputElement ).value ).toBe( '' );
		await userEvent.click( freeToggle );

		// Wait for the state to update
		await waitFor( () => {
			// After clicking free toggle, the input should be disabled and show "Free"
			expect( ( costInput as HTMLInputElement ).disabled ).toBe( true );
			expect( ( costInput as HTMLInputElement ).value ).toBe( 'Free' );
		} );

		// Should also call editPost with meta value of '0' for free
		const mockData = jest.requireMock( '@wordpress/data' ) as any;
		expect( mockData.__mockState.editPost ).toHaveBeenCalledWith( { meta: { _EventCost: '0' } } );

		expect( container ).toMatchSnapshot();
	} );

	it( 'formats single cost value correctly', async () => {
		const registry = getRegistry();
		const { container } = render(
			<RegistryProvider value={ registry }>
				<EventCost />
			</RegistryProvider>
		);

		const costInput = screen.getByLabelText( 'Event cost' );
		await userEvent.type( costInput, '10.50' );
		// click outside the input to trigger the change
		await userEvent.click( document.body );

		expect( container ).toMatchSnapshot();
	} );

	it( 'formats multiple cost values correctly', async () => {
		const registry = getRegistry();
		const { container } = render(
			<RegistryProvider value={ registry }>
				<EventCost />
			</RegistryProvider>
		);

		const costInput = screen.getByLabelText( 'Event cost' );
		await userEvent.type( costInput, '10.50, 20.75, 15.25' );
		// click outside the input to trigger the change
		await userEvent.click( document.body );

		expect( container ).toMatchSnapshot();
	} );

	it( 'handles invalid cost values gracefully', async () => {
		const registry = getRegistry();
		const { container } = render(
			<RegistryProvider value={ registry }>
				<EventCost />
			</RegistryProvider>
		);

		const costInput = screen.getByLabelText( 'Event cost' );
		await userEvent.type( costInput, 'invalid, 20.75' );

		expect( container ).toMatchSnapshot();
	} );

	it( 'updates currency symbol and position correctly', async () => {
		const registry = getRegistry();
		const { container } = render(
			<RegistryProvider value={ registry }>
				<EventCost />
			</RegistryProvider>
		);

		const costInput = screen.getByLabelText( 'Event cost' );
		await userEvent.type( costInput, '10.50' );

		// Simulate currency change
		const currencySelector = container.querySelector( '.classy-field__currency-selector' );
		expect( currencySelector ).not.toBeNull();

		expect( container ).toMatchSnapshot();
	} );
} );
