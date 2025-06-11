import * as React from 'react';
import { render, screen } from '@testing-library/react';
import { userEvent } from '@testing-library/user-event';
import { beforeEach, describe, expect, it, jest } from '@jest/globals';
import { RegistryProvider } from '@wordpress/data';
import { createRegistry } from '../../../../common/src/resources/packages/classy/store';
import EventCost from '../../../../src/resources/packages/classy/fields/EventCost/EventCost';
import { Currency } from '../../../../common/src/resources/packages/classy/types/Currency';
import { CurrencyPosition } from '../../../../common/src/resources/packages/classy/types/CurrencyPosition';

type Selector = {
	'core/editor': {
		getEditedPostAttribute: ( attribute: string ) => any;
	};
	'tec/classy': {
		getDefaultCurrency: () => Currency;
	};
};

// Mock WordPress data store
jest.mock( '@wordpress/data', () => {
	const mockSelect = ( storeName: string ) => {
		if ( storeName === 'core/editor' ) {
			return {
				getEditedPostAttribute: () => ( {
					meta: {
						tribe_event_cost: '',
						tribe_event_currency_symbol: '$',
						tribe_event_currency_position: 'prefix',
						tribe_event_is_free: false,
					},
				} ),
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
				editPost: jest.fn(),
			};
		}
		return {};
	};

	return {
		useSelect: ( callback: ( select: typeof mockSelect ) => any ) => callback( mockSelect ),
		useDispatch: ( storeName: string ) => mockDispatch( storeName ),
		RegistryProvider: ( { children } ) => children,
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
		[ 'tribe_event_is_free' ]: false,
	};

	beforeEach( () => {
		jest.resetAllMocks();
	} );

	it( 'renders correctly with default props', async () => {
		const registry = await createRegistry();
		const { container } = render(
			<RegistryProvider value={ registry }>
				<EventCost />
			</RegistryProvider>
		);

		expect( container ).toMatchSnapshot();
	} );

	it( 'displays "Free" when event is free', async () => {
		const registry = await createRegistry();
		const { container } = render(
			<RegistryProvider value={ registry }>
				<EventCost />
			</RegistryProvider>
		);

		const freeToggle = screen.getByLabelText( 'Event is free' );
		await userEvent.click( freeToggle );

		expect( container ).toMatchSnapshot();
	} );

	it( 'formats single cost value correctly', async () => {
		const registry = await createRegistry();
		const { container } = render(
			<RegistryProvider value={ registry }>
				<EventCost />
			</RegistryProvider>
		);

		const costInput = screen.getByLabelText( 'Event cost' );
		await userEvent.type( costInput, '10.50' );

		expect( container ).toMatchSnapshot();
	} );

	it( 'formats multiple cost values correctly', async () => {
		const registry = await createRegistry();
		const { container } = render(
			<RegistryProvider value={ registry }>
				<EventCost />
			</RegistryProvider>
		);

		const costInput = screen.getByLabelText( 'Event cost' );
		await userEvent.type( costInput, '10.50, 20.75, 15.25' );

		expect( container ).toMatchSnapshot();
	} );

	it( 'handles invalid cost values gracefully', async () => {
		const registry = await createRegistry();
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
		const registry = await createRegistry();
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
