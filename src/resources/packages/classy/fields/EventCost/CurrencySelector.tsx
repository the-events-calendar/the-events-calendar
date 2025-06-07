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
import { CurrencyPosition } from '@tec/common/classy/types/CurrencyPosition';
import { Currency } from '@tec/common/classy/types/Currency';

type CurrencySelectorProps = {
	/**
	 * The title of the currency selector field.
	 */
	title?: string;
};

// todo: Replace with API call to fetch available currencies.
const Currencies: Currency[] = [
	{ symbol: '$', code: 'USD', position: 'prefix' },
	{ symbol: '€', code: 'EUR', position: 'prefix' },
	{ symbol: '£', code: 'GBP', position: 'prefix' },
	{ symbol: '¥', code: 'JPY', position: 'prefix' },
	{ symbol: '₹', code: 'INR', position: 'prefix' },
];

type CurrencySelectOption = {
	value: string;
	label: string;
};

const currencyDefaultOption: CurrencySelectOption = {
	label: _x( 'Default site currency', 'Default option for the currency selector', 'the-events-calendar' ),
	value: 'default',
};

/**
 * Renders a currency in the format of "symbol code" or "code symbol" based on the currency position.
 *
 * @since TBD
 *
 * @param {Currency} currency The Currency object containing the code, symbol, and position.
 * @returns {string} The formatted currency string.
 */
const renderCurrency = ( currency: Currency ): string => {
	return currency.position === 'prefix'
		? `${ currency.symbol }${ currency.code }`
		: `${ currency.code }${ currency.symbol }`;
};

const buildOptionFromCurrency = ( currency: Currency ): CurrencySelectOption => {
	return {
		label: renderCurrency( currency ),
		value: currency.code,
	};
};

const mapCurrenciesToOptions = ( currencies: Currency[] ): CurrencySelectOption[] => {
	return currencies.map( buildOptionFromCurrency );
};

export default function CurrencySelector( props: CurrencySelectorProps ) {
	const { meta, defaultCurrency } = useSelect( ( select ) => {
		const { getDefaultCurrency }: { getDefaultCurrency: () => Currency } = select( 'tec/classy/events' );
		const { getEditedPostAttribute }: { getEditedPostAttribute: ( attribute: string ) => any } = select( 'core/editor' );
		return {
			meta: getEditedPostAttribute( 'meta' ) || {},
			defaultCurrency: getDefaultCurrency(),
		};
	}, [] );

	const { editPost } = useDispatch( 'core/editor' );

	const eventCurrencyCodeMeta: string = meta[ METADATA_EVENT_CURRENCY ] || defaultCurrency.code;
	const [ eventCurrencyCode, seteventCurrencyCode ] = useState< string >( eventCurrencyCodeMeta );

	const eventCurrencySymbolMeta: string = meta[ METADATA_EVENT_CURRENCY_SYMBOL ] || defaultCurrency.symbol;
	const [ currencySymbol, setCurrencySymbol ] = useState< string >( eventCurrencySymbolMeta );

	const eventCurrencyPosition: CurrencyPosition =
		meta[ METADATA_EVENT_CURRENCY_POSITION ] ||
		Currencies.find( ( currency ) => currency.code === eventCurrencyCode )?.position ||
		defaultCurrency.position;
	const [ currencyPosition, setCurrencyPosition ] = useState< CurrencyPosition >( eventCurrencyPosition );

	useEffect( () => {
		seteventCurrencyCode( eventCurrencyCodeMeta );
	}, [ eventCurrencyCodeMeta ] );

	useEffect( () => {
		setCurrencyPosition( eventCurrencyPosition );
	}, [ eventCurrencyPosition ] );

	useEffect( () => {
		setCurrencySymbol( eventCurrencySymbolMeta );
	}, [ eventCurrencySymbolMeta ] );

	/**
	 * Sets the event currency to the default currency.
	 *
	 * This function updates the event's currency code, symbol, and position to the default values,
	 * and also updates the post metadata accordingly. The post metadata for the currency code is
	 * set to an empty string, indicating that the default currency should be used for display.
	 *
	 * @since TBD
	 */
	const setToDefaultCurrency = (): void => {
		seteventCurrencyCode( defaultCurrency.code );
		setCurrencySymbol( defaultCurrency.symbol );
		setCurrencyPosition( defaultCurrency.position );
		editPost( {
			meta: {
				[ METADATA_EVENT_CURRENCY ]: '',
				[ METADATA_EVENT_CURRENCY_SYMBOL ]: defaultCurrency.symbol,
				[ METADATA_EVENT_CURRENCY_POSITION ]: defaultCurrency.position,
			},
		} );
	};

	const onCurrencyChange = ( nextValue: string | undefined ): void => {
		const selectedCurrency: Currency = Currencies.find( ( currency ) => currency.code === nextValue );
		if ( ! selectedCurrency || nextValue === 'default' ) {
			setToDefaultCurrency();
			return;
		}

		// Set the selected currency code and symbol. Position is determined separately.
		seteventCurrencyCode( selectedCurrency.code );
		setCurrencySymbol( selectedCurrency.symbol );
		editPost( {
			meta: {
				[ METADATA_EVENT_CURRENCY ]: selectedCurrency.code,
				[ METADATA_EVENT_CURRENCY_SYMBOL ]: selectedCurrency.symbol,
			},
		} );
	};

	useEffect( () => {
		setCurrencySymbol( eventCurrencySymbolMeta );
	}, [ eventCurrencySymbolMeta ] );

	const onCurrencyPositionChange = ( nextValue: boolean ): void => {
		const newPosition: CurrencyPosition = nextValue ? 'prefix' : 'postfix';
		setCurrencyPosition( newPosition );
		editPost( { meta: { [ METADATA_EVENT_CURRENCY_POSITION ]: newPosition } } );
	};

	const [ isSelectingCurrency, setIsSelectingCurrency ] = useState< boolean >( false );

	const onCurrencyClick = (): void => {
		setIsSelectingCurrency( ! isSelectingCurrency );
	};

	const currencyOptions = [ currencyDefaultOption, ...mapCurrenciesToOptions( Currencies ) ];

	const onClose = (): void => {
		setIsSelectingCurrency( false );
	};

	return (
		<div className="classy-field classy-field--currency-selector">
			<Button className="is-link--dark" variant="link" onClick={ onCurrencyClick }>
				{ renderCurrency( {
					code: eventCurrencyCode,
					symbol: currencySymbol,
					position: currencyPosition,
				} ) }
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
							__nextHasNoMarginBottom
							__next40pxDefaultSize
						/>

						<ToggleControl
							label={ _x(
								'Currency symbol precedes price',
								'Event currency position toggle label',
								'the-events-calendar'
							) }
							checked={ currencyPosition === 'prefix' }
							onChange={ onCurrencyPositionChange }
							__nextHasNoMarginBottom
						/>
					</div>
				</Popover>
			) }
		</div>
	);
}
