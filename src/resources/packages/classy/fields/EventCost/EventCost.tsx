import * as React from 'react';
import { Fragment, useEffect, useState } from 'react';
import { __, _x } from '@wordpress/i18n';
import { __experimentalInputControl as InputControl, ToggleControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { CurrencySelector } from '@tec/common/classy/components';
import {
	METADATA_EVENT_COST,
	METADATA_EVENT_CURRENCY,
	METADATA_EVENT_CURRENCY_POSITION,
	METADATA_EVENT_CURRENCY_SYMBOL,
	METADATA_EVENT_IS_FREE
} from '../../constants';
import { Currency } from '@tec/common/classy/types/Currency';
import { CurrencyPosition } from '@tec/common/classy/types/CurrencyPosition';

export default function EventCost(): JSX.Element {
	const { meta, defaultCurrency } = useSelect( ( select ) => {
		const { getEditedPostAttribute }: {
			getEditedPostAttribute: ( attribute: string ) => any
		} = select( 'core/editor' );
		const { getDefaultCurrency }: {
			getDefaultCurrency: () => Currency
		} = select( 'tec/classy' );
		return {
			meta: getEditedPostAttribute( 'meta' ) || {},
			defaultCurrency: getDefaultCurrency(),
		};
	}, [] );

	const freeText = __( 'Free', 'the-events-calendar' );

	const { editPost } = useDispatch( 'core/editor' );

	const isFreeMeta: boolean = meta[ METADATA_EVENT_IS_FREE ] || false;
	const [ isFree, setIsFree ] = useState< boolean >( isFreeMeta );

	const eventCostMeta: string = meta[ METADATA_EVENT_COST ] || '';
	const [ eventCostValue, setEventCostValue ] = useState< string >( isFree ? freeText : eventCostMeta );

	const eventCurrencySymbolMeta: string = meta[ METADATA_EVENT_CURRENCY_SYMBOL ] || defaultCurrency.symbol;
	const [ currencySymbol, setCurrencySymbol ] = useState< string >( eventCurrencySymbolMeta );

	const eventCurrencyPosition: CurrencyPosition =
		meta[ METADATA_EVENT_CURRENCY_POSITION ] ||
		defaultCurrency.position;
	const [ currencyPosition, setCurrencyPosition ] = useState< CurrencyPosition >( eventCurrencyPosition );

	const [ costHasFocus, setCostHasFocus ] = useState< boolean >( false );

	// Track changes to the post content and update the state accordingly.
	useEffect( () => {
		setEventCostValue( eventCostMeta );
	}, [ eventCostMeta ] );

	useEffect( () => {
		setIsFree( isFreeMeta );
	}, [ isFreeMeta ] );

	useEffect( () => {
		setCurrencySymbol( eventCurrencySymbolMeta );
	}, [ eventCurrencySymbolMeta ] );

	useEffect( () => {
		setCurrencyPosition( eventCurrencyPosition );
	}, [ eventCurrencyPosition ] );

	// Handle changes to the event cost input.
	const onCostChange = ( nextValue: string | undefined ): void => {
		setEventCostValue( nextValue ?? '' );
		editPost( { meta: { [ METADATA_EVENT_COST ]: nextValue } } );
	};

	// Handle changes to the "is free" toggle.
	const onFreeChange = ( nextValue: boolean ): void => {
		setIsFree( nextValue );
		setEventCostValue( nextValue ? freeText : eventCostMeta );

		editPost( { meta: { [ METADATA_EVENT_IS_FREE ]: nextValue } } );
	};

	/**
	 * Formats a currency value based on the current currency position.
	 *
	 * This function takes a numeric value as a string and formats it with the currency symbol,
	 * according to the specified currency position (prefix or postfix).
	 *
	 * @since TBD
	 *
	 * @param {string} value
	 * @return {string} The formatted currency value.
	 */
	const formatCurrencyValue = ( value: string ): string => {
		return currencyPosition === 'prefix'
			? `${ currencySymbol }${ value }`
			: `${ value }${ currencySymbol }`;
	}

	/**
	 * Formats the event cost value for display.
	 *
	 * This function formats the event cost value based on whether the input has focus or if the event is free.
	 * It handles multiple prices separated by commas and returns a formatted string.
	 *
	 * @since TBD
	 *
	 * @param {string} value The raw event cost value as a string.
	 * @return {string} The formatted event cost value.
	 */
	const formatEventCostValue = ( value: string ): string => {
		// If the cost input has focus or the event is free, return the value as is.
		if ( costHasFocus || isFree ) {
			return value;
		}

		const pieces = value
			.split( ',' )
			.map( ( piece ) => piece.trim() )
			.filter( ( piece ) => piece !== '' );

		if ( pieces.length === 0 ) {
			return value;
		}

		// Convert pieces to numbers and find min/max
		const numbers = pieces.map( piece => {
			const num = parseFloat( piece );
			return isNaN( num ) ? 0 : Number( num.toFixed( 2 ) );
		} );

		const min = Math.min( ...numbers );
		const max = Math.max( ...numbers );
		const formattedMin = formatCurrencyValue( min.toFixed( 2 ) );
		const formattedMax = formatCurrencyValue( max.toFixed( 2 ) );

		// If min and max are the same, return just one formatted number
		if ( min === max ) {
			return formatCurrencyValue( min.toFixed( 2 ) );
		}

		// Otherwise return the range
		return `${ formattedMin } - ${ formattedMax }`;
	};

	return (
		<Fragment>
			<div className="classy-field__group classy-field__event-cost">
				<div className="classy-field__input classy-field__input-full-width">
					<div className="classy-field__control classy-field__control--input">
						<InputControl
							label={
								<span className="classy-field__input-title">
									{ _x( 'Event cost', 'Event cost input title', 'the-events-calendar' ) }
								</span>
							}
							value={ formatEventCostValue( eventCostValue ) }
							onChange={ onCostChange }
							disabled={ isFree }
							onFocus={ () => setCostHasFocus( true ) }
							onBlur={ () => setCostHasFocus( false ) }
						/>
					</div>
				</div>

				<div className="classy-field__input classy-field__input--height-100">
					<CurrencySelector
						currencyCodeMeta={ METADATA_EVENT_CURRENCY }
						currencyPositionMeta={ METADATA_EVENT_CURRENCY_POSITION }
						currencySymbolMeta={ METADATA_EVENT_CURRENCY_SYMBOL }
					/>
				</div>

				<div className="classy-field__input classy-field__input--height-75">
					<ToggleControl
						label={ _x( 'Event is free', 'Event cost toggle label', 'the-events-calendar' ) }
						checked={ isFree }
						onChange={ onFreeChange }
						__nextHasNoMarginBottom
					/>
				</div>
			</div>

			<div className="classy-field__input-note">
				{ _x(
					'If multiple entry prices are available, list each price separated by commas.',
					'Event cost input note',
					'the-events-calendar'
				) }
			</div>
		</Fragment>
	);
}
