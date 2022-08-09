/**
 * External dependencies
 */
import { trim, isEmpty } from 'lodash';
import { connect } from 'react-redux';
import { compose } from 'redux';

/**
 * Internal dependencies
 */
import { range } from '@moderntribe/common/utils';
import { withStore, withBlockCloser } from '@moderntribe/common/hoc';
import {
	actions as priceActions,
	selectors as priceSelectors,
	utils as priceUtils,
} from '@moderntribe/events/data/blocks/price';
import EventPrice from './template';

/**
 * Module Code
 */

const showCurrencySymbol = ( cost ) => {
	const parsed = range.parser( cost );
	return ! isEmpty( trim( parsed ) ) && ! range.isFree( cost );
};

const showCost = ( cost ) => {
	const parsed = range.parser( cost );
	return ! isEmpty( trim( parsed ) ) || range.isFree( cost );
};

const mapStateToProps = ( state, ownProps ) => ( {
	cost: priceSelectors.getPrice( state ),
	currencyPosition: priceSelectors.getPosition( state ),
	currencySymbol: priceSelectors.getSymbol( state ),
	currencyCode: priceSelectors.getCode( state ),
	showCurrencySymbol: showCurrencySymbol( priceSelectors.getPrice( state ) ),
	showCost: showCost( priceSelectors.getPrice( state ) ),
	showCostDescription: ! isEmpty( trim( ownProps.attributes.costDescription ) ),
	isFree: range.isFree( priceSelectors.getPrice( state ) ),
} );

const mapDispatchToProps = ( dispatch, ownProps ) => ( {
	setCost: ( event ) => {
		ownProps.setAttributes( { cost: event.target.value } );
		dispatch( priceActions.setCost( event.target.value ) );
	},
	setSymbol: ( symbol ) => {
		ownProps.setAttributes( { currencySymbol: symbol } );
		dispatch( priceActions.setSymbol( symbol ) );
	},
	setCode: ( code ) => {
		ownProps.setAttributes( { currencyCode: code } );
		dispatch( priceActions.setCode( code ) );
	},
	setCurrencyPosition: ( value ) => {
		const position = priceUtils.getPosition( ! value );
		ownProps.setAttributes( { currencyPosition: position } );
		dispatch( priceActions.setPosition( position ) );
	},
} );

export default compose(
	withStore(),
	connect(
		mapStateToProps,
		mapDispatchToProps,
	),
	withBlockCloser,
)( EventPrice );
