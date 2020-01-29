/**
 * Wordpress dependenciess
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { settings, priceSettings } from '@moderntribe/common/utils/globals';
import { string, globals } from '@moderntribe/common/utils';
import * as types from './types';

const position = string.isTruthy( settings() && settings().reverseCurrencyPosition )
	? 'suffix'
	: 'prefix';

export const getDefaultState = () => {
	if ( globals.wpCoreEditor.isCleanNewPost() ) {
		return;
	}

	const postId = globals.wpCoreEditor.getCurrentPostId();
	const entityRecord = globals.wpCore.getEntityRecord( 'postType', 'tribe_events', postId );

	DEFAULT_STATE = {
		position: entityRecord.meta._EventCurrencyPosition,
		symbol: entityRecord.meta._EventCurrencySymbol,
		cost: entityRecord.meta._EventCost,
		description: '',
	};
};

export let DEFAULT_STATE = {
	position: priceSettings() && priceSettings().defaultCurrencyPosition
		? priceSettings().defaultCurrencyPosition
		: position,
	symbol: priceSettings() && priceSettings().defaultCurrencySymbol
		? priceSettings().defaultCurrencySymbol
		: __( '$', 'the-events-calendar' ),
	cost: '',
	description: '',
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
		case types.SET_PRICE_DESCRIPTION:
			return {
				...state,
				description: action.payload.description,
			};
		default:
			return state;
	}
};
