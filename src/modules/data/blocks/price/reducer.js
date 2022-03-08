/**
 * Wordpress dependenciess
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { settings, priceSettings } from '@moderntribe/common/utils/globals';
import { string } from '@moderntribe/common/utils';
import * as types from './types';

const position = string.isTruthy( settings() && settings().reverseCurrencyPosition )
	? 'suffix'
	: 'prefix';

export const DEFAULT_STATE = {
	position: priceSettings() && priceSettings().defaultCurrencyPosition
		? priceSettings().defaultCurrencyPosition
		: position,
	symbol: priceSettings() && priceSettings().defaultCurrencySymbol
		? priceSettings().defaultCurrencySymbol
		: __( '$', 'the-events-calendar' ),
	code: priceSettings() && priceSettings().defaultCurrencyCode
		? priceSettings().defaultCurrencyCode
		: __( 'USD', 'the-events-calendar' ),
	cost: '',
};

export const defaultStateToMetaMap = {
	position: '_EventCurrencyPosition',
	symbol: '_EventCurrencySymbol',
	code: '_EventCurrencyCode',
	cost: '_EventCost',
};

export const setInitialState = ( data ) => {
	const { meta } = data;

	Object.keys( defaultStateToMetaMap ).forEach( ( key ) => {
		const metaKey = defaultStateToMetaMap[ key ];
		if ( meta.hasOwnProperty( metaKey ) ) {
			DEFAULT_STATE[ key ] = meta[ metaKey ];
		}
	} );
};

export default ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.SET_PRICE_COST:
			return {
				...state,
				cost: action.payload.cost,
			};
		case types.SET_PRICE_POSITION:
			return {
				...state,
				position: action.payload.position,
			};
		case types.SET_PRICE_SYMBOL:
			return {
				...state,
				symbol: action.payload.symbol,
			};
		case types.SET_PRICE_CODE:
			return {
				...state,
				symbol: action.payload.code,
			};
		default:
			return state;
	}
};
