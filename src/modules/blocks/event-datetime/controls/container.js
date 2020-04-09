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
	selectors as dateTimeSelectors,
} from '@moderntribe/events/data/blocks/datetime';
import { withStore } from '@moderntribe/common/hoc';
import EventDateTimeControls from './template';

/**
 * Module Code
 */

const mapStateToProps = ( state ) => ( {
	isEditable: dateTimeSelectors.isEditable( state ),
	separatorDate: dateTimeSelectors.getDateSeparator( state ),
	separatorTime: dateTimeSelectors.getTimeSeparator( state ),
	timeZone: dateTimeSelectors.getTimeZone( state ),
} );

const mapDispatchToProps = ( dispatch, { setDateTimeAttributes } ) => ( {
	setSeparatorDate: ( value ) => {
		setDateTimeAttributes( { separatorDate: value } );
		dispatch( dateTimeActions.setSeparatorDate( value ) );
	},
	setSeparatorTime: ( value ) => {
		setDateTimeAttributes( { separatorTime: value } );
		dispatch( dateTimeActions.setSeparatorTime( value ) );
	},
	setTimeZone: ( value ) => {
		setDateTimeAttributes( { timeZone: value } );
		dispatch( dateTimeActions.setTimeZone( value ) );
	},
} );

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps ),
)( EventDateTimeControls );
