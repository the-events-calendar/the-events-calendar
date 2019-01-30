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

const showCostDescription = ( description ) => ! isEmpty( trim( description ) );

const mapStateToProps = ( state ) => ( {
	isDashboardOpen: UISelectors.getDashboardPriceOpen( state ),
	isOpen: UISelectors.getDashboardPriceOpen( state ),
	cost: priceSelectors.getPrice( state ),
	currencyPosition: priceSelectors.getPosition( state ),
	currencySymbol: priceSelectors.getSymbol( state ),
	costDescription: priceSelectors.getDescription( state ),
	showCurrencySymbol: showCurrencySymbol( priceSelectors.getPrice( state ) ),
	showCost: showCost( priceSelectors.getPrice( state ) ),
	showCostDescription: showCostDescription( priceSelectors.getDescription( state ) ),
	isFree: range.isFree( priceSelectors.getPrice( state ) ),
} );

const mapDispatchToProps = ( dispatch ) => ( {
	...bindActionCreators( priceActions, dispatch ),
	setInitialState: ( props ) => {
		dispatch( priceActions.setInitialState( props ) );
		dispatch( UIActions.setInitialState( props ) );
	},
	onClose: () => dispatch( UIActions.closeDashboardPrice() ),
	openDashboard: () => dispatch( UIActions.openDashboardPrice() ),
	setCurrencyPosition: ( value ) => dispatch( priceActions.togglePosition( ! value ) ),
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
