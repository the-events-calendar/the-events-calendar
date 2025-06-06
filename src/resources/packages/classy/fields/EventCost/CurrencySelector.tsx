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
	key: string;
	value: string;
	label: string;
};

const currencyDefaultOption: CurrencySelectOption = {
	key: '0',
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
		key: currency.code,
		label: renderCurrency( currency ),
		value: currency.code,
	};
};

const mapCurrenciesToOptions = ( currencies: Currency[] ): CurrencySelectOption[] => {
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
		Currencies.find( ( currency ) => currency.code === eventCurrency )?.position ||
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
		const selectedCurrency: Currency = Currencies.find( ( currency ) => currency.code === nextValue );
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

		setEventCurrency( selectedCurrency.code );
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
		const newPosition = nextValue ? 'prefix' : 'postfix';
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
