/**
 * External dependencies
 */
import { trim, isEmpty } from 'lodash';
import { connect } from 'react-redux';
import { bindActionCreators, compose } from 'redux';

/**
 * Internal dependencies
 */
import { range } from '@moderntribe/common/utils';
import { withStore, withSaveData, withBlockCloser } from '@moderntribe/common/hoc';
import {
	actions as priceActions,
	selectors as priceSelectors,
} from '@moderntribe/events/data/blocks/price';
import {
	actions as UIActions,
	selectors as UISelectors,
} from '@moderntribe/events/data/ui';
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
	isDashboardOpen: UISelectors.getDashboardPriceOpen( state ),
	isOpen: UISelectors.getDashboardPriceOpen( state ),
	cost: priceSelectors.getPrice( state ),
	currencyPosition: priceSelectors.getPosition( state ),
	currencySymbol: priceSelectors.getSymbol( state ),
	showCurrencySymbol: showCurrencySymbol( priceSelectors.getPrice( state ) ),
	showCost: showCost( priceSelectors.getPrice( state ) ),
	showCostDescription: ! isEmpty( trim( ownProps.attributes.costDescription ) ),
	isFree: range.isFree( priceSelectors.getPrice( state ) ),
} );

const mapDispatchToProps = ( dispatch, ownProps ) => ( {
	setCost: ( event ) => {
		ownProps.setAttributes( { cost: event.target.value } );
		dispatch( actions.setCost( event.target.value ) );
	},
	setSymbol: symbol  => dispatch( actions.setSymbol( symbol ) ),
	setCurrencyPosition: value => dispatch( priceActions.togglePosition( ! value ) ),
	onClose: () => dispatch( UIActions.closeDashboardPrice() ),
	openDashboard: () => dispatch( UIActions.openDashboardPrice() ),
} );

export default compose(
	withStore(),
	connect(
		mapStateToProps,
		mapDispatchToProps
	),
	withSaveData(),
	withBlockCloser,
)( EventPrice );
