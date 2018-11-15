/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose, bindActionCreators } from 'redux';

/**
 * Internal dependencies
 */
import {
	actions as dateTimeActions,
	selectors as dateTimeSelectors,
} from '@moderntribe/events/data/blocks/datetime';
import { withStore, withSaveData, withBlockCloser } from '@moderntribe/common/hoc';
import EventDateTimeControls from './template';

/**
 * Module Code
 */

const onTimeZoneVisibilityChange = ( dispatch ) => ( checked ) => (
	dispatch( dateTimeActions.setTimeZoneVisibility( checked ) )
);

const mapStateToProps = ( state ) => ( {
	isEditable: dateTimeSelectors.isEditable( state ),
	separatorDate: dateTimeSelectors.getDateSeparator( state ),
	separatorTime: dateTimeSelectors.getTimeSeparator( state ),
	showTimeZone: dateTimeSelectors.getTimeZoneVisibility( state ),
	timeZone: dateTimeSelectors.getTimeZone( state ),
} );

const mapDispatchToProps = ( dispatch ) => ( {
	...bindActionCreators( dateTimeActions, dispatch ),
	onTimeZoneVisibilityChange: onTimeZoneVisibilityChange( dispatch ),
} );

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps ),
	withSaveData(),
	withBlockCloser,
)( EventDateTimeControls );
