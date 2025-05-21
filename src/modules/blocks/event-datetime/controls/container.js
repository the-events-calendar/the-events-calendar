/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose } from 'redux';

/**
 * Internal dependencies
 */
import { actions as dateTimeActions, selectors as dateTimeSelectors } from '@moderntribe/events/data/blocks/datetime';
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

const mapDispatchToProps = ( dispatch, { setAttributes } ) => ( {
	setSeparatorDate: ( value ) => {
		setAttributes( { separatorDate: value } );
		dispatch( dateTimeActions.setSeparatorDate( value ) );
	},
	setSeparatorTime: ( value ) => {
		setAttributes( { separatorTime: value } );
		dispatch( dateTimeActions.setSeparatorTime( value ) );
	},
	setTimeZone: ( value ) => {
		setAttributes( { timeZone: value } );
		dispatch( dateTimeActions.setTimeZone( value ) );
	},
} );

export default compose( withStore(), connect( mapStateToProps, mapDispatchToProps ) )( EventDateTimeControls );
