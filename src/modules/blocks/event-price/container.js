/**
 * External dependencies
 */
import { trim, isEmpty } from 'lodash';
import { connect } from 'react-redux';
import { bindActionCreators, compose } from 'redux';

/**
 * Internal dependencies
 */
import { dom, range } from '@moderntribe/common/utils';
import { withStore, withSaveData } from '@moderntribe/common/hoc';
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

const ESCAPE_KEY = 27;

const showCurrencySymbol = ( cost ) => {
	const parsed = range.parser( cost );
	return ! isEmpty( trim( parsed ) ) && ! range.isFree( cost );
};

const showCost = ( cost ) => {
	const parsed = range.parser( cost );
	return ! isEmpty( trim( parsed ) ) || range.isFree( cost );
};

const showCostDescription = ( description ) => ! isEmpty( trim( description ) );

const isTargetInBlock = ( target ) => (
	dom.searchParent( target, ( testNode ) => {
		if ( testNode.classList.contains( 'editor-block-list__block' ) ) {
			return Boolean( testNode.querySelector( '.tribe-editor__event-price' ) );
		}
		return false;
	} )
);

const isTargetInSidebar = ( target ) => (
	dom.searchParent( target, ( testNode ) => (
		testNode.classList.contains( 'edit-post-sidebar' )
	) )
);

const onKeyDown = ( e, dispatch ) => {
	if ( e.keyCode === ESCAPE_KEY ) {
		dispatch( UIActions.closeDashboardPrice() );
	}
};

const onClick = ( e, dispatch ) => {
	const { target } = e;
	if (
		! isTargetInBlock( target )
		&& ! isTargetInSidebar( target )
	) {
		dispatch( UIActions.closeDashboardPrice() );
	}
};

const mapStateToProps = ( state ) => ( {
	isDashboardOpen: UISelectors.getDashboardPriceOpen( state ),
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
	onKeyDown: ( e ) => onKeyDown( e, dispatch ),
	onClick: ( e ) => onClick( e, dispatch ),
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
)( EventPrice );
