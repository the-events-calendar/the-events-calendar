/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose } from 'redux';

/**
 * Internal dependencies
 */
import {
	selectors as dateTimeSelectors,
} from '@moderntribe/events/data/blocks/datetime';
import {
	selectors as priceSelectors,
	actions as priceActions,
} from '@moderntribe/events/data/blocks/price';
import { withStore } from '@moderntribe/common/hoc';
import EventDateTimeContent from './template';

/**
 * Module Code
 */

const mapStateToProps = ( state ) => ( {
	allDay: dateTimeSelectors.getAllDay( state ),
	cost: priceSelectors.getPrice( state ),
	currencyPosition: priceSelectors.getPosition( state ),
	currencySymbol: priceSelectors.getSymbol( state ),
	currencyCode: priceSelectors.getCode( state ),
	end: dateTimeSelectors.getEnd( state ),
	isEditable: dateTimeSelectors.isEditable( state ),
	multiDay: dateTimeSelectors.getMultiDay( state ),
	sameStartEnd: dateTimeSelectors.getSameStartEnd( state ),
	separatorDate: dateTimeSelectors.getDateSeparator( state ),
	separatorTime: dateTimeSelectors.getTimeSeparator( state ),
	start: dateTimeSelectors.getStart( state ),
} );

const mapDispatchToProps = ( dispatch, ownProps ) => ( {
	setCost: ( value ) => {
		ownProps.setAttributes( { cost: value } );
		dispatch( priceActions.setCost( value ) );
	},
} );

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps ),
)( EventDateTimeContent );
