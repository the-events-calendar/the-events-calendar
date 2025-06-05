import React, { useEffect, useState } from 'react';
import { __, _x } from '@wordpress/i18n';
import { Button, Popover, SelectControl, ToggleControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	METADATA_EVENT_CURRENCY,
	METADATA_EVENT_CURRENCY_POSITION,
	METADATA_EVENT_CURRENCY_SYMBOL,
} from '../../constants';
import { IconClose } from '@tec/common/classy/components';

type CurrencySelectorProps = {
	/**
	 * The title of the currency selector field.
	 */
	title?: string;
};

type CurrencyPosition = 'prefix' | 'postfix';

type CurrencyProps = {
	symbol: string;
	currency: string;
	position: CurrencyPosition;
};

// todo: Replace with API call to fetch available currencies.
const Currencies: CurrencyProps[] = [
	{ symbol: '$', currency: 'USD', position: 'prefix' },
	{ symbol: '€', currency: 'EUR', position: 'prefix' },
	{ symbol: '£', currency: 'GBP', position: 'prefix' },
	{ symbol: '¥', currency: 'JPY', position: 'prefix' },
	{ symbol: '₹', currency: 'INR', position: 'prefix' },
];

type CurrencySelectOption = {
	key: string;
	value: string;
	label: string;
};

const currencyDefaultOption: CurrencySelectOption = {
	key: '0',
	label: _x( 'Default site currency', 'Default option for the currency selector', 'the-events-calendar' ),
	value: 'default',
};

const buildOptionFromCurrency = ( currency: CurrencyProps ): CurrencySelectOption => {
	return {
		key: currency.currency,
		label: `${ currency.symbol } ${ currency.currency }`,
		value: currency.currency,
	};
};

const mapCurrenciesToOptions = ( currencies: CurrencyProps[] ): CurrencySelectOption[] => {
	return currencies.map( buildOptionFromCurrency );
};

export default function CurrencySelector( props: CurrencySelectorProps ) {
	const { meta } = useSelect( ( select ) => {
		const selector = select( 'core/editor' );
		return {
			// @ts-ignore
			meta: selector.getEditedPostAttribute( 'meta' ) || {},
		};
	}, [] );

	const { editPost } = useDispatch( 'core/editor' );

	// todo: pull the default currency from the store using the settings.
	const defaultCurrency: string = 'USD';
	const defaultCurrencySymbol: string = '$';
	const defaultCurrencyPosition: CurrencyPosition = 'prefix';

	const eventCurrencyMeta: string = meta[ METADATA_EVENT_CURRENCY ] || defaultCurrency;
	const [ eventCurrency, setEventCurrency ] = useState< string >( eventCurrencyMeta );

	const eventCurrencySymbolMeta: string = meta[ METADATA_EVENT_CURRENCY_SYMBOL ] || defaultCurrencySymbol;
	const [ currencySymbol, setCurrencySymbol ] = useState< string >( eventCurrencySymbolMeta );

	const eventCurrencyPosition: CurrencyPosition =
		meta[ METADATA_EVENT_CURRENCY_POSITION ] ||
		Currencies.find( ( currency ) => currency.currency === eventCurrency )?.position ||
		defaultCurrencyPosition;
	const [ currencyPosition, setCurrencyPosition ] = useState< CurrencyPosition >( eventCurrencyPosition );

	useEffect( () => {
		setEventCurrency( eventCurrencyMeta );
	}, [ eventCurrencyMeta ] );

	useEffect( () => {
		setCurrencyPosition( eventCurrencyPosition );
	}, [ eventCurrencyPosition ] );

	useEffect( () => {
		setCurrencySymbol( eventCurrencySymbolMeta );
	}, [ eventCurrencySymbolMeta ] );

	const onCurrencyChange = ( nextValue: string | undefined ): void => {
		const selectedCurrency: CurrencyProps = Currencies.find( ( currency ) => currency.currency === nextValue );
		if ( ! selectedCurrency || nextValue === 'default' ) {
			setEventCurrency( defaultCurrency );
			setCurrencySymbol( defaultCurrencySymbol );
			setCurrencyPosition( defaultCurrencyPosition );
			editPost( {
				meta: {
					[ METADATA_EVENT_CURRENCY ]: defaultCurrency,
					[ METADATA_EVENT_CURRENCY_SYMBOL ]: defaultCurrencySymbol,
					[ METADATA_EVENT_CURRENCY_POSITION ]: defaultCurrencyPosition,
				},
			} );
			return;
		}

		setEventCurrency( selectedCurrency.currency );
		setCurrencySymbol( selectedCurrency.symbol );
		editPost( {
			meta: {
				[ METADATA_EVENT_CURRENCY ]: selectedCurrency.currency,
				[ METADATA_EVENT_CURRENCY_SYMBOL ]: selectedCurrency.symbol,
			},
		} );
	};

	useEffect( () => {
		setCurrencySymbol( eventCurrencySymbolMeta );
	}, [ eventCurrencySymbolMeta ] );

	const onCurrencyPositionChange = ( nextValue: boolean ): void => {
		const newPosition = nextValue ? 'prefix' : 'postfix';
		setCurrencyPosition( newPosition );
		editPost( { meta: { [ METADATA_EVENT_CURRENCY_POSITION ]: newPosition } } );
	};

	const [ isSelectingCurrency, setIsSelectingCurrency ] = useState< boolean >( false );

	const onCurrencyClick = (): void => {
		setIsSelectingCurrency( ! isSelectingCurrency );
	};

	const renderCurrency = (): string => {
		if ( ! currencySymbol || ! eventCurrency ) {
			return '';
		}

		if ( currencyPosition === 'prefix' ) {
			return `${ currencySymbol }${ eventCurrency }`;
		}

		return `${ eventCurrency }${ currencySymbol }`;
	};

	const currencyOptions = [ currencyDefaultOption, ...mapCurrenciesToOptions( Currencies ) ];

	const onClose = (): void => {
		setIsSelectingCurrency( false );
	};

	return (
		<div className="classy-field classy-field--currency-selector">
			<Button className="is-link--dark" variant="link" onClick={ onCurrencyClick }>
				{ renderCurrency() }
			</Button>

			{ isSelectingCurrency && (
				<Popover
					className="classy-component__popover classy-component__popover--choice"
					expandOnMobile={ true }
					placement="bottom-start"
					noArrow={ true }
					offset={ 4 }
					onClose={ () => setIsSelectingCurrency( false ) }
				>
					<div className="classy-component__popover-content">
						<Button variant="link" onClick={ onClose } className="classy-component__popover-close">
							<IconClose />
						</Button>

						<h4 className="classy-component__popover-title">
							{ _x( 'Currency', 'Event currency selector title', 'the-events-calendar' ) }
						</h4>

						<p className="classy-component__popover-description">
							{ __(
								'Choose a different currency than your default for this event.',
								'the-events-calendar'
							) }
						</p>

						<SelectControl
							label={ _x( 'Currency', 'Event currency selector label', 'the-events-calendar' ) }
							hideLabelFromVision={ true }
							value={ eventCurrency }
							onChange={ onCurrencyChange }
							options={ currencyOptions }
							__nextHasNoMarginBottom={ true }
							__next40pxDefaultSize={ true }
						/>

						<ToggleControl
							label={ _x(
								'Currency symbol precedes price',
								'Event currency position toggle label',
								'the-events-calendar'
							) }
							checked={ currencyPosition === 'prefix' }
							onChange={ onCurrencyPositionChange }
						/>
					</div>
				</Popover>
			) }
		</div>
	);
}
