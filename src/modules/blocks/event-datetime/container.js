/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose } from 'redux';

/**
 * Internal dependencies
 */
import {
	actions as dateTimeActions,
	thunks as dateTimeThunks,
	selectors as dateTimeSelectors,
} from '@moderntribe/events/data/blocks/datetime';
import {
	actions as UIActions,
	selectors as UISelectors,
} from '@moderntribe/events/data/ui';
import {
	selectors as priceSelectors,
	actions as priceActions,
} from '@moderntribe/events/data/blocks/price';
import { withStore, withSaveData, withBlockCloser } from '@moderntribe/common/hoc';
import EventDateTime from './template';

/**
 * Module Code
 */

const mapStateToProps = ( state ) => ( {
	isOpen: UISelectors.getDashboardDateTimeOpen( state ),
	start: dateTimeSelectors.getStart( state ),
	end: dateTimeSelectors.getEnd( state ),
	allDay: dateTimeSelectors.getAllDay( state ),
	separatorDate: dateTimeSelectors.getDateSeparator( state ),
	separatorTime: dateTimeSelectors.getTimeSeparator( state ),
	showTimeZone: dateTimeSelectors.getTimeZoneVisibility( state ),
	timeZone: dateTimeSelectors.getTimeZone( state ),
	timeZoneLabel: dateTimeSelectors.getTimeZoneLabel( state ),
	cost: priceSelectors.getPrice( state ),
	currencySymbol: priceSelectors.getSymbol( state ),
	currencyPosition: priceSelectors.getPosition( state ),
} );

const mapDispatchToProps = ( dispatch ) => ( {
	setInitialState: ( props ) => {
		dispatch( priceActions.setInitialState( props ) );
		dispatch( dateTimeThunks.setInitialState( props ) );
		dispatch( UIActions.setInitialState( props ) );
	},
	onClose: () => {
		dispatch( dateTimeActions.setDateInputVisibility( false ) );
		dispatch( UIActions.closeDashboardDateTime() );
	},
} );

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps ),
	withSaveData(),
	withBlockCloser,
)( EventDateTime );
